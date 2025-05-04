<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldSuspendedYearsToPayrollSuspensionVacationRequestsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * Migración para borrar campo suspended_years de la tabla de suspension a la solicitud de vacaciones
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldSuspendedYearsToPayrollSuspensionVacationRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_suspension_vacation_requests')) {
            Schema::table('payroll_suspension_vacation_requests', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_suspension_vacation_requests', 'suspended_years')) {
                    $table->dropColumn('suspended_years');
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
        if (Schema::hasTable('payroll_suspension_vacation_requests')) {
            Schema::table('payroll_suspension_vacation_requests', function (Blueprint $table) {
                $table->longText('suspended_years')
                        ->comment('Años suspendidos de la solicitud de vacaciones');
            });
        }
    }
}
