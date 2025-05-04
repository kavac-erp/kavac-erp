<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDocumentStatusIdToPayrollTimeSheetsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDocumentStatusIdToPayrollTimeSheetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_time_sheets', function (Blueprint $table) {
            $table
                ->foreignId('document_status_id')
                ->constrained('document_status')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_time_sheets', function (Blueprint $table) {
            $table->dropForeign(['document_status_id']);
            $table->dropColumn('document_status_id');
        });
    }
}
