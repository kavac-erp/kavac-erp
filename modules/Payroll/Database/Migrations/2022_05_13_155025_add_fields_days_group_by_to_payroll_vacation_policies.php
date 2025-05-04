<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsDaysGroupByToPayrollVacationPolicies
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsDaysGroupByToPayrollVacationPolicies extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_vacation_policies', 'days_on_scale')) {
                $table->string('days_on_scale')->nullable()
                ->comment('variable al escalafon asociado a Los días de disfrute se establecen de acuerdo a un escalafón');
            };
            if (!Schema::hasColumn('payroll_vacation_policies', 'days_group_by')) {
                $table->string('days_group_by')->nullable()
                ->comment('grupo de escalafon asociado a Los días de disfrute se establecen de acuerdo a un escalafón');
            };
            if (!Schema::hasColumn('payroll_vacation_policies', 'days_type')) {
                $table->string('days_type')->nullable()
                ->comment('tipo de escalafon asociado a Los días de disfrute se establecen de acuerdo a un escalafón');
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
            if (Schema::hasColumn('payroll_vacation_policies', 'days_on_scale')) {
                $table->dropColumn('days_on_scale');
            };
            if (Schema::hasColumn('payroll_vacation_policies', 'days_group_by')) {
                $table->dropColumn('days_group_by');
            };
            if (Schema::hasColumn('payroll_vacation_policies', 'days_type')) {
                $table->dropColumn('days_type');
            };
        });
    }
}
