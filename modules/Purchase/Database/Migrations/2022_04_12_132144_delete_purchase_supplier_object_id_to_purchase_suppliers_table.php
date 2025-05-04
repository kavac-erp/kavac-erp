<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeletePurchaseSupplierObjectIdToPurchaseSuppliersTable
 * @brief Elimina llave foranea de objetos de proveedor de la tabla proveedores
 *
 * Elimina llave foranea de objetos de proveedor de la tabla proveedores
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeletePurchaseSupplierObjectIdToPurchaseSuppliersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_suppliers', 'purchase_supplier_object_id')) {
                $table->dropForeign(['purchase_supplier_object_id']);
                $table->dropColumn('purchase_supplier_object_id');
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
        Schema::table('purchase_suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_suppliers', 'purchase_supplier_object_id')) {
                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación del objeto del proveedor
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación a la información del objeto del proveedor
                */
                $table->foreignId('purchase_supplier_object_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
