<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToAssetAsignationsTable
 * @brief Agrega un campo ids_assets a la tabla asset_asignations
 *
 * Agrega un campo ids_assets a la tabla asset_asignations
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldIdsAssetsToAssetAsignationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_asignations')) {
            Schema::table('asset_asignations', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_asignations', 'ids_assets')) {
                    $table->json('ids_assets')->nullable()->comment('Lista de ids. de los bienes asignado o entregados, pertenecientes a un asignación');
                }
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
        if (Schema::hasTable('asset_asignations')) {
            Schema::table('asset_asignations', function (Blueprint $table) {
                if (Schema::hasColumn('asset_asignations', 'ids_assets')) {
                    $table->dropColumn('ids_assets');
                }
            });
        }
    }
}
