<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\DocumentStatus;
use App\Models\Institution;
use App\Models\Profile;
use App\Notifications\System;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollTimeSheetExport;
use Modules\Payroll\Http\Resources\TimeSheetPendingResource;
use Modules\Payroll\Imports\PayrollTimeSheetImport;
use Modules\Payroll\Models\PayrollSupervisedGroup;
use Modules\Payroll\Models\PayrollTimeSheetPending;
use Modules\Payroll\Rules\PayrollTimeSheetDataRequired;
use Modules\Payroll\Rules\PayrollTimeSheetPendingConceptsRequired;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class PayrollTimeSheetPendingController
 * @brief Controlador de la gestión de las hojas de tiempo pendientes
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetPendingController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.timesheetpending.index', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.timesheetpending.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.timesheetpending.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.timesheetpending.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.timesheetpending.approve', ['only' => 'approve']);
        $this->middleware('permission:payroll.timesheetpending.reject', ['only' => 'reject']);
        $this->middleware('permission:payroll.timesheetpending.confirm', ['only' => 'confirm']);


        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_supervised_group_id' => ['required'],
            'payroll_time_sheet_parameter_id' => ['required'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'time_sheet_data' => [new PayrollTimeSheetDataRequired(), new PayrollTimeSheetPendingConceptsRequired()],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'from_date.required' => 'El campo desde es obligatorio',
            'to_date.required' => 'El campo hasta es obligatorio',
            'to_date.after_or_equal' => 'El campo hasta debe ser mayor o igual que el campo desde',
            'payroll_supervised_group_id.required' => 'El campo código es obligatorio',
            'payroll_time_sheet_parameter_id.required' => 'El campo parámetros de hoja de tiempo es obligatorio',
        ];
    }

    /**
     * Muestra la lista de hojas de tiempo pendientes
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::time_sheet_pendings.index');
    }

    /**
     * Muestra el formulario para crear una nueva hoja de tiempo pendiente
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::time_sheet_pendings.create-edit');
    }

    /**
     * Almacena la hoja de tiempo pendiente
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $payrollLastTimeSheet = PayrollTimeSheetPending::query()
            ->where([
                'institution_id' => $institution->id,
                'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
                'payroll_time_sheet_parameter_id' => $request->payroll_time_sheet_parameter_id
            ])
            ->orderBy('to_date')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        if (isset($payrollLastTimeSheet)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'from_date' => ['required', 'date', 'after:' . $payrollLastTimeSheet?->to_date],
                ]
            );

            $to_date_obj = date_create_from_format('Y-m-d', $payrollLastTimeSheet->to_date);
            $to_date_formatted = date_format($to_date_obj, 'd-m-Y');

            $messages = array_merge(
                $messages,
                [
                    'from_date.after' =>
                        'Ya existe un registro para este grupo de supervisados con el periodo indicado.
                            El campo Desde debe ser mayor al ' . $to_date_formatted . '.',
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        DB::transaction(function () use ($request) {
            $status = DocumentStatus::where('action', 'EL')->first();

            $profileUser = auth()->user()->profile;
            if ($profileUser && $profileUser->institution_id !== null) {
                $institution = Institution::find($profileUser->institution_id);
            } else {
                $institution = Institution::where('active', true)->where('default', true)->first();
            }

            $payrollTimeSheetPending = PayrollTimeSheetPending::create([
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
                'payroll_time_sheet_parameter_id' => $request->payroll_time_sheet_parameter_id,
                'document_status_id' => $status->id,
                'time_sheet_data' => $request->time_sheet_data,
                'time_sheet_columns' => $request->time_sheet_columns,
                'institution_id' => $institution->id
            ]);

            $supervisedGroup = PayrollSupervisedGroup::find($request->payroll_supervised_group_id);

            if ($supervisedGroup) {
                $profile = Profile::query()
                    ->with('user')
                    ->has('user')
                    ->where('employee_id', $supervisedGroup->approver_id)
                    ->first();

                if ($profile) {
                    $profile->user->notify(
                        new SystemNotification(
                            'Exito',
                            'Se ha realizado un nuevo registro de hoja de tiempo para su aprobación'
                        )
                    );

                    $profile->user->notify(
                        new System(
                            'Registro de hoja de tiempo',
                            'Talento Humano',
                            'Se ha registrado una nueva hoja de tiempo para su aprobación',
                            true
                        )
                    );
                }
            }
        });

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['redirect' => route('payroll.time-sheet-pending.index')], 200);
    }

    /**
     * Muestra información de la hoja de tiempo pendiente
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar la hoja de tiempo pendiente
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $payrollTimeSheetPending = PayrollTimeSheetPending::find($id);
        return view('payroll::time_sheet_pendings.create-edit', compact('payrollTimeSheetPending'));
    }

    /**
     * Actualiza la hoja de tiempo pendiente
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollTimeSheetPending = PayrollTimeSheetPending::find($id);

        $this->validateRules['from_date'] = [
            'required',
            Rule::unique('payroll_time_sheet_pendings', 'from_date')
                ->where('to_date', $request->to_date)
                ->where('payroll_supervised_group_id', $request->payroll_supervised_group_id)
                ->where('payroll_time_sheet_parameter_id', $request->payroll_time_sheet_parameter_id)
                ->ignore($payrollTimeSheetPending->id)
        ];

        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollTimeSheetPending) {
            $payrollTimeSheetPending->from_date = $request->from_date;
            $payrollTimeSheetPending->to_date = $request->to_date;
            $payrollTimeSheetPending->payroll_supervised_group_id = $request->payroll_supervised_group_id;
            $payrollTimeSheetPending->payroll_time_sheet_parameter_id = $request->payroll_time_sheet_parameter_id;
            $payrollTimeSheetPending->time_sheet_data = $request->time_sheet_data;
            $payrollTimeSheetPending->time_sheet_columns = $request->time_sheet_columns;
            $payrollTimeSheetPending->save();
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.time-sheet-pending.index')], 200);
    }

    /**
     * Elimina la hoja de tiempo pendiente
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollTimeSheetPending = PayrollTimeSheetPending::find($id);
        $payrollTimeSheetPending->delete();

        return response()->json(['record' => $payrollTimeSheetPending, 'message' => 'Success'], 200);
    }

    /**
     * Aprueba la hoja de tiempo pendiente
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'AP')->first();
        $payrollTimeSheetPending = PayrollTimeSheetPending::find($id);
        $payrollTimeSheetPending->document_status_id = $status->id;
        $payrollTimeSheetPending->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheetPending->payroll_supervised_group_id);

        if ($supervisedGroup) {
            $profile = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->supervisor_id)
                ->first();

            if ($profile) {
                $profile->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha aprobado la hoja de tiempo registrada'
                    )
                );

                $profile->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha aprobado su registro de hoja de tiempo del periodo ' .
                            $payrollTimeSheetPending->from_date . ' - ' . $payrollTimeSheetPending->to_date,
                        true
                    )
                );
            }
        }

        $request->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Hoja de tiempo aprobada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.time-sheet-pending.index')], 200);
    }

    /**
     * Rechaza la hoja de tiempo pendiente
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'RE')->first();
        $payrollTimeSheetPending = PayrollTimeSheetPending::find($id);
        $payrollTimeSheetPending->document_status_id = $status->id;
        $payrollTimeSheetPending->observations .= '<br>' . $request->observation;
        $payrollTimeSheetPending->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheetPending->payroll_supervised_group_id);

        if ($supervisedGroup) {
            $profile = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->supervisor_id)
                ->first();

            if ($profile) {
                $profile->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha rechazado la hoja de tiempo registrada debido a las siguientes observaciones: ' .
                        $request->observation
                    )
                );

                $profile->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha rechazado su registro de hoja de tiempo del periodo ' .
                            $payrollTimeSheetPending->from_date . ' - ' . $payrollTimeSheetPending->to_date .
                            ' debido a las siguientes observaciones: ' . $request->observation,
                        true
                    )
                );
            }
        }

        $request->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Hoja de tiempo rechazada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.time-sheet-pending.index')], 200);
    }

    /**
     * Confirma la aprobación de la hoja de tiempo pendiente
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'CE')->first();
        $payrollTimeSheetPending = PayrollTimeSheetPending::find($id);
        $payrollTimeSheetPending->document_status_id = $status->id;
        $payrollTimeSheetPending->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheetPending->payroll_supervised_group_id);

        if ($supervisedGroup) {
            $supervisor = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->supervisor_id)
                ->first();

            $approver = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->approver_id)
                ->first();

            if ($supervisor) {
                $supervisor->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha confirmado el periodo ' . $payrollTimeSheetPending->from_date . ' a ' .
                            $payrollTimeSheetPending->to_date . ' de hoja de tiempo de pendientes'
                    )
                );

                $supervisor->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha confirmado el periodo ' . $payrollTimeSheetPending->from_date . ' a ' .
                            $payrollTimeSheetPending->to_date . ' de hoja de tiempo de pendientes',
                        true
                    )
                );
            }

            if ($approver) {
                $approver->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha confirmado el periodo ' . $payrollTimeSheetPending->from_date . ' a ' .
                            $payrollTimeSheetPending->to_date . ' de hoja de tiempo de pendientes'
                    )
                );

                $approver->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha confirmado el periodo ' . $payrollTimeSheetPending->from_date . ' a ' .
                            $payrollTimeSheetPending->to_date . ' de hoja de tiempo de pendientes',
                        true
                    )
                );
            }
        }

        $request->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Hoja de tiempo confirmada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.time-sheet-pending.index')], 200);
    }

    /**
     * Listado de hojas de tiempo pendientes
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        $user = auth()->user();
        $profileUser = $user->profile;

        if ($user->hasRole('admin, payroll')) {
            return response()->json(['records' => TimeSheetPendingResource::collection(PayrollTimeSheetPending::query()
                ->with([
                    'payrollTimeSheetParameters.payrollParameterTimeSheetParameters.parameter',
                ])
                ->get())
            ], 200);
        } else {
            return response()->json(['records' => TimeSheetPendingResource::collection(PayrollTimeSheetPending::query()
                ->with([
                    'payrollTimeSheetParameters.payrollParameterTimeSheetParameters.parameter',
                ])
                ->whereHas('payrollSupervisedGroup', function ($query) use ($profileUser) {
                    $query
                        ->where('supervisor_id', $profileUser->employee_id)
                        ->orWhere('approver_id', $profileUser->employee_id);
                })
                ->get())
            ], 200);
        }
    }

    /**
     * Información de la hoja de tiempo pendiente
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        return response()->json(['record' => TimeSheetPendingResource::make(PayrollTimeSheetPending::query()
            ->with([
                'payrollTimeSheetParameters.payrollParameterTimeSheetParameters.parameter',
            ])
            ->find($id))
        ], 200);
    }

    /**
     * Importa datos de las hojas de tiempo pendientes
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function import()
    {
        $rows = Excel::toCollection(new PayrollTimeSheetImport(), request()->file('file'));
        $rows = $rows[0];
        return response()->json($rows);
    }

    /**
     * Exporta datos de las hojas de tiempo pendientes
     *
     * @param     Request    $request         Datos de la petición
     *
     * @return    BinaryFileResponse
     */
    public function export(Request $request)
    {
        return Excel::download(new PayrollTimeSheetExport($request->all()), 'payroll-time-sheet-pending.xlsx');
    }
}
