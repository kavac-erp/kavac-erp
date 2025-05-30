<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Modules\Warehouse\Models\WarehouseRequest;
use Modules\Warehouse\Models\WarehouseInventoryProductMovement;
use Modules\Warehouse\Models\WarehouseInstitutionWarehouse;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseInventoryRule;
use Modules\Warehouse\Models\WarehouseProductAttribute;
use Modules\Warehouse\Models\WarehouseProductValue;
use Modules\Warehouse\Models\WarehouseMovement;

/**
 * @class WarehouseMovementController
 * @brief Controlador de los movimientos de productos entre almacenes
 *
 * Clase que gestiona los movimientos de los productos registrados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseMovementController extends Controller
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
        $this->middleware('permission:warehouse.movement.list', ['only' => 'index']);
        $this->middleware('permission:warehouse.movement.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse.movement.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.movement.delete', ['only' => 'destroy']);
        $this->middleware('permission:warehouse.movement.approve', ['only' => 'approvedMovement']);
        $this->middleware('permission:warehouse.movement.decline', ['only' => 'rejectedMovement']);
        $this->middleware('permission:warehouse.movement.confirm', ['only' => 'confirmMovement']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'description'            => ['required'],
            'initial_warehouse_id'   => ['required'],
            'end_warehouse_id'       => ['required'],
            'initial_institution_id' => ['required'],
            'end_institution_id'     => ['required'],
            'warehouse_inventory_products'  => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'description.required'            => 'El campo descripción es obligatorio.',
            'initial_warehouse_id.required'   => 'El campo nombre del almacén de origen es obligatorio.',
            'end_warehouse_id.required'       => 'El campo nombre del almacén destino es obligatorio.',
            'initial_institution_id.required' => 'El campo nombre de la organización de origen es obligatorio.',
            'end_institution_id.required'     => 'El campo nombre de la organización de destino es obligatorio. ',
            'warehouse_inventory_products.required' =>
            'Debe ingresar la cantidad solicitada para cada insumo seleccionado.'
        ];
    }

    /**
     * Muestra un listado de los movimientos de almacén registrados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('warehouse::movements.list');
    }

    /**
     * Muestra el formulario para registrar un nuevo Movimiento de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('warehouse::movements.create');
    }

    /**
     * Valida y Registra un nuevo Movimiento de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $codeSetting = CodeSetting::where('table', 'warehouse_movements')->first();

        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('warehouse.setting.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            WarehouseMovement::class,
            $codeSetting->field
        );

        $initial_inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $request->initial_warehouse_id)
            ->where('institution_id', $request->initial_institution_id)->first();

        $end_inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $request->end_warehouse_id)
            ->where('institution_id', $request->end_institution_id)->first();

        DB::transaction(function () use ($request, $initial_inst_ware, $end_inst_ware, $code) {
            $movement = WarehouseMovement::create([
                'code'                                       => $code,
                'type'                                       => 'M',
                'description'                                => $request->description,
                'state'                                      => 'Pendiente',
                'warehouse_institution_warehouse_initial_id' => ($initial_inst_ware) ? $initial_inst_ware->id : null,
                'warehouse_institution_warehouse_end_id'     => ($end_inst_ware) ? $end_inst_ware->id : null,
                'user_id'                                    => Auth::id(),
            ]);
            $equal = null;

            foreach ($request->warehouse_inventory_products as $product) {
                $minimum = $product['minimum'];
                $maximum = $product['maximum'];

                $inventory_product_init = WarehouseInventoryProduct::
                with('warehouseInventoryRule')->find($product['id']);
                if (!is_null($inventory_product_init)) {
                    $exist_real = $inventory_product_init->exist - $inventory_product_init->reserved;
                    if ($exist_real >= $product['movemented']) {
                        /* Se verifica si el almacén destino tiene un registro previo del producto movilizado */
                        $products_inventory = WarehouseInventoryProduct::with('warehouseInventoryRule')
                            ->where(
                                'warehouse_institution_warehouse_id',
                                $end_inst_ware->id
                            )->where('warehouse_product_id', $inventory_product_init->warehouse_product_id)
                            ->where('unit_value', $inventory_product_init->unit_value)->get();

                        /* Si existe se comparan los atributos perzonalizados si los tiene */
                        if ((count($products_inventory) > 0)) {
                            foreach ($products_inventory as $inventory_product_finish) {
                                /* Define si los atributos coinciden con los registrados */
                                $equal = true;

                                /* Se verifica que tengan los mismos atributos */
                                $attributes = WarehouseProductAttribute::where(
                                    'warehouse_product_id',
                                    $inventory_product_init->warehouse_product_id
                                )
                                    ->with('warehouseProductValue')->get();

                                foreach ($attributes as $attribute) {
                                    $value_init = WarehouseProductValue::where(
                                        'warehouse_product_attribute_id',
                                        $attribute->id
                                    )->where('warehouse_inventory_product_id', $inventory_product_init->id)
                                        ->first();

                                    $value_finish = WarehouseProductValue::where(
                                        'warehouse_product_attribute_id',
                                        $attribute->id
                                    )->where('warehouse_inventory_product_id', $inventory_product_finish->id)
                                        ->first();

                                    /** Si algun atributo no existe o es diferente termina el ciclo de control */
                                    if (
                                        is_null($value_init) || is_null($value_finish)
                                        || ($value_init != $value_finish)
                                    ) {
                                        $equal = false;
                                        break;
                                    }
                                }
                                /* Si todos los atributos son iguales se genera el movimiento */
                                if ($equal == true) {
                                    /* Se actualiza la regla de abastecimiento */
                                    if (isset($minimum)) {
                                        $rule = WarehouseInventoryRule::updateOrCreate([
                                            'warehouse_inventory_product_id' => $inventory_product_finish->id,
                                        ], [
                                            'minimum' => $minimum,
                                            'maximum' => $maximum,
                                            'user_id' => Auth::id(),
                                        ]);
                                    }

                                    $inventory_movement = WarehouseInventoryProductMovement::create([
                                        'quantity'                               => $product['movemented'],
                                        'new_value'                              => $inventory_product_init->unit_value,
                                        'warehouse_movement_id'                  => $movement->id,
                                        'warehouse_initial_inventory_product_id' => $inventory_product_init->id,
                                        'warehouse_inventory_product_id'         => $inventory_product_finish->id,
                                    ]);
                                    break;
                                }
                            }
                        }
                        /* Si no existe registro previo se genera un nuevo registro de inventario y un movimiento */
                        if ((count($products_inventory) == 0) || ($equal == false)) {
                            $codeSetting = CodeSetting::where('table', 'warehouse_inventory_products')->first();

                            $currentFiscalYear = FiscalYear::select('year')
                                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

                            $codep  = generate_registration_code(
                                $codeSetting->format_prefix,
                                strlen($codeSetting->format_digits),
                                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                                    $currentFiscalYear->year : date('Y')),
                                $codeSetting->model,
                                $codeSetting->field
                            );

                            /* Se declara la existencia en null hasta que se confirme la operación */
                            $inventory_product_finish = WarehouseInventoryProduct::create([
                                'code' => $codep,
                                'warehouse_product_id' => $inventory_product_init->warehouse_product_id,
                                'unit_value' => $inventory_product_init->unit_value,
                                'currency_id' => $inventory_product_init->currency_id,
                                'warehouse_institution_warehouse_id' => $end_inst_ware->id,
                            ]);

                            if (isset($minimum)) {
                                $rule = WarehouseInventoryRule::create([
                                    'minimum' => $minimum,
                                    'maximum' => $maximum,
                                    'warehouse_inventory_product_id' => $inventory_product_finish->id,
                                    'user_id' => Auth::id(),
                                ]);
                            }

                            /* Se genera el movimiento */
                            $inventory_movement = WarehouseInventoryProductMovement::create([
                                'quantity'                               => $product['movemented'],
                                'new_value'                              => $inventory_product_init->unit_value,
                                'warehouse_movement_id'                  => $movement->id,
                                'warehouse_initial_inventory_product_id' => $inventory_product_init->id,
                                'warehouse_inventory_product_id'         => $inventory_product_finish->id,
                            ]);
                        }
                    } else {
                        /* Si la exitencia del producto es menor que lo que queremos desplazar se revierten los cambios */
                        DB::rollback();
                    }
                } else {
                    /*
                     | Si no existe un registro del producto en el almacén inicial a ocurrido un error
                     | se revierten los cambios
                     */
                    DB::rollback();
                }
            }
        });
        $warehouse_movement = WarehouseMovement::where('code', $code)->first();
        if (is_null($warehouse_movement)) {
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
        return response()->json(['result' => true, 'redirect' => route('warehouse.movement.index')], 200);
    }

    /**
     * Muestra el formulario para editar un movimiento de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $movement = WarehouseMovement::find($id);
        return view('warehouse::movements.create', compact('movement'));
    }

    /**
     * Actualiza la información de los movimientos de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $movement = WarehouseMovement::find($id);

        $this->validate($request, $this->validateRules, $this->messages);

        $end_inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $request->end_warehouse_id)
            ->where('institution_id', $request->end_institution_id)->first();

        DB::transaction(function () use ($request, $movement, $end_inst_ware) {
            $movement->description = $request->description;
            $movement->save();
            $equal = null;

            $update = now();

            /* Se agregan los nuevos elementos a la solicitud */
            foreach ($request->warehouse_inventory_products as $product) {
                $minimum = $product['minimum'];
                $maximum = $product['maximum'];
                $inventory_product_init = WarehouseInventoryProduct::find($product['id']);
                if (!is_null($inventory_product_init)) {
                    $exist_real = $inventory_product_init->exist - $inventory_product_init->reserved;
                    $old_product_movement = WarehouseInventoryProductMovement::where(
                        'warehouse_initial_inventory_product_id',
                        $inventory_product_init->id
                    )->where('warehouse_movement_id', $movement->id)->first();

                    if (!is_null($old_product_movement)) {
                        $old_product_movement->quantity = $product['movemented'];
                        $old_product_movement->updated_at = $update;
                        $old_product_movement->save();
                    } elseif ($exist_real >= $product['movemented']) {
                        /* Se verifica si el almacén destino tiene un registro previo del producto movilizado */
                        $products_inventory = WarehouseInventoryProduct::where(
                            'warehouse_institution_warehouse_id',
                            $end_inst_ware->id
                        )->where(
                            'warehouse_product_id',
                            $inventory_product_init->warehouse_product_id
                        )->where('unit_value', $inventory_product_init->unit_value)->get();

                        /* Si existe un registro se comparan los atributos perzonalizados, si los tiene */
                        if (!empty($products_inventory)) {
                            foreach ($products_inventory as $inventory_product_finish) {
                                /** @var boolean $equal Define si los atributos coinciden con los registrados */
                                $equal = true;

                                $attributes = WarehouseProductAttribute::where(
                                    'warehouse_product_id',
                                    $inventory_product_init->warehouse_product_id
                                )->with('warehouseProductValue')->get();

                                foreach ($attributes as $attribute) {
                                    $value_init = WarehouseProductValue::where(
                                        'warehouse_product_attribute_id',
                                        $attribute->id
                                    )->where(
                                        'warehouse_inventory_product_id',
                                        $inventory_product_init->id
                                    )->first();

                                    $value_finish = WarehouseProductValue::where(
                                        'warehouse_product_attribute_id',
                                        $attribute->id
                                    )->where(
                                        'warehouse_inventory_product_id',
                                        $inventory_product_finish->id
                                    )->first();

                                    /* Si algun atributo no existe o es diferente termina el ciclo de control */
                                    if (
                                        !(!is_null($value_init) && (!is_null($value_finish)) &&
                                        ($value_init == $value_finish))
                                    ) {
                                        $equal = false;
                                        break;
                                    }
                                }
                                /** Si todos los atributos son iguales se genera el movimiento */
                                if ($equal == true) {
                                    $inventory_movement = WarehouseInventoryProductMovement::create([
                                        'quantity' => $product['movemented'],
                                        'new_value' => $inventory_product_init->unit_value,
                                        'warehouse_movement_id' => $movement->id,
                                        'warehouse_initial_inventory_product_id' => $inventory_product_init->id,
                                        'warehouse_inventory_product_id' => $inventory_product_finish->id,
                                        'updated_at' => $update,
                                    ]);
                                    break;
                                }
                            }
                        }
                        /* Si no existe registro previo se genera un nuevo registro de inventario y un movimiento */
                        if (empty($products_inventory) || $equal == false) {
                            $codeSetting = CodeSetting::where('table', 'warehouse_inventory_products')->first();

                            $currentFiscalYear = FiscalYear::select('year')
                                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

                            $codep  = generate_registration_code(
                                $codeSetting->format_prefix,
                                strlen($codeSetting->format_digits),
                                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                                    $currentFiscalYear->year : date('Y')),
                                $codeSetting->model,
                                $codeSetting->field
                            );

                            /* Se declara la existencia en null hasta que se confirme la operación */
                            $inventory_product_finish = WarehouseInventoryProduct::create([
                                'code' => $codep,
                                'warehouse_product_id' => $inventory_product_init->warehouse_product_id,
                                'unit_value' => $inventory_product_init->unit_value,
                                'currency_id' => $inventory_product_init->currency_id,
                                'warehouse_institution_warehouse_id' => $end_inst_ware->id,
                            ]);

                            /* Se genera el movimiento */
                            $inventory_movement = WarehouseInventoryProductMovement::create([
                                'quantity' => $product['movemented'],
                                'new_value' => $inventory_product_init->unit_value,
                                'warehouse_movement_id' => $movement->id,
                                'warehouse_initial_inventory_product_id' => $inventory_product_init->id,
                                'warehouse_inventory_product_id' => $inventory_product_finish->id,
                                'updated_at' => $update,
                            ]);
                        }
                    } else {
                        /* Si la exitencia del producto es menor que lo que queremos desplazar se revierten los cambios */
                        DB::rollback();
                    }
                } else {
                    /* Si no existe un registro del producto en el almacén inicial a ocurrido un error */
                    DB::rollback();
                }
            }

            /* Se eliminan los demas elementos de la solicitud */
            $warehouse_inventory_product_movements = WarehouseInventoryProductMovement::where(
                'warehouse_movement_id',
                $movement->id
            )->where('updated_at', '!=', $update)->get();

            foreach ($warehouse_inventory_product_movements as $warehouse_inventory_product_movement) {
                $warehouse_inventory_product_movement->delete();
            }
        });
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('warehouse.movement.index')], 200);
    }

    /**
     * Elimina un movimiento de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $warehouse_movement = WarehouseMovement::find($id);
        $warehouse_movement->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Lista de movimientos de almacén
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        $warehouse_movements = WarehouseMovement::whereNotNull('warehouse_institution_warehouse_initial_id')
            ->with(
                'warehouseInstitutionWarehouseInitial',
                'warehouseInstitutionWarehouseEnd'
            )->get();
        return response()->json(['records' => $warehouse_movements], 200);
    }

    /**
     * Lista de productos de almacén
     *
     * @param integer $warehouse Identificador del almacén
     * @param integer $institution Identificador de la institución
     * @param integer $movementid Identificador del movimiento
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListProducts($warehouse, $institution, $movementid)
    {
        $inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $warehouse)
            ->where('institution_id', $institution)->first();
        $records = [];
        $productsQuantity = [];
        $codeEdit = [];

        if ($inst_ware) {
            $warehouse_product = WarehouseInventoryProduct::
            whereNotNull('exist')->where('warehouse_institution_warehouse_id', $inst_ware->id)
                ->with(['warehouseProductValues' => function ($query) {
                    $query->with('warehouseProductAttribute');
                }, 'currency',
                'warehouseProduct.measurementUnit', 'warehouseInstitutionWarehouse' => function ($query) {
                    $query->with('warehouse');
                }, 'warehouseInventoryRule'])->get();

            foreach ($warehouse_product as $queryRecord) {
                if ($queryRecord->real != 0) {
                    /* Calculo de cantidad de productos pendiente */
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
                            foreach ($productRequest['warehouseInventoryProductRequests'] as $product) {
                                $totalQuantity = $totalQuantity + $product['quantity'];
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
                            /* Verifica código de productos a editar */
                            if (intval($movementid) == $queryRecordMovement->id) {
                                array_push($codeEdit, $queryRecord->code);
                            } else {
                                foreach ($queryRecordMovement->warehouseInventoryProductMovements as $productMovement) {
                                        $totalQuantityMovement = $totalQuantityMovement + $productMovement['quantity'];
                                }
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
                        //array_push($records, $queryRecord);
                    } else { /* En caso contrario cumplir la regla en mostrar producto */
                        if ($queryRecord->real - ($totalQuantity  + $totalQuantityMovement) > 0) {
                            array_push($productsQuantity, [
                                'id'       => $queryRecord->id,
                                'code'     => $queryRecord->code,
                                'quantity' => $totalQuantity + $totalQuantityMovement]);
                            array_push($records, $queryRecord);
                        }
                        //array_push($records, $queryRecord);
                    }
                }
            }
            return response()->json(['records' => $records, 'productsQuantity' => $productsQuantity], 200);
        }
        return response()->json(['records' => $records, 'productsQuantity' => $productsQuantity], 200);
    }

    /**
     * Vizualiza la información de un movimientro entre almacenes
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        return response()->json([
            'records' => WarehouseMovement::where('id', $id)
                ->with(
                    ['warehouseInventoryProductMovements' => function ($query) {
                        $query->with(['warehouseInventoryProduct' => function ($query) {
                            $query->with(['warehouseProduct' => function ($query) {
                                $query->with('measurementUnit');
                            }, 'warehouseProductValues' => function ($query) {
                                $query->with('warehouseProductAttribute');
                            }, 'currency']);
                        }]);
                    }, 'warehouseInstitutionWarehouseInitial', 'warehouseInstitutionWarehouseEnd']
                )->first()
        ], 200);
    }

    /**
     * Confirma la solicitud de un movimientro entre almacenes
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmMovement(Request $request, $id)
    {
        $warehouse_movement = WarehouseMovement::find($id);
        DB::transaction(function () use ($warehouse_movement, $request) {
            $warehouse_movement->observations = !empty($request->observations) ? $request->observations : 'N/A';
            $warehouse_movement->state = 'Confirmado';
            $warehouse_movement->save();

            $warehouse_inventory_product_movements = $warehouse_movement->WarehouseInventoryProductMovements;

            foreach ($warehouse_inventory_product_movements as $warehouse_inventory_product_movement) {
                $warehouse_inventory_product = $warehouse_inventory_product_movement->WarehouseInventoryProduct;
                $warehouse_initial_inventory_product =
                    $warehouse_inventory_product_movement->WarehouseInitialInventoryProduct;
                /* Se agregan los nuevos productos a la existencia del inventario final */
                if ($warehouse_inventory_product->exist > 0) {
                    $warehouse_inventory_product->exist +=
                        $warehouse_inventory_product_movement->quantity;
                    $warehouse_inventory_product->save();
                } else {
                    $warehouse_inventory_product->exist =
                        $warehouse_inventory_product_movement->quantity;
                    $warehouse_inventory_product->save();
                }

                /* Se extraen los productos del inventario inicial */
                if (
                    ($warehouse_initial_inventory_product->reserved >= $warehouse_inventory_product_movement->quantity)
                    && ($warehouse_initial_inventory_product->exist >= $warehouse_inventory_product_movement->quantity)
                ) {
                    $warehouse_initial_inventory_product->reserved -=
                        $warehouse_inventory_product_movement->quantity;
                    $warehouse_initial_inventory_product->exist -=
                        $warehouse_inventory_product_movement->quantity;
                    $warehouse_initial_inventory_product->save();
                } else {
                    /* Si la exitencia del producto es menor que lo que queremos desplazar se revierten los cambios */
                    DB::rollback();
                }
            }
        });
        /* Si no se completo la operación, informo sobre el error */
        if ($warehouse_movement->state != 'Confirmado') {
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
        return response()->json(['result' => true, 'redirect' => route('warehouse.movement.index')], 200);
    }

    /**
     * Rechaza la solicitud de un movimientro entre almacenes
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param   $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectedMovement(Request $request, $id)
    {
        $warehouse_movement = WarehouseMovement::find($id);
        $warehouse_movement->state = 'Rechazado';
        $warehouse_movement->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('warehouse.movement.index')], 200);
    }

    /**
     * Aprueba la solicitud de un movimientro entre almacenes
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedMovement(Request $request, $id)
    {
        $warehouse_movement = WarehouseMovement::find($id);
        DB::transaction(function () use ($warehouse_movement) {
            $warehouse_movement->state = 'Aprobado';
            $warehouse_movement->save();

            $warehouse_inventory_product_movements = $warehouse_movement->WarehouseInventoryProductMovements;

            foreach ($warehouse_inventory_product_movements as $warehouse_inventory_product_movement) {
                $warehouse_inventory_product = $warehouse_inventory_product_movement->WarehouseInventoryProduct;
                $warehouse_initial_inventory_product =
                    $warehouse_inventory_product_movement->WarehouseInitialInventoryProduct;
                $exist_real =
                    $warehouse_initial_inventory_product->exist - $warehouse_initial_inventory_product->reserved;
                if ($exist_real < $warehouse_inventory_product_movement->quantity) {
                    /* Si la exitencia del producto es menor que lo que queremos desplazar se revierten los cambios */
                    DB::rollback();
                } else {
                    if ($warehouse_initial_inventory_product->reserved > 0) {
                        $warehouse_initial_inventory_product->reserved +=
                            $warehouse_inventory_product_movement->quantity;
                        $warehouse_initial_inventory_product->save();
                    } else {
                        $warehouse_initial_inventory_product->reserved =
                            $warehouse_inventory_product_movement->quantity;
                        $warehouse_initial_inventory_product->save();
                    }
                }
            }
        });
        if ($warehouse_movement->state != 'Aprobado') {
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
        return response()->json(['result' => true, 'redirect' => route('warehouse.movement.index')], 200);
    }
}
