<?php

use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancePayOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('finance_pay_orders')) {
            Schema::create('finance_pay_orders', function (Blueprint $table) {
                $table->id();

                $table->string('code', 100)->unique()->comment('Código de la órden de pago');
                $table->date('ordered_at')->default(DB::Raw('CURRENT_TIMESTAMP'))->comment('Fecha de la orden de pago');
                $table->enum('type', ['PR', 'NP'])->default('PR')
                      ->comment('Tipo de órden de pago. PR = Presupuestario, NP = No Presupuestario');
                $table->boolean('is_partial')->default(false)->comment('Establece si el monto de la órden es parcial');
                $table->decimal('pending_amount')->default(0)->comment('Monto pendiente por cancelar de la órden de pago');
                $table->boolean('completed')->default(true)
                      ->comment('Establece si la órden de pago tiene el monto completo ordenado. Para cuando es pago Total el valor es verdadero, cuando el pago es parcial, el monto es falso hasta completar el total del documento de origen');
                $table->string('document_number')->nullable()
                      ->comment('Número del documento que genera la órden de pago. Este campo es opcional si proviene de una fuente previamente registrada en otro módulo de la aplicación');
                $table->decimal('source_amount', 20, 10)->default(0)
                      ->comment('Monto del documento fuente que genera la órden de pago');
                $table->decimal('amount', 20, 10)->default(0)->comment('Monto de la órden de pago');
                $table->longText('concept')->nullable()->comment('Concepto de la órden de pago');
                $table->longText('observations')->nullable()->comment('Observaciones de la órden de pago');
                $table->enum('status', ['PE', 'PA'])->default('PE')
                      ->comment('Establece el estatus de la órden de pago: (PE)ndiente, (PA)gada');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');

                if (Module::has('Budget') && Module::isEnabled('Budget')) {
                    /** Relación a la acción específica en presupuesto */
                    $table->foreignId('budget_specific_action_id')->nullable()->constrained()
                          ->onDelete('restrict')->onUpdate('cascade');
                }
                $table->foreignId('finance_payment_method_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('finance_bank_account_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('institution_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                /* Relación al documento de origen */
                $table->nullableMorphs('document_sourceable');
                /* Relación al proveedor o beneficiario de la órden de pago */
                $table->nullableMorphs('name_sourceable');
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
        Schema::dropIfExists('finance_pay_orders');
    }
}
