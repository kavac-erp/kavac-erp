<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RenameFieldNameToAccountingReportHistoriesTable
 * @brief Ejecuta la migración para modificar el campo name a la tabla accounting_report_histories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RenameFieldNameToAccountingReportHistoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_report_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_report_histories', 'report')) {
                $table->renameColumn('name', 'report');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_report_histories', function (Blueprint $table) {
            $table->renameColumn('report', 'name');
        });
    }
}
