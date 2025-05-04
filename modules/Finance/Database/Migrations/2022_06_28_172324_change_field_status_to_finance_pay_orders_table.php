<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldStatusToFinancePayOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (Schema::hasColumn('finance_pay_orders', 'status')) {
                $table->dropColumn('status');
            }
        });
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_pay_orders', 'status')) {
                $table->string('status', 2)->default('PE')
                      ->comment('Estatus de la orden: (PE)ndiente, (PA)gada, (PP)arcialmente pagada');
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
            if (Schema::hasColumn('finance_pay_orders', 'status')) {
                $table->dropColumn('status');
            }
        });
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_pay_orders', 'status')) {
                $table->enum('status', ['PE', 'PA'])->default('PE')
                      ->comment('Establece el estatus de la órden de pago: (PE)ndiente, (PA)gada');
            }
        });
    }
}
