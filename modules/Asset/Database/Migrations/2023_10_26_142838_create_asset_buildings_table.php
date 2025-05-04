<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetBuildingsTable
 * @brief clase que ejecuta las migraciones de la tabla asset_buildings en el mòdulo de Asset
 *
 * Crea o elimina la tabla asset_buildings
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetBuildingsTable extends Migration
{
    /**
     * Ejecuta las migraciones para crear la tabla asset_buildings
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_buildings')) {
            Schema::create('asset_buildings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 100)->comment('Nombre de la edificaciòn');
                $table->string('description', 200)->nullable()->comment('Descripciòn de la edificaciòn');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migracione eliminando la tabla asset_buildings.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_buildings');
    }
}
