<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPercentageToProjectTrackingActivityPlansActivityTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPercentageToProjectTrackingActivityPlansActivityTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_tracking_activity_plans_activity', function (Blueprint $table) {

        });
        if (Schema::hasTable('project_tracking_activity_plans_activity')) {
            Schema::table('project_tracking_activity_plans_activity', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_activity_plans_activity', 'percentage')) {
                    $table->string('percentage')->comment(
                        'Porcentaje asociado a la actividad'
                    )->nullable();
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
        if (Schema::hasTable('project_tracking_activity_plans_activity')) {
            Schema::table('project_tracking_activity_plans_activity', function (Blueprint $table) {
                if (Schema::hasColumn('project_tracking_activity_plans_activity', 'percentage')) {
                    $table->dropColumn('percentage');
                };
            });
        };
    }
}
