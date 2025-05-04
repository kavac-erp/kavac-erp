<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateFinanceBankingMovementsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateFinanceBankingMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_banking_movements', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date')->comment('Fecha del pago');
            $table->string('transaction_type')->comment('Tipo de transacción');
            $table->string('reference')->comment('Documento de referencia');
            $table->string('concept')->comment('Concepto');
            $table->string('amount')->comment('Monto');
            $table->foreignId('finance_bank_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('currency_id')->nullable()->constrained()->onDelete('cascade')->comment('Tipo de moneda');

            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                /** Relación a la acción específica en presupuesto */
                $table->foreignId('accounting_entry_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }

            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                /** Relación a la acción específica en presupuesto */
                $table->foreignId('budget_compromise_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }

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
        Schema::dropIfExists('finance_banking_movements');
    }
}
