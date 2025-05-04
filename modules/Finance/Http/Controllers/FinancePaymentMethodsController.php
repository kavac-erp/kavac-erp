<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinancePaymentMethods;

/**
 * @class FinanceAccountTypeController
 * @brief Controlador para los tipos de cuenta bancaria
 *
 * Clase que gestiona las formas de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @author Ing. Marco Ocanto
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePaymentMethodsController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Método constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];
    }

    /**
     * Listado de métodos de pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => FinancePaymentMethods::all()], 200);
    }

    /**
     * Muestra un formulario para registrar un nuevo método de pago
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Almacena un nuevo método de pago
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:finance_payment_methods,name'],
            'description' => ['required', 'max:100', 'unique:finance_payment_methods,description'],
        ]);

        $financePaymentMethods = FinancePaymentMethods::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['record' => $financePaymentMethods, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles de un método de pago
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario de edición de un método de pago
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Actualiza la información de un método de pago
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id ID del tipo de pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos del tipo de pago */
        $financePaymentMethods = FinancePaymentMethods::find($id);

        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:finance_payment_methods,name,' . $financePaymentMethods->id],
            'description' => ['required', 'max:100', 'unique:finance_payment_methods,description,' . $financePaymentMethods->id],
        ]);

        $financePaymentMethods->name = $request->name;
        $financePaymentMethods->description = $request->description;
        $financePaymentMethods->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $id ID del tipo de pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos del tipo de pago */
        $financePaymentMethods = FinancePaymentMethods::find($id);
        $financePaymentMethods->delete();
        return response()->json(['record' => $financePaymentMethods, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de pagos
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods()
    {
        foreach (FinancePaymentMethods::all() as $payment_methods) {
            $this->data[] = [
                'id' => $payment_methods->id,
                'text' => $payment_methods->name,
                'description' => $payment_methods->description,
            ];
        }

        return response()->json($this->data);
    }
}
