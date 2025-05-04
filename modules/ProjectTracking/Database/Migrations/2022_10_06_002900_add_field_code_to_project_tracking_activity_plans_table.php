<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldCodeToProjectTrackingActivityPlansTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldCodeToProjectTrackingActivityPlansTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_activity_plans')) {
            Schema::table('project_tracking_activity_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_activity_plans', 'code')) {
                    $table->string('code')->comment(
                        'Código del plan de actividades'
                    );
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
        if (Schema::hasTable('project_tracking_activity_plans')) {
            Schema::table('project_tracking_activity_plans', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        };
    }
}
