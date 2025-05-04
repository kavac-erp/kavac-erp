<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingPersonalRegistersTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Oscar González <xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingPersonalRegistersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_personal_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nombre de la persona');
            $table->string('last_name', 50)->comment('Apellido de la persona');
            $table->string('id_number', 50)->comment('Cédula de la persona');
            $table->foreignId('position_id')->references('id')->on('project_tracking_positions')->onDelete('cascade')->onUpdate('cascade')->comment('Cargo de la persona');
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
        Schema::dropIfExists('project_tracking_personal_registers');
    }
}
