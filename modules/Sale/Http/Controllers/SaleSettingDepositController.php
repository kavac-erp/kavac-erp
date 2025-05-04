<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleSettingDeposit;

/**
 * @class SaleSettingDepositController
 * @brief Gestiona los datos de la configuración de depósitos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleSettingDepositController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */

    public function __construct()
    {
        /* Establece permisos de acceso para cada forma del controlador */
        $this->middleware('permission:sale.setting.deposit.list', ['only' => 'index']);
        $this->middleware('permission:sale.setting.deposit.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.setting.deposit.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.setting.deposit.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de las formas de pago
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos
     */
    public function index()
    {
         return response()->json(['records' => SaleSettingDeposit::all()], 200);
    }

    /**
     * Muestra el formulario para la creación de un nuevo registro de depósito
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Valida y registra un nuevo tipo de forma de pago
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'description' => ['required'],
            'deposit_attributes' => ['required']
        ]);
        $saleSettingDeposit = SaleSettingDeposit::create(['name' => $request->name, 'description' => $request->description, 'deposit_attributes' => $request->deposit_attributes]);
        return response()->json(['record' => $saleSettingDeposit, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de la configuración de depósitos
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para editar una configuración de depósitos
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza la información de las formas de pago
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Datos de la petición
     * @param  integer $id                          Identificador del datos a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $saleSettingDeposit = SaleSettingDeposit::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'description' => ['required'],
            'deposit_attributes' => ['required'],
        ]);
        $saleSettingDeposit->name  = $request->name;
        $saleSettingDeposit->description  = $request->description;
        $saleSettingDeposit->deposit_attributes  = $request->deposit_attributes;
        $saleSettingDeposit->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina una configuración de depósitos
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del producto a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $saleSettingDeposit = SaleSettingDeposit::find($id);
        $saleSettingDeposit->delete();
        return response()->json(['record' => $saleSettingDeposit, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las formas de pago registradas
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleSettingDeposit()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleSettingDeposit', 'name', '', false, true));
    }
}
