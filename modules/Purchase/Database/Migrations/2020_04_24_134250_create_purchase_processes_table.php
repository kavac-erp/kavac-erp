<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseProcessesTable
 * @brief Migración encargada de crear la tabla de procesos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseProcessesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_processes')) {
            Schema::create('purchase_processes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->comment('Nombre del proceso de compra');
                $table->text('description')->comment('Descripción del proceso de compra');
                $table->boolean('require_documents')->default(false)
                      ->comment('Indica si el proceso de compra require cargar documentos');
                $table->longText('list_documents')->nullable()
                      ->comment('Listado de documentos a consignar en el proceso de compra');
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
        Schema::dropIfExists('purchase_processes');
    }
}
