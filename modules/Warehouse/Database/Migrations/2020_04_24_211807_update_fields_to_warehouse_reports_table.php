<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToWarehouseReportsTable
 * @brief Actualiza los campos de la tabla de reportes de almacén
 *
 * Gestiona la creación o eliminación de los campos de la tabla de reportes de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToWarehouseReportsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_reports')) {
            Schema::table('warehouse_reports', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_reports', 'warehouse_product_id')) {
                    $table->dropForeign('warehouse_reports_warehouse_product_id_foreign');
                    $table->dropColumn(['warehouse_product_id']);
                };
                if (Schema::hasColumn('warehouse_reports', 'warehouse_id')) {
                    $table->dropForeign('warehouse_reports_warehouse_id_foreign');
                    $table->dropColumn(['warehouse_id']);
                };
                if (!Schema::hasColumn('warehouse_reports', 'filename')) {
                    $table->string('filename')->nullable()->comment('nombre del documento');
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
        if (Schema::hasTable('warehouse_reports')) {
            Schema::table('warehouse_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_reports', 'warehouse_product_id')) {
                    $table->foreignId('warehouse_product_id')->nullable()->constrained()
                          ->onDelete('restrict')->onUpdate('cascade')
                          ->comment('Identificador único del producto almacenable asociado al reporte');
                };
                if (!Schema::hasColumn('warehouse_reports', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->constrained()
                          ->onDelete('restrict')->onUpdate('cascade')
                          ->comment('Identificador único del almacén asociado al reporte');
                };
                if (Schema::hasColumn('warehouse_reports', 'filename')) {
                    $table->dropColumn(['filename']);
                };
            });
        };
    }
}
