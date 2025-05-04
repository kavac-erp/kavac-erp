<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTypeToPayrollSalaryScalesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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
