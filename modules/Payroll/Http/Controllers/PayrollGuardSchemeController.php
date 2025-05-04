<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Controllers;

use App\Models\DocumentStatus;
use App\Models\User;
use App\Notifications\System;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Actions\GetPayrollConfirmedGuardPeriodsAction;
use Modules\Payroll\Http\Resources\GuardSchemaResource;
use Modules\Payroll\Models\PayrollGuardScheme;
use Modules\Payroll\Models\PayrollGuardSchemePeriod;

/**
 * @class PayrollGuardSchemeController
 * @brief Controlador de esquemas de guardias
 *
 * Clase que gestiona los esquemas de guardias
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class PayrollGuardSchemeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param array $validateRules Reglas de validación
     * @param array $messages      Mensajes de validación
     *
     * @return void
     */
    public function __construct(
        protected array $validateRules = [],
        protected array $messages = [],
    ) {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.guard.scheme.index', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.guard.scheme.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.guard.scheme.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.guard.scheme.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.guard.scheme.confirm', ['only' => 'confirm']);
        $this->middleware('permission:payroll.guard.scheme.approve', ['only' => ['approve', 'reject']]);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'institution_id'              => ['required'],
            'from_date'                   => ['required', 'date'],
            'to_date'                     => ['required', 'after:from_date'],
            'payroll_supervised_group_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo organización es requerido',
            'from_date.required' => 'El campo desde es requerido',
            'to_date.required' => 'El campo hasta es requerido',
            'to_date.after' => 'La fecha final debe ser mayor a la fecha inicial',
            'payroll_supervised_group_id.required' => 'El campo código es requerido',
        ];
    }

    /**
     * Muestra todos los registros de esquemas de guardias
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::guard_schemes.index');
    }

    /**
     * Muestra el formulario para registrar un nuevo esquema de guardias
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::guard_schemes.create-edit');
    }

    /**
     * Valida y registra un nuevo esquema de guardias
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse Json con objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $payrollLastGuardScheme = PayrollGuardScheme::query()
            ->where([
                'institution_id' => $request->institution_id,
                'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
            ])
            ->orderBy('to_date')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        if (isset($payrollLastGuardScheme)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'from_date' => ['required', 'date', 'after:' . $payrollLastGuardScheme?->to_date],
                ]
            );
            $messages = array_merge(
                $messages,
                [
                    'from_date.after' => 'La fecha inical debe ser mayor a ' . $payrollLastGuardScheme?->to_date,
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        PayrollGuardScheme::create([
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'institution_id' => $request->institution_id,
            'data_source' => $request->data_source,
            'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['redirect' => route('payroll.guard-schemes.index')], 200);
    }

    /**
     * Muestra el formulario para editar un esquema de guardias
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id Identificador del esquema de guardias
     *
     * @return    \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $guardScheme = PayrollGuardScheme::find($id);
        return view('payroll::guard_schemes.create-edit', compact('guardScheme'));
    }

    /**
     * Actualiza la información de un esquema de guardias
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param integer $guardSchemeId Identificador del esquema de guardias
     *
     * @return \Illuminate\Http\JsonResponse Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, int $guardSchemeId)
    {
        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        $payrollGuardScheme = PayrollGuardScheme::query()
            ->find($guardSchemeId);

        $schemeLastPeriod = $payrollGuardScheme?->payrollGuardSchemePeriods()
            ->whereHas('documentStatus', function ($query) {
                $query
                    ->where('action', 'AP')
                    ->orWhere('action', 'CE');
            })->first();

        if (isset($schemeLastPeriod) && $payrollGuardScheme->payroll_supervised_group_id != $request->payroll_supervised_group_id) {
            $validateRules = array_merge(
                $validateRules,
                [
                    'scheme_last_period' => ['required'],
                ]
            );
            $messages = array_merge(
                $messages,
                [
                    'scheme_last_period.required' => 'Ya existe un periodo aprobado/cerrado para este grupo de supervisados.',
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        $guardScheme = PayrollGuardScheme::find($guardSchemeId);
        $guardScheme->update([
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'institution_id' => $request->institution_id,
            'data_source' => $request->data_source,
            'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
        ]);

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.guard-schemes.index')], 200);
    }

    /**
     * Muestra detalles de un esquema de guardia
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        return response()->json(
            [
                'record' => GuardSchemaResource::make(PayrollGuardScheme::find($id))
            ],
            200
        );
    }

    /**
     * Elimina un registro de tipo de excepción
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $guardSchemeId Id del esquema de gardia
     *
     * @return \Illuminate\Http\JsonResponse Registro de tipo de excepción eliminado
     */
    public function destroy(int $guardSchemeId)
    {
        $guardScheme = PayrollGuardScheme::find($guardSchemeId);
        if ($guardScheme->payrollGuardSchemePeriods->count() > 0) {
            /**@todo Eliminación en período de guardia confirmado */
            return response()->json(
                [
                    'error' => true,
                    'message' => 'El registro no puede ser eliminado ' .
                    'si ya existen períodos aprobados y/o confirmados.'
                ],
                200
            );
        }

        $guardScheme->delete();

        return response()->json(['record' => $guardScheme, 'message' => 'Success'], 200);
    }

    /**
     * Muestra todos los registros de esquemas de guardias
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos de los esquemas de guardias
     */
    public function vueList()
    {
        return response()->json(
            [
                'records' => GuardSchemaResource::collection(PayrollGuardScheme::all())
            ],
            200
        );
    }

    /**
     * Aprobar un esquema de guardia
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $guardSchemePeriodId Id del esquema de guardia
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, int $guardSchemePeriodId)
    {
        $documentStatus = DocumentStatus::where('action', 'AP')->first();
        $guardSchemePeriod = PayrollGuardSchemePeriod::find($guardSchemePeriodId);
        $userId = $guardSchemePeriod?->payrollGuardScheme?->payrollSupervisedGroup?->supervisor?->payrollEmployment?->profile?->user_id;

        $guardSchemePeriod->update([
            'document_status_id' => $documentStatus->id,
        ]);

        $user = User::find($userId);
        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha aprobado el esquema de guardias para el periodo ' . $request->from_date
                        . ' al ' . $request->to_date
                )
            );

            $user->notify(
                new System(
                    'Esquema de guardias',
                    'Talento Humano',
                    'Se ha aprobado el esquema de guardias para el periodo ' . $request->from_date
                        . ' al ' . $request->to_date,
                    true
                )
            );
        }
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.guard-schemes.index')], 200);
    }

    /**
     * Rechaza un esquema de guardia
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $guardSchemePeriodId Id del esquema de guardia
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, int $guardSchemePeriodId)
    {
        $documentStatus = DocumentStatus::where('action', 'RE')->first();
        $guardSchemePeriod = PayrollGuardSchemePeriod::find($guardSchemePeriodId);
        $userId = $guardSchemePeriod?->payrollGuardScheme?->payrollSupervisedGroup?->supervisor?->payrollEmployment?->profile?->user_id;

        $guardSchemePeriod->update([
            'document_status_id' => $documentStatus->id,
            'observations' => $request->observation
        ]);

        $user = User::find($userId);
        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Error',
                    'Se ha rechazado el esquema de guardias para el periodo ' . $request->from_date
                    . ' al ' . $request->to_date
                )
            );

            $user->notify(
                new System(
                    'Esquema de guardias',
                    'Talento Humano',
                    'Se ha rechazado el esquema de guardias para el periodo ' . $request->from_date
                        . ' al ' . $request->to_date,
                    true
                )
            );
        }
        $request->session()->flash('message', ['type' => 'other', 'text' => 'Periodo de esquema de guardias rechazado correctamente, se ha notificado al supervisor correspondiente']);
        return response()->json(['redirect' => route('payroll.guard-schemes.index')], 200);
    }

    /**
     * Valida y registra un nuevo periodo de esquema de guardias
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse Json con objeto guardado y mensaje de confirmación de la operación
     */
    public function addPeriod(Request $request)
    {
        $payrollGuardScheme = PayrollGuardScheme::query()
            ->find($request->payroll_guard_scheme_id);

        $schemeLastPeriod = $payrollGuardScheme?->payrollGuardSchemePeriods()
            ->orderBy('to_date')
            ?->get()
            ?->last();

        $this->validate($request, [
            'from_date' => ['required', 'date', (isset($schemeLastPeriod) ? ('date_equals:' . Carbon::parse($schemeLastPeriod?->to_date)->addDay()->format('Y-m-d')) : ('date_equals:' . $payrollGuardScheme->from_date)),],
            'to_date'   => ['required', 'after:from_date', 'before_or_equal:' . $payrollGuardScheme->to_date,],
        ], [
            'from_date.required' => 'El campo desde es requerido',
            'from_date.after' => 'La fecha inical debe ser mayor a la fecha final del periodo anterior',
            'date_equals' => (isset($schemeLastPeriod) ? ('La fecha inicial del periodo debe ser igual a la fecha ' . Carbon::parse($schemeLastPeriod?->to_date)->addDay()->format('d-m-Y')) : ('La fecha inicial del primer periodo debe ser igual a la fecha ' . Carbon::parse($payrollGuardScheme->from_date)->format('d-m-Y'))),
            'to_date.required' => 'El campo hasta es requerido',
            'to_date.after' => 'La fecha final debe ser mayor a la fecha inicial',
            'to_date.before_or_equal' => 'La fecha final debe ser menor o igual a la fecha final del esquema de guardias',
        ]);

        $documentDefault = DocumentStatus::query()
            ->whereAction('PR')
            ->first();
        $period = PayrollGuardSchemePeriod::create([
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'document_status_id' => $documentDefault->id,
            'payroll_guard_scheme_id' => $request->payroll_guard_scheme_id,
        ]);

        $userId = $period?->payrollGuardScheme?->payrollSupervisedGroup?->approver?->payrollEmployment?->profile?->user_id;

        $user = User::find($userId);
        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha registrado un nuevo periodo del esquema de guardias, pendiente su revisión'
                )
            );

            $user->notify(
                new System(
                    'Esquema de guardias',
                    'Talento Humano',
                    'Se ha registrado un nuevo periodo del esquema de guardias, pendiente su revisión',
                    true
                )
            );
        }

        return response()->json(['record' => [
            'id' => $period->id,
            'from_date' => $period->from_date,
            'to_date' => $period->to_date,
            'document_status_id' => $period->document_status_id,
            'document_status' => $period->documentStatus,
            'observations' => $period->observations ?? ''
        ]], 200);
    }

    /**
     * Actualiza la información de un periodo de esquema de guardias
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param integer $periodId Periodo de esquema de guardias
     *
     * @return \Illuminate\Http\JsonResponse Json con objeto guardado y mensaje de confirmación de la operación
     */
    public function editPeriod(Request $request, int $periodId)
    {
        $period = PayrollGuardSchemePeriod::find($periodId);
        $payrollGuardScheme = $period->payrollGuardScheme;

        $this->validate($request, [
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'after:from_date', 'before_or_equal:' . $payrollGuardScheme->to_date,],
        ], [
            'from_date.required' => 'El campo desde es requerido',
            'from_date.after' => 'La fecha inical debe ser mayor a la fecha final del periodo anterior',
            'to_date.required' => 'El campo hasta es requerido',
            'to_date.after' => 'La fecha final debe ser mayor a la fecha inicial',
            'to_date.before_or_equal' => 'La fecha final debe ser menor o igual a la fecha final del esquema de guardias',
        ]);

        $period->update([
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
        ]);

        $userId = $period?->payrollGuardScheme?->payrollSupervisedGroup?->approver?->payrollEmployment?->profile?->user_id;

        $user = User::find($userId);
        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha actualizado un periodo del esquema de guardias, pendiente su revisión'
                )
            );

            $user->notify(
                new System(
                    'Esquema de guardias',
                    'Talento Humano',
                    'Se ha actualizado un periodo del esquema de guardias, pendiente su revisión',
                    true
                )
            );
        }

        return response()->json(['record' => [
            'id' => $period->id,
            'from_date' => $period->from_date,
            'to_date' => $period->to_date,
            'document_status_id' => $period->document_status_id,
            'document_status' => $period->documentStatus,
            'observations' => $period->observations ?? ''
        ]], 200);
    }

    /**
     * Confirma el período de un esquema de guardias
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $guardSchemePeriodId Id del periodo de guardias
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function confirmPeriod(Request $request, int $guardSchemePeriodId)
    {
        $documentStatus = DocumentStatus::where('action', 'CE')->first();
        $period = PayrollGuardSchemePeriod::find($guardSchemePeriodId);

        $period->update([
            'document_status_id' => $documentStatus->id,
            'observations' => $request->observation
        ]);

        return response()->json(['record' => [
            'id' => $period->id,
            'from_date' => $period->from_date,
            'to_date' => $period->to_date,
            'document_status_id' => $period->document_status_id,
            'document_status' => $period->documentStatus,
            'observations' => $period->observations ?? ''
        ]], 200);
    }

    /**
     * Solicitar revisión de un período de esquema de guardias
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $guardSchemePeriodId Id del periodo de guardias
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function requestReviewPeriod(Request $request, int $guardSchemePeriodId)
    {
        $documentStatus = DocumentStatus::where('action', 'PR')->first();
        $period = PayrollGuardSchemePeriod::find($guardSchemePeriodId);

        $period->update([
            'document_status_id' => $documentStatus->id,
            'observations' => $request->observation
        ]);

        $userId = $period?->payrollGuardScheme?->payrollSupervisedGroup?->approver?->payrollEmployment?->profile?->user_id;

        $user = User::find($userId);
        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha realizado una nueva solicitud de revisión de un período en esquema de guardias'
                )
            );

            $user->notify(
                new System(
                    'Esquema de guardias',
                    'Talento Humano',
                    'Se ha realizado una nueva solicitud de revisión de un período en esquema de guardias',
                    true
                )
            );
        }

        return response()->json(['record' => [
            'id' => $period->id,
            'from_date' => $period->from_date,
            'to_date' => $period->to_date,
            'document_status_id' => $period->document_status_id,
            'document_status' => $period->documentStatus,
            'observations' => $period->observations ?? ''
        ]], 200);
    }

    /**
     * Obtener los periodos confirmados del esquema de guardias
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param \Modules\Payroll\Actions\GetPayrollConfirmedGuardPeriodsAction $getConfirmedPeriods Acción para obtener los periodos confirmados
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getPayrollConfirmedGuardPeriods(Request $request, GetPayrollConfirmedGuardPeriodsAction $getConfirmedPeriods)
    {
        return response()->json(
            $getConfirmedPeriods->invoke(
                $request->from_date,
                $request->to_date,
                $request->payroll_supervised_group_id,
                $request->payroll_time_sheet_parameter_id,
                $request->institution_id
            ),
            200
        );
    }
}
