<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDeductedAtToFinancePaymentDeductionsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDeductedAtToFinancePaymentDeductionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_payment_deductions', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_payment_deductions', 'deducted_at')) {
                $table->date('deducted_at')->nullable()->comment('Fecha en la que se aplicó la retención');
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
            if (Schema::hasColumn('finance_payment_deductions', 'deducted_at')) {
                $table->dropColumn('deducted_at');
            }
        });
    }
}
