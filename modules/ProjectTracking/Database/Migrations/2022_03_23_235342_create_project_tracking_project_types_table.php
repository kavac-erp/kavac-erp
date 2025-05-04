<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingProjectTypesTable
 * @brief Migración que se encarga de crear la tabla project_tracking_project_types en la base de datos
 *
 * Creación de la tabla project_tracking_project_types
 *
 * @author José Jorge Briceño <josejorgebriceno9@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingProjectTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_project_types', function (Blueprint $table) {
            $table->id()->comment('Identificador único del registro');
            $table->string('name', 100)->comment('Nombre del proyecto');
            $table->string('description', 250)->comment('Descripción del proyecto');
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
        Schema::dropIfExists('project_tracking_project_types');
    }
}
