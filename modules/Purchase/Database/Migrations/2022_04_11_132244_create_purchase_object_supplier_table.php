<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseObjectSupplierTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseObjectSupplierTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_object_supplier', function (Blueprint $table) {
            $table->bigIncrements('id');

            /*
            * -----------------------------------------------------------------------
            * Clave foránea a la relación del proveedor
            * -----------------------------------------------------------------------
            *
            * Define la estructura de relación a la información del proveedor
            */
            $table->foreignId('purchase_supplier_id')->constrained()
                    ->onDelete('restrict')->onUpdate('cascade');

            /*
            * -----------------------------------------------------------------------
            * Clave foránea a la relación del objeto del proveedor
            * -----------------------------------------------------------------------
            *
            * Define la estructura de relación a la información del objeto del proveedor
            */
            $table->foreignId('purchase_supplier_object_id')->constrained()
                    ->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
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
