<?php

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseRequirementItemsTable
 * @brief Migración encargada de crear la tabla de items de requerimientos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseRequirementItemsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_requirement_items')) {
            Schema::create('purchase_requirement_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->comment('Nombre del producto a solicitar para su compra');
                $table->string('description')->nullable()->comment('descripción del producto a solicitar');
                $table->longText('technical_specifications')->nullable()
                          ->comment('Especificaciones técnicas del producto a requerir. Opcional');
                $table->float('quantity')->default(1)->comment("Cantidad del producto a solicitar");

                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación del producto
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación al producto
                */
                $table->bigInteger('warehouse_product_id')->unsigned()->nullable()
                          ->comment(
                              'Identificador del producto a solicitar para su compra si ya existe un producto
                                    registrado con las mismas características'
                          );
                if (Module::has('Wareouse') && Module::isEnabled('Wareouse')) {
                    $table->foreign('warehouse_product_id')->references('id')
                              ->on('warehouse_products')->onDelete('restrict')
                              ->onUpdate('cascade');
                }

                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación del requerimiento
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación al requerimiento
                */
                $table->bigInteger('purchase_requirement_id')->unsigned()
                          ->comment('Identificador del requerimiento de compra');
                $table->foreign('purchase_requirement_id')->references('id')
                          ->on('purchase_requirements')->onDelete('restrict')
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
        Schema::dropIfExists('purchase_requirement_items');
    }
}
