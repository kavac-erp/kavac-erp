<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTimeUnitToPayrollPermissionPoliciesTable
 * @brief Migración para agregar el campo de unidad de tiempo a la tabla de politicas de permisos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTimeUnitToPayrollPermissionPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_permission_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_permission_policies', 'time_unit')) {
                $table->string('time_unit')->nullable()->comment('Indica la unidad de tiempo definida en la politica de permiso');
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
        Schema::table('payroll_permission_policies', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_permission_policies', 'time_unit')) {
                $table->dropColumn('time_unit');
            };
        });
    }
}
