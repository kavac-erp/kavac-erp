<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceConciliationBankMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_conciliation_bank_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_conciliation_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('finance_banking_movement_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

            $table->longText('concept')->nullable()->comment('Concepto de la órden de pago');

            $table->float('debit', 30, 10)->comment('Monto asignado en débito');
            $table->float('assets', 30, 10)->comment('Monto asignado al crédito');

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
        Schema::dropIfExists('finance_conciliation_bank_movements');
    }
}
