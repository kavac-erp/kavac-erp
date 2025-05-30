<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollStaffType;

/**
 * @class StaffTypeController
 * @brief Controlador de tipos de personal
 *
 * Clase que gestiona el tipo de personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        /*$this->middleware('permission:payroll.staff.types.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.staff.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.staff.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.staff.types.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de personal
     */
    public function index()
    {
        return response()->json(['records' => PayrollStaffType::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de tipo de personal
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_staff_types,name'],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollStaffType = PayrollStaffType::create(['name' => $request->name,'description' => $request->description]);
        return response()->json(['record' => $payrollStaffType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de personal
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar la información de un tipo de personal
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del tipo de personal a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollStaffType = PayrollStaffType::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_staff_types,name,' . $payrollStaffType->id],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollStaffType->name  = $request->name;
        $payrollStaffType->description = $request->description;
        $payrollStaffType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del tipo de personal a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollStaffType = PayrollStaffType::find($id);
        $payrollStaffType->delete();
        return response()->json(['record' => $payrollStaffType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de personal registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de personal
     */
    public function getPayrollStaffTypes()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollStaffType', 'name', '', true));
    }
}
