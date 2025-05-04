<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTimeSheetColumnsToPayrollTimeSheetPendingsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTimeSheetColumnsToPayrollTimeSheetPendingsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_time_sheet_pendings', function (Blueprint $table) {
            $table
                ->json('time_sheet_columns')
                ->comment('Columnas de la hoja de tiempo')
                ->nullable();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_time_sheet_pendings', function (Blueprint $table) {
            $table->dropColumn('time_sheet_columns');
        });
    }
}
