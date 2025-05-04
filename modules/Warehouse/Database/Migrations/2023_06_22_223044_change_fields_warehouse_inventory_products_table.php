<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldsWarehouseInventoryProductsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsWarehouseInventoryProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_inventory_products', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_inventory_products', 'exist')) {
                $table->float('exist')->nullable()->comment('Cantidad de productos en existencia, incluye los reservados por solicitudes almacén')->change();
            };
            if (Schema::hasColumn('warehouse_inventory_products', 'reserved')) {
                $table->float('reserved')->nullable()->comment('Cantidad de productos reservados por solicitudes de almacén')->change();
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
        Schema::table('warehouse_inventory_products', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_inventory_products', 'exist')) {
                $table->integer('exist')->unsigned()->comment('Cantidad de productos en existencia, incluye los reservados por solicitudes almacén')->change();
            }
            if (Schema::hasColumn('warehouse_inventory_products', 'reserved')) {
                $table->integer('reserved')->unsigned()->comment('Cantidad de productos reservados por solicitudes de almacén')->change();
            }
        });
    }
}
