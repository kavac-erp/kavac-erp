<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddForeignKeysToAssetAsignationsTable
 * @brief Agrega las llaves foraneas building_id, floor_id, y section_id a la tabla asset_asignations
 *
 * Agrega llaves foraneas a la tabla asset_asignations
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddForeignKeysToAssetAsignationsTable extends Migration
{
    /**
     * Agrega las llaves foraneas
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_asignations')) {
            Schema::table('asset_asignations', function (Blueprint $table) {
                $table->foreignId('building_id')->nullable()->constrained('asset_buildings')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('floor_id')->nullable()->constrained('asset_floors')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('section_id')->nullable()->constrained('asset_sections')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::table('asset_asignations', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropForeign(['floor_id']);
            $table->dropForeign(['section_id']);
            $table->dropColumn(['building_id', 'floor_id', 'section_id']);
        });
    }
}
