<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateFinancePaymentDeductionsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateFinancePaymentDeductionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_payment_deductions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount')->default(0)->comment('Monto de la retención');
            $table->foreignId('deduction_id')->comment('Identificador único asociado a la retención')->constrained()
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('finance_payment_execute_id')
                  ->comment('Identificador único asociado a la ejecución de pago')->constrained()
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
        Schema::dropIfExists('finance_payment_deductions');
    }
}
