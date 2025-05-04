<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Institution;
use App\Repositories\ReportRepository;
use Modules\Sale\Models\SaleService;
use Modules\Sale\Models\SaleClient;
use Modules\Sale\Models\SaleGoodsToBeTraded;
use Modules\Sale\Models\SaleClientsEmail;
use Modules\Sale\Models\SaleRegisterPayment;
use App\Models\Phone;
use App\Models\HistoryTax;

/**
 * @class SalePaymentController
 * @brief Gestiona los datos sobre los pagos
 *
 * Registros y aprobación de pagos.
 *
 * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SalePaymentController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Lista de estatus de los pagos
     *
     * @var array $status_list
     */
    protected $status_list = [-1 => 'Cancelado', 0 => 'Creado', 1 => 'Aprobado', 2 => 'Pago de Anticipo'];

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.payment.list', ['only' => 'index']);
        $this->middleware('permission:sale.payment.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.payment.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.payment.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'bank_id'               => ['required'],
            'currency_id'           => ['required'],
            'number_reference'      => ['required'],
            'payment_date'          => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'bank_id.required'          => 'El campo entidad bancaria es obligatorio.',
            'currency_id.required'            => 'El campo forma de pago es obligatorio.',
            'number_reference.required'             => 'El campo número de referencia de la operación es obligatorio.',
            'payment_date.required' => 'El campo fecha en que se realizó el pago es obligatorio.',
        ];
    }

    /**
     * Muestra el listado de pagos
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        $records = SaleRegisterPayment::all();
        return view('sale::payment.list', compact('records'));
    }

    /**
     * Muestra el formulario para crear un pago
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::payment.create');
    }

    /**
     * Almacena un nuevo pago
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Llama la definición de los mensajes de validación.
        $this->validate($request, $this->validateRules, $this->messages);

        //Establecer servicio. (true) pedido, (false).
        $order_or_service_define_attributes = ($request->sale_service_id) ? true : false;
        //valor de orden o servicio.
        $order_service_id = ($request->sale_service_id) ? $request->sale_service_id : $request->sale_order_id;

        //Si es servicio calcula el monto
        if ($order_or_service_define_attributes) {
            $SaleService = SaleService::find($request->sale_service_id);
            $sale_goods_to_be_traded_count = count($SaleService->sale_goods_to_be_traded);
            for ($i = 0; $i < $sale_goods_to_be_traded_count; $i++) {
                //Consulta valor de servicio segun el id de servicios
                $SaleGoodsToBeTraded = SaleGoodsToBeTraded::find($SaleService->sale_goods_to_be_traded[$i]);
                // valor de impuesto
                if ($SaleGoodsToBeTraded->history_tax_id) {
                    $HistoryTax = HistoryTax::find($SaleGoodsToBeTraded->history_tax_id);
                    // valor de servicio con impuesto
                    $porcentaje = ((float)$HistoryTax->percentage * $SaleGoodsToBeTraded->unit_price) / 100;
                } else {
                    $porcentaje = 0;
                }
                $sumatoria[$i] = $porcentaje + $SaleGoodsToBeTraded->unit_price;
            };
            $total_amount = array_sum($sumatoria);
        }
        $this->validate($request, [
            'bank_id' => ['required'],
            'currency_id' => ['required'],
            'number_reference' => ['required'],
            'payment_date' => ['required'],
        ]);

        //anticipo
        $advance_define_attributes = ($request->advance == null) ? false : true;
        $SalePayment = SaleRegisterPayment::create([
            'order_or_service_define_attributes' => $order_or_service_define_attributes,
            'order_service_id' => $order_service_id,
            'total_amount' => $total_amount,
            'way_to_pay' => $request->currency_id,
            'banking_entity' => $request->bank_id,
            'reference_number' => $request->number_reference,
            'payment_date' => $request->payment_date,
            'advance_define_attributes' => $advance_define_attributes
        ]);


        if ($advance_define_attributes == true) {
            /* base para generar el pdf del recibo de pago */
            $pdf = new ReportRepository();

            /* Definicion de las caracteristicas generales de la página pdf */
            $institution = null;
            $is_admin = auth()->user()->isAdmin();
            $user = auth()->user();

            if (!$is_admin && $user->profile && $user->profile->institution_id) {
                $institution = Institution::find($user->profile->institution_id);
            } else {
                $institution = '';
            }

            $pdf->setConfig(['institution' => Institution::first()]);
            $pdf->setHeader('Recibo de Pago');
            $pdf->setFooter();
            $pdf->setBody('sale::pdf.payment_advance_receipt', true, [
                'pdf'         => $pdf,
                'SalePayment' => $SalePayment
            ]);
        }
        return response()->json(['record' => $SalePayment, 'message' => 'Success', 'redirect' => route('sale.payment.index')], 200);
    }

    /**
     * Muestra la información de un pago
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function show($id)
    {
        //
    }

    /**
     * Obtiene un listado de los pagos registrados
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {

        $saleservice = SaleService::with([
            'SaleGoodsToBeTraded'
        ])->join(
            "sale_register_payments",
            "sale_services.id",
            "=",
            "sale_register_payments.order_service_id"
        )->join(
            "sale_clients",
            "sale_services.sale_client_id",
            "=",
            "sale_clients.id"
        )->select(
            'sale_register_payments.id as id',
            'code',
            'payment_date',
            'name',
            'total_amount',
            'reference_number',
            'sale_goods_to_be_traded',
            'order_or_service_define_attributes',
            'type_person_juridica',
            'name',
            'id_number',
            'organization',
            'rif',
            'phones',
            'emails',
            'payment_date',
            'payment_refuse',
            'payment_approve',
            'advance_define_attributes'
        )->orderBy('id')->get();

        $values_all = [];
        foreach ($saleservice as $value) {
            $value->status = $value->payment_approve && !$value->advance_define_attributes ? 1 : 0;
            $value->status = $value->payment_approve && $value->advance_define_attributes ? 2 : $value->status;
            $value->status = $value->payment_refuse ? -1 : $value->status;
            $value->tstatus = isset($this->status_list[$value->status]) ? $this->status_list[$value->status] : $value->status;
            if ($value->order_or_service_define_attributes == true) {
                $value_2 = $value;
                array_push($values_all, $value_2);
            }
        }

        return response()->json(['records' => collect($values_all)], 200);
    }

    /**
     * Servicios pendientes
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function pending()
    {
        $saleservice = SaleService::with([
            'SaleGoodsToBeTraded'
        ])->join(
            "sale_register_payments",
            "sale_services.id",
            "=",
            "sale_register_payments.order_service_id"
        )->join(
            "sale_clients",
            "sale_services.sale_client_id",
            "=",
            "sale_clients.id"
        )->select(
            'sale_register_payments.id as id',
            'code',
            'payment_date',
            'name',
            'total_amount',
            'reference_number',
            'sale_goods_to_be_traded',
            'order_or_service_define_attributes',
            'type_person_juridica',
            'name',
            'id_number',
            'organization',
            'rif',
            'phones',
            'emails',
            'payment_date',
            'payment_refuse'
        )->orderBy('id')->where('payment_approve', '=', false)->where('payment_refuse', '=', false)->get();

        $values_all = [];
        foreach ($saleservice as $value) {
            $bolean = true;
            if ($bolean == true && $value->order_or_service_define_attributes == true) {
                $value_2 = $value;
                array_push($values_all, $value_2);
            }
        }
        return response()->json(['records' => collect($values_all)], 200);
    }

    /**
     * Aprobación de pagos
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function paymentApprove()
    {
        $saleservice = SaleService::with([
            'SaleGoodsToBeTraded'
        ])->join(
            "sale_register_payments",
            "sale_services.id",
            "=",
            "sale_register_payments.order_service_id"
        )->join(
            "sale_clients",
            "sale_services.sale_client_id",
            "=",
            "sale_clients.id"
        )->select(
            'sale_register_payments.id as id',
            'code',
            'payment_date',
            'name',
            'total_amount',
            'reference_number',
            'sale_goods_to_be_traded',
            'order_or_service_define_attributes',
            'type_person_juridica',
            'name',
            'id_number',
            'organization',
            'rif',
            'phones',
            'emails',
            'payment_date',
            'payment_refuse'
        )->orderBy('id')->where(
            'payment_approve',
            '=',
            true
        )->where(
            'payment_refuse',
            '=',
            false
        )->where(
            'advance_define_attributes',
            '=',
            false
        )->get();

        $values_all = [];
        foreach ($saleservice as $value) {
            $bolean = true;
            if ($bolean == true && $value->order_or_service_define_attributes == true) {
                $value_2 = $value;
                array_push($values_all, $value_2);
            }
        }

        return response()->json(['records' => collect($values_all)], 200);
    }

    /**
     * Rechazo de pagos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentRejected()
    {
        $saleservice = SaleService::with([
            'SaleGoodsToBeTraded'
        ])->join(
            "sale_register_payments",
            "sale_services.id",
            "=",
            "sale_register_payments.order_service_id"
        )->join(
            "sale_clients",
            "sale_services.sale_client_id",
            "=",
            "sale_clients.id"
        )->select(
            'sale_register_payments.id as id',
            'code',
            'payment_date',
            'name',
            'total_amount',
            'reference_number',
            'sale_goods_to_be_traded',
            'order_or_service_define_attributes',
            'type_person_juridica',
            'name',
            'id_number',
            'organization',
            'rif',
            'phones',
            'emails',
            'payment_date',
            'payment_refuse'
        )->orderBy('id')->where('payment_approve', '=', false)->where('payment_refuse', '=', true)->get();

        $values_all = [];
        foreach ($saleservice as $value) {
            $bolean = true;
            if ($bolean == true && $value->order_or_service_define_attributes == true) {
                $value_2 = $value;
                array_push($values_all, $value_2);
            }
        }

        return response()->json(['records' => collect($values_all)], 200);
    }

    /**
     * Atributo de pago definido
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function advanceDefineAttributesApprove()
    {
        $saleservice = SaleService::with([
            'SaleGoodsToBeTraded'
        ])->join(
            "sale_register_payments",
            "sale_services.id",
            "=",
            "sale_register_payments.order_service_id"
        )->join(
            "sale_clients",
            "sale_services.sale_client_id",
            "=",
            "sale_clients.id"
        )->select(
            'sale_register_payments.id as id',
            'code',
            'payment_date',
            'name',
            'total_amount',
            'reference_number',
            'sale_goods_to_be_traded',
            'order_or_service_define_attributes',
            'type_person_juridica',
            'name',
            'id_number',
            'organization',
            'rif',
            'phones',
            'emails',
            'payment_date',
            'payment_refuse'
        )->orderBy('id')->where(
            'advance_define_attributes',
            '=',
            true
        )->where(
            'payment_approve',
            '=',
            true
        )->where(
            'payment_refuse',
            '=',
            false
        )->get();

        $values_all = [];
        foreach ($saleservice as $value) {
            $bolean = true;
            if ($bolean == true && $value->order_or_service_define_attributes == true) {
                $value_2 = $value;
                array_push($values_all, $value_2);
            }
        }

        return response()->json(['records' => collect($values_all)], 200);
    }

    /**
     * Muestra el formulario de edición de un pago
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {

        $payment = SaleRegisterPayment::find($id);

        return view('sale::payment.create', compact("payment"));
    }

    /**
     * Actualiza la información de un pago
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina un pago
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payment = SaleRegisterPayment::find($id);
        $payment->delete();
        return response()->json(['record' => $payment, 'message' => 'Success', 'redirect' => route('sale.payment.index')], 200);
    }

    /**
     * Vizualiza información de una solicitud de pagos
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        $payment = SaleRegisterPayment::with('saleService')->where('id', $id)->first();

        return response()->json(['record' => $payment], 200);
    }

    /**
     * Muestra una lista de pediros registrados
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @return array con los pediros registrados a mostrar
     */
    public function getSaleOrderList()
    {
        return template_choices('Modules\Sale\Models\SaleOrderManagement', ['code', '-', 'name'], '', true);
    }

    /**
     * Muestra una lista de servicios registrados
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @return array con los servicios registrados a mostrar
     */
    public function getSaleServiceList()
    {
        return template_choices('Modules\Sale\Models\SaleService', ['code', '-', 'organization'], '', true);
    }

    /**
     * Muestra una Forma de cobro registrada
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @return array con los registros a mostrar
     */
    public function getCurrencie()
    {
        return template_choices('Modules\Sale\Models\SaleFormPayment', ['name_form_payment', '-', 'description_form_payment'], '', true);
    }

    /**
     * Obtiene los bancos registrados
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @return array
     */
    public function getSaleBank()
    {

        return template_choices('Modules\Finance\Models\FinanceBank', ['code', '-', 'name', '-', 'short_name'], '', true);
    }

    /**
     * Obtiene los servicios registrados
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  integer $id Identificador del servicio
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los servicios
     */
    public function getSaleService($id)
    {
        $saleService = SaleService::with(['id', 'code', 'status', 'sale_goods_to_be_traded', 'sale_client_id'])->find($id);
        return response()->json(['sale_service' => $saleService], 200);
    }

    /**
     * Obtiene los Datos de la solicitud de servicios registrados
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  integer $id Identificador del servicio
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la solicitud de servicios
     */
    public function getSaleGoodsToBeTraded($id)
    {
        $sale_goods_to_be_traded = SaleGoodsToBeTraded::with([
            'name',
            'description',
            'unit_price',
            'currency_id',
            'measurement_unit_id',
            'department_id',
            'sale_type_good_id',
            'payroll_staff_id'
        ])->find($id);
        return response()->json(['sale_goods_to_be_traded' => $sale_goods_to_be_traded], 200);
    }

    /**
     * Obtiene los clientes registrados
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  integer $id Identificador del cliente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleClient($id)
    {
        $SaleService = SaleService::find($id);
        //datos del cliente
        $saleClients = SaleClient::find($SaleService->sale_client_id);
        //email
        $saleClientsEmail = SaleClientsEmail::find($SaleService->sale_client_id);
        //teléfono
        $saleClientsPhone = Phone::find($SaleService->sale_client_id);
        $sale_goods_to_be_traded_count = count($SaleService->sale_goods_to_be_traded);
        for ($i = 0; $i < $sale_goods_to_be_traded_count; $i++) {
            //Consulta valor de servicio segun el id de servicios
            $SaleGoodsToBeTraded = SaleGoodsToBeTraded::find($SaleService->sale_goods_to_be_traded[$i]);
            // valor de impuesto
            if ($SaleGoodsToBeTraded->history_tax_id) {
                $HistoryTax = HistoryTax::find($SaleGoodsToBeTraded->history_tax_id);
                // valor de servicio con impuesto
                $porcentaje = ((float)$HistoryTax->percentage * $SaleGoodsToBeTraded->unit_price) / 100;
            } else {
                $porcentaje = 0;
            }
            //total de servicio + impuesto
            $sumatoria[$i] = $porcentaje + $SaleGoodsToBeTraded->unit_price;
        };
        $total = array_sum($sumatoria);
        $value = [
            'code' =>  $SaleService->code,
            'total' =>  $total,
            'name' =>  $saleClients->name,
            'idntifiaction' =>  $saleClients->id_type,
            'identification_number' =>  $saleClients->id_number,
            'rif' =>  $saleClients->rif,
            'email' =>  $saleClientsEmail->email,
            'phone_extension' =>  $saleClientsPhone->extension,
            'phone_area_code' =>  $saleClientsPhone->area_code,
            'phone_number' =>  $saleClientsPhone->number
        ];
        return response()->json(['sale_service' => $value], 200);
    }

    /**
     * Aprueba la solicitud realizada
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  integer $id Identificador del pago
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los clientes
     */
    public function approvedPayment($id)
    {
        $payment = SaleRegisterPayment::find($id);
        if (!$payment->payment_refuse and !$payment->payment_approve) {
            $payment->payment_approve = true;
            $payment->save();
            return response()->json(['record' => $payment, 'message' => 'Success', 'redirect' => route('sale.payment.index')], 200);
        }
        return response()->json(['message' => 'error al guardar', 'redirect' => route('sale.payment.index')], 404);
    }
    /**
     * Rechaza la solicitud realizada
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  integer $id Identificador del pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refusePayment($id)
    {
        $payment = SaleRegisterPayment::find($id);
        if (!$payment->payment_approve and !$payment->payment_refuse) {
            $payment->payment_refuse = true;
            $payment->save();
            return response()->json(['record' => $payment, 'message' => 'Success', 'redirect' => route('sale.payment.index')], 200);
        }
        return response()->json(['message' => 'error al guardar', 'redirect' => route('sale.payment.index')], 404);
    }

    /**
     * Genera el reporte del pago
     *
     * @author Miguel Narvaez <mnarvaezcenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request  datos de la petición
     * @param  integer $id Identificador del pago
     *
     * @return void
     */
    public function pdfGenerator(Request $request, $id)
    {

        $SalePayment = SaleRegisterPayment::find($id);

        /* base para generar el pdf del recibo de pago */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;
        $is_admin = auth()->user()->isAdmin();
        $user = auth()->user();

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if (!$is_admin && $user->profile && $user->profile->institution_id) {
            $institution = Institution::find($user->profile->institution_id);
        } else {
            $institution = '';
        }

        $pdf->setConfig(['institution' => Institution::first()]);
        $pdf->setHeader('Pago Registrado');
        $pdf->setFooter();
        $pdf->setBody('sale::pdf.payment_record_information', true, [
            'pdf'         => $pdf,
            'SalePayment' => $SalePayment
        ]);
    }
}
