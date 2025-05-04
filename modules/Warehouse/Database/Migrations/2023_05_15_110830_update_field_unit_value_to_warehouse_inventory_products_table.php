<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldUnitValueToWarehouseInventoryProductsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldUnitValueToWarehouseInventoryProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_inventory_products')) {
            Schema::table('warehouse_inventory_products', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_inventory_products', 'unit_value')) {
                    $table->float('unit_value')->nullable()->unsigned()->comment('Valor por unidad del producto en el almacén')->change();
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
        if (Schema::hasTable('warehouse_inventory_products')) {
            Schema::table('warehouse_inventory_products', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_inventory_products', 'unit_value')) {
                    $table->float('unit_value')->unsigned()->comment('Valor por unidad del producto en el almacén')->change();
                };
            });
        };
    }
}
