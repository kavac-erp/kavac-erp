<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetAditionalCreditAccountsTable
 * @brief Crear tabla de cuentas presupuestarias a asociar a créditos adicionales
 *
 * Gestiona la creación o eliminación de la tabla de cuentas presupuestarias a asociar a créditos adicionales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetAditionalCreditAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_aditional_credit_accounts')) {
            Schema::create('budget_aditional_credit_accounts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->float('amount', 30, 10)->comment('Monto asignado a la cuenta presupuestaria');
                $table->unsignedBigInteger('budget_sub_specific_formulation_id')->nullable()
                      ->comment('Identificador asociado a la Formulación');
                $table->foreign(
                    'budget_sub_specific_formulation_id',
                    'budget_aditional_credit_accounts_sub_specific_fk'
                )->references('id')->on('budget_sub_specific_formulations')->onUpdate('cascade');


                $table->foreignId('budget_account_id')->nullable()->constrained()->onUpdate('cascade');
                $table->unsignedBigInteger('budget_aditional_credit_id')->nullable()
                      ->comment('Identificador asociado al crédito adicional');
                $table->foreign(
                    'budget_aditional_credit_id',
                    'budget_aditional_credit_accounts_aditional_credit_id'
                )->references('id')->on('budget_aditional_credits')->onUpdate('cascade');

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
        Schema::dropIfExists('budget_aditional_credit_accounts');
    }
}
