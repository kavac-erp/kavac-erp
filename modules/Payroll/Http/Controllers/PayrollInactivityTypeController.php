<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollInactivityType;

/**
 * @class PayrollInactivityTypeController
 * @brief Controlador del tipo de inactividad
 *
 * Clase que gestiona los tipos de inactividad
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollInactivityTypeController extends Controller
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
        /*$this->middleware('permission:payroll.inactivity.types.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.inactivity.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.inactivity.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.inactivity.types.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de inactividad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de inactividad
     */
    public function index()
    {
        return response()->json(['records' => PayrollInactivityType::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de inactividad
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de inactividad
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
            'name' => ['required', 'max:100', 'unique:payroll_inactivity_types,name']
        ]);

        $payrollInactivityType = PayrollInactivityType::create(['name' => $request->name]);
        return response()->json(['record' => $payrollInactivityType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de inactividad
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar un tipo de inactividad
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de inactividad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del tipo de inactividad a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollInactivityType = PayrollInactivityType::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_inactivity_types,name,' . $payrollInactivityType->id]
        ]);
        $payrollInactivityType->name = $request->name;
        $payrollInactivityType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de inactividad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del tipo de inactividad a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollInactivityType = PayrollInactivityType::find($id);
        $payrollInactivityType->delete();
        return response()->json(['record' => $payrollInactivityType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de inactividad registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de inactividad
     */
    public function getPayrollInactivityTypes()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollInactivityType', 'name', [], true));
    }
}
