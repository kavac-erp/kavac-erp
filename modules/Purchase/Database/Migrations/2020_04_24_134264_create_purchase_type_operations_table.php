<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseTypeOperationsTable
 * @brief Migración encargada de crear los tipos de operaciones de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseTypeOperationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_type_operations')) {
            Schema::create('purchase_type_operations', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('name')->comment('Nombre del tipo de compra');
                $table->text('description')->nullable()->comment('Descripción del tipo de compra');

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
        Schema::dropIfExists('purchase_type_operations');
    }
}
