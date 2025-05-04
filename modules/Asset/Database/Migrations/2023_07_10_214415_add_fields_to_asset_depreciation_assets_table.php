<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToAssetDepreciationAssetsTable
 * @brief Agrega campos a la tabla asset_depreciation_assets
 *
 * Agrega campos a la tabla asset_depreciation_assets
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToAssetDepreciationAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_depreciation_assets', function (Blueprint $table) {
            $table->string('depreciated_years')->nullable()->comment('Años depreciados');
            $table->string('days_remaining')->nullable()->comment('Días pendientes por depreciar');
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
        Schema::table('asset_depreciation_assets', function (Blueprint $table) {
            $table->dropForeign('asset_depreciation_assets_asset_book_id_foreign');
            $table->dropColumn(['depreciated_years', 'days_remaining', 'asset_book_id']);
        });
    }
}
