<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldWorksheetCodeToPayrollEmploymentsTable
 * @brief Añadir campo worksheet_code en payroll_employments
 *
 * Migración para añadir un campo llamado ficha en expediente de trabajador
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldWorksheetCodeToPayrollEmploymentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            $table->integer('worksheet_code')->unsigned()->nullable()->comment('Número de ficha de empleado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            $table->dropColumn('worksheet_code');
        });
    }
}
