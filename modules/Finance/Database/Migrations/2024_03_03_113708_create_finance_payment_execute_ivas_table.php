<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancePaymentExecuteIvasTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_payment_execute_ivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_payment_execute_id')->comment('Identificador único asociado a la ejecución de pago')
            ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('finance_payment_deductions_id')->comment('Identificador único asociado a la deduciones de ejecución de pago')
            ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->string('code', 20)->nullable()->comment('Código asociado a la retencion generada');
            $table->string('percentage', 10)->nullable()->comment('Porcentaje de Iva');
            $table->string('total_purchases_iva', 100)->nullable()->comment('Total compras con Iva');
            $table->string('total_purchases_without_iva', 100)->nullable()->comment('Total compras sin Iva');
            $table->string('percentage_retained', 100)->nullable()->comment('Porcentaje de Iva retenido');
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
        Schema::dropIfExists('finance_payment_execute_ivas');
    }
}
