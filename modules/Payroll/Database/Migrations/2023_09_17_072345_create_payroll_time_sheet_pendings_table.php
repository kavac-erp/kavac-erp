<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollTimeSheetPendingsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollTimeSheetPendingsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_time_sheet_pendings', function (Blueprint $table) {
            $table->id();
            $table->date('from_date')->comment('Campo desde para el periodo de la hoja de tiempo pendiente');
            $table->date('to_date')->comment('Campo hasta para el periodo de la hoja de tiempo pendiente');
            $table
                ->foreignId('payroll_time_sheet_parameter_id')
                ->constrained('payroll_time_sheet_parameters')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table
                ->foreignId('payroll_supervised_group_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table
                ->foreignId('document_status_id')
                ->constrained('document_status')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->json('time_sheet_data')->comment('Datos de la hoja de tiempo pendiente');
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
        Schema::dropIfExists('payroll_time_sheet_pendings');
    }
}
