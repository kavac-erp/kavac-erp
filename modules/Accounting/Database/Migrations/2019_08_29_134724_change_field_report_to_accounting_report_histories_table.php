<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldReportToAccountingReportHistoriesTable
 * @brief Ejecuta la migración para modificar el campo report a la tabla accounting_report_histories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldReportToAccountingReportHistoriesTable extends Migration
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
                $table->string('report')->comment('Tipo de reporte')->change();
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
            if (!Schema::hasColumn('accounting_report_histories', 'report')) {
                $table->enum('report', [1, 2, 3, 4, 5, 6])
                    ->comment(
                        'Tipo de reporte generado: (1)Balance de comprobacion,
                        (2)Mayor Analítico, (3) Libro Diario, (4)Libro Auxiliar,
                         (5)Balance general, (6)Estado de resultados'
                    )->change();
            }
        });
    }
}
