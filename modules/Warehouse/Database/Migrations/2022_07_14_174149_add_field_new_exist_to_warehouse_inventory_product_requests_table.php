<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldNewExistToWarehouseInventoryProductRequestsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldNewExistToWarehouseInventoryProductRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {

            Schema::table('warehouse_inventory_product_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_inventory_product_requests', 'new_exist')) {
                    $table->integer('new_exist')->nullable()->comment('Nueva existencia');
                };
            });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_inventory_product_requests', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_inventory_product_requests', 'new_exist')) {
                $table->dropColumn('new_exist');
            }
        });
    }
}
