<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPercentageToPayrollSalaryTabulatorsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPercentageToPayrollSalaryTabulatorsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_salary_tabulators', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_salary_tabulators', 'percentage')) {
                $table->boolean('percentage')->default(false)->comment('Indica si el valor del tabulador esta expresado en porcentajes');
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
        Schema::table('payroll_salary_tabulators', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_salary_tabulators', 'percentage')) {
                $table->dropColumn('percentage');
            };
        });
    }
}
