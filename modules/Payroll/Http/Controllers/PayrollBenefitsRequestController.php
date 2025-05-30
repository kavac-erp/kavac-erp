<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollBenefitsRequest;
use Modules\Payroll\Models\Institution;
use App\Models\CodeSetting;
use App\Models\FiscalYear;

/**
 * @class      PayrollBenefitsRequestController
 * @brief      Controlador de solicitudes de prestaciones sociales
 *
 * Clase que gestiona las solicitudes de prestaciones sociales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollBenefitsRequestController extends Controller
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
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.benefits.requests.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.benefits.requests.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.benefits.requests.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.benefits.requests.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_staff_id' => ['required'],
            'amount_requested' => ['required'],
            'motive'           => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'payroll_staff_id.required' => 'El campo trabajador es obligatorio.',
            'amount_requested.required' => 'El campo monto solicitado es obligatorio.',
            'motive.required'           => 'El campo motivo de adelanto de prestaciones es obligatorio.'
        ];
    }

    /**
     * Muestra un listado de las solicitudes de adelanto de prestaciones registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::requests.benefits.index');
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud de adelanto de prestaciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin, payroll');
        $profile = auth()->user()->profile;
        $userId = -1;
        if ($profile) {
            if ($profile->employee_id) {
                $userId = $profile->employee_id;
            }
        }
        if ($isAdmin) {
            $userId = 0;
        }
        return view('payroll::requests.benefits.create-edit', compact('isAdmin', 'userId'));
    }

    /**
     * Valida y registra una nueva solicitud de adelanto de prestaciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'payroll_benefits_requests')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('payroll.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            PayrollBenefitsRequest::class,
            $codeSetting->field
        );

        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        /* Objeto asociado al modelo PayrollBenefitsRequest */
        $payrollBenefitsRequest = PayrollBenefitsRequest::create([
            'code'             => $code,
            'status'           => 'pending',
            'amount_requested' => $request->input('amount_requested'),
            'motive'           => $request->input('motive'),
            'payroll_staff_id' => $request->input('payroll_staff_id'),
            'institution_id'   => $request->input('institution_id') ?? $institution->id
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('payroll.benefits-requests.index')], 200);
    }

    /**
     * Muestra los datos de la información de la solicitud de adelanto de prestaciones seleccionada
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer        $id    Identificador único de la solicitud de adelanto de prestaciones
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function show($id)
    {
        $payrollBenefitsRequest = PayrollBenefitsRequest::find($id);
        return response()->json(['record' => $payrollBenefitsRequest], 200);
    }

    /**
     * Muestra el formulario para actualizar la información de una solicitud de adelanto de prestaciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer        $id    Identificador único del registro de solicitud de adelanto de prestaciones
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        /* Objeto asociado al modelo PayrollBenefitsRequest */
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin, payroll');
        $profile = auth()->user()->profile;
        $userId = -1;
        if ($profile) {
            if ($profile->employee_id) {
                $userId = $profile->employee_id;
            }
        }
        if ($isAdmin) {
            $userId = 0;
        }

        $payrollBenefitsRequest = PayrollBenefitsRequest::find($id);
        return view('payroll::requests.benefits.create-edit', compact('isAdmin', 'userId', 'payrollBenefitsRequest'));
    }

    /**
     * Actualiza la información de la solicitud de adelanto de prestaciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                     $id         Identificador único asociado a la solicitud
     *
     * @param     \Illuminate\Http\Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        /* Objeto asociado al modelo PayrollBenefitsRequest */
        $payrollBenefitsRequest = PayrollBenefitsRequest::find($id);
        $this->validate($request, $this->validateRules, $this->messages);

        $profileUser = auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $payrollBenefitsRequest->update([
            'status'           => $payrollBenefitsRequest->status,
            'amount_requested' => $request->input('amount_requested'),
            'motive'           => $request->input('motive'),
            'payroll_staff_id' => $request->input('payroll_staff_id'),
            'institution_id'   => $request->input('institution_id')
        ]);

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.benefits-requests.index')], 200);
    }

    /**
     * Elimina una solicitud de adelanto de prestaciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer        $id    Identificador único de la solicitud de adelanto de prestaciones a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto asociado al modelo PayrollBenefitsRequest */
        $payrollBenefitsRequest = PayrollBenefitsRequest::find($id);
        $payrollBenefitsRequest->delete();

        return response()->json(['record' => $payrollBenefitsRequest, 'message' => 'Success'], 200);
    }

    /**
     * Muestra un listado de las solicitudes de adelanto de prestaciones registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        if ($user->hasRole('admin, payroll')) {
            $records = PayrollBenefitsRequest::where('institution_id', $institution->id)->get();
        } else {
            $records = [];
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Muestra un listado de las solicitudes de adelanto de prestaciones pendientes registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vuePendingList()
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        if ($user->hasRole('admin, payroll')) {
            $records = PayrollBenefitsRequest::where('institution_id', $institution->id)
                ->where('status', 'pending')
                ->get();
        } else {
            $records = [];
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Muestra el listado de solicitudes de adelanto de prestaciones según el trabajador seleccionado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $staff_id    Identificador único del trabajador registrado
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function getBenefitsRequests($staff_id)
    {
        $payrollBenefitsRequests = PayrollBenefitsRequest::where('payroll_staff_id', $staff_id)
            ->whereIn('status', ['pending', 'approved'])->get();
        return response()->json(['records' => $payrollBenefitsRequests], 200);
    }

    /**
     * Actualiza la información de la solicitud de adelanto de prestaciones seleccionada
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request    $request    Datos de la petición
     * @param     integer                     $id         Identificador único asociado a la solicitud
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function review(Request $request, $id)
    {
        /* Objeto asociado al modelo PayrollBenefitsRequest */
        $payrollBenefitsRequest = PayrollBenefitsRequest::find($id);
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollBenefitsRequest->update([
            'status'               => $request->input('status'),
            'status_parameters'    => json_encode($request->input('status_parameters')),
        ]);

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.benefits-requests.index')], 200);
    }
}
