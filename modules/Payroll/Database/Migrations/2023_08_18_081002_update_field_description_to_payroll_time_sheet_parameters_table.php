<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldDescriptionToPayrollTimeSheetParametersTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldDescriptionToPayrollTimeSheetParametersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_time_sheet_parameters', function (Blueprint $table) {
            $table
                ->string('description', 200)
                ->comment('Descripción de los parámetros de la hoja de tiempo')
                ->nullable()
                ->change();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_time_sheet_parameters', function (Blueprint $table) {
            $table
                ->string('description', 200)
                ->comment('Descripción de los parámetros de la hoja de tiempo')
                ->change();
        });
    }
}
