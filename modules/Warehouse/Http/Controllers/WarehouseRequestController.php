<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseInventoryProductRequest;
use Modules\Warehouse\Models\WarehouseRequest;
use Modules\Warehouse\Models\WarehouseMovement;
use Modules\Warehouse\Models\WarehouseInventoryProductMovement;

/**
 * @class WarehouseRequestController
 * @brief Controlador de solicitudes de almacén
 *
 * Clase que gestiona las solicitudes de los productos de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseRequestController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:warehouse.request.list', ['only' => 'index']);
        $this->middleware('permission:warehouse.request.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse.request.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.request.delete', ['only' => 'destroy']);
        $this->middleware('permission:warehouse.setting.product.delivery', ['only' => 'confirmRequest']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'warehouse_products.*'           => ['required'],
            'budget_specific_action_id'      => ['required'],
            'department_id'                  => ['required'],
            'motive'                         => ['required'],
            'request_date'                   => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'budget_specific_action_id.required' => 'El campo "Acción específica" es obligatorio',
            'department_id.required'             => 'El campo "Dependencia solicitante" es obligatorio',
            'motive.required'                    => 'El campo "Motivo de la solicitud" es obligatorio',
            'request_date.required'              => 'El campo "Fecha de la solicitud" es obligatorio'
        ];
    }

    /**
     * Muestra un listado de las solicitudes de almacén registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('warehouse::requests.list');
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('warehouse::requests.create');
    }

    /**
     * Valida y registra una nueva solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validateRules = $this->validateRules;
        $messages = $this->messages;
        for ($i = 0; $i < count($request->warehouse_products); $i++) {
            $validateRules = array_merge($validateRules, [
                'warehouse_products.' . $i . '.requested'
                => ['required', 'max:' . WarehouseInventoryProduct::find($request->warehouse_products[$i]['id'])->real],
            ]);
            $products = WarehouseInventoryProduct::where('id', $request->warehouse_products[$i]['id'])
                ->with('warehouseProduct')->first();

            $messages = array_merge($messages, [
                'warehouse_products.' . $i . '.requested.max'
                => 'El producto "' . $products->warehouseProduct->name . '" no posee suficiente existencia en almacén'
            ]);
        }

        $this->validate($request, $validateRules, $messages);

        $codeSetting = CodeSetting::where('table', 'warehouse_requests')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false,
                'redirect' => route('warehouse.setting.index')], 200);
        }
        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])
            ->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            WarehouseRequest::class,
            $codeSetting->field
        );

        DB::transaction(function () use ($request, $code) {
            $data_request = WarehouseRequest::create([
                'code' => $code,
                'request_date' => $request->input('request_date'),
                'state' => 'Pendiente',
                'motive' => $request->input('motive'),
                'budget_specific_action_id' => $request->input('budget_specific_action_id'),
                'department_id' => $request->input('department_id'),
                'payroll_staff_id' => $request->input('payroll_staff_id'),
            ]);

            foreach ($request->warehouse_products as $product) {
                $inventory_product = WarehouseInventoryProduct::find($product['id']);
                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;
                    if ($exist_real >= $product['requested']) {
                        WarehouseInventoryProductRequest::create([
                            'warehouse_inventory_product_id' => $inventory_product->id,
                            'warehouse_request_id' => $data_request->id,
                            'quantity' => $product['requested'],
                            'new_exist' => $exist_real - $product['requested'],
                        ]);
                    } else {
                        /* Si la exitencia del producto es menor que lo que se solicita se revierten los cambios */
                        DB::rollback();
                    }
                } else {
                    /* Si no existe el registro en inventario se revierten los cambios */
                    DB::rollback();
                }
            }
        });
        $warehouse_request = WarehouseRequest::where('code', $code)->first();
        if (is_null($warehouse_request)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación.'
                ]
            );
        } else {
            $request->session()->flash('message', ['type' => 'store']);
        }
        return response()->json([
            'result' => true, 'redirect' =>
            route('warehouse.request.index')], 200);
    }

    /**
     * Muestra el formulario para editar una solicitud de  almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del ingreso de almacén
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $warehouse_request = WarehouseRequest::find($id);
        return view('warehouse::requests.create', compact('warehouse_request'));
    }

    /**
     * Actualiza la información de las solicitudes de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $warehouse_request = WarehouseRequest::find($id);

        $validateRules = $this->validateRules;
        $messages = $this->messages;
        for ($i = 0; $i < count($request->warehouse_products); $i++) {
            $validateRules = array_merge($validateRules, [
                'warehouse_products.' . $i . '.requested' =>
                 ['required', 'max:' . WarehouseInventoryProduct::find($request->warehouse_products[$i]['id'])->real],
            ]);
            $warehouseProductId = $request->warehouse_products[$i]['id'];
            $products = WarehouseInventoryProduct::where('id', $warehouseProductId)->with('warehouseProduct')->first();

            $messages = array_merge($messages, [
                'warehouse_products.' . $i . '.requested.max' =>
                'El producto "' . $products->warehouseProduct->name . '" no posee suficiente existencia en almacén'
            ]);
        }

        $this->validate($request, $validateRules, $messages);

        DB::transaction(function () use ($request, $warehouse_request) {
            $warehouse_request->request_date = $request->input('request_date');
            $warehouse_request->motive = $request->input('motive');
            $warehouse_request->budget_specific_action_id = $request->input('budget_specific_action_id');
            $warehouse_request->department_id = $request->input('department_id');
            $warehouse_request->save();
            $update = now();

            /* Se agregan los nuevos elementos a la solicitud */
            foreach ($request->warehouse_products as $product) {
                $inventory_product = WarehouseInventoryProduct::find($product['id']);
                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;
                    if ($exist_real >= $product['requested']) {
                        $old_request = WarehouseInventoryProductRequest::where(
                            'warehouse_request_id',
                            $warehouse_request->id
                        )
                            ->where('warehouse_inventory_product_id', $inventory_product->id)->first();
                        if (!is_null($old_request)) {
                            $old_request->quantity = $product['requested'];
                            $old_request->updated_at = $update;
                            $old_request->new_exist = $exist_real - $product['requested'];
                            $old_request->save();
                        } else {
                            WarehouseInventoryProductRequest::create([
                                'warehouse_inventory_product_id' => $inventory_product->id,
                                'warehouse_request_id' => $warehouse_request->id,
                                'quantity' => $product['requested'],
                                'updated_at' => $update,
                                'new_exist' => $exist_real - $product['requested'],
                            ]);
                        }
                    } else {
                        /* Si la exitencia del producto es menor que lo que se solicita se revierten los cambios */
                        DB::rollback();
                    }
                }
            };

            /* Se eliminan los demas elementos de la solicitud */
            $warehouse_request_products = WarehouseInventoryProductRequest::where(
                'warehouse_request_id',
                $warehouse_request->id
            )->where('updated_at', '!=', $update)->get();

            foreach ($warehouse_request_products as $warehouse_request_product) {
                $warehouse_request_product->delete();
            }
        });
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true, 'redirect' => route('warehouse.request.index')
        ], 200);
    }

    /**
     * Confirma la entrega de una solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function confirmRequest(Request $request, $id)
    {
        $warehouse_request = WarehouseRequest::find($id);
        if (!is_null($warehouse_request)) {
            $warehouse_request->observations = !empty($request->observations) ? $request->observations : 'N/A';
            $warehouse_request->delivered = true;
            $warehouse_request->state = 'Entregado';
            $warehouse_request->delivery_date = now();
            $warehouse_request->save();
            $request->session()->flash('message', ['type' => 'update']);
            return response()->json([
                'result' => true,
                'redirect' => route('warehouse.request.index')
            ], 200);
        }
    }

    /**
     * Rechaza la solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectedRequest(Request $request, $id)
    {
        $warehouse_request = WarehouseRequest::find($id);
        $warehouse_request->state = 'Rechazado';
        $warehouse_request->save();
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true, 'redirect' => route('warehouse.request.index')
        ], 200);
    }

    /**
     * Aprueba la solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedRequest(Request $request, $id)
    {
        $warehouse_request = WarehouseRequest::find($id);
        /* verificar si aun no se a ejecutado la solicitud */
        if ($warehouse_request['state'] != 'Aprobado') {
            DB::transaction(function () use ($warehouse_request) {
                $warehouse_request->state = 'Aprobado';
                $warehouse_request->save();
                $warehouse_request_products = $warehouse_request->WarehouseInventoryProductRequests;
                foreach ($warehouse_request_products as $warehouse_request_product) {
                    $warehouse_inventory_product =
                        WarehouseInventoryProduct::find($warehouse_request_product->warehouse_inventory_product_id);
                    if (!is_null($warehouse_inventory_product)) {
                        $exist_real =
                            $warehouse_inventory_product->exist - $warehouse_inventory_product->reserved;
                        if ($exist_real < $warehouse_request_product->quantity) {
                            /* Si la exitencia del producto es menor que lo que solicitamos se revierten los cambios */
                            DB::rollback();
                        } else {
                            if ($warehouse_inventory_product->reserved > 0) {
                                $warehouse_inventory_product->reserved +=
                                    $warehouse_request_product->quantity;
                                $warehouse_inventory_product->save();
                            } else {
                                $warehouse_inventory_product->reserved =
                                    $warehouse_request_product->quantity;
                                $warehouse_inventory_product->save();
                            }
                        };
                    } else {
                        /* Si no existe el registro en inventario se revierten los cambios */
                        DB::rollback();
                    }
                }
            });

            if ($warehouse_request->state != 'Aprobado') {
                $request->session()->flash(
                    'message',
                    [
                        'type' => 'other',
                        'title' => 'Alerta',
                        'icon' => 'screen-error',
                        'class' => 'growl-danger',
                        'text' => 'No se pudo completar la operación.'
                    ]
                );
            } else {
                $request->session()->flash('message', ['type' => 'update']);
            }
        } else { /* En caso contrario la solicitud ya fue ejecutada */
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación porque ya fue aceptada la solicitud.'
                ]
            );
        }
        return response()->json([
            'result' => true, 'redirect' => route('warehouse.request.index')
        ], 200);
    }

    /**
     * Elimina una solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $warehouse_request = WarehouseRequest::find($id);
        $warehouse_request->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene la información de los productos inventariados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInventoryProduct($requestid)
    {
        $reserved = 0;
        $records = [];
        $productsQuantity = [];
        $codeEdit = [];
        $queryRecords = WarehouseInventoryProduct::whereNotNull('exist')
        ->with(['warehouseProductValues' => function ($query) {
            $query->with('warehouseProductAttribute');
        }, 'currency', 'warehouseProduct.measurementUnit', 'warehouseInstitutionWarehouse' => function ($query) {
            $query->with('warehouse');
        }, 'warehouseInventoryRule'])->get();

        foreach ($queryRecords as $queryRecord) {
            if ($queryRecord->real != 0) {
                /*Calculo de cantidad de productos pendiente */
                /* Total de producto pendiente por solicitud */
                $totalQuantity = 0;
                $productRequests = WarehouseRequest::where('state', 'Pendiente')
                ->with(['warehouseInventoryProductRequests' => function ($q) use ($queryRecord) {
                        $q->with(['warehouseInventoryProduct' => function ($qq) use ($queryRecord) {
                            $qq->where('code', $queryRecord->code);
                        }])->where('warehouse_inventory_product_id', $queryRecord->id);
                }])->get();

                foreach ($productRequests as $productRequest) {
                    if ((count($productRequest['warehouseInventoryProductRequests'])) > 0) {
                        /*Verifica código de productos a editar*/
                        if (intval($requestid) == $productRequest->id) {
                            array_push($codeEdit, $queryRecord->code);
                        } else {
                            foreach ($productRequest['warehouseInventoryProductRequests'] as $product) {
                                $totalQuantity = $totalQuantity + $product['quantity'];
                            }
                        }
                    }
                }
                /* Total de producto pendiente por movimiento */
                $code = $queryRecord->code;
                $idInventoryProduct = $queryRecord->id;
                $totalQuantityMovement = 0;
                $queryRecordsMovement = WarehouseMovement::where('state', 'Pendiente')
                    ->whereNotNull('warehouse_institution_warehouse_initial_id')
                    ->with(['warehouseInventoryProductMovements' => function ($q) use ($idInventoryProduct) {
                        $q->where('warehouse_initial_inventory_product_id', $idInventoryProduct);
                    }])->get();

                foreach ($queryRecordsMovement as $queryRecordMovement) {
                    if (count($queryRecordMovement->warehouseInventoryProductMovements) > 0) {
                        foreach ($queryRecordMovement->warehouseInventoryProductMovements as $productMovement) {
                                $totalQuantityMovement = $totalQuantityMovement + $productMovement['quantity'];
                        }
                    }
                }

                if ($queryRecord->reserved == null) {
                    $reserved = 0;
                } else {
                    $reserved = $queryRecord->reserved;
                }
                /* Si Edita solicitud no cumplir regla en mostrar producto */
                if (in_array($queryRecord->code, $codeEdit)) {
                    array_push($productsQuantity, [
                        'id'       => $queryRecord->id,
                        'code'     => $queryRecord->code,
                        'quantity' => $totalQuantity + $totalQuantityMovement]);
                    array_push($records, $queryRecord);
                } else { /* En caso contrario cumplir la regla en mostrar producto */
                    if ($queryRecord->real - ($totalQuantity  + $totalQuantityMovement) > 0) {
                        array_push($productsQuantity, [
                            'id'       => $queryRecord->id,
                            'code'     => $queryRecord->code,
                            'quantity' => $totalQuantity + $totalQuantityMovement]);
                        array_push($records, $queryRecord);
                    }
                }
            }
        }
        return response()->json([
            'records' => $records, 'productsQuantity' => $productsQuantity
        ], 200);
    }

    /**
     * Vizualiza información de una solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        return response()->json(['records' => WarehouseRequest::where('id', $id)->with(
            [
                'budgetSpecificAction',
                'payrollStaff',
                'department',
                'warehouseInventoryProductRequests' => function ($query) {
                    $query->with(['warehouseInventoryProduct' => function ($query) {
                        $query->with(['warehouseProduct' => function ($query) {
                            $query->with('measurementUnit');
                        }, 'currency']);
                    }]);
                }
            ]
        )->first()], 200);
    }

    /**
     * Obtiene un listado de las solicitudes de almacén registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $warehouse_requests = WarehouseRequest::with('department')
            ->whereNotNull('budget_specific_action_id')
            ->get();
        return response()->json(['records' => $warehouse_requests], 200);
    }

    /**
     * Obtiene un listado de las solicitudes de almacén pendientes
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vuePendingList()
    {
        $warehouse_requests = WarehouseRequest::with('department', 'payrollStaff')
            ->get();
        return response()->json(['records' => $warehouse_requests], 200);
    }
}
