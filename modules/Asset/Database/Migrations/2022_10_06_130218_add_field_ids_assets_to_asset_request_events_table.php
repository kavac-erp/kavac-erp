<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldIdsAssetsToAssetRequestEventsTable
 * @brief Agrega un campo ids_assets a la tabla asset_request_events
 *
 * Agrega un campo ids_assets a la tabla asset_request_events
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldIdsAssetsToAssetRequestEventsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_request_events', function (Blueprint $table) {
            $table->json('ids_assets')->nullable()
                ->comment('Lista de ids. de los bienes pertenecientes a un evento');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_request_events', function (Blueprint $table) {
            $table->dropColumn('ids_assets');
        });
    }
}
