<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SalePaymentMethod;

/**
 * @class SalePaymentMethodController
 * @brief Controlador que gestiona los métodos de pago
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SalePaymentMethodController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador/*
        $this->middleware('permission:sale.payment.method.list', ['only' => 'index']);
        $this->middleware('permission:sale.payment.method.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.payment.method.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.payment.method.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de personal
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SalePaymentMethod::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo método de pago
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Valida y registra un nuevo método de pago
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $this->salePaymentMethodeValidate($request);

        $salePaymentMethod = SalePaymentMethod::create([
            'name' => $request->name,'description' => $request->description
        ]);
        return response()->json(['record' => $salePaymentMethod, 'message' => 'Success'], 200);
    }

    /**
     * Validacion de los datos
     *
     * @author Ing. Jose Puentes <jpuentes@cenditel.gob.ve>
     *
     * @param     Request    $request Datos de la petición
     *
     * @return    void
     */
    public function salePaymentMethodeValidate(Request $request)
    {
        $attributes = [
            'name' => 'Nombre',
            'description' => 'Descripción'
        ];
        $validation = [];
        $validation['name'] = ['required', 'max:100'];
        $validation['description'] = ['required', 'max:200'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Muestra información de un método de pago
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para editar la información del método de pago
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza la información del metodo de pago
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del datos a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $salePaymentMethod = SalePaymentMethod::find($id);

        $this->salePaymentMethodeValidate($request);

        $salePaymentMethod->name  = $request->name;
        $salePaymentMethod->description = $request->description;
        $salePaymentMethod->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el metodo de pago
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del metodo de pago a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $salePaymentMethod = SalePaymentMethod::find($id);
        $salePaymentMethod->delete();
        return response()->json(['record' => $salePaymentMethod, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de pago registrados
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalePaymentMethod()
    {
        return response()->json(template_choices('Modules\Sale\Models\SalePaymentMethod', 'name', '', true));
    }
}
