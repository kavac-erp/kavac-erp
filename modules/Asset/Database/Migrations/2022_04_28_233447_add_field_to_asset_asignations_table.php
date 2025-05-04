<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToAssetAsignationsTable
 * @brief Agrega campo institution_id a la tabla de asignación de bienes
 *
 * Agrega campo institution_id a la tabla de asignación de bienes
 *
 * @author Ing. José Briceño <jbricenyo@acenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldToAssetAsignationsTable extends Migration
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
                if (!Schema::hasColumn('asset_asignations', 'institution_id')) {
                    $table->foreignId('institution_id')->nullable()->constrained()
                        ->onDelete('restrict')->onUpdate('cascade');
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
                if (Schema::hasColumn('asset_asignations', 'institution_id')) {
                    $table->dropForeign(['institution_id']);
                    $table->dropColumn('institution_id');
                }
            });
        }
    }
}
