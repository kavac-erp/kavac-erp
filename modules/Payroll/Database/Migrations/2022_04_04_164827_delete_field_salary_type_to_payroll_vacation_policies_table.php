<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldSalaryTypeToPayrollVacationPoliciesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldSalaryTypeToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_vacation_policies', 'salary_type')) {
                $table->dropColumn('salary_type');
            };
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->enum(
                'salary_type',
                ['base_salary', 'comprehensive_salary', 'normal_salary', 'dialy_salary']
            )->nullable()->comment('Establece el salario a emplear para el cálculo del bono vacacional ' .
                       '(base_salary: Salario base, comprehensive_salary: Salario integral,' .
                       ' normal_salary: Salario normal, dialy_salary: Salario diario)');
        });
    }
}
