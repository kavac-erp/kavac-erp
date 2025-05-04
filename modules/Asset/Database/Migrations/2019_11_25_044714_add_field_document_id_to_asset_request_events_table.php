<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDocumentIdToAssetRequestEventsTable
 * @brief Agregar campo documento a la tabla de los eventos ocurridos a bienes solicitados
 *
 * Gestiona la creación o eliminación del campo documeto de la tabla de eventos asociados a bienes solicitados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDocumentIdToAssetRequestEventsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_request_events')) {
            Schema::table('asset_request_events', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_request_events', 'document_id')) {
                    $table->foreignId('document_id')->nullable()->constrained()
                          ->onDelete('restrict')->onUpdate('cascade');
                };
            });
        };
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('asset_request_events')) {
            Schema::table('asset_request_events', function (Blueprint $table) {
                if (Schema::hasColumn('asset_request_events', 'document_id')) {
                    $table->dropForeign(['document_id']);
                    $table->dropColumn('document_id');
                };
            });
        };
    }
}
