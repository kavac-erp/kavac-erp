<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsDaysToPayrollPermissionPoliciesTable
 * @brief Migración para cambiar los campos de la tabla payroll_permission_policies
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsDaysToPayrollPermissionPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_permission_policies', function (Blueprint $table) {
            $table->renameColumn('day_min', 'time_min');
            $table->renameColumn('day_max', 'time_max');
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
            $table->renameColumn('time_min', 'day_min');
            $table->renameColumn('time_max', 'day_max');
        });
    }
}
