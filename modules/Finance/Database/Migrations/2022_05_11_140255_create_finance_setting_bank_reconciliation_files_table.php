<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceSettingBankReconciliationFilesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('finance_setting_bank_reconciliation_files')) {
            Schema::create('finance_setting_bank_reconciliation_files', function (Blueprint $table) {
                $table->id();
                $table->string('bank_id')->comment('Id del banco');
                $table->boolean('read_start_line')->default(false)->comment('Indica si leerá la línea de inicio');
                $table->boolean('read_end_line')->default(false)->comment('Indica si leerá la línea final');
                $table->integer('position_reference_column')->nullable()->comment('Referencia');
                $table->integer('position_date_column')->nullable()->comment('Fecha');
                $table->integer('position_debit_amount_column')->nullable()->comment('Monto débito');
                $table->integer('position_credit_amount_column')->nullable()->comment('Monto crédito');
                $table->integer('position_description_column')->nullable()->comment('Descripción');
                $table->string('separated_by')->nullable()->comment('Columnas separadas por');
                $table->string('date_format')->nullable()->comment('Formato de fecha');
                $table->string('thousands_separator')->nullable()->comment('Separador de miles');
                $table->string('decimal_separator')->nullable()->comment('Separador de decimales');
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
        Schema::dropIfExists('finance_setting_bank_reconciliation_files');
    }
}
