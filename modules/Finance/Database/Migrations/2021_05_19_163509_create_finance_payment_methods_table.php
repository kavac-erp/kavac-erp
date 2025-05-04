<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancePaymentMethodsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('finance_payment_methods')) {
            Schema::create('finance_payment_methods', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 100)->unique()->comment('Nombre de la forma de pago');
                $table->string('description')->comment('Descripción de la forma de pago');
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
        Schema::dropIfExists('finance_payment_methods');
    }
}
