<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingSubProjectsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Pedro Contreras <pdrocont@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingSubProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_sub_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id', 100)->comment('Proyecto');
            $table->string('name', 50)->comment('Nombre del subproyecto');
            $table->string('description', 500)->nullable()->comment('Descripción del subproyecto');
            $table->string('id_number', 20)->unique()->comment('Código');
            $table->foreignId('responsable', 50)->references('id')->on('project_tracking_personal_registers')->onDelete('cascade')->onUpdate('cascade')->comment('Responsable del proyecto');
            $table->date('start_date', 50)->comment('Fecha de inicio');
            $table->date('end_date', 50)->comment('Fecha de fin');
            $table->string('financement_amount', 500)->comment('Monto de financiamiento');
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
        Schema::dropIfExists('project_tracking_sub_projects');
    }
}
