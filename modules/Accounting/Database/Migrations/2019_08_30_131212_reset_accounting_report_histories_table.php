<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ResetAccountingReportHistoriesTable
 * @brief Ejecuta la migración para resetear la tabla accounting_report_histories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ResetAccountingReportHistoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('accounting_report_histories')) {
            // TODO: Antes de eliminar la tabla se debe realizar el procedimiento de respaldo de los datos en caso de que existan registros
            Schema::dropIfExists('accounting_report_histories');

            Schema::create('accounting_report_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('url');
                $table->string('report')->comment('Tipo de reporte')
                ->comment(
                    'Tipo de reporte generado: (1)Balance de comprobacion,
                    (2)Mayor Analítico, (3) Libro Diario, (4)Libro Auxiliar,
                     (5)Balance general, (6)Estado de resultados'
                );
                $table->timestamps();
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('accounting_report_histories')) {
            Schema::create('accounting_report_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('url');
                $table->string('report')->comment('Tipo de reporte')
                ->comment(
                    'Tipo de reporte generado: (1)Balance de comprobacion,
                    (2)Mayor Analítico, (3) Libro Diario, (4)Libro Auxiliar,
                     (5)Balance general, (6)Estado de resultados'
                );
                $table->timestamps();
            });
        }
    }
}
