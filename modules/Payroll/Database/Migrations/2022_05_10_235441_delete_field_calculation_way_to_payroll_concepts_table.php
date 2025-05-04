<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldCalculationWayToPayrollConceptsTable
 * @brief Migración para eliminar el campo de la tabla de conceptos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldCalculationWayToPayrollConceptsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_concepts', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_concepts', 'calculation_way')) {
                $table->dropColumn('calculation_way');
            };
            if (Schema::hasColumn('payroll_concepts', 'payroll_salary_tabulator_id')) {
                $table->dropForeign(['payroll_salary_tabulator_id']);
                $table->dropColumn('payroll_salary_tabulator_id');
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
        Schema::table('', function (Blueprint $table) {
        });
    }
}
