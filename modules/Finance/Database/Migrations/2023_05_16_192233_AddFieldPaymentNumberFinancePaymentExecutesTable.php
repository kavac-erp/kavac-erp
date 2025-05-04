<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPaymentNumberFinancePaymentExecutesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPaymentNumberFinancePaymentExecutesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('finance_payment_executes')) {
            Schema::table('finance_payment_executes', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_payment_executes', 'payment_number')) {
                    $table->string('payment_number', 200)->nullable()->comment('numero de referencia o factura');
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
        Schema::table('finance_payment_executes', function (Blueprint $table) {
            if (Schema::hasColumn('finance_payment_executes', 'payment_number')) {
                $table->dropColumn('payment_number');
            }
        });
    }
}
