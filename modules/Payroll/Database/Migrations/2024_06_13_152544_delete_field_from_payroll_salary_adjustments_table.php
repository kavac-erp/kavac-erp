<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldFromPayrollSalaryAdjustmentsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldFromPayrollSalaryAdjustmentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_salary_adjustments')) {
            Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_salary_adjustments', 'increase_of_date')) {
                    $table->dropColumn('increase_of_date');
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
        Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
            $table->date('increase_of_date')->nullable()->comment('Fecha del aumento');
        });
    }
}
