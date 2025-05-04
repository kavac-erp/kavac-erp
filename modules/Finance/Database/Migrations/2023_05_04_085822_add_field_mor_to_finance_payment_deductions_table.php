<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldMorToFinancePaymentDeductionsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldMorToFinancePaymentDeductionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('finance_payment_deductions')) {
            Schema::table('finance_payment_deductions', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_payment_deductions', 'mor')) {
                    $table->decimal('mor')->nullable()->comment('M.O.R / Base imponible');
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
        if (Schema::hasTable('finance_payment_deductions')) {
            Schema::table('finance_payment_deductions', function (Blueprint $table) {
                if (Schema::hasColumn('finance_payment_deductions', 'mor')) {
                    $table->dropColumn('mor');
                }
            });
        }
    }
}
