<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldCurrencyIdToFinancePayOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_pay_orders', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()
                    ->comment('Identificador único asociado al tipo de moneda')->constrained()
                    ->onDelete('restrict')->onUpdate('cascade');
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
            if (Schema::hasColumn('finance_pay_orders', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });
    }
}
