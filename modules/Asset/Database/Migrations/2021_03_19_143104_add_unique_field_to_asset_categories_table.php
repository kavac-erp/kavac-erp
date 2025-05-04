<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddUniqueFieldToAssetCategoriesTable
 * @brief Agrega una clave única a la tabla de categorías de bienes
 *
 * Agrega una clave única a la tabla de categorías de bienes
 *
 * @author Ing. Yennifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddUniqueFieldToAssetCategoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_categories')) {
            Schema::table('asset_categories', function (Blueprint $table) {
                $table->unique(['name'])->comment('Nombre de la categoria general del bien');
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
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
}
