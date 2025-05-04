<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class AddFieldDocumentStatusIdToAssetDisincorporationTable
 * @brief Agrega un campo document_status_id a la tabla asset_disincorporations
 *
 * Agrega un campo document_status_id a la tabla asset_disincorporations
 *
 * @author Ing. Manuel Zambrano <mzambrano@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDocumentStatusIdToAssetDisincorporationTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_disincorporations')) {
            Schema::table('asset_disincorporations', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_disincorporations', 'document_status_id')) {
                    $table->foreignId('document_status_id')->default(default_document_status()->id)->nullable()
                        ->comment('Identificador único asociado al estatus del documento')
                        ->constrained('document_status')
                        ->onDelete('restrict')->onUpdate('cascade');
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
        if (Schema::hasTable('asset_disincorporations')) {
            Schema::table('asset_disincorporations', function (Blueprint $table) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
            });
        }
    }
}
