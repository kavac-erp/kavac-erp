<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPayrollBenefitsPoliciesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToPayrollBenefitsPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_benefits_policies', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_benefits_policies', 'benfits_advance_payment')) {
                $table->dropColumn('benfits_advance_payment');
            };
        });
        Schema::table('payroll_benefits_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_benefits_policies', 'benfits_advance_payment')) {
                $table->boolean('benefits_advance_payment')->default(false)
                          ->comment('Establece si se habilita el anticipo de prestaciones');
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
        Schema::table('payroll_benefits_policies', function (Blueprint $table) {
            Schema::table('payroll_benefits_policies', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_benefits_policies', 'benfits_advance_payment')) {
                    $table->boolean('benfits_advance_payment')->default(false)
                              ->comment('Establece si se habilita el anticipo de prestaciones');
                };
            });
            Schema::table('payroll_benefits_policies', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_benefits_policies', 'benefits_advance_payment')) {
                    $table->dropColumn('benefits_advance_payment');
                };
            });
        });
    }
}
