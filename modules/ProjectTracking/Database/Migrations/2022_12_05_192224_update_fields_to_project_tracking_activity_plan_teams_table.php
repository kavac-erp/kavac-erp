<?php

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToProjectTrackingActivityPlanTeamsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToProjectTrackingActivityPlanTeamsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_activity_plan_teams')) {
            Schema::table('project_tracking_activity_plan_teams', function (Blueprint $table) {
                if (Schema::hasColumn('project_tracking_activity_plan_teams', 'employers_id')) {
                    $table->dropForeign('project_tracking_activity_plan_teams_employers_id_foreign');
                    $table->dropColumn('employers_id');
                }
            });
        }
        if (Schema::hasTable('project_tracking_activity_plan_teams')) {
            Schema::table('project_tracking_activity_plan_teams', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_activity_plan_teams', 'employers_id')) {
                    if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                        $table->foreignId('employers_id')->references('id')->on('payroll_staffs')->onDelete('restrict')
                        ->onUpdate('cascade')->comment('Trabajador');
                    } else {
                        $table->foreignId('employers_id')->references('id')->on('project_tracking_personal_registers')->onDelete('restrict')
                        ->onUpdate('cascade')->comment('Trabajador');
                    }
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
        Schema::table('project_tracking_activity_plan_teams', function (Blueprint $table) {
        });
    }
}
