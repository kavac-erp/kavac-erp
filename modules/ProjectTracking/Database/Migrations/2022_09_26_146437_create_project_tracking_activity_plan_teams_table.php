<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingActivityPlanTeamsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingActivityPlanTeamsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_activity_plan_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employers_id')->references('id')->on('project_tracking_personal_registers')->onDelete('cascade')->onUpdate('cascade')->comment('Trabajador');
            $table->foreignId('staff_classification_id')->references('id')->on('projecttracking_staff_classifications')->onDelete('cascade')->onUpdate('cascade')->comment('Rol');
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
        Schema::dropIfExists('project_tracking_activity_plan_teams');
    }
}
