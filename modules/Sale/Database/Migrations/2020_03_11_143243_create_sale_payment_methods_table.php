<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSalePaymentMethodsTable
 * @brief Migración encargada de crear la tabla de tipos de pago de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSalePaymentMethodsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sale_payment_methods')) {
            Schema::create('sale_payment_methods', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->string('name', 100)->unique()->comment('Nombre');
                $table->string('description', 200)->nullable()->comment('Descripción');
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
        Schema::dropIfExists('sale_payment_methods');
    }
}
