<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollContractType;

/**
 * @class PayrollContractTypeController
 * @brief Controlador del tipo de contrato
 *
 * Clase que gestiona los tipos de contrato
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollContractTypeController extends Controller
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
        /*$this->middleware('permission:payroll.contract.types.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.contract.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.contract.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.contract.types.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de contrato
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de contrato
     */
    public function index()
    {
        return response()->json(['records' => PayrollContractType::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de contrato
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de contrato
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
            'name' => ['required', 'max:100', 'unique:payroll_contract_types,name']
        ]);

        $payrollContractType = PayrollContractType::create(['name' => $request->name]);
        return response()->json(['record' => $payrollContractType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de un tipo de contrato
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar la información de un tipo de contrato
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de contrato
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del tipo de contrato a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollContractType = PayrollContractType::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_contract_types,name,' . $payrollContractType->id]
        ]);

        $payrollContractType->name = $request->name;
        $payrollContractType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de contrato
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del tipo de contrato a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollContractType = PayrollContractType::find($id);
        $payrollContractType->delete();
        return response()->json(['record' => $payrollContractType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de contrato
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de contrato
     */
    public function getPayrollContractTypes()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollContractType', 'name', [], true));
    }
}
