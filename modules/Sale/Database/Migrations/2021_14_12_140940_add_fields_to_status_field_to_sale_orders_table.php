<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class AddFieldsToStatusFieldToSaleOrdersTable
 * @brief Migración encargada de agregar campos adicionales a la tabla de ordenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToStatusFieldToSaleOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sale_orders')) {
            Schema::table('sale_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_orders', 'status')) {
                    $table->json('status')->nullable()->comment('Status de la solicitud');
                }
                if (!Schema::hasColumn('sale_orders', 'id_number')) {
                    $table->json('id_number')->nullable()->comment('Status de la solicitud');
                }
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sale_orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('sale_orders', 'id_number')) {
                $table->dropColumn('id_number');
            }
        });
    }
}
