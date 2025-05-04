<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTypeAssetToAssetReportsTable
 * @brief Migración para agregar el campo type_asset a la tabla asset_reports
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTypeAssetToAssetReportsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_reports')) {
            Schema::table('asset_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_reports', 'type_asset')) {
                    $table->string('type_asset')->nullable()->comment('Tipo de bien');
                }
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('asset_reports')) {
            Schema::table('asset_reports', function (Blueprint $table) {
                if (Schema::hasColumn('asset_reports', 'type_asset')) {
                    $table->dropColumn('type_asset');
                }
            });
        };
    }
}
