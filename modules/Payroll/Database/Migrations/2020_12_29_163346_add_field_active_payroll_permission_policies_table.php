<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldActivePayrollPermissionPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_permission_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_permission_policies', 'active')) {
                $table->boolean('active')->default(false)->comment('Establece si esta activo o no');
            }
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
            if (Schema::hasColumn('payroll_permission_policies', 'active')) {
                $table->dropColumn(['active']);
            }
        });
    }
}
