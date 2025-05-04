<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddNewFieldsToPayrollConceptsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddNewFieldsToPayrollConceptsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_concepts')) {
            Schema::table('payroll_concepts', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_concepts', 'budget_project_id')) {
                    $table->foreignId('budget_project_id')->nullable()
                          ->comment('Identificador único asociado al proyecto presupuestario')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };

                if (!Schema::hasColumn('payroll_concepts', 'budget_centralized_action_id')) {
                    $table->foreignId('budget_centralized_action_id')->nullable()
                          ->comment('Identificador único asociado a la acción centralizada presupuestaria')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };

                if (!Schema::hasColumn('payroll_concepts', 'budget_specific_action_id')) {
                    $table->foreignId('budget_specific_action_id')->nullable()
                          ->comment('Identificador único asociado a la acción específica presupuestaria')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };
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
        if (Schema::hasTable('payroll_concepts')) {
            Schema::table('payroll_concepts', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_concepts', 'budget_project_id')) {
                    $table->dropForeign(['budget_project_id']);
                    $table->dropColumn('budget_project_id');
                };

                if (Schema::hasColumn('payroll_concepts', 'budget_centralized_action_id')) {
                    $table->dropForeign(['budget_centralized_action_id']);
                    $table->dropColumn('budget_centralized_action_id');
                };

                if (Schema::hasColumn('payroll_concepts', 'budget_specific_action_id')) {
                    $table->dropForeign(['budget_specific_action_id']);
                    $table->dropColumn('budget_specific_action_id');
                };
            });
        };
    }
}
