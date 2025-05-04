<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldMorphToPayrollConceptAssignOptionsTable
 * @brief Agrega las columnas porfológica applicable a la tabla payroll_concept_assign_options
 *
 * Agrega las columnas porfológica
 *
 * @author     Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldMorphToPayrollConceptAssignOptionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_concept_assign_options')) {
            Schema::table('payroll_concept_assign_options', function (Blueprint $table) {
                $table->nullableMorphs('applicable', 'payroll_concept_assign_options_applicable_morphs_index');
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_concept_assign_options', function (Blueprint $table) {
            $table->dropMorphs('applicable', 'payroll_concept_assign_options_applicable_morphs_index');
        });
    }
}
