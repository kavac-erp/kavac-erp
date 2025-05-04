<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldAssignToPayrollVacationPolicies
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAssignToPayrollVacationPolicies extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_vacation_policies')) {
            Schema::table('payroll_vacation_policies', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_vacation_policies', 'assign_to')) {
                    $table->longText('assign_to')->nullable()
                          ->comment('Registros de grupo opciones a los que se le asigna el concepto');
                };
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_vacation_policies')) {
            Schema::table('payroll_vacation_policies', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_vacation_policies', 'assign_to')) {
                    $table->dropColumn('assign_to');
                };
            });
        };
    }
}
