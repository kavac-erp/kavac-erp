<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceConciliationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_conciliations', function (Blueprint $table) {
            $table->id();

            $table->string('code')->comment('Código que identifica de la conciliacion bancaria');

            $table->foreignId('finance_bank_account_id')->comment('Identificador único asociado a la cuenta bancaria')
                  ->constrained()->onDelete('restrict')->onUpdate('cascade');

            $table->date('start_date')->comment('Rango de fecha');
            $table->date('end_date')->comment('Rango de fecha');

            $table->foreignId('institution_id')->nullable()->constrained()
                ->onDelete('restrict')->onUpdate('cascade')->comment('Institución');

            $table->foreignId('currency_id')->comment('Identificador único asociado al tipo de moneda')->constrained()
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreignId('document_status_id')->nullable()
                ->default(default_document_status()->id)
                ->comment('Identificador único asociado al estatus del documento')->constrained('document_status')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->float('bank_balance', 30, 10)->comment('saldo en el banco');

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
        Schema::dropIfExists('finance_conciliations');
    }
}
