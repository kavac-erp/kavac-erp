<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPayrollVacationPoliciesTable
 * @brief Migración para agregar campos adicionales a la tabla de políticas de vacaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToPayrollVacationPoliciesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_policies', function (Blueprint $table) {
            $table->boolean('on_scale')->default(false)->nullable()->comment('¿Los días de bonificación se establecen de acuerdo a un escalafón?');

            $table->boolean('worker_arises')->default(false)->nullable()->comment('¿El pago de vacaciones se realiza cuando nace el derecho a vacaciones del trabajador?');

            $table->string('generate_worker_arises', 15, 8)->nullable()->comment('Monto pago de vacaciones automáticamente');

            $table->integer('min_days_advance')->nullable()->comment('Días de anticipación (mínimo)');

            $table->integer('max_days_advance')->nullable()->comment('Días de anticipación (máximo)');
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
            Schema::table('payroll_vacation_policies', function (Blueprint $table) {
                $table->dropColumn('on_scale');

                $table->dropColumn('worker_arises');

                $table->dropColumn('generate_worker_arises');

                $table->dropColumn('min_days_advance');

                $table->dropColumn('max_days_advance');
            });
        });
    }
}
