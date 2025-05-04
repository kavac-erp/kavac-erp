<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToSaleQuoteTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToSaleQuoteTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_quotes', function (Blueprint $table) {
            if (Schema::hasColumn('sale_quotes', 'sale_form_payment_id')) {
                $table->dropColumn('sale_form_payment_id');

                $table->foreignId('sale_charge_money_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->references('id')
                ->on('sale_charge_money')
                ->comment('Método de cobro');

                $table->foreignId('sale_warehouse_method_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->references('id')
                ->on('sale_warehouses')
                ->comment('Almacén');
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
        Schema::table('sale_quotes', function (Blueprint $table) {
            if (Schema::hasColumn('sale_quotes', 'sale_charge_money_id', 'sale_warehouse_method_id')) {
                $table->dropColumn('sale_charge_money_id');
                $table->dropColumn('sale_warehouse_method_id');

                $table->foreignId('sale_form_payment_id')
                ->nullable()
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade');
            };
        });
    }
}
