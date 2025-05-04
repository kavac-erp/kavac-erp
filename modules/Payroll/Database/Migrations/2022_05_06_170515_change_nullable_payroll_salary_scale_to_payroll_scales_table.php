<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeNullablePayrollSalaryScaleToPayrollScalesTable
 * @brief Migración para cambiar los campos de la tabla payroll_scales
 *
 * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeNullablePayrollSalaryScaleToPayrollScalesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_scales', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_salary_scale_id')->nullable()->change();

            $table->dropForeign(['payroll_salary_scale_id']);
            $table->foreign('payroll_salary_scale_id')
                ->references('id')
                ->on('payroll_salary_scales')
                ->onDelete('set null');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_scales', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_salary_scale_id')->change();
        });
    }
}
