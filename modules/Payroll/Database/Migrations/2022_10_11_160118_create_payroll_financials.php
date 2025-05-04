<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollFinancials
 * @brief Migración para crear tabla de finanzas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollFinancials extends Migration
{
    /**
     * Ejecuta la Migración
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_financials')) {
            Schema::create('payroll_financials', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('payroll_staff_id')->unique()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('finance_bank_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('finance_account_type_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->string('payroll_account_number', 20)->comment('Número de cuenta');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_financials');
    }
}
