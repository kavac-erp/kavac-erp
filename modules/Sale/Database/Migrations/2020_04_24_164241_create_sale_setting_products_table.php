<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleSettingProductsTable
 * @brief Migración encargada de crear la tabla de productos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleSettingProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_setting_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Nombre');
            $table->foreignId('sale_setting_product_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->string('code')->comment('Código');
            $table->string('description')->comment('Descripción');
            $table->string('price')->comment('Precio unitario');
            $table->string('iva')->nullable()
                  ->comment('IVA');

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
        Schema::dropIfExists('sale_setting_products');
    }
}
