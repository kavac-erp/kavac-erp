<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetFloorsTable
 * @brief Clase que gestiona las migraciones de la tabla asset_floors en el modulo de Asset
 *
 * Crea o elimina la tabla asset_floors
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetFloorsTable extends Migration
{
    /**
     * Ejecuta las migraciones para crear la tabla asset_floors.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_floors')) {
            Schema::create('asset_floors', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('building_id')
                    ->constrained('asset_buildings')->onDelete('cascade')->onUpdate('cascade');
                $table->string('name', 100)->comment('Nombre del nivel de la edificación');
                $table->string('description', 200)->nullable()->comment('Descripcion del nivel de la edificación');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones eliminando la tabla asset_floors.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_floors');
    }
}
