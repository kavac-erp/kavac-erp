<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldQuantityWarehouseInventoryProductRequestsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldQuantityWarehouseInventoryProductRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_inventory_product_requests', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_inventory_product_requests', 'quantity')) {
                $table->float('quantity')->nullable()->comment('    Cantidad solicitada del producto')->change();
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
            if (Schema::hasColumn('warehouse_inventory_product_requests', 'quantity')) {
                $table->integer('quantity')->unsigned()->comment('  Cantidad solicitada del producto')->change();
            }
        });
    }
}
