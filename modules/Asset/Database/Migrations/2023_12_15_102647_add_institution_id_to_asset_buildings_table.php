<?php

use App\Models\Institution;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddInstitutionIdToAssetBuildingsTable
 * @brief Agrega la llave foranea institution_id a la tabla asset_buildings
 *
 * Clase que ejecuta las migraciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddInstitutionIdToAssetBuildingsTable extends Migration
{
    /**
     * Agrega la llave foranea
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('asset_buildings', 'institution_id')) {
            Schema::table('asset_buildings', function (Blueprint $table) {
                $table->foreignIdFor(Institution::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::table('asset_buildings', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['institution_id']);
        });
    }
}
