<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsSalaryTabulatorAndConceptTypesToPayrollsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsSalaryTabulatorAndConceptTypesToPayrollsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payrolls')) {
            if (!Schema::hasColumn('payrolls', 'salary_tabulators')) {
                Schema::table('payrolls', function (Blueprint $table) {
                    $table->json('salary_tabulators')->nullable()->comment('Tabuladores salariales aplicados en la nómina');
                });
            }
            if (!Schema::hasColumn('payrolls', 'concept_types')) {
                Schema::table('payrolls', function (Blueprint $table) {
                    $table->json('concept_types')->nullable()->comment('Tipos de conceptos aplicados en la nómina');
                });
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payrolls')) {
            if (Schema::hasColumn('payrolls', 'salary_tabulators')) {
                Schema::table('payrolls', function (Blueprint $table) {
                    $table->dropColumn('salary_tabulators');
                });
            }
            if (Schema::hasColumn('payrolls', 'concept_types')) {
                Schema::table('payrolls', function (Blueprint $table) {
                    $table->dropColumn('concept_types');
                });
            }
        }
    }
}
