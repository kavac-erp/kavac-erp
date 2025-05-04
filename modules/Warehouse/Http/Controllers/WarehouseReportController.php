<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Warehouse\Pdf\WarehouseReport as ReportRepository;
use Modules\Warehouse\Models\WarehouseInventoryProductRequest;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseInventoryRule;
use Modules\Warehouse\Models\WarehouseReport;
use App\Models\Institution;
use App\Models\FiscalYear;
use App\Models\Currency;
use Modules\Warehouse\Models\WarehouseMovement;

/**
 * @class WarehouseReportController
 * @brief Controlador de los reportes de productos registrados en almacén
 *
 * Clase que gestiona los reportes de productos registrados en almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseReportController extends Controller
{
    use ValidatesRequests;

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
        $this->middleware('permission:warehouse.report.product.request', ['only' => 'requestProducts']);
        $this->middleware('permission:warehouse.report.least.inventory', ['only' => ['stocks', 'inventoryProducts']]);
    }

    /**
     * Muestra un listado de los reportes generados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('warehouse::reports.create');
    }

    /**
     * Reporte de solicitudes de productos
     *
     * @return \Illuminate\View\View
     */
    public function requestProducts()
    {
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }
        return view('warehouse::reports.warehouse-report-request-products', compact('institution'));
    }

    /**
     * Reporte de stock
     *
     * @return \Illuminate\View\View
     */
    public function stocks()
    {
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }
        return view('warehouse::reports.warehouse-report-stocks', compact('institution'));
    }

    /**
     * Reporte de inventario de productos
     *
     * @return \Illuminate\View\View
     */
    public function inventoryProducts()
    {
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }
        return view('warehouse::reports.warehouse-report-products', compact('institution'));
    }

    /**
     * Muestra un listado de los reportes generados
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        if ($request->current == "inventory-products") {
            $fields = WarehouseInventoryProduct::with(
                [
                    'currency',
                    'warehouseProduct.measurementUnit',
                    'warehouseInventoryRule',
                    'warehouseProductValues' => function ($query) {
                        $query->with('warehouseProductAttribute');
                    },
                    'warehouseInstitutionWarehouse' => function ($query) {
                        $query->with('warehouse');
                    },
                ]
            )->orderBy('warehouse_product_id');
            /*consulta para obtener los productos solicitados*/
            $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
                ->whereHas('warehouseRequest', function ($q) {
                    $q->where('state', 'Pendiente');
                });

            /*consulta para obtener los productos solicitados en movimiento de almacén*/
            $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements.warehouseInitialInventoryProduct']);

            if ($request->warehouse_product_id > 0) {
                $fields = $fields->where(
                    'warehouse_product_id',
                    $request->warehouse_product_id
                );

                $id_product = $request->warehouse_product_id;
                /*Consulta para obtener las solicitudes de un producto en especifico */
                $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
                    ->whereHas('warehouseRequest', function ($q) {
                        $q->where('state', 'Pendiente');
                    })
                    ->whereHas('warehouseInventoryProduct', function ($qq) use ($id_product) {
                        $qq->where('warehouse_product_id', $id_product);
                    });
                /*Consulta para obtener las solicitudes de movimiento un producto en especifico */
                $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements'])
                    ->whereHas('warehouseInventoryProductMovements', function ($q) use ($id_product) {
                        $q->with(['warehouseInitialInventoryProduct'])
                            ->whereHas('warehouseInitialInventoryProduct', function ($qq) use ($id_product) {
                                $qq->where('warehouse_product_id', $id_product);
                            });
                    });
            }
            if ($request->institution_id > 0) {
                $institutionsWarehouses = DB::table('warehouse_institution_warehouses')
                    ->where('institution_id', $request->institution_id)
                    ->select('id')->get()->pluck('id')->toArray();
                $fields = $fields->whereIn(
                    'warehouse_institution_warehouse_id',
                    $institutionsWarehouses
                );
            }
            if ($request->warehouse_id > 0) {
                $institutionsWarehouses = DB::table('warehouse_institution_warehouses')
                    ->where('warehouse_id', $request->warehouse_id)
                    ->select('id')->get()->pluck('id')->toArray();
                $fields = $fields->whereIn(
                    'warehouse_institution_warehouse_id',
                    $institutionsWarehouses
                );
            }
            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                        $products = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                        $productsMovement = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                        $products = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                        $productsMovement = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    }
                }
            }
            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                        $products = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                        $productsMovement = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                        $products = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                        $productsMovement = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
        } elseif ($request->current == "request-products") {
            $fields = WarehouseInventoryProductRequest::with(
                [
                    'warehouseInventoryProduct' => function ($query) {
                        $query->with(
                            [
                                'currency',
                                'warehouseProduct.measurementUnit',
                                'warehouseInventoryRule',
                                'warehouseProductValues' => function ($query) {
                                    $query->with('warehouseProductAttribute');
                                },
                                'warehouseInstitutionWarehouse' => function ($query) {
                                    $query->with('warehouse');
                                },
                            ]
                        );
                    },
                    'warehouseRequest' => function ($query) {
                        $query->with(
                            'institution',
                            'department',
                            'budgetSpecificAction'
                        );
                    }
                ]
            )->whereHas('warehouseRequest', function ($query) use ($request) {
                if ($request->institution_id) {
                    $query->whereHas('department', function ($q) use ($request) {
                        $q->where('institution_id', $request->institution_id);
                    });
                }
                if ($request->department_id) {
                    $query->where('department_id', $request->department_id);
                }
                if ($request->payroll_staff_id) {
                    $query->whereNotNull('payroll_staff_id')
                        ->where('payroll_staff_id', $request->payroll_staff_id);
                }
                if ($request->budget_specific_action_id) {
                    $query->whereNotNull('budget_specific_action_id')
                        ->where('budget_specific_action_id', $request->budget_specific_action_id);
                }
                if ($request->budget_project_id) {
                    $query->whereNotNull('budget_specific_action_id')
                        ->whereHas('budgetSpecificAction', function ($q) use ($request) {
                            $q->where('specificable_id', $request->budget_project_id);
                        });
                }
                if ($request->budget_centralized_action_id) {
                    $query->whereNotNull('budget_specific_action_id')
                        ->whereHas('budgetSpecificAction', function ($q) use ($request) {
                            $q->where('specificable_id', $request->budget_centralized_action_id);
                        });
                }
            });
            /*Busca los registros con solicitudes pendiente*/
            $products = [];
            foreach ($fields->get() as $field) {
                if ($field->warehouseRequest['state'] == 'Pendiente') {
                    array_push($products, $field);
                }
            }
            /*consulta para obtener los productos solicitados en movimiento de almacén*/
            $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements.warehouseInitialInventoryProduct'])->get();

            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                    }
                }
            }

            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
            /*
        Revisar filtros
            whereHas
        */
        } elseif ($request->current == "stocks") {
            /*consulta para obtener los productos solicitados*/
            $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
                ->whereHas('warehouseRequest', function ($q) {
                    $q->where('state', 'Pendiente');
                });

            /*consulta para obtener los productos solicitados en movimiento de almacén*/
            $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements.warehouseInitialInventoryProduct']);

            $fields = WarehouseInventoryRule::with(
                [
                    'warehouseInventoryProduct' => function ($query) {
                        $query->with(['WarehouseProduct', 'warehouseInstitutionWarehouse']);
                    }
                ]
            )->whereHas('warehouseInventoryProduct', function ($query) use ($request) {
                if ($request->institution_id) {
                    $query->whereHas('warehouseInstitutionWarehouse', function ($q) use ($request) {
                        $q->where('institution_id', $request->institution_id);
                    });
                }
                if ($request->warehouse_id) {
                    $query->whereHas('warehouseInstitutionWarehouse', function ($q) use ($request) {
                        $q->where('warehouse_id', $request->warehouse_id);
                    });
                }
                if ($request->warehouse_product_id) {
                    $query->where('warehouse_product_id', $request->warehouse_product_id);
                }
                $query->whereNotNull('exist');
            });

            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                    }
                }
            }

            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
        }
        /*Manejo de cantidad de las solicitudes de los productos*/
        if ($request->current == "request-products") {
            $codeProducts = $this->getCodeProductsRequest($products, $productsMovement);
            $productsQuantity = [];
            if (count($codeProducts)) {
                foreach ($codeProducts as $codeProduct) {
                    $totalQuantity = 0;
                    foreach ($products as $product) {
                        if ($product->warehouseInventoryProduct->code == $codeProduct) {
                            $totalQuantity = $totalQuantity + $product->quantity;
                        }
                    }
                    array_push($productsQuantity, [
                        'code' => $codeProduct,
                        'quantity' => $totalQuantity]);
                }
            }
        } else {
            //$codeProducts = $this->getCodeProductsRequest($products->get(), $productsMovement->get());
            $codeProducts = $this->getCodeProductsRequest($products, $productsMovement);
            $productsQuantity = [];
            if (count($codeProducts)) {
                foreach ($codeProducts as $codeProduct) {
                    $totalQuantity = 0;
                    /* Cuenta la cantidad de los productos solicitado */
                    if (count($products->get()) > 0) {
                        foreach ($products->get() as $product) {
                            if ($product->warehouseInventoryProduct->code == $codeProduct) {
                                $totalQuantity = $totalQuantity + $product->quantity;
                            }
                        }
                    }
                    /* Cuenta la cantidad de los productos solicitado por movimiento*/
                    //if (count($productsMovement->get()) > 0) {
                    if (count($productsMovement) > 0) {
                        //foreach ($productsMovement->get() as $product) {
                        foreach ($productsMovement as $product) {
                            if (count($product->warehouseInventoryProductMovements) > 0) {
                                foreach ($product->warehouseInventoryProductMovements as $movement) {
                                    if ($movement->warehouseInitialInventoryProduct['code'] == $codeProduct) {
                                        $totalQuantity = $totalQuantity + $movement->quantity;
                                    }
                                }
                            }
                        }
                    }
                    array_push($productsQuantity, [
                        'code' => $codeProduct,
                        'quantity' => $totalQuantity]);
                }
            }
        }

        return response()->json(['records' => $fields->get(), 'productsQuantity' => $productsQuantity], 200);
    }

    /**
     * Crea un nuevo reporte de inventario de productos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        if ($request->current == "inventory-products") {
            $fields = WarehouseInventoryProduct::with(
                [
                    'currency',
                    'warehouseProduct',
                    'warehouseInventoryRule',
                    'warehouseProductValues' => function ($query) {
                        $query->with('warehouseProductAttribute');
                    },
                    'warehouseInstitutionWarehouse' => function ($query) {
                        $query->with('warehouse');
                    },
                ]
            )->orderBy('warehouse_product_id');

            /*consulta para obtener los productos solicitados*/
            $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
                ->whereHas('warehouseRequest', function ($q) {
                    $q->where('state', 'Pendiente');
                });

            /*consulta para obtener los productos solicitados en movimiento de almacén*/
            $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements.warehouseInitialInventoryProduct']);

            if ($request->warehouse_product_id > 0) {
                $fields = $fields->where(
                    'warehouse_product_id',
                    $request->warehouse_product_id
                );

                $id_product = $request->warehouse_product_id;
                /*Consulta para obtener las solicitudes de un producto en especifico */
                $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
                    ->whereHas('warehouseRequest', function ($q) {
                        $q->where('state', 'Pendiente');
                    })
                    ->whereHas('warehouseInventoryProduct', function ($qq) use ($id_product) {
                        $qq->where('warehouse_product_id', $id_product);
                    });
                /*Consulta para obtener las solicitudes de movimiento un producto en especifico */
                $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements'])
                    ->whereHas('warehouseInventoryProductMovements', function ($q) use ($id_product) {
                        $q->with(['warehouseInitialInventoryProduct'])
                            ->whereHas('warehouseInitialInventoryProduct', function ($qq) use ($id_product) {
                                $qq->where('warehouse_product_id', $id_product);
                            });
                    });
            }
            if ($request->institution_id > 0) {
                $institutionsWarehouses = DB::table('warehouse_institution_warehouses')
                    ->where('institution_id', $request->institution_id)
                    ->select('id')->get()->pluck('id')->toArray();
                $fields = $fields->whereIn(
                    'warehouse_institution_warehouse_id',
                    $institutionsWarehouses
                );
            }
            if ($request->warehouse_id > 0) {
                $institutionsWarehouses = DB::table('warehouse_institution_warehouses')
                    ->where('warehouse_id', $request->warehouse_id)
                    ->select('id')->get()->pluck('id')->toArray();
                $fields = $fields->whereIn(
                    'warehouse_institution_warehouse_id',
                    $institutionsWarehouses
                );
            }
            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                        $products = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                        $productsMovement = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                        $products = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                        $productsMovement = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    }
                }
            }
            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                        $products = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                        $productsMovement = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                        $products = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                        $productsMovement = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
        } elseif ($request->current == "request-products") {
            $fields = WarehouseInventoryProductRequest::with(
                [
                    'warehouseInventoryProduct' => function ($query) {
                        $query->with(
                            [
                                'currency',
                                'warehouseProduct',
                                'warehouseInventoryRule',
                                'warehouseProductValues' => function ($query) {
                                    $query->with('warehouseProductAttribute');
                                },
                                'warehouseInstitutionWarehouse' => function ($query) {
                                    $query->with('warehouse');
                                },
                            ]
                        );
                    },
                    'warehouseRequest' => function ($query) {
                        $query->with(
                            'institution',
                            'department',
                            'budgetSpecificAction'
                        );
                    }
                ]
            )->whereHas('warehouseRequest', function ($query) use ($request) {
                if ($request->institution_id) {
                    $query->whereHas('department', function ($q) use ($request) {
                        $q->where('institution_id', $request->institution_id);
                    });
                }
                if ($request->department_id) {
                    $query->where('department_id', $request->department_id);
                }
                if ($request->payroll_staff_id) {
                    $query->whereNotNull('payroll_staff_id')
                        ->where('payroll_staff_id', $request->payroll_staff_id);
                }
                if ($request->budget_specific_action_id) {
                    $query->whereNotNull('budget_specific_action_id')
                        ->where('budget_specific_action_id', $request->budget_specific_action_id);
                }
                if ($request->budget_project_id) {
                    $query->whereNotNull('budget_specific_action_id')
                        ->whereHas('budgetSpecificAction', function ($q) use ($request) {
                            $q->where('specificable_id', $request->budget_project_id);
                        });
                }
                if ($request->budget_centralized_action_id) {
                    $query->whereNotNull('budget_specific_action_id')
                        ->whereHas('budgetSpecificAction', function ($q) use ($request) {
                            $q->where('specificable_id', $request->budget_centralized_action_id);
                        });
                }
            });
            /*Busca los registros con solicitudes pendiente*/
            $products = [];
            foreach ($fields->get() as $field) {
                if ($field->warehouseRequest['state'] == 'Pendiente') {
                    array_push($products, $field);
                }
            }
            /*consulta para obtener los productos solicitados en movimiento de almacén*/
            $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements.warehouseInitialInventoryProduct'])->get();

            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                    }
                }
            }

            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
            /*
        Revisar filtros
            whereHas
        */
        } elseif ($request->current == "stocks") {
            /*consulta para obtener los productos solicitados*/
            $products = WarehouseInventoryProductRequest::with('warehouseRequest', 'warehouseInventoryProduct')
                ->whereHas('warehouseRequest', function ($q) {
                    $q->where('state', 'Pendiente');
                });

            /*consulta para obtener los productos solicitados en movimiento de almacén*/
            $productsMovement = WarehouseMovement::where('state', 'Pendiente')
                ->whereNotNull('warehouse_institution_warehouse_initial_id')
                ->with(['warehouseInventoryProductMovements.warehouseInitialInventoryProduct']);
            $fields = WarehouseInventoryRule::with(
                [
                    'warehouseInventoryProduct' => function ($query) {
                        $query->with(['WarehouseProduct', 'warehouseInstitutionWarehouse']);
                    }
                ]
            )->whereHas('warehouseInventoryProduct', function ($query) use ($request) {
                if ($request->institution_id) {
                    $query->whereHas('warehouseInstitutionWarehouse', function ($q) use ($request) {
                        $q->where('institution_id', $request->institution_id);
                    });
                }
                if ($request->warehouse_id) {
                    $query->whereHas('warehouseInstitutionWarehouse', function ($q) use ($request) {
                        $q->where('warehouse_id', $request->warehouse_id);
                    });
                }
                if ($request->warehouse_product_id) {
                    $query->where('warehouse_product_id', $request->warehouse_product_id);
                }
                $query->whereNotNull('exist');
            });

            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                    }
                }
            }

            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
        }
        /*Manejo de cantidad de las solicitudes de los productos*/
        if ($request->current == "request-products") {
            $codeProducts = $this->getCodeProductsRequest($products, $productsMovement);
            $productsQuantity = [];
            if (count($codeProducts)) {
                foreach ($codeProducts as $codeProduct) {
                    $totalQuantity = 0;
                    foreach ($products as $product) {
                        if ($product->warehouseInventoryProduct->code == $codeProduct) {
                            $totalQuantity = $totalQuantity + $product->quantity;
                        }
                    }
                    array_push($productsQuantity, [
                        'code' => $codeProduct,
                        'quantity' => $totalQuantity]);
                }
            }
        } else {
            //$codeProducts = $this->getCodeProductsRequest($products->get(), $productsMovement->get());
            $codeProducts = $this->getCodeProductsRequest($products, $productsMovement);
            $productsQuantity = [];
            if (count($codeProducts)) {
                foreach ($codeProducts as $codeProduct) {
                    $totalQuantity = 0;
                    /* Cuenta la cantidad de los productos solicitado */
                    if (count($products->get()) > 0) {
                        foreach ($products->get() as $product) {
                            if ($product->warehouseInventoryProduct->code == $codeProduct) {
                                $totalQuantity = $totalQuantity + $product->quantity;
                            }
                        }
                    }
                    /* Cuenta la cantidad de los productos solicitado por movimiento*/
                    //if (count($productsMovement->get()) > 0) {
                    if (count($productsMovement) > 0) {
                        foreach ($productsMovement as $product) {
                        //foreach ($productsMovement->get() as $product) {
                            if (count($product->warehouseInventoryProductMovements) > 0) {
                                foreach ($product->warehouseInventoryProductMovements as $movement) {
                                    if ($movement->warehouseInitialInventoryProduct['code'] == $codeProduct) {
                                        $totalQuantity = $totalQuantity + $movement->quantity;
                                    }
                                }
                            }
                        }
                    }
                    array_push($productsQuantity, [
                        'code' => $codeProduct,
                        'quantity' => $totalQuantity]);
                }
            }
        }

        $institution = Institution::where('default', true)
            ->where('active', true)->first();
        $pdf = new ReportRepository();

        $codeSetting = CodeSetting::where('table', 'warehouse_reports')->first();
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
            WarehouseReport::class,
            $codeSetting->field
        );

        $filename = 'warehouse-report-' . $code . '.pdf';

        $report = WarehouseReport::create([
            'code'           => $code,
            'type_report'    => $request->current,
            'institution_id' => $institution->id,
            'filename'       => $filename
        ]);

        /* Definicion de las caracteristicas generales de la página */
        if ($report->type_report == 'stocks') {
            $body = 'warehouse::pdf.warehouse-report-stocks';
        }
        if ($report->type_report == 'inventory-products') {
            $body = 'warehouse::pdf.warehouse-report-product';
        }
        if ($report->type_report == 'request-products') {
            $body = 'warehouse::pdf.warehouse-report-request';
        }
        $institution = Institution::find(1);

        $fiscal_year = FiscalYear::where('active', true)->first();

        $currency = Currency::where('default', true)->first();

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify'   => url(''),
                'orientation' => 'L',
                'filename'    => $filename
            ]
        );

        $pdf->setHeader("Reporte de Almacén");
        $pdf->setFooter(true, strip_tags($institution->legal_address));
        $pdf->setBody(
            $body,
            true,
            [
                'pdf'    => $pdf,
                'fields' => $fields->get(),
                'productsQuantity' => $productsQuantity,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
            ]
        );

        $url = route('warehouse.report.show', ['code' => $report->code]);
        return response()->json(['result' => true, 'redirect' => $url], 200);
    }

    /**
     * Descarga el reporte
     *
     * @param string $code Código del reporte
     *
     * @return mixed|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show($code)
    {
        $file = storage_path() . '/reports/' . 'warehouse-report-' . $code . '.pdf';

        return response()->download($file, $code, [], 'inline');
    }

    /**
     * Obtiene el código de la solicitud de productos
     *
     * @param array $products Lista de productos
     * @param array $productsMovement Lista de movimientos de productos
     *
     * @return array
     */
    public function getCodeProductsRequest($products, $productsMovement)
    {
        $codeProducts = [];
        /*Codigos de productos por solicitudes pendiente*/
        if (count($products) > 0) {
            foreach ($products as $product) {
                if (!in_array($product->warehouseInventoryProduct->code, $codeProducts)) {
                    array_push($codeProducts, $product->warehouseInventoryProduct->code);
                }
            }
        }
        /*Codigos de productos por solicitudes pendiente por movimiento*/
        if (count($productsMovement) > 0) {
            foreach ($productsMovement as $product) {
                if (count($product->warehouseInventoryProductMovements) > 0) {
                    foreach ($product->warehouseInventoryProductMovements as $movement) {
                        if (!in_array($movement->warehouseInitialInventoryProduct['code'], $codeProducts)) {
                            array_push($codeProducts, $movement->warehouseInitialInventoryProduct['code']);
                        }
                    }
                }
            }
        }
        return $codeProducts;
    }
}
