<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSalaryAdjustmentsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSalaryAdjustmentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_salary_adjustments', function (Blueprint $table) {
            $table->id();

            $table->date('increase_of_date')->nullable()->comment('Fecha del aumento');
            $table->string('increase_of_type')->nullable()->comment('Tipo de aumento');
            $table->decimal('value')->nullable()->comment('Valor');
            $table->foreignId('payroll_salary_tabulator_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')->comment('Tabulador asociado al registro');
            
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
        Schema::dropIfExists('payroll_salary_adjustments');
    }
}
