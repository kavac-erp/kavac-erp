<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Http\Controllers;

use App\Models\DocumentStatus;
use App\Models\Institution;
use App\Models\Profile;
use App\Notifications\System;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollTimeSheetExport;
use Modules\Payroll\Http\Resources\TimeSheetResource;
use Modules\Payroll\Imports\PayrollTimeSheetImport;
use Modules\Payroll\Models\PayrollSupervisedGroup;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Rules\PayrollTimeSheetDataRequired;

/**
 * @class PayrollTimeSheetController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetController extends Controller
{
    use ValidatesRequests;

    protected $validateRules;
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.time_sheet.index', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.time_sheet.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.time_sheet.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.time_sheet.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.time_sheet.approve', ['only' => 'approve']);
        $this->middleware('permission:payroll.time_sheet.reject', ['only' => 'reject']);
        $this->middleware('permission:payroll.time_sheet.confirm', ['only' => 'confirm']);

        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_supervised_group_id' => ['required'],
            'payroll_time_sheet_parameter_id' => ['required'],
            'from_date' => [],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'time_sheet_data' => [new PayrollTimeSheetDataRequired()],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'from_date.required' => 'El campo desde es obligatorio',
            'from_date.unique' => 'La hoja de tiempo del periodo seleccionado ya ha sido ' .
                'registrada para este grupo de trabajadores',
            'to_date.required' => 'El campo hasta es obligatorio',
            'to_date.after_or_equal' => 'El campo hasta debe ser mayor o igual que el campo desde',
            'payroll_supervised_group_id.required' => 'El campo código es obligatorio',
            'payroll_time_sheet_parameter_id.required' => 'El campo parámetros de hoja de tiempo es obligatorio',
        ];
    }

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('payroll::time_sheets.index');
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('payroll::time_sheets.create-edit');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validateRules['from_date'] = [
            'required',
            Rule::unique('payroll_time_sheets', 'from_date')
                ->where('to_date', $request->to_date)
                ->where('payroll_supervised_group_id', $request->payroll_supervised_group_id)
                ->where('payroll_time_sheet_parameter_id', $request->payroll_time_sheet_parameter_id)
        ];

        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request) {
            $status = DocumentStatus::where('action', 'EL')->first();

            $profileUser = Auth()->user()->profile;
            if ($profileUser && $profileUser->institution_id !== null) {
                $institution = Institution::find($profileUser->institution_id);
            } else {
                $institution = Institution::where('active', true)->where('default', true)->first();
            }

            $payrollTimeSheet = PayrollTimeSheet::create([
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
        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        return view('payroll::time_sheets.create-edit', compact('payrollTimeSheet'));
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $payrollTimeSheet = PayrollTimeSheet::find($id);

        $this->validateRules['from_date'] = [
            'required',
            Rule::unique('payroll_time_sheets', 'from_date')
                ->where('to_date', $request->to_date)
                ->where('payroll_supervised_group_id', $request->payroll_supervised_group_id)
                ->where('payroll_time_sheet_parameter_id', $request->payroll_time_sheet_parameter_id)
                ->ignore($payrollTimeSheet->id)
        ];

        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollTimeSheet) {
            $payrollTimeSheet->from_date = $request->from_date;
            $payrollTimeSheet->to_date = $request->to_date;
            $payrollTimeSheet->payroll_supervised_group_id = $request->payroll_supervised_group_id;
            $payrollTimeSheet->payroll_time_sheet_parameter_id = $request->payroll_time_sheet_parameter_id;
            $payrollTimeSheet->time_sheet_data = $request->time_sheet_data;
            $payrollTimeSheet->time_sheet_columns = $request->time_sheet_columns;
            $payrollTimeSheet->save();
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->delete();

        return response()->json(['record' => $payrollTimeSheet, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    approve
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function approve(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'AP')->first();
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->document_status_id = $status->id;
        $payrollTimeSheet->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheet->payroll_supervised_group_id);

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
                            $payrollTimeSheet->from_date . ' - ' . $payrollTimeSheet->to_date,
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

        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    reject
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function reject(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'RE')->first();
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->document_status_id = $status->id;
        $payrollTimeSheet->observations .= '<br>' . $request->observation;
        $payrollTimeSheet->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheet->payroll_supervised_group_id);

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
                            $payrollTimeSheet->from_date . ' - ' . $payrollTimeSheet->to_date .
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

        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    confirm
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function confirm(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'CE')->first();
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->document_status_id = $status->id;
        $payrollTimeSheet->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheet->payroll_supervised_group_id);

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
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo'
                    )
                );

                $supervisor->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo',
                        true
                    )
                );
            }

            if ($approver) {
                $approver->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo'
                    )
                );

                $approver->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo',
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

        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    vueList
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function vueList()
    {
        $user = Auth()->user();
        $profileUser = $user->profile;

        if ($user->hasRole('admin, payroll')) {
            return response()->json(['records' => TimeSheetResource::collection(PayrollTimeSheet::query()
                ->with([
                    'payrollTimeSheetParameters.payrollParameterTimeSheetParameters.parameter',
                ])
                ->get())
            ], 200);
        } else {
            return response()->json(['records' => TimeSheetResource::collection(PayrollTimeSheet::query()
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
     * [descripción del método]
     *
     * @method    vueInfo
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function vueInfo($id)
    {
        return response()->json(['record' => TimeSheetResource::make(PayrollTimeSheet::query()
            ->with([
                'payrollTimeSheetParameters.payrollParameterTimeSheetParameters.parameter',
            ])
            ->find($id))
        ], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    vueInfo
     *
     * @author    [nombre del autor] [correo del autor]
     *
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function import()
    {
        $rows = Excel::toCollection(new PayrollTimeSheetImport(), request()->file('file'));
        $rows = $rows[0];
        return response()->json($rows);
    }

    /**
     * [descripción del método]
     *
     * @method    vueInfo
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function export(Request $request)
    {
        return Excel::download(new PayrollTimeSheetExport($request->all()), 'payroll-time-sheet.xlsx');
    }
}
