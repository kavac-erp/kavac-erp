<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldAccountingEntryAccountToFinanceBankMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_conciliation_bank_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_conciliation_bank_movements', 'finance_banking_movement_id')) {
                $table->dropForeign(['finance_banking_movement_id']);
                $table->dropColumn('finance_banking_movement_id');
            }
        });

        Schema::table('finance_conciliation_bank_movements', function (Blueprint $table) {
            $table->foreignId('accounting_entry_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
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
            $table->dropForeign(['accounting_entry_account_id']);
            $table->dropColumn('accounting_entry_account_id');

            $table->foreignId('finance_banking_movement_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
        });
    }
}
