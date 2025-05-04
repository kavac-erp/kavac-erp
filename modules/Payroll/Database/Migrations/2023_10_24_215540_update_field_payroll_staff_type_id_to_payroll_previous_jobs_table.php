<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldPayrollStaffTypeIdToPayrollPreviousJobsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldPayrollStaffTypeIdToPayrollPreviousJobsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_previous_jobs')) {
            if (Schema::hasColumn('payroll_previous_jobs', 'payroll_staff_type_id')) {
                Schema::table('payroll_previous_jobs', function (Blueprint $table) {
                    $table
                        ->foreignId('payroll_staff_type_id')
                        ->nullable()
                        ->change();
                });
            }
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
            if (Schema::hasColumn('payroll_previous_jobs', 'payroll_staff_type_id')) {
                Schema::table('payroll_previous_jobs', function (Blueprint $table) {
                    $table
                        ->foreignId('payroll_staff_type_id')
                        ->nullable()
                        ->change();
                });
            }
        }
    }
}
