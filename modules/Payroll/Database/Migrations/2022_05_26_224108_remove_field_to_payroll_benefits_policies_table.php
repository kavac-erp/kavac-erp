<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RemoveFieldToPayrollBenefitsPoliciesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveFieldToPayrollBenefitsPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_benefits_policies')) {
            Schema::table('payroll_benefits_policies', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_benefits_policies', 'salary_type')) {
                    $table->dropColumn('salary_type');
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
        if (Schema::hasTable('payroll_benefits_policies')) {
            Schema::table('payroll_benefits_policies', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_benefits_policies', 'salary_type')) {
                    $table->enum(
                        'salary_type',
                        ['base_salary', 'comprehensive_salary', 'normal_salary', 'dialy_salary']
                    )->nullable()->comment('Establece el salario a emplear para el cálculo del bono vacacional ' .
                        '(base_salary: Salario base, comprehensive_salary: Salario integral,' .
                        ' normal_salary: Salario normal, dialy_salary: Salario diario)');
                }
            });
        }
    }
}
