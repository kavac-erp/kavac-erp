<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchasePivotModelsToRequirementItemsTable
 * @brief Migración encargada de crear la tabla pivote de items de requerimientos de compras
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchasePivotModelsToRequirementItemsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_pivot_models_to_requirement_items')) {
            Schema::create('purchase_pivot_models_to_requirement_items', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->morphs('relatable', 'purchase_pivot_models_to_requirement_items_index');

                $table->unsignedBigInteger('purchase_requirement_item_id');

                $table->float('unit_price', 10, 10)->nullable()
                              ->comment('Precio unitario del producto o servicio. asignado en orden de compra');

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
        Schema::dropIfExists('purchase_pivot_models_to_requirement_items');
    }
}
