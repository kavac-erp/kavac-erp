<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToFinanceSettingBankReconciliationFiles extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_setting_bank_reconciliation_files', function (Blueprint $table) {
            $table->integer('balance_according_bank')->nullable()->comment('Saldo según banco');
            $table->integer('position_balance_according_bank')->nullable()->comment('Posición del saldo según banco en el archivo');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_setting_bank_reconciliation_files', function (Blueprint $table) {
            $table->dropColumn('balance_according_bank');
            $table->dropColumn('position_balance_according_bank');
        });
    }
}
