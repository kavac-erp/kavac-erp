<?php

namespace Modules\Payroll\Http\Controllers;

use Carbon\Carbon;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use App\Repositories\UploadDocRepository;
use App\Repositories\UploadImageRepository;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollVacationRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSuspensionVacationRequest;

/**
 * @class PayrollSuspensionVacationRequestController
 * @brief Controlador para suspension de vacaciones
 *
 * Controlador para suspension de vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSuspensionVacationRequestController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author     Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return     void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.suspension.vacation.requests.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.suspension.vacation.requests.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.suspension.vacation.requests.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.suspension.vacation.requests.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.suspension.vacation.requests.approved', ['only' => 'approved']);
        $this->middleware('permission:payroll.suspension.vacation.requests.rejected', ['only' => 'rejected']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'enjoyed_days'       => ['required'],
            'pending_days'       => ['nullable'],
            'suspension_reason' => ['required', 'string'],
            'file' => [
                'nullable',
                'mimes:doc,docx,odt,pdf,png,jpg,jpeg'
            ],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'suspension_reason.required' => 'El campo motivo de la suspensión de vacaciones es obligatorio.',
            'enjoyed_days.required'       => 'El campo días efectivamente disfrutados es obligatorio.',
            'start_date.required' => 'El campo fecha de inicio de la solicitud de vacaciones es obligatorio.',
            'end_date.required' => 'El campo fecha de finalización de la solicitud de vacaciones es obligatorio.',
            'end_date.before' => 'La fecha de inicio de la solicitud de vacaciones no puede ser posterior a la fecha de finalización de la solicitud de vacaciones.',
            'start_date.after' => 'La fecha de finalización de la solicitud de vacaciones no puede ser anterior a la fecha de inicio de la solicitud de vacaciones.',
            'date_request.required' => 'El campo fecha de suspensión de vacaciones es obligatorio.',
            'date_request.before_or_equal' => 'La fecha de suspensión de vacaciones no puede ser posterior a la fecha de finalización de la solicitud de vacaciones.',
            'date_request.after_or_equal' => 'La fecha de suspensión de vacaciones no puede ser anterior a la fecha de inicio de la solicitud de vacaciones.',
            'file.mimes' => 'El archivo debe ser de los siguientes tipos: doc, docx ,odt, pdf, png, jpg, jpeg.',
        ];
    }

    /**
     * Muestra un listado de las solicitudes de suspension de vacaciones
     *
     * @author <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return     \Illuminate\View\View
     */
    public function index(): View
    {
        return view('payroll::requests.vacations.suspensions.index');
    }

    /**
     * Muestra el formulario para crear una nueva solicitud de suspension de vacaciones
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra una nueva solicitud de suspension de vacaciones
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request, UploadImageRepository $upImage, UploadDocRepository $upDoc): JsonResponse
    {
        // Revisar si hay algun registro de suspension pendiente
        $pending_suspensions = PayrollSuspensionVacationRequest::where('payroll_vacation_request_id', $request->payroll_vacation_request_id)
            ->where('status', 'pending')->get();

        if ($pending_suspensions->isNotEmpty()) {
            $request->session()->flash('message', ['type' => 'error']);
            $errors[0] = ["No puede haber más de una suspension pendiente."];
            return response()->json(['result' => true, 'errors' => $errors], 422);
        }

        // Validar fechas de solicitud de vacaciones con la fecha de suspensión
        $payrollVacationRequest = PayrollVacationRequest::find($request->payroll_vacation_request_id);
        $request->request->add([
            'start_date' => $payrollVacationRequest?->start_date,
            'end_date' => $payrollVacationRequest?->end_date,
        ]);
        $this->validateRules = array_merge($this->validateRules, [
            'start_date' => ['required', 'before:end_date'],
            'end_date' => ['required', 'after:start_date'],
            'date_request' => [
                'required',
                'before_or_equal:end_date',
                'after_or_equal:start_date',
            ],
        ]);
        $this->validate($request, $this->validateRules, $this->messages);


        // Cambiar el estado de la solicitud vacaional como suspendida
        $payrollVacationRequest->status = 'suspended';
        $payrollVacationRequest->save();

        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        /* Objeto asociado al modelo PayrollSuspendVacationRequest */
        $payrollSuspensionVacationRequest = PayrollSuspensionVacationRequest::query()->create([
            'status'               => 'pending',
            'suspension_reason'    => $request->suspension_reason,
            'enjoyed_days'         => $request->enjoyed_days,
            'pending_days'         => $request->missing_days,
            'date_request'         => $request->date_request,
            'payroll_vacation_request_id' => $request->payroll_vacation_request_id,
        ]);

        /* Se guardan los documentos, según sea el tipo (imágenes y/o documentos)*/
        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        $imageFormat = ['jpeg', 'jpg', 'png'];

        if ($request->has('file')) {
            $file = $request->file('file');
            $extensionFile = $file->getClientOriginalExtension();
            if (in_array($extensionFile, $documentFormat)) {
                $upDoc->uploadDoc(
                    $file,
                    'documents',
                    PayrollSuspensionVacationRequest::class,
                    $payrollSuspensionVacationRequest->id
                );
            } elseif (in_array($extensionFile, $imageFormat)) {
                $upImage->uploadImage(
                    $file,
                    'pictures',
                    PayrollSuspensionVacationRequest::class,
                    $payrollSuspensionVacationRequest->id
                );
            }
        }

        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Muestra los datos de la información de la solicitud de suspension de vacaciones seleccionada
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer        $id    Identificador único de la solicitud de vacaciones
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(PayrollSuspensionVacationRequest $suspension_vacation_request): JsonResponse
    {
        return response()->json(['record' => $suspension_vacation_request], 200);
    }

    /**
     * Muestra el formulario para editar la información de la solicitud de suspension de vacaciones
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información de la solicitud de suspension de vacaciones
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer                     $id         Identificador único asociado a la solicitud de vacaciones
     *
     * @param     \Illuminate\Http\Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        PayrollSuspensionVacationRequest $suspension_vacation_request,
        UploadImageRepository $upImage,
        UploadDocRepository $upDoc
    ): JsonResponse {
        // Validar fechas de solicitud de vacaciones con la fecha de suspensión
        $payrollVacationRequest = PayrollVacationRequest::find($request->payroll_vacation_request_id);
        $request->request->add([
            'start_date' => $payrollVacationRequest?->start_date,
            'end_date' => $payrollVacationRequest?->end_date,
        ]);

        $this->validateRules = array_merge($this->validateRules, [
            'start_date' => ['required', 'before:end_date'],
            'end_date' => ['required', 'after:start_date'],
            'date_request' => [
                'required',
                'before_or_equal:end_date',
                'after_or_equal:start_date',
            ],
        ]);

        $this->validate($request, $this->validateRules, $this->messages);

        $profileUser = auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $totalDays = $suspension_vacation_request->enjoyed_days + $suspension_vacation_request->pending_days;
        $pending_days = $totalDays - $request->enjoyed_days;

        $suspension_vacation_request->update([
            'suspension_reason' => $request->suspension_reason,
            'status'            => 'pending',
            'enjoyed_days'      => $request->enjoyed_days,
            'pending_days'      => $pending_days,
            'date_request'      => $request->date_request,
        ]);

        /* Se guardan los documentos, según sea el tipo (imágenes y/o documentos)*/
        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        $imageFormat = ['jpeg', 'jpg', 'png'];

        if ($request->has('file')) {
            $file = $request->file('file');
            $extensionFile = $file->getClientOriginalExtension();
            if (in_array($extensionFile, $documentFormat)) {
                $upDoc->uploadDoc(
                    $file,
                    'documents',
                    PayrollSuspensionVacationRequest::class,
                    $suspension_vacation_request->id
                );
            } elseif (in_array($extensionFile, $imageFormat)) {
                $upImage->uploadImage(
                    $file,
                    'pictures',
                    PayrollSuspensionVacationRequest::class,
                    $suspension_vacation_request->id
                );
            }
        }

        return response()->json([
            'message' => 'Success',
            'redirect' => route('payroll.vacation-requests.index'),
        ], 200);
    }

    /**
     * Elimina una solicitud de suspension de vacaciones
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer        $id    Identificador único de la solicitud de vacaciones a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(PayrollSuspensionVacationRequest $suspension_vacation_request): JsonResponse
    {
        // Actualizar estado de la solicitud vacacional
        $payrollVacationRequest = PayrollVacationRequest::find($suspension_vacation_request->payroll_vacation_request_id);
        $payrollVacationRequest->status = "approved";
        $payrollVacationRequest->save();

        $suspension_vacation_request->delete();

        return response()->json([
            'record' => $suspension_vacation_request,
            'redirect' => route('payroll.vacation-requests.index'),
            'message' => 'Success'
        ], 200);
    }

    /**
     * Muestra un listado de las solicitudes de suspension de vacaciones registradas
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList(): JsonResponse
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        if ($user->hasRole('admin, payroll')) {
            $records = PayrollSuspensionVacationRequest::with(['payrollVacationRequest' => function ($query) use ($institution): void {
                $query->where('institution_id', $institution->id);
            }])
                ->get();
        } else {
            $records = PayrollSuspensionVacationRequest::with(['payrollVacationRequest' => function ($query) use ($institution): void {
                $query->where('institution_id', $institution->id);
            }])
                ->get();
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Aprueba una solicitud de suspensión de vacaciones
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function approved(Request $request, $id, $check_permission = false): JsonResponse
    {
        if ($check_permission) {
            $payrollSuspensionVacationRequest = PayrollSuspensionVacationRequest::find($id);

            // Actualizar fecha fin de solicitud vacacional con la fecha de suspension
            $payrollVacationRequestId = $payrollSuspensionVacationRequest->payrollVacationRequest->id;
            $payrollVacationRequest = PayrollVacationRequest::find($payrollVacationRequestId);
            $suspension_end_date =
                Carbon::parse($payrollSuspensionVacationRequest->date_request)->subDays(1)->format('Y-m-d');


            // Actualizar estado de solicitud vacacional y periodos de año vacacional
            $payrollVacationRequest->status_parameters =
                json_encode($payrollSuspensionVacationRequest->suspension_reason);
            $period_years = json_decode($payrollVacationRequest->vacation_period_year);
            $count_days = $payrollSuspensionVacationRequest->pending_days;
            $count_enjoyed = $payrollSuspensionVacationRequest->enjoyed_days;

            foreach ($period_years as $period) {
                if (!isset($period->pending_days)) {
                    $period->pending_days = 0;
                }

                if (intval($count_enjoyed) > 0) {
                    if ($period->vacation_days > $count_enjoyed) {
                        $period->pending_days = $period->vacation_days - $count_enjoyed - $period->pending_days;
                        $count_enjoyed = 0;
                    } else {
                        $count_enjoyed -= $period->vacation_days;
                        $period->pending_days = 0;
                    }
                } else {
                    $period->pending_days = $period->vacation_days;
                }
            }

            $payrollVacationRequest->vacation_period_year = json_encode($period_years);
            $payrollVacationRequest->save();

            $payrollSuspensionVacationRequest->status = 'approved';
            $payrollSuspensionVacationRequest->save();
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Rechaza una solicitud de suspensión de vacaciones
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id Identificador de la solicitud de suspensión de vacaciones
     * @param boolean $check_permission Determina si se verifica el permiso de acceso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejected(Request $request, $id, $check_permission = false): JsonResponse
    {
        if ($check_permission) {
            $payrollSuspensionVacationRequest = PayrollSuspensionVacationRequest::find($id);
            $payrollSuspensionVacationRequest->status = 'rejected';
            $payrollSuspensionVacationRequest->save();

            // Actualizar estado de la solicitud vacacional
            $payrollVacationRequest =
                PayrollVacationRequest::find($payrollSuspensionVacationRequest->payroll_vacation_request_id);
            $payrollVacationRequest->status = "approved";
            $payrollVacationRequest->save();
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Muestra el listado de suspensiones de solicitud de vacaciones según el trabajador seleccionado
     *
     * @author    Fabian Palmera <fapalmera@cenditel.gob.ve>
     *
     * @param     integer $staff_id    Identificador único del trabajador registrado
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function getSuspensionVacationRequests($staff_id)
    {
        $payrollVacationRequest = PayrollVacationRequest::where('payroll_staff_id', $staff_id)
            ->whereIn('status', ['suspended']);

        $suspensions = [];
        if ($payrollVacationRequest) {
            foreach ($payrollVacationRequest->get() as $vacation_request) {
                $suspensions[] = $vacation_request->payrollSuspensionVacationRequest->toArray();
            }
        }
        return response()->json(['records' => $suspensions], 200);
    }
}
