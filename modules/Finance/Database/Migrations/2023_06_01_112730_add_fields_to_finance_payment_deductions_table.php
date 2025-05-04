<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToFinancePaymentDeductionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_payment_deductions', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_payment_deductions', 'deductionable_type') && !Schema::hasColumn('finance_payment_deductions', 'deductionable_id')) {
                $table->nullableMorphs('deductionable');
            }
            if (Schema::hasColumn('finance_payment_deductions', 'deduction_id')) {
                $table->foreignId('deduction_id')->comment('Identificador único asociado a la retención')->nullable()
                  ->onDelete('restrict')->onUpdate('cascade')->change();
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
        Schema::table('finance_payment_deductions', function (Blueprint $table) {
            if (Schema::hasColumn('finance_payment_deductions', 'deductionable_type') && !Schema::hasColumn('finance_payment_deductions', 'deductionable_id')) {
                $table->dropColumn('deductionable_type');
                $table->dropColumn('deductionable_id');
            }

            if (Schema::hasColumn('finance_payment_deductions', 'deduction_id')) {
                $table->foreignId('deduction_id')->comment('Identificador único asociado a la retención')
                  ->onDelete('restrict')->onUpdate('cascade')->change();
            }
        });
    }
}
