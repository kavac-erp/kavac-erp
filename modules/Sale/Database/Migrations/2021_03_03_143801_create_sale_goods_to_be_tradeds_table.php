<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleGoodsToBeTradedsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleGoodsToBeTradedsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_goods_to_be_tradeds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nombre');
            $table->string('description', 500)->comment('descripción');
            $table->integer('unit_price')->comment('Precio Unitario');
            $table->integer('coin')->comment('Moneda');
            $table->float('iva');
            $table->string('custom_attribute', 500)->comment('Atributo Personalizado');
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
        Schema::dropIfExists('sale_goods_to_be_tradeds');
    }
}
