<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddUniqueFieldToAssetUseFunctionsTable
 * @brief Agrega una clave única a la tabla asset_use_functions
 *
 * Agrega una clave única a la tabla asset_use_functions
 *
 * @author Ing. Yennifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddUniqueFieldToAssetUseFunctionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_use_functions')) {
            Schema::table('asset_use_functions', function (Blueprint $table) {

                $table->unique(['name'])->comment('Nombre de la función de uso');
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
        Schema::table('asset_use_functions', function (Blueprint $table) {

            $table->dropUnique(['name']);
        });
    }
}
