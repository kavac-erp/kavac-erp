<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToFinancePaymentDeductionsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToFinancePaymentDeductionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_payment_deductions', function (Blueprint $table) {
            $table->decimal('amount', 24, 2)->default(0)->comment('Monto de la retención')->change();
            $table->decimal('mor', 24, 2)->nullable()->comment('M.O.R / Base imponible')->change();
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
            $table->decimal('amount')->default(0)->comment('Monto de la retención')->change();
            $table->decimal('mor')->nullable()->comment('M.O.R / Base imponible')->change();
        });
    }
}
