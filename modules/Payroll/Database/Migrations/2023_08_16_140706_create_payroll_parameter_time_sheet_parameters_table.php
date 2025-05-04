<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollParameterTimeSheetParametersTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollParameterTimeSheetParametersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_parameter_time_sheet_parameters', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('payroll_time_sheet_parameter_id')
                ->nullable()
                ->constrained('payroll_time_sheet_parameters')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table
                ->foreignId('parameter_id')
                ->nullable()
                ->constrained('parameters')
                ->onDelete('restrict')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('payroll_parameter_time_sheet_parameters');
    }
}
