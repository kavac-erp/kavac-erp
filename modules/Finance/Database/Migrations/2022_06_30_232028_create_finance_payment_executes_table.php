<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateFinancePaymentExecutesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateFinancePaymentExecutesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_payment_executes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique()->comment('Código de la ejecución de pago');
            $table->date('paid_at')->default(DB::Raw('CURRENT_TIMESTAMP'))->comment('Fecha del pago o rechazo');
            $table->boolean('has_budget')->default(false)->comment('Establece si el pago contiene datos de presupuesto');
            $table->boolean('is_partial')->default(false)->comment('Establece si el monto del pago es parcial');
            $table->decimal('source_amount', 20, 10)->default(0)
                      ->comment('Monto de la órden de pago');
            $table->decimal('deduction_amount')->default(0)->comment('Monto total de las retenciones');
            $table->decimal('paid_amount', 20, 10)->default(0)->comment('Monto a pagar en la ejecución de pago');
            $table->decimal('pending_amount')->default(0)->comment('Monto pendiente por cancelar de la órden de pago');
            $table->boolean('completed')->default(true)
                  ->comment('Establece si el pago esta completo en relación a las órdenes de pago');
            $table->longText('observations')->nullable()->comment('Observaciones del pago');
            $table->string('status', 2)->default('PE')->comment('Estatus de la orden: (PE)ndiente, (PA)gada, (PP)arcialmente pagado');
            $table->foreignId('document_status_id')->comment('Identificador único asociado al estatus del documento')
                  ->constrained('document_status')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('currency_id')->comment('Identificador único asociado al tipo de moneda')->constrained()
                  ->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('finance_payment_executes');
    }
}
