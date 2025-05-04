<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateFinancePayOrderFinancePaymentExecuteTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateFinancePayOrderFinancePaymentExecuteTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_pay_order_finance_payment_execute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_pay_order_id')->comment('Identificador único asociado a la órden de pago')
                  ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('finance_payment_execute_id')->comment('Identificador único asociado a la ejecución de pago')
                  ->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('finance_pay_order_finance_payment_execute');
    }
}
