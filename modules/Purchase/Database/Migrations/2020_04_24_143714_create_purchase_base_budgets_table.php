<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseBaseBudgetsTable
 * @brief Migración encargada de crear la tabla de presupuestos base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseBaseBudgetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_base_budgets')) {
            Schema::create('purchase_base_budgets', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->integer('currency_id')->unsigned()->nullable()
                      ->comment('Unidad monetaria en la que se expresara el presupuesto base.');

                $table->float('subtotal', 12, 10)->nullable()
                          ->comment('Subtotal del registro de presupuesto base');

                $table->enum('status', ['WAIT', 'QUOTED', 'WAIT_QUOTATION', 'BOUGHT'])->default('WAIT')
                          ->comment(
                              'Determina el estatus del presupuesto base
                              (WAIT) - espera por ser completado.
                              (WAIT_QUOTATION) - espera ser cotizado.
                              (QUOTED) - Cotizado,
                              (BOUGHT) - comprado',
                          );
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
        Schema::dropIfExists('purchase_base_budgets');
    }
}
