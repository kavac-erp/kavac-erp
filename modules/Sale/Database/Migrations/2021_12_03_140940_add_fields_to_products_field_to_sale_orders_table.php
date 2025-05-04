<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class AddFieldsToProductsFieldToSaleOrdersTable
 * @brief Migración encargada de agregar campos adicionales a la tabla de ordenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToProductsFieldToSaleOrdersTable extends Migration
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
                if (!Schema::hasColumn('sale_orders', 'products')) {
                    $table->json('products')->nullable()->comment('Lista de productos');
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
            if (Schema::hasColumn('sale_orders', 'products')) {
                $table->dropColumn('products');
            }
        });
    }
}
