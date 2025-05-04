<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeletePurchaseSupplierTypeIdToPurchaseRequirementsTable
 * @brief Migración encargada de borrar el campo purchase_supplier_type_id de la tabla purchase_requirements
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeletePurchaseSupplierTypeIdToPurchaseRequirementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('', function (Blueprint $table) {
        });
        Schema::table('purchase_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requirements', 'purchase_supplier_type_id')) {
                $table->dropForeign(['purchase_supplier_type_id']);
                $table->dropColumn('purchase_supplier_type_id');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        // No es necesario ya que se corrigio la relacion
        // Schema::table('purchase_requirements', function (Blueprint $table) {
        //     if (!Schema::hasColumn('purchase_requirements', 'purchase_supplier_type_id')) {
        //         $table->foreignId('purchase_supplier_type_id')->nullable()->constrained()->onDelete('cascade')->comment(
        //             'id del tipo de tipo de proveedor a relacionar con el registro'
        //         );
        //     }
        // });
    }
}
