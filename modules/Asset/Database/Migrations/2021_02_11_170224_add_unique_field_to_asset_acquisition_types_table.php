<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddUniqueFieldToAssetAcquisitionTypesTable
 * @brief Agrega una clave única a la tabla de tipos de adquisición de bienes
 *
 * Agrega una clave única a la tabla de tipos de adquisición de bienes
 *
 * @author Ing. Yennifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddUniqueFieldToAssetAcquisitionTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_acquisition_types')) {
            Schema::table('asset_acquisition_types', function (Blueprint $table) {

                $table->unique(['name'])->comment('Nombre del tipo de adquisición');
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
        Schema::table('asset_acquisition_types', function (Blueprint $table) {

            $table->dropUnique(['name']);
        });
    }
}
