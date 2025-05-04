<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldNameToProjectTrackingStaffClassificationsTable
 * @brief Actualiza campos de la tabla clasificación del personal
 *
 * Gestiona la creación o eliminación de campos de la tabla clasificación del personal
 *
 * @author Oscar González <xxmaestroyix@gmail.com> | <ollacar@outlook.es>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingStaffClassificationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projecttracking_staff_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nombre del rol');
            $table->string('description', 200)->nullable()->comment('Descripción del rol');
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
        Schema::dropIfExists('projecttracking_staff_classifications');
    }
}
