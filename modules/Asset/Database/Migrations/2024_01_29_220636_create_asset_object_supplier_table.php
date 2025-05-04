<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetObjectSupplierTable
 * @brief Crea la tabla de asset_object_suppliers
 *
 * Crea la tabla de asset_object_suppliers
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetObjectSupplierTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_object_supplier')) {
            Schema::create('purchase_object_supplier', function (Blueprint $table) {
                $table->bigIncrements('id');

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

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_object_supplier');
    }
}
