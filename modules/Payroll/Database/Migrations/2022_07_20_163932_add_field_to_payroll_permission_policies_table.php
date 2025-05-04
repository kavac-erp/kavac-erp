<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToPayrollPermissionPoliciesTable
 * @brief Migración para agregar campos a la tabla payroll_permission_policies
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldToPayrollPermissionPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_permission_policies', function (Blueprint $table) {
            $table->boolean('business_days')->default(false)->nullable()->comment('¿Toma en cuenta los días hábiles?');
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
            $table->dropColumn('business_days');
        });
    }
}
