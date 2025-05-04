<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddUniqueFieldToAssetConditionsTable
 * @brief Agrega una clave única a la tabla asset_conditions
 *
 * Agrega una clave única a la tabla asset_conditions
 *
 * @author Ing. Yennifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddUniqueFieldToAssetConditionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_conditions')) {
            Schema::table('asset_conditions', function (Blueprint $table) {

                $table->unique(['name'])->comment('Nombre de la condición física del bien');
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
        Schema::table('asset_conditions', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
}
