<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPurchaseSupplierObjectIdToPurchaseDirectHiresTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPurchaseSupplierObjectIdToPurchaseDirectHiresTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_direct_hires', function (Blueprint $table) {
            $table->foreignId('purchase_supplier_object_id')->nullable()->constrained()->onDelete('cascade')->comment(
                'id del tipo de objeto de proveedor a relacionar con el registro'
            );
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_direct_hires', function (Blueprint $table) {
            $table->dropForeign(['purchase_supplier_object_id']);
            $table->dropColumn('purchase_supplier_object_id');
        });
    }
}
