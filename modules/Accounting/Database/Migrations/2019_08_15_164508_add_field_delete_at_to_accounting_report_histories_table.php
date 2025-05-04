<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDeleteAtToAccountingReportHistoriesTable
 * @brief Ejecuta la migración para agregar el campo deleted_at a la tabla accounting_report_histories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDeleteAtToAccountingReportHistoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_report_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_report_histories', 'deleted_at')) {
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
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
            if (Schema::hasColumn('accounting_report_histories', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
}
