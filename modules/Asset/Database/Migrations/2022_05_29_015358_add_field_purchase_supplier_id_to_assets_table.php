<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPurchaseSupplierIdToAssetsTable
 * @brief Agrega una clave foraénea purchase_supplier_id de la tabla de bienes
 *
 * Agrega una clave foraénea purchase_supplier_id de la tabla de bienes
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPurchaseSupplierIdToAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (!Schema::hasColumn('assets', 'purchase_supplier_id')) {
                    $table->foreignId('purchase_supplier_id')->nullable()->constrained()
                          ->onDelete('restrict')->onUpdate('cascade');
                };
            });
        };
    }

    /**
     *  Método que elimina las migraciones
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'purchase_supplier_id')) {
                    $table->dropForeign(['purchase_supplier_id']);
                    $table->dropColumn(['purchase_supplier_id']);
                };
            });
        };
    }
}
