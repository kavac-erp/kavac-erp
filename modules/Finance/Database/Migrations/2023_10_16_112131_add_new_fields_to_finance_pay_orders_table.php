<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToFinancePayOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (!Schema::hasColumns('finance_pay_orders', ['month', 'period'])) {
                $table->string('month', 2)->nullable()->comment('Número del mes al que pertenece el periodo de la orden de pago de la retención');
                $table->string('period', 2)->nullable()->comment('Periodo de la orden de pago de la retención: 1 = Primera quincena, 2 = Segunda quincena, 3 = Mensual');
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
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (Schema::hasColumns('finance_pay_orders', ['month', 'period'])) {
                $table->dropColumn('month');
                $table->dropColumn('period');
            }
        });
    }
}
