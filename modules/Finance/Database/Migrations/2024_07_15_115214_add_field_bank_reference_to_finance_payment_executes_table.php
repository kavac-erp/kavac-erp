<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldBankReferenceToFinancePaymentExecutesTable extends Migration
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
                if (!Schema::hasColumn('finance_payment_executes', 'general_bank_reference')) {
                    $table->integer('general_bank_reference')->nullable()->comment('Número de referencia bancaria');
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
            if (Schema::hasColumn('finance_payment_executes', 'general_bank_reference')) {
                $table->dropColumn('general_bank_reference');
            }
        });
    }
}
