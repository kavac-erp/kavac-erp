<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangePurchaseObjectSupplierTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangePurchaseObjectSupplierTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_object_supplier', function (Blueprint $table) {
            /*
            * -----------------------------------------------------------------------
            * Clave foránea a la relación del proveedor
            * -----------------------------------------------------------------------
            */
            $table->dropForeign(['purchase_supplier_id']);
            $table->dropColumn('purchase_supplier_id');

            /*
            * -----------------------------------------------------------------------
            * Clave foránea a la relación del objeto del proveedor
            * -----------------------------------------------------------------------
            */
            $table->dropForeign(['purchase_supplier_object_id']);
            $table->dropColumn('purchase_supplier_object_id');
        });

        Schema::table('purchase_object_supplier', function (Blueprint $table) {
            /*
            * -----------------------------------------------------------------------
            * Clave foránea a la relación del proveedor
            * -----------------------------------------------------------------------
            */
            $table->foreignId('purchase_supplier_id')->nullable()->constrained('purchase_suppliers')
                ->cascadeOnUpdate()->nullOnDelete();

            /*
            * -----------------------------------------------------------------------
            * Clave foránea a la relación del objeto del proveedor
            * -----------------------------------------------------------------------
            */
            $table->foreignId('purchase_supplier_object_id')->nullable()->constrained('purchase_supplier_objects')
                ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
