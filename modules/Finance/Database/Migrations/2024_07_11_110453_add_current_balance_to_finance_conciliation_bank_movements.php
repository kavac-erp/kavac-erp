<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentBalanceToFinanceConciliationBankMovements extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_conciliation_bank_movements', function (Blueprint $table) {
            $table->float('current_balance', 30, 10)->nullable()->comment('Saldo disponible');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_conciliation_bank_movements', function (Blueprint $table) {
            $table->dropColumn('current_balance');
        });
    }
}
