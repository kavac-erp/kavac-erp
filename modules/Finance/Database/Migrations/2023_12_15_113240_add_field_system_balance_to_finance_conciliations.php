<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldSystemBalanceToFinanceConciliations extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_conciliations', function (Blueprint $table) {
            $table->float('system_balance', 30, 10)->nullable()->comment('saldo en el sistema');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_conciliations', function (Blueprint $table) {
            $table->dropColumn('system_balance');
        });
    }
}
