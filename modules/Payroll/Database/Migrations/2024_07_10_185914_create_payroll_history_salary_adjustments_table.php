<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollHistorySalaryAdjustmentsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollHistorySalaryAdjustmentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_history_salary_adjustments')) {
            Schema::create('payroll_history_salary_adjustments', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');
                $table->date('increase_of_date')->comment('Fecha de entrada en vigencia del ajuste salarial');
                $table->date('end_increase_date')->nullable()->comment('Fecha de culminación del ajuste salarial');
                $table->longText('salary_values')->nullable()
                    ->comment('Valores asignados al tabulador de nómina para el ajuste salarial');
                $table->foreignId('payroll_salary_adjustment_id')->constrained()->onUpdate('cascade');
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
        Schema::dropIfExists('payroll_history_salary_adjustments');
    }
}
