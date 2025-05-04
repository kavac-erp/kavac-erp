<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldsFromPayrollVacationPoliciesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldsFromPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->dropColumn('payment_calculation');
            $table->dropColumn('vacation_pay_days');
            $table->dropColumn('max_days_advance');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->integer('vacation_pay_days')->nullable()
                      ->comment('Días a otorgar para el pago de vacaciones');
            $table->enum('payment_calculation', ['enjoyment_days', 'general_days'])
                      ->nullable()
                      ->comment('Establece el pago de acuerdo a: ' .
                                '(enjoyment_days: Días de disfrute, general_days: Días generales)');
            $table->integer('max_days_advance')->nullable()->comment('Días de anticipación (máximo)');
        });
    }
}
