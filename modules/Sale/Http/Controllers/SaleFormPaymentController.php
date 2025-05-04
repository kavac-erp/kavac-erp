<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleFormPayment;

/**
 * @class SaleFormPaymentController
 * @brief Controlador que gestiona los datos de los formularios de pago
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleFormPaymentController extends Controller
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
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.setting.form.payment', ['only' => 'index']);
    }

    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Listado de formas de pago para select
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = [];
        $records = SaleFormPayment::all();
        foreach ($records as $record) {
            $list_attributes = [];
            $attrib = json_decode($record->attributes_form_payment, true);

            foreach ($attrib as $row) {
                $list_attributes[] = ["attributes" => $row];
            }

            $data[] = [
                'id' => $record->id,
                'name_form_payment' => $record->name_form_payment,
                'description_form_payment' => $record->description_form_payment,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
                'name_attributes' => implode(", ", $attrib),
                'list_attributes' => $list_attributes
            ];
        }

        return response()->json(['records' => $data], 200);
    }

    /**
     * Muestra el formulario para crear una nueva forma de pago
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Almacena una nueva forma de pago
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $attributes = [];
        if ($request->list_attributes && !empty($request->list_attributes)) {
            foreach ($request->list_attributes as $attribute) {
                $attributes[] = $attribute['attributes'];
            }
        }

        $this->saleFormPaymentValidate($request);

        $form_payment = SaleFormPayment::create([
            'name_form_payment' => $request->name_form_payment,
            'description_form_payment' => $request->description_form_payment,
            'attributes_form_payment' => json_encode($attributes, JSON_FORCE_OBJECT)
        ]);

        return response()->json(['record' => $form_payment, 'message' => 'Success'], 200);
    }

    /**
     * Validacion de los datos
     *
     * @author Ing. Jose Puentes <jpuentes@cenditel.gob.ve>
     *
     * @param     Request    $request
     *
     * @return    void
     */
    public function saleFormPaymentValidate(Request $request)
    {
        $attributes = [
            'name_form_payment' => 'Nombre de la forma de cobro',
            'description_form_payment' => 'Descripción de la forma de cobro'
        ];
        $validation = [];
        $validation['name_form_payment'] = ['required', 'max:100'];
        $validation['description_form_payment'] = ['required', 'max:100'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Muestra el formulario para editar una forma de pago
     *
     * @param integer $id Identificador de la forma de pago
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar una forma de pago
     *
     * @param integer $id Identificador de la forma de pago
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de una forma de pago
     *
     * @param  Request $request Datos de la petición
     * @param integer $id Identificador de la forma de pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos de la forma de cobro */
        $form_payment = SaleFormPayment::find($id);

        $this->saleFormPaymentValidate($request);

        $attributes = [];
        if ($request->list_attributes && !empty($request->list_attributes)) {
            foreach ($request->list_attributes as $attribute) {
                $attributes[] = $attribute['attributes'];
            }
        }

        $form_payment->name_form_payment = $request->name_form_payment;
        $form_payment->description_form_payment = $request->description_form_payment;
        $form_payment->attributes_form_payment = json_encode($attributes, JSON_FORCE_OBJECT);
        $form_payment->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina una forma de pago
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador de la forma de pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $form_payment = SaleFormPayment::find($id);
        $form_payment->delete();

        return response()->json(['record' => $form_payment, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las formas de pago registradas
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleFormPayment()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleFormPayment', 'name_form_payment', '', true));
    }
}
