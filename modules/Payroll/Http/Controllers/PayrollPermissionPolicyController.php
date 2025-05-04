<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollPermissionPolicy;
use Modules\Payroll\Rules\PayrollPermissionPolicyDaysRange;

/**
 * @class PayrollPermissionPolicyController *
 * @brief Controlador de políticas de permisos
 *
 * Clase que gestiona los políticas de permisos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPermissionPolicyController extends Controller
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
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {

        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.permission.policies.create', ['only' => 'store']);
        $this->middleware('permission:payroll.permission.policies.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.permission.policies.delete', ['only' => 'destroy']);

       /* Define las reglas de validación para el formulario */
        $this->validateRules = [
           'name'             => ['required', 'max:100'],
           'anticipation_day' => ['required'],
           'time_min'         => ['required'],
           'time_max'         => ['required'],
           'time_unit'        => ['required'],
           'institution_id'   => ['required']
        ];

       /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
           'name.required'    => 'El campo nombre es obligatorio.',
           'name.max'         => 'El campo nombre no debe contener más de 100 caracteres.',
           'name.unique'      => 'El campo nombre ya ha sido registrado.',
           'anticipation_day.required' => 'El campo días de anticipación es obligatorio.',
           'time_min.required'  => 'El campo rango mínimo es obligatorio.',
           'time_max.required'  => 'El campo rango máximo es obligatorio.',
           'time_unit.required' => 'El campo unidad de tiempo es obligatorio.',
           'institution_id.required' => 'El campo organización es obligatorio.',
        ];
    }
    /**
     * Listado de políticas de permisos
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PayrollPermissionPolicy::all()], 200);
    }

    /**
     * Muestra el formulario para registrar una nueva política de permiso
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**     *
     * Valida y registra un nuevo tipo de permiso
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name'           => ['required', 'max:100', 'unique:payroll_permission_policies,name']]
        );
        $validateRules  = array_merge(
            ['id' => [new PayrollPermissionPolicyDaysRange($request->input('time_min'), $request->input('time_max'))]],
            $validateRules
        );
        $this->validate($request, $validateRules, $this->messages);

        $payrollPermissionPolicy = PayrollPermissionPolicy::create([
            'name'             => $request->name,
            'anticipation_day' => $request->anticipation_day,
            'time_min'         => $request->input('time_min'),
            'time_max'         => $request->input('time_max'),
            'time_unit'        => $request->input('time_unit'),
            'active'           => $request->active,
            'business_days'    => $request->business_days,
            'institution_id'   => $request->institution_id
        ]);
        return response()->json(['record' => $payrollPermissionPolicy, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de permiso
     *
     * @param integer $id identificador del tipo de permiso
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para actualizar la información de un tipo de permiso
     *
     * @param integer $id identificador del tipo de permiso
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de permiso
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param integer $id identificador del tipo de permiso
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollPermissionPolicy = PayrollPermissionPolicy::find($id);
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'max:100', 'unique:payroll_permission_policies,name,' . $payrollPermissionPolicy->id]]
        );
        $validateRules  = array_merge(
            [
                'id' => [
                    new PayrollPermissionPolicyDaysRange(
                        $request->input('time_min'),
                        $request->input('time_max')
                    )
                ]
            ],
            $validateRules
        );

        $this->validate($request, $validateRules, $this->messages);

        $payrollPermissionPolicy->name             = $request->name;
        $payrollPermissionPolicy->anticipation_day = $request->anticipation_day;
        $payrollPermissionPolicy->time_min         = $request->input('time_min');
        $payrollPermissionPolicy->time_max         = $request->input('time_max');
        $payrollPermissionPolicy->time_unit        = $request->input('time_unit');
        $payrollPermissionPolicy->active           = $request->active;
        $payrollPermissionPolicy->business_days    = $request->business_days;
        $payrollPermissionPolicy->institution_id   = $request->institution_id;
        $payrollPermissionPolicy->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de permiso
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param integer $id identificador del tipo de permiso
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $payrollPermissionPolicy = PayrollPermissionPolicy::find($id);
        $payrollPermissionPolicy->delete();
        return response()->json(['record' => $payrollPermissionPolicy, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de políticas de permisos
     *
     * @return array
     */
    public function getPermissionPolicies()
    {
        $records = PayrollPermissionPolicy::where('active', true)->get();
        $options = [['id' => '', 'text' => 'Seleccione...']];
        foreach ($records as $rec) {
            array_push($options, [
                'id'               => $rec->id,
                'text'             => $rec->name,
                'anticipation_day' => $rec->anticipation_day,
                'time_min'         => $rec->time_min,
                'time_max'         => $rec->time_max,
                'time_unit'        => $rec->time_unit,
                'business_days'    => $rec->business_days
            ]);
        };
        return $options;
    }
}
