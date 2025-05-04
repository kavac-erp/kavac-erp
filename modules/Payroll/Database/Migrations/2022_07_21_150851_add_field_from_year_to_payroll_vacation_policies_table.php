<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldFromYearToPayrollVacationPoliciesTable
 * @brief Migración para agregar campo from_year a la tabla de politicas de vacaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldFromYearToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->integer('from_year')->nullable()
                      ->comment('A partir del año');
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
            $table->dropColumn('from_year');
        });
    }
}
