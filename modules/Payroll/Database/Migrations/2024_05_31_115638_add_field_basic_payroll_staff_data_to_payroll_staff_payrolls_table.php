<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldBasicPayrollStaffDataToPayrollStaffPayrollTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldBasicPayrollStaffDataToPayrollStaffPayrollsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_staff_payrolls')
            && !Schema::hasColumn('payroll_staff_payrolls', 'basic_payroll_staff_data')) {
            Schema::table('payroll_staff_payrolls', function (Blueprint $table) {
                $table->json('basic_payroll_staff_data')->nullable()->comment('Información basica del trabajador');
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
        if (Schema::hasTable('payroll_staff_payrolls')
            && Schema::hasColumn('payroll_staff_payrolls', 'basic_payroll_staff_data')) {
            Schema::table('payroll_staff_payrolls', function (Blueprint $table) {
                $table->dropColumn('basic_payroll_staff_data');
            });
        }
    }
}
