<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseTypesTable
 * @brief Migración encargada de crear la tabla de tipos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_types')) {
            Schema::create('purchase_types', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('name')->comment('Nombre del tipo o modalidad de compra');
                $table->text('description')->comment('Descripción del tipo de compra de compra');

                $table->bigInteger('purchase_processes_id')->unsigned()->nullable()
                          ->comment('Clave foránea a la relación del proceso de compra');
                $table->foreign('purchase_processes_id')->references('id')
                          ->on('purchase_processes')->onDelete('restrict')
                          ->onUpdate('cascade');

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
        Schema::dropIfExists('purchase_types');
    }
}
