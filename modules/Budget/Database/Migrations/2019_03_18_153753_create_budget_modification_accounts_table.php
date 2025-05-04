<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetModificationAccountsTable
 * @brief Crear tabla de cuentas contables a asociar a modificaciones presupuestarias
 *
 * Gestiona la creación o eliminación de la tabla de cuentas contables a asociar a modificaciones presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetModificationAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_modification_accounts')) {
            Schema::create('budget_modification_accounts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->float('amount', 30, 10)->comment('Monto asignado a la cuenta presupuestaria');
                $table->enum('operation', ['I', 'D'])
                      ->comment('Operación a realizar: (I)ncrementa o (D)isminuye');
                $table->unsignedBigInteger('budget_sub_specific_formulation_id')
                      ->comment('Identificador asociado a la Formulación');
                $table->foreign(
                    'budget_sub_specific_formulation_id',
                    'budget_modification_accounts_sub_specific_formulation_fk'
                )->references('id')->on('budget_sub_specific_formulations')->onUpdate('cascade');

                $table->foreignId('budget_account_id')->constrained()->onUpdate('cascade');
                $table->foreignId('budget_modification_id')->constrained()->onUpdate('cascade');
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
        Schema::dropIfExists('budget_modification_accounts');
    }
}
