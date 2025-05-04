<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldNewValueToWarehouseInventoryProductMovementsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldNewValueToWarehouseInventoryProductMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_inventory_product_movements')) {
            Schema::table('warehouse_inventory_product_movements', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_inventory_product_movements', 'new_value')) {
                    $table->float('new_value')->nullable()->comment('Nuevo precio del producto movilizado')->change();
                };
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('warehouse_inventory_product_movements')) {
            Schema::table('warehouse_inventory_product_movements', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_inventory_product_movements', 'new_value')) {
                    $table->float('new_value')->comment('Nuevo precio del producto movilizado')->change();
                };
            });
        };
    }
}
