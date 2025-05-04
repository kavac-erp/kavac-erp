<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldYearsForAdditionalDaysToPayrollVacationPoliciesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldYearsForAdditionalDaysToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {

            if (Schema::hasColumn('payroll_vacation_policies', 'years_for_additional_days')) {
                $table->unsignedInteger('years_for_additional_days')->nullable()->change();
                if (Schema::hasColumn('payroll_vacation_policies', 'years_for_additional_days')) {
                    DB::table('payroll_vacation_policies')
                        ->where('vacation_type', '=', 'collective_vacations')
                        ->update(['years_for_additional_days' => null]);
                }
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
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_vacation_policies', 'years_for_additional_days')) {
                $table->unsignedInteger('years_for_additional_days')->nullable(false)->change();
            }
        });
    }
}
