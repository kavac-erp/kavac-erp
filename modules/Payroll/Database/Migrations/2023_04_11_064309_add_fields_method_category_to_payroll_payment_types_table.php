<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsMethodCategoryToPayrollPaymentTypesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsMethodCategoryToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            if (Module::has('Finance') && Module::isEnabled('Finance')) {
                $table->foreignId('finance_payment_method_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                $table->foreignId('accounting_entry_category_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_payment_types', 'finance_payment_method_id')) {
                $table->dropForeign(['finance_payment_method_id']);
                $table->dropColumn('finance_payment_method_id');
            }

            if (Schema::hasColumn('payroll_payment_types', 'accounting_entry_category_id')) {
                $table->dropForeign(['accounting_entry_category_id']);
                $table->dropColumn('accounting_entry_category_id');
            }
        });
    }
}
