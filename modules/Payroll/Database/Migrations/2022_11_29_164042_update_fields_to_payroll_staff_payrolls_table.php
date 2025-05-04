<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToPayrollStaffPayrollsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToPayrollStaffPayrollsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_staff_payrolls')) {
            Schema::table('payroll_staff_payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_staff_payrolls', 'assignments')) {
                    $table->dropColumn('assignments');
                }
                if (Schema::hasColumn('payroll_staff_payrolls', 'deductions')) {
                    $table->dropColumn('deductions');
                }
                if (!Schema::hasColumn('payroll_staff_payrolls', 'concept_type')) {
                    $table->json('concept_type')->nullable()
                      ->comment('Conjunto de [nombre - valor] de los conceptos establecidos en el salario');
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
        if (Schema::hasTable('payroll_staff_payrolls')) {
            Schema::table('payroll_staff_payrolls', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_staff_payrolls', 'assignments')) {
                    $table->text('assignments')->nullable()
                      ->comment('Conjunto de [nombre - valor] de los conceptos establecidos como ' .
                                'ingresos adicionales al salario');
                }
                if (!Schema::hasColumn('payroll_staff_payrolls', 'deductions')) {
                    $table->text('deductions')->nullable()
                      ->comment('Conjunto de [nombre - valor] de los conceptos establecidos como ' .
                                'descuentos al salario');
                }
                if (Schema::hasColumn('payroll_staff_payrolls', 'concept_type')) {
                    $table->dropColumn('concept_type');
                }
            });
        }
    }
}
