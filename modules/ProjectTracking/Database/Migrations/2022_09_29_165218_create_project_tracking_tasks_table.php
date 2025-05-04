<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingTasksTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingTasksTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_name')->nullable()->references('id')->on('project_tracking_projects')->onDelete('cascade')->onUpdate('cascade')->comment('Proyecto Asociado');
            $table->foreignId('subproject_name')->nullable()->references('id')->on('project_tracking_sub_projects')->onDelete('cascade')->onUpdate('cascade')->comment('Subproyecto Asociado');
            $table->foreignId('product_name')->nullable()->references('id')->on('project_tracking_products')->onDelete('cascade')->onUpdate('cascade')->comment('Producto Asociado');
            $table->foreignId('activity_plan_id')->nullable()->references('id')->on('project_tracking_activity_plans_activity')->onDelete('cascade')->onUpdate('cascade')->comment('Actividades macro Asociadas');
            $table->string('name', 50)->comment('Nombre de la Tarea');
            $table->string('description', 250)->nullable()->comment('Descripción de la Tarea');
            $table->foreignId('employers_id')->nullable()->references('id')->on('project_tracking_activity_plan_teams')->onDelete('cascade')->onUpdate('cascade')->comment('Empleados Asociados');
            $table->foreignId('priority_id')->references('id')->on('project_tracking_priorities')->onDelete('cascade')->onUpdate('cascade')->comment('Prioridad de la Tarea');
            $table->date('start_date')->comment('Fecha de inicio');
            $table->date('end_date')->comment('Fecha de culminación');
            $table->foreignId('activity_status_id')->references('id')->on('project_tracking_activity_statuses')->onDelete('cascade')->onUpdate('cascade')->comment('Estatus de la Tarea');
            $table->bigInteger('weight')->nullable()->comment('Peso de la actividad');
            $table->boolean('completed')->default(false);
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
        Schema::dropIfExists('project_tracking_tasks');
    }
}
