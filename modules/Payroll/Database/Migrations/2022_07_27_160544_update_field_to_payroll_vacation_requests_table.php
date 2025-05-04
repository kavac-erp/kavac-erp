<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPayrollVacationRequestsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToPayrollVacationRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_vacation_requests', 'vacation_period_year')) {
                $table->dropColumn('vacation_period_year');
            };
        });

        Schema::table('payroll_vacation_requests', function (Blueprint $table) {
            $table->longText('vacation_period_year')->nullable()
                      ->comment('Periodos vacaionales');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_vacation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_vacation_requests', 'vacation_period_year')) {
                $table->dropColumn('vacation_period_year');
            };
        });

        Schema::table('payroll_vacation_requests', function (Blueprint $table) {
            $table->integer('vacation_period_year')->nullable()
                      ->comment('Periodos vacaionales');
        });
    }
}
