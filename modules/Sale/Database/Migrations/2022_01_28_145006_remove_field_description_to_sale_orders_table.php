<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class RemoveFieldDescriptionToSaleOrdersTable
 * @brief Migración encargada de eliminar el campo descripción de la tabla de ordenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveFieldDescriptionToSaleOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sale_orders', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_orders', 'description')) {
                $table->string('description', 200)->comment('Descripción de la actividad económica');
            }
        });
    }
}
