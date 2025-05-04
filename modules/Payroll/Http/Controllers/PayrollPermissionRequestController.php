<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollPermissionRequest;
use Modules\Payroll\Models\Institution;

/**
 * @class      PayrollPermissionRequestController
 * @brief      Controlador de solicitudes de permisos
 *
 * Clase que gestiona las solicitudes de permisos
 *
 * @author     Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
*/
class PayrollPermissionRequestController extends Controller
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
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.permission.requests.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.permission.requests.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.permission.requests.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.permission.requests.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.permission.requests.approved', ['only' => 'approved']);
        $this->middleware('permission:payroll.permission.requests.rejected', ['only' => 'rejected']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'date'                             => ['required'],
            'payroll_staff_id'                 => ['required'],
            'payroll_permission_policy_id'     => ['required'],
            'start_date'                       => ['required'],
            'end_date'                         => ['required', 'date', 'after_or_equal:start_date'],
            'time_permission'                   => ['required'],
            'motive_permission'                => ['required', 'max:200'],

        ];
        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'payroll_staff_id.required'             => 'El campo trabajador es obligatorio.',
            'payroll_permission_policy_id.required' => 'El campo tipo de permiso es obligatorio.',
            'start_date.required'                   => 'El campo desde es obligatorio.',
            'end_date.required'                     => 'El campo hasta es obligatorio.',
            'end_date.after_or_equal'    => 'La fecha hasta debe ser una fecha posterior o igual a la fecha desde.',
            'time_permission.required'               => 'El campo tiempo de permiso es obligatorio.',
            'motive_permission.required'            => 'El campo motivo del permiso es obligatorio.',
            'motive_permission.max'                 => 'El campo motivo del permiso no debe
                                                        contener más de 200 caracteres.',
        ];
    }

    /**
     * Muestra el listado de solicitudes de permisos
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::requests.permissions.index');
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud de permiso
     *
     * @return \Illuminate\View\View
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
        return view('payroll::requests.permissions.create', compact('isAdmin', 'userId'));
    }

    /**
     * Valida y registra una nueva solicitud de permiso
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollPermissionRequest = PayrollPermissionRequest::create([
            'status'                           => 'Pendiente',
            'date'                             => $request->date,
            'payroll_staff_id'                 => $request->payroll_staff_id,
            'payroll_permission_policy_id'     => $request->payroll_permission_policy_id,
            'start_date'                       => $request->start_date,
            'end_date'                         => $request->end_date,
            'start_time'                       => $request->start_time,
            'end_time'                         => $request->end_time,
            'time_permission'                  => $request->time_permission,
            'motive_permission'                => $request->motive_permission,
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('payroll.permission-requests.index')], 200);
    }

    /**
     * Muestra los datos de la información de la solicitud de permiso seleccionada
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param integer $id Identificador de la solicitud de permiso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $payrollPermissionRequest = PayrollPermissionRequest::find($id);
        return response()->json(['record' => $payrollPermissionRequest], 200);
    }

    /**
     * Muestra el formulario para actualizar la información de una solicitud de permiso
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param integer $id Identificador de la solicitud de permiso
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $payrollPermissionRequest = PayrollPermissionRequest::find($id);

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin, payroll');
        $profile = $user->profile;
        $userId = -1;
        if ($profile) {
            if ($profile->employee_id) {
                $userId = $profile->employee_id;
            }
        }
        if ($isAdmin) {
            $userId = 0;
        }
        return view(
            'payroll::requests.permissions.create',
            compact('payrollPermissionRequest', 'isAdmin', 'userId')
        );
    }

    /**
     * Actualiza la información de la solicitud de permiso
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador de la solicitud de permiso
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $payrollPermissionRequest = PayrollPermissionRequest::find($id);
        $payrollPermissionRequest->status                           = 'Pendiente';
        $payrollPermissionRequest->date                             = $request->date;
        $payrollPermissionRequest->payroll_staff_id                 = $request->payroll_staff_id;
        $payrollPermissionRequest->payroll_permission_policy_id     = $request->payroll_permission_policy_id;
        $payrollPermissionRequest->start_date                       = $request->start_date;
        $payrollPermissionRequest->end_date                         = $request->end_date;
        $payrollPermissionRequest->start_time                       = $request->start_time;
        $payrollPermissionRequest->end_time                         = $request->end_time;
        $payrollPermissionRequest->time_permission                  = $request->time_permission;
        $payrollPermissionRequest->motive_permission                = $request->motive_permission;
        $payrollPermissionRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.permission-requests.index')], 200);
    }

    /**
     * Elimina una solicitud de permiso
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param integer $id Identificador de la solicitud de permiso
     * @return \Illuminate\Http\JsonResponse
    */
    public function destroy($id)
    {
        $payrollPermissionRequest = PayrollPermissionRequest::find($id);
        $payrollPermissionRequest->delete();

        return response()->json(['record' => $payrollPermissionRequest, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de los permisos
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
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
            return response()->json(['records' => PayrollPermissionRequest::with(['payrollStaff'])->get()], 200);
        } else {
            $records = PayrollPermissionRequest::with('payrollStaff')->whereHas('payrollStaff', function ($query) use ($profileUser) {
                    $query->whereHas('payrollEmployment', function ($q) use ($profileUser) {
                        $q->where('id', $profileUser->employee_id);
                    });
            })->get();

            return response()->json(['records' => $records], 200);
        }
    }

    /**
     * Obtiene información de una solicitud de permiso
     *
     * @param integer $id Identificador de la solicitud de permiso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        $payrollPermissionRequest = PayrollPermissionRequest::where('id', $id)->with([
            'payrollStaff', 'payrollPermissionPolicy'])->first();
        return response()->json(['record' => $payrollPermissionRequest], 200);
    }

    /**
     * Muestra un listado de las solicitudes de permiso pendientes
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
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
            return response()->json(['records' => PayrollPermissionRequest::where('status', 'Pendiente')->with([
            'payrollStaff'])->get()], 200);
        } else {
            $records = PayrollPermissionRequest::where('status', 'Pendiente')->with('payrollStaff')
                            ->whereHas('payrollStaff', function ($query) use ($profileUser) {
                                $query->whereHas('payrollEmployment', function ($q) use ($profileUser) {
                                    $q->where('id', $profileUser->employee_id);
                                });
                            })->get();

            return response()->json(['records' => $records], 200);
        }
    }

    /**
     * Aprueba una solicitu de permiso
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id Identificador de la solicitud de permiso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approved(Request $request, $id)
    {
        $payrollPermissionRequest = PayrollPermissionRequest::find($id);
        $payrollPermissionRequest->status = 'Aprobado';
        $payrollPermissionRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.permission-requests.index')], 200);
    }

    /**
     * Rechaza una solicitud de permiso
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id Identificador de la solicitud de permiso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejected(Request $request, $id)
    {
        $payrollPermissionRequest = PayrollPermissionRequest::find($id);
        $payrollPermissionRequest->status = 'Rechazado';
        $payrollPermissionRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.permission-requests.index')], 200);
    }
}
