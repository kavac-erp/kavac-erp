<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingReportHistoriesTable
 * @brief Ejecuta la migración del reporte histórico de contabilidad
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingReportHistoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_report_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url');
            $table->enum('name', [1, 2, 3, 4, 5, 6])
            ->comment(
                'Tipo de reporte generado: (1)Balance de comprobacion,
                (2)Mayor Analítico, (3) Libro Diario, (4)Libro Auxiliar,
                 (5)Balance general, (6)Estado de resultados'
            );
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_report_histories');
    }
}
