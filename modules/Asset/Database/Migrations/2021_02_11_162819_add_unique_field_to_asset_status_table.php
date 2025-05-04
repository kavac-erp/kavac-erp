<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddUniqueFieldToAssetStatusTable
 * @brief Agrega una clave única a la tabla de estatus de bienes
 *
 * Agrega una clave única a la tabla de estatus de bienes
 *
 * @author Ing. Yennifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddUniqueFieldToAssetStatusTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_status')) {
            Schema::table('asset_status', function (Blueprint $table) {

                $table->unique(['name'])->comment('Nombre del estatus de uso');
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
        Schema::table('asset_status', function (Blueprint $table) {

            $table->dropUnique(['name']);
        });
    }
}
