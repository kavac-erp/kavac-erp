<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetAccountsTable
 * @brief Crear tabla de cuentas presupuestarias
 *
 * Gestiona la creación o eliminación de la tabla de cuentas presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_accounts')) {
            Schema::create('budget_accounts', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');
                $table->char('group', 1)->comment('Grupo al que pertenece la cuenta');
                $table->char('item', 2)->comment('Item de la cuenta');
                $table->char('generic', 2)->comment('Código genérico de la cuenta');
                $table->char('specific', 2)->comment('Específica de la cuenta');
                $table->char('subspecific', 2)->comment('Subespecífica de la cuenta');
                $table->text('denomination')->comment('Descripción de la cuenta');
                $table->boolean('active')->default(true)->comment('Indica si la cuenta esta activa');
                $table->date('inactivity_date')->nullable()->comment('Fecha en la que se inactiva la cuenta');
                $table->boolean('resource')->comment('Indica si es una cuenta de reursos');
                $table->boolean('egress')->comment('Indica si es una cuenta de egresos');
                $table->boolean('original')->default(true)
                      ->comment('Indica si la cuenta es del clasificador presupuestario original');
                $table->bigInteger('parent_id')->nullable()->unsigned()
                      ->comment('Identificador asociado a la cuenta padre');
                $table->foreignId('tax_id')->nullable()->constrained()->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });

            Schema::table('budget_accounts', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('budget_accounts')->onUpdate('cascade');
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
        Schema::dropIfExists('budget_accounts');
    }
}
