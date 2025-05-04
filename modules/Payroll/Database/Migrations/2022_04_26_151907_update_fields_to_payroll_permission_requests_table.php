<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToPayrollPermissionRequestsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToPayrollPermissionRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_permission_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_permission_requests', 'start_time')) {
                $table->time('start_time', $precision = 0)->nullable();
            }
            if (!Schema::hasColumn('payroll_permission_requests', 'end_time')) {
                $table->time('end_time', $precision = 0)->nullable();
            }
            if (Schema::hasColumn('payroll_permission_requests', 'day_permission')) {
                $table->dropColumn('day_permission');
                $table->string('time_permission')->nullable()->comment('Tiempo establecido para el permiso solicitado');
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
        Schema::table('payroll_permission_requests', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_permission_requests', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('payroll_permission_requests', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (!Schema::hasColumn('payroll_permission_requests', 'day_permission')) {
                $table->dropColumn('time_permission');
                $table->integer('day_permission')->nullable();
            }
        });
    }
}
