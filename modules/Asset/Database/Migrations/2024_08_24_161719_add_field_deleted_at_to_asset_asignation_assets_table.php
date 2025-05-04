<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldUpdatedAtToAssetAsignationAssetsTable
 * @brief Agrega el campo de deleted_at a la tabla asset_asignation_assets
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDeletedAtToAssetAsignationAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_asignation_assets', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_asignation_assets', 'deleted_at')) {
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_asignation_assets', function (Blueprint $table) {
            if (Schema::hasColumn('asset_asignation_assets', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
}
