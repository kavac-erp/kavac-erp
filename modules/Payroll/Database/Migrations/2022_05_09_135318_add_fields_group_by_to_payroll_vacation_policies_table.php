<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsGroupByToPayrollVacationPoliciesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsGroupByToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_vacation_policies', 'group_by')) {
                $table->string('group_by')->nullable()->comment('Escalas o niveles del escalafón');
            };
            if (!Schema::hasColumn('payroll_vacation_policies', 'type')) {
                $table->string('type')->nullable()->comment('Indica el tipo de escalafón');
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
            if (Schema::hasColumn('payroll_vacation_policies', 'group_by')) {
                $table->dropColumn('group_by');
            };
            if (Schema::hasColumn('payroll_vacation_policies', 'type')) {
                $table->dropColumn('type');
            };
        });
    }
}
