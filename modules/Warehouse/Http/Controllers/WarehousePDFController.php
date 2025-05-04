<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Warehouse\Models\Warehouse;
use Modules\Warehouse\Models\WarehouseProduct;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseInstitutionWarehouse;
use App\Models\Institution;
use App\Models\Parameter;
use Modules\Warehouse\Pdf\WarehouseReport as ReportRepository;
use Carbon\Carbon;

/**
 * @class WarehousePDFController
 * @brief Controlador de los reportes de almacén
 *
 * Clase que gestiona los reportes de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehousePDFController extends Controller
{
    /**
     * Crea un informe para un tipo específico de almacén o producto.
     *
     * @param integer $type Tipo de almacén o producto (1 para producto, otro valor para almacén)
     * @param integer|null $id ID del producto o almacén
     *
     * @return void
     */
    public function createForType($type, $id = null)
    {
        $field = ($type == 1) ? WarehouseProduct::find($id) : Warehouse::find($id);

        if (is_null($field)) {
            $inventory_product = WarehouseInventoryProduct::with(
                [
                    'currency',
                    'warehouseProduct',
                    'warehouseInventoryRule',
                    'warehouseProductValues' => function ($query) {
                        $query->with('warehouseProductAttribute');
                    },
                    'warehouseInstitutionWarehouse' => function ($query) {
                        $query->with('warehouse');
                    }
                ]
            )->orderBy('warehouse_product_id')->get();
        } elseif ($type == 1) {
            $inventory_product = WarehouseInventoryProduct::where('warehouse_product_id', $field->id)
                ->with(
                    [
                        'currency',
                        'warehouseProduct',
                        'warehouseInventoryRule',
                        'warehouseProductValues' => function ($query) {
                            $query->with('warehouseProductAttribute');
                        },
                        'warehouseInstitutionWarehouse' => function ($query) {
                            $query->with('warehouse');
                        }
                    ]
                )->orderBy('warehouse_institution_warehouse_id')->get();
        } else {
            $inventory_product = WarehouseInventoryProduct::where('warehouse_institution_warehouse_id', $field->id)
                ->with(
                    [
                        'currency',
                        'warehouseProduct',
                        'warehouseInventoryRule',
                        'warehouseProductValues' => function ($query) {
                            $query->with('warehouseProductAttribute');
                        },
                        'warehouseInstitutionWarehouse' => function ($query) {
                            $query->with('warehouse');
                        }
                    ]
                )->orderBy('warehouse_product_id')->get();
        };

        $this->createReport($inventory_product);
    }

    /**
     * Crea un inventario de productos en un almacén específico.
     *
     * @param integer $warehouse_id ID del almacén.
     * @param integer $product_id ID del producto.
     *
     * @return void
     */
    public function create($warehouse_id, $product_id)
    {
        $product = WarehouseProduct::find($product_id);
        $institution = Institution::where('active', true)->where('default', true)->first();
        $inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $warehouse_id)
            ->where('institution_id', $institution->id)->first();

        if ((!is_null($product)) && (!is_null($inst_ware))) {
            $inventory_product = WarehouseInventoryProduct::where('warehouse_institution_warehouse_id', $inst_ware->id)
                ->where('warehouse_product_id', $product->id)
                ->with(
                    [
                        'currency',
                        'warehouseProduct',
                        'warehouseInventoryRule',
                        'warehouseProductValues' => function ($query) {
                            $query->with('warehouseProductAttribute');
                        },
                        'warehouseInstitutionWarehouse' => function ($query) {
                            $query->with('warehouse');
                        }
                    ]
                )->orderBy('warehouse_product_id')->orderBy('warehouse_institution_warehouse_id')->get();

            $this->createReport($inventory_product);
        }
    }

    /**
     * Crea un informe de productos en almacén.
     *
     * @return void
     */
    public function createWarehouseProducts()
    {
        $inventory_product = WarehouseInventoryProduct::with(
            [
                'currency',
                'warehouseProduct',
                'warehouseInventoryRule',
                'warehouseProductValues' => function ($query) {
                    $query->with('warehouseProductAttribute');
                },
                'warehouseInstitutionWarehouse' => function ($query) {
                    $query->with('warehouse');
                }
            ]
        )->orderBy('warehouse_product_id')->orderBy('warehouse_institution_warehouse_id')->get();

        $this->createReport($inventory_product);
    }

    /**
     * Crea un informe de productos en almacén para un producto específico.
     *
     * @param integer $product_id ID del producto para el cual se creará el informe
     *
     * @return void
     */
    public function createForProduct($product_id)
    {
        $product = WarehouseProduct::find($product_id);

        if (!is_null($product)) {
            $inventory_product = WarehouseInventoryProduct::where('warehouse_product_id', $product->id)
                ->with(
                    [
                        'currency',
                        'warehouseProduct',
                        'warehouseInventoryRule',
                        'warehouseProductValues' => function ($query) {
                            $query->with('warehouseProductAttribute');
                        },
                        'warehouseInstitutionWarehouse' => function ($query) {
                            $query->with('warehouse');
                        }
                    ]
                )->orderBy('warehouse_product_id')->orderBy('warehouse_institution_warehouse_id')->get();

            $this->createReport($inventory_product);
        }
    }

    /**
     * Crea un informe de productos en almacén para un almacén específico.
     *
     * @param integer $warehouse_id ID del almacén para el cual se creará el informe
     *
     * @return void
     */
    public function createForWarehouse($warehouse_id)
    {
        $institution = Institution::where('active', true)->where('default', true)->first();
        $inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $warehouse_id)
                    ->where('institution_id', $institution->id)->first();

        if (!is_null($inst_ware)) {
            $inventory_product = WarehouseInventoryProduct::where('warehouse_institution_warehouse_id', $inst_ware->id)
                ->with(
                    [
                        'currency',
                        'warehouseProduct',
                        'warehouseInventoryRule',
                        'warehouseProductValues' => function ($query) {
                            $query->with('warehouseProductAttribute');
                        },
                        'warehouseInstitutionWarehouse' => function ($query) {
                            $query->with('warehouse');
                        }
                    ]
                )->orderBy('warehouse_product_id')->orderBy('warehouse_institution_warehouse_id')->get();

            $this->createReport($inventory_product);
        }
    }

    /**
     * Genera un informe de productos en almacén en formato PDF.
     *
     * @param mixed $inventory_product Colección de productos en almacén
     *
     * @return void
     */
    public function createReport($inventory_product)
    {
        $multi_inst =  Parameter::where('p_key', 'multi_institution')
            ->where('active', true)->first();
        $institution = Institution::where('default', true)
            ->where('active', true)->first();
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página */
        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify'   => url(''),
                'orientation' => 'L',
                'filename'    => 'warehouse-report-' . Carbon::now() . '.pdf'
            ]
        );

        $pdf->setHeader('Inventario de Productos de Almacén');
        $pdf->setFooter();
        $pdf->setBody(
            'warehouse::pdf.warehouse-report-product',
            true,
            [
                'pdf'    => $pdf,
                'inventory_product' => $inventory_product
            ]
        );
    }
}
