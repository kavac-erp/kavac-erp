<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPayrollPaymentTypesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_payment_types')) {
            Schema::table('payroll_payment_types', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_payment_types', 'finance_bank_account_id')) {
                    $table->foreignId('finance_bank_account_id')->nullable()
                          ->comment('Identificador único asociado a la cuenta bancaria')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };

                if (!Schema::hasColumn('payroll_payment_types', 'accounting_account_id')) {
                    $table->foreignId('accounting_account_id')->nullable()
                          ->comment('Identificador único asociado a la cuenta contable')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        if (Schema::hasTable('payroll_payment_types')) {
            Schema::table('payroll_payment_types', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_payment_types', 'accounting_account_id')) {
                    $table->dropForeign(['accounting_account_id']);
                    $table->dropColumn('accounting_account_id');
                };

                if (Schema::hasColumn('payroll_payment_types', 'finance_bank_account_id')) {
                    $table->dropForeign(['finance_bank_account_id']);
                    $table->dropColumn('finance_bank_account_id');
                };
            });
        };
    }
}
