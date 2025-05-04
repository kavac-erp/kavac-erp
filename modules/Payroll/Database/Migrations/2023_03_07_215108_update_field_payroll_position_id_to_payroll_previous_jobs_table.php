<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldPayrollPositionIdToPayrollPreviousJobsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldPayrollPositionIdToPayrollPreviousJobsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_previous_jobs')) {
            Schema::table('payroll_previous_jobs', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_previous_jobs', 'payroll_position_id')) {
                    $table->dropForeign('payroll_previous_jobs_payroll_position_id_foreign');
                    $table->dropColumn('payroll_position_id');
                }
                if (!Schema::hasColumn('payroll_previous_jobs', 'previous_position')) {
                    $table->string('previous_position')->nullable()->comment('Cargo');
                }
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
        if (Schema::hasTable('payroll_previous_jobs')) {
            Schema::table('payroll_previous_jobs', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_previous_jobs', 'payroll_position_id')) {
                    $table->foreignId('payroll_position_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade')->comment('Identificador único del cargo asociado al registro');
                }
                if (Schema::hasColumn('payroll_previous_jobs', 'previous_position')) {
                    $table->dropColumn('previous_position');
                }
            });
        }
    }
}
