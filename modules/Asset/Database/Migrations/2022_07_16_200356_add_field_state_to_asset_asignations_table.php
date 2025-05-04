<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldStateToAssetAsignationsTable
 * @brief Agrega un campo state a la tabla asset_asignations
 *
 * Agrega un campo state a la tabla asset_asignations
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldStateToAssetAsignationsTable extends Migration
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
                if (!Schema::hasColumn('asset_asignations', 'state')) {
                    $table->string('state')->nullable()->comment('Estado de la solictud de entrega');
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
                if (Schema::hasColumn('asset_asignations', 'state')) {
                    $table->dropColumn('state');
                }
            });
        }
    }
}
