<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldCurrencyIdToAccountingReportHistoriesTable
 * @brief Ejecuta la migración para agregar el campo currency_id a la tabla accounting_report_histories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldCurrencyIdToAccountingReportHistoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_report_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_report_histories', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'id del tipo de moneda en que se expresa el asiento'
                );
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
            if (Schema::hasColumn('accounting_report_histories', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });
    }
}
