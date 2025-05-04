<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollPermissionPolicy;
use Modules\Payroll\Rules\PayrollPermissionPolicyDaysRange;

class PayrollPermissionPolicyController extends Controller
{
    use ValidatesRequests;

    /**
    * Arreglo con las reglas de validación sobre los datos de un formulario
    * @var Array $validateRules
    */
    protected $validateRules;

   /**
    * Arreglo con los mensajes para las reglas de validación
    * @var Array $messages
    */
    protected $messages;

    public function __construct()
    {

        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.permission-policies.create', ['only' => 'store']);
        $this->middleware('permission:payroll.permission-policies.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.permission-policies.delete', ['only' => 'destroy']);

       /** Define las reglas de validación para el formulario */
        $this->validateRules = [
           'name'             => ['required', 'max:100'],
           'anticipation_day' => ['required'],
           'time_min'         => ['required'],
           'time_max'         => ['required'],
           'time_unit'        => ['required'],
           'institution_id'   => ['required']
        ];

       /** Define los mensajes de validación para las reglas del formulario */
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
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return response()->json(['records' => PayrollPermissionPolicy::all()], 200);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     *
     * Valida y registra un nuevo tipo de permiso
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
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
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de permiso
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $payrollPermissionPolicy = PayrollPermissionPolicy::find($id);
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name'           => ['required', 'max:100', 'unique:payroll_permission_policies,name,' . $payrollPermissionPolicy->id]]
        );
        $validateRules  = array_merge(
            ['id' => [new PayrollPermissionPolicyDaysRange($request->input('time_min'), $request->input('time_max'))]],
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
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */

    public function destroy($id)
    {
        $payrollPermissionPolicy = PayrollPermissionPolicy::find($id);
        $payrollPermissionPolicy->delete();
        return response()->json(['record' => $payrollPermissionPolicy, 'message' => 'Success'], 200);
    }

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
