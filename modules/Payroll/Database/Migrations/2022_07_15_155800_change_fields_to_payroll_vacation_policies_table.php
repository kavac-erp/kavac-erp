<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPayrollVacationPoliciesTable
 * @brief Migración para agregar campos adicionales a la tabla de politicas de vacaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->boolean('business_days')->default(false)->nullable()->comment('¿Toma en cuenta los días hábiles?');
            $table->boolean('old_jobs')->default(false)->nullable()->comment('¿Toma en cuenta los años de servicios en otras instituciones públicas?');
            $table->integer('vacation_pay_days')->nullable()
                      ->comment('Días a otorgar para el pago de vacaciones');
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
            $table->dropColumn('business_days');
            $table->dropColumn('old_jobs');
            $table->dropColumn('vacation_pay_days');
        });
    }
}
