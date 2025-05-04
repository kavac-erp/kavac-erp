<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseSupplierObjectsTable
 * @brief Migración encargada de crear la tabla de objetos de proveedores
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseSupplierObjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_supplier_objects')) {
            Schema::create('purchase_supplier_objects', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->enum('type', ['B', 'O', 'S'])
                      ->comment('Tipo de objeto de la empresa. (B)ienes, (O)bras y (S)ervicios');
                $table->string('name')->comment('Nombre del objeto del proveedor');
                $table->text('description')->nullable()
                      ->comment('Descripción del objeto del proveedor');
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
        Schema::dropIfExists('purchase_supplier_objects');
    }
}
