<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetInstitutionStoragesTable
 * @brief Crear tabla de intermediario para las instituciones y depósitos
 *
 * Gestiona la creación o eliminación de la tabla de depósitos
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetInstitutionStoragesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @author  Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_institution_storages')) {
            Schema::create('asset_institution_storages', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->foreignId('institution_id')->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('storage_id')->constrained()->onDelete('restrict')
                      ->onUpdate('cascade')->references('id')->on('asset_storages');

                $table->boolean('main')->default(false)
                      ->comment('Define si es el depósito principal');

                $table->boolean('manage')->default(true)
                      ->comment('Estatus de gestión. (true) activo, (false) inactivo');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_institution_storages');
    }
}
