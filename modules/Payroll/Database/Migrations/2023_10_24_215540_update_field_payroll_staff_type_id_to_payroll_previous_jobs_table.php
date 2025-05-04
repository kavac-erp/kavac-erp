<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldPayrollStaffTypeIdToPayrollPreviousJobsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
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
