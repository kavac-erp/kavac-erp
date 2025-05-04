<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldFinanceBankIdToFinanceBankAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('finance_bank_accounts')) {
            Schema::table('finance_bank_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_bank_accounts', 'finance_bank_id')) {
                    $table->foreignId('finance_bank_id')->nullable()->references('id')->on('finance_banks')->onDelete('restrict')->onUpdate('cascade')->comment('Indica el id del banco al cual pertenece la cuenta bancaria');
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
        if (Schema::hasTable('finance_bank_accounts')) {
            Schema::table('finance_bank_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('finance_bank_accounts', 'finance_bank_id')) {
                    $table->dropForeign(['finance_bank_id']);
                    $table->dropColumn('finance_bank_id');
                }
            });
        }
    }
}
