<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddPurchaseSupplierObjectIdToPurchaseRequirementsTable
 * @brief Migración encargada de agregar el campo purchase_supplier_object_id a la tabla purchase_requirements
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddPurchaseSupplierObjectIdToPurchaseRequirementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requirements', 'purchase_supplier_object_id')) {
                /*
                * -----------------------------------------------------------------------
                * Clave foránea a la relación del producto
                * -----------------------------------------------------------------------
                *
                * Define la estructura de relación al producto
                */
                $table->foreignId('purchase_supplier_object_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'id del tipo de objeto de proveedor a relacionar con el registro'
                );
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
        // No se requiere ya que este campo siempre debe estar
        // Schema::table('purchase_requirements', function (Blueprint $table) {
        //     if (Schema::hasColumn('purchase_requirements', 'purchase_supplier_object_id')) {
        //         $table->dropForeign(['purchase_supplier_object_id']);
        //         $table->dropColumn('purchase_supplier_object_id');
        //     }
        // });
    }
}
