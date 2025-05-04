<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetSectionsTable
 * @brief Clase que gestiona las migraciones de la tabla asset_sections en el modulo de Asset
 *
 * Crea o elimina la tabla asset_sections
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetSectionsTable extends Migration
{
    /**
     * Ejecuta las migraciones para crear la tabla asset_sections
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_sections')) {
            Schema::create('asset_sections', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 100)->comment('Nombre de la sección de la edificación');
                $table->string('description', 200)->nullable()->comment('Descripcion de la sección');
                $table->foreignId('building_id')->constrained('asset_buildings')->onUpdate('cascade')->onDelete('cascade');
                $table->foreignId('floor_id')->constrained('asset_floors')->onUpdate('cascade')->onDelete('cascade');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones eliminando la tabla asset_sections
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_sections');
    }
}
