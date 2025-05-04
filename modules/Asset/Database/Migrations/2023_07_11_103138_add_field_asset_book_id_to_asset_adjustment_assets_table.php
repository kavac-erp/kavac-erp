<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldAssetBookIdToAssetAdjustmentAssetsTable
 * @brief Agrega un campo asset_book_id a la tabla asset_adjustment_assets
 *
 * Agrega un campo asset_book_id a la tabla asset_adjustment_assets
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAssetBookIdToAssetAdjustmentAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_adjustment_assets', function (Blueprint $table) {
            $table
                ->foreignId('asset_book_id')
                ->nullable()
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_adjustment_assets', function (Blueprint $table) {
            $table->dropForeign('asset_adjustment_assets_asset_book_id_foreign');
            $table->dropColumn('asset_book_id');
        });
    }
}
