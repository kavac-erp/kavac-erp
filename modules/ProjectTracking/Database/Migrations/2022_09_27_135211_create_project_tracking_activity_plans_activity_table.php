<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingActivityPlansActivityTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingActivityPlansActivityTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_activity_plans_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->references('id')->on('project_tracking_activities')->onDelete('cascade')->onUpdate('cascade')->comment('Actividad');
            $table->foreignId('responsable_activity_id')->references('id')->on('project_tracking_activity_plan_teams')->onDelete('cascade')->onUpdate('cascade')->comment('Resposanble de la actividad');
            $table->date('start_date')->comment('Fecha de inicio');
            $table->date('end_date')->comment('Fecha fin');
            $table->foreignId('activity_plan_id')->references('id')->on('project_tracking_activity_plans')->onDelete('cascade')->onUpdate('cascade')->comment('Plan de actividades');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_tracking_activity_plans_activity');
    }
}
