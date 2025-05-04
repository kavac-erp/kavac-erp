<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldYearsForAdditionalDaysToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_vacation_policies', 'years_for_additional_days')) {
                $table->unsignedInteger('years_for_additional_days')->default(1)
                    ->comment('Indica el intervalo en años de servicios para el aumento de días de disfrute');
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
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->dropColumn('years_for_additional_days');
        });
    }
}
