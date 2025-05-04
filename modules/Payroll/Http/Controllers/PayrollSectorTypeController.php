<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSectorType;

/**
 * @class PayrollSectorTypeController
 * @brief Controlador del tipo de sector
 *
 * Clase que gestiona los tipos de sector
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class PayrollSectorTypeController extends Controller
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
        /*$this->middleware('permission:payroll.sector.types.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.sector.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.sector.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.sector.types.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de sector
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de sector
     */
    public function index()
    {
        return response()->json(['records' => PayrollSectorType::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de sector
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de sector
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
            'name' => ['required', 'max:100', 'unique:payroll_sector_types,name']
        ]);

        $payrollSectorType = PayrollSectorType::create(['name' => $request->name]);
        return response()->json(['record' => $payrollSectorType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información del tipo de sector
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar el tipo de sector
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de sector
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del tipo de sector a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request)
    {
        $payrollSectorType = PayrollSectorType::find($request->id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_sector_types,name,' . $payrollSectorType->id]
        ]);

        $payrollSectorType->name = $request->name;
        $payrollSectorType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de sector
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del tipo de sector a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollSectorType = PayrollSectorType::find($id);
        $payrollSectorType->delete();
        return response()->json(['record' => $payrollSectorType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de sector
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de sector
     */
    public function getPayrollSectorTypes()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollSectorType', 'name', [], true));
    }
}
