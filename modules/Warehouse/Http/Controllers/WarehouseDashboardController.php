<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseInventoryProductRequest;
use Modules\Warehouse\Models\WarehouseMovement;
use Modules\Warehouse\Models\WarehouseRequest;

class WarehouseDashboardController extends Controller
{
    /**
     * Muestra el panel de control del módulo de almacén
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('warehouse::dashboard');
    }

    /**
     * Obtiene el reporte de almacén
     *
     * @param integer $type_operation Tipo de operación
     * @param string $code Código de la operación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOperation($type_operation, $code)
    {
        return response()->json(['result' => true, 'redirect' => '/warehouse/reports/show/' . $code], 200);
    }

    /**
     * Listado de operaciones de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListOperations()
    {
        $tables = ['warehouse_movements', 'warehouse_requests'];
        $fields = [];
        $result = [];

        foreach ($tables as $table) {
            if ($table == 'warehouse_movements') {
                $type_operation = 'movements';
                $columns = ['code', 'created_at', 'description'];
            } elseif ($table == 'warehouse_requests') {
                $type_operation = 'requests';
                $columns = ['code', 'created_at'];
            }
            $rec = DB::table($table)->select($columns)->get()->map(function ($w) use ($type_operation) {
                return [
                    'code' => $w->code,
                    'type_operation' => $type_operation,
                    'description' => ($type_operation == 'movements')
                    ? strip_tags($w->description ?? '') : 'Solicitud de productos al almacén',
                    'created_at' => $w->created_at,
                ];
            })->toArray();
            if (count($rec) > 0) {
                array_push($fields, $rec);
            }
        }
        if (array_key_exists(0, $fields)) {
            $result = $fields[0];
            if (array_key_exists(1, $fields)) {
                $result = array_merge($fields[0], $fields[1]);
            }
        }
        return response()->json(['records' => $result]);
    }

    /**
     * Obtiene información de las operaciones de almacén
     *
     * @param integer $type_operation Tipo de operación
     * @param string $code Código de la operación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($type_operation, $code)
    {
        if ($type_operation == 'movements') {
            $field = WarehouseMovement::where('code', $code)
                ->with(
                    ['warehouseInventoryProductMovements' => function ($query) {
                        $query->with(['warehouseInventoryProduct' => function ($query) {
                            $query->with(['warehouseProduct' => function ($query) {
                                $query->with('measurementUnit');
                            }, 'warehouseProductValues' => function ($query) {
                                $query->with('warehouseProductAttribute');
                            }, 'currency']);
                        }]);
                    }]
                )->first();
            return response()->json(['records' => $field->warehouseInventoryProductMovements], 200);
        } elseif ($type_operation == 'requests') {
            $field = WarehouseRequest::where('code', $code)
                ->with(
                    [
                        'warehouseInventoryProductRequests' => function ($query) {
                            $query->with(['warehouseInventoryProduct' => function ($query) {
                                $query->with(['warehouseProduct' => function ($query) {
                                    $query->with('measurementUnit');
                                }, 'currency']);
                            }]);
                        },
                    ]
                )->first();
            return response()->json(['records' => $field->warehouseInventoryProductRequests], 200);
        } else {
            return response()->json(['records' => []], 200);
        }
    }

    /**
     * Obtiene un listado de mínimo de productos en almacén
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function vueListMinProducts()
    {
        $fields = WarehouseInventoryProduct::with(
            [
                'warehouseProduct' => function ($query) {
                    $query->with('measurementUnit');
                },
                'warehouseProductValues' => function ($query) {
                    $query->with('warehouseProductAttribute');
                },
                'warehouseInventoryRule',
                'currency',
                'warehouseInstitutionWarehouse' => function ($query) {
                    $query->with('warehouse', 'institution');
                },
            ]
        )
            ->get();

        $warehouse_products = [];
        foreach ($fields as $field) {
            $minimum = ($field->warehouseInventoryRule == null) ? 0 : $field->warehouseInventoryRule->minimum;
            $exist_teorica = ($field->exist > 0) ? $field->exist - $minimum : 0;
            $product_free = $exist_teorica - $field->reserved;
            $product_free = ($exist_teorica > 0) ? $product_free * 100 / ($exist_teorica) : 0;
            $warehouse_product = [
                'code' => $field->code,
                'exist' => ($field->exist != 0) ? $field->exist : 0,
                'real' => $field->exist - $field->reserved,
                'reserved' => $field->reserved,
                'free' => $product_free,
                'currency' => $field->currency,
                'unit_value' => $field->unit_value,
                'measurement_unit' => ($field->warehouseProduct->measurement_unit_id != null)
                ? $field->warehouseProduct->measurementUnit
                : null,
                'warehouse_product' => $field->warehouseProduct,
                'warehouse_product_values' => $field->warehouseProductValues,
                'warehouse_institution_warehouse' => $field->warehouseInstitutionWarehouse,
            ];
            array_push($warehouse_products, $warehouse_product);
        }
        /*consulta para obtener los productos solicitados*/
        $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
            ->whereHas('warehouseRequest', function ($q) {
                $q->where('state', 'Pendiente');
            });

        $codeProducts = $this->getCodeProductsRequest($products->get());
        $productsQuantity = [];
        if (count($codeProducts)) {
            foreach ($codeProducts as $codeProduct) {
                $totalQuantity = 0;
                foreach ($products->get() as $product) {
                    if ($product->warehouseInventoryProduct->code == $codeProduct) {
                        $totalQuantity = $totalQuantity + $product->quantity;
                    }
                }
                array_push($productsQuantity, [
                    'code' => $codeProduct,
                    'quantity' => $totalQuantity]);
            }
        }
        return response()->json(['records' => $warehouse_products, 'productsQuantity' => $productsQuantity]);
    }


    /**
     * Obtiene las solicitudes de productos a almacén
     *
     * @param array $products Lista de productos
     *
     * @return array
     */
    public function getCodeProductsRequest($products)
    {
        $codeProducts = [];
        if (count($products) > 0) {
            foreach ($products as $product) {
                if (!in_array($product->warehouseInventoryProduct->code, $codeProducts)) {
                    array_push($codeProducts, $product->warehouseInventoryProduct->code);
                }
            }
        }
        return $codeProducts;
    }
}
