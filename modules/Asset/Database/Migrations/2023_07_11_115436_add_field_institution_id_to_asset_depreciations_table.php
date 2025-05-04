<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldCurrencyIdToAssetDepreciationsTable
 * @brief Agrega un campo currency_id a la tabla asset_depreciations
 *
 * Agrega un campo currency_id a la tabla asset_depreciations
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldInstitutionIdToAssetDepreciationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_depreciations', function (Blueprint $table) {
            $table
                ->foreignId('institution_id')
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
        Schema::table('asset_depreciations', function (Blueprint $table) {
            $table->dropForeign('asset_depreciations_institution_id_foreign');
            $table->dropColumn('institution_id');
        });
    }
}
