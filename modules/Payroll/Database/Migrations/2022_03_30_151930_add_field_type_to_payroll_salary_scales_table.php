<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTypeToPayrollSalaryScalesTable
 * @brief Migración para agregar el campo de tipo en escalas salariales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTypeToPayrollSalaryScalesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_salary_scales', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_salary_scales', 'type')) {
                $table->string('type')->nullable()->comment('Indica el tipo de escalafón');
            };
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_salary_scales', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_salary_scales', 'type')) {
                $table->dropColumn('type');
            };
        });
    }
}
