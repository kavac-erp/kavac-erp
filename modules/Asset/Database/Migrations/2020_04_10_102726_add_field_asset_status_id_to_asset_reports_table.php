<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class      AddFieldAssetStatusIdToAssetReportsTable
 * @brief      Agrega el campo asset_status_id a la tabla asset_reports
 *
 * Gestiona la creación o eliminación del campo  asset_status_id de la tabla asset_reports
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAssetStatusIdToAssetReportsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @return    void
     */
    public function up()
    {
        if (Schema::hasTable('asset_reports')) {
            Schema::table('asset_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_reports', 'asset_status_id')) {
                    $table->foreignId('asset_status_id')->nullable()->constrained('asset_status')
                          ->onDelete('restrict')->onUpdate('cascade');
                }
            });
        };
    }

    /**
     * Método que elimina las migraciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @return    void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('asset_reports')) {
            Schema::table('asset_reports', function (Blueprint $table) {
                if (Schema::hasColumn('asset_reports', 'asset_status_id')) {
                    $table->dropForeign(['asset_status_id']);
                    $table->dropColumn(['asset_status_id']);
                };
            });
        };
        Schema::enableForeignKeyConstraints();
    }
}
