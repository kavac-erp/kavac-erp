<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSignprofilesTable
 * @brief Crear tabla de para gestionar firmas de usuarios
 *
 * Gestiona la creación o eliminación de la tabla para gestionar firmas de usuarios
 *
 * @author Ing. Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSignprofilesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signprofiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->comment('Identificador del usuario');
            $table->string('cert', 3000)->comment('Certificado firmante');
            $table->string('pkey', 2000)->comment('Clave privada asociada al certificado del firmante');
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
        Schema::dropIfExists('signprofiles');
    }
}
