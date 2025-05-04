<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldLocationPlaceToAssetAsignationsTable
 * @brief Agrega un campo location_place a la tabla asset_asignations
 *
 * Agrega un campo location_place a la tabla asset_asignations
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldLocationPlaceToAssetAsignationsTable extends Migration
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
                if (!Schema::hasColumn('asset_asignations', 'location_place')) {
                    $table->string('location_place', 100)->nullable()->comment('Lugar de ubicación del bien asignado');
                };
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
        if (Schema::hasTable('asset_asignations')) {
            Schema::table('asset_asignations', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'location_place')) {
                    $table->dropColumn(['location_place']);
                };
            });
        };
    }
}
