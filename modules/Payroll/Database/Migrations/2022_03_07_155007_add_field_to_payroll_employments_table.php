<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToPayrollEmploymentsTable
 * @brief Migración para agregar el campo de años en otras instituciones publicas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldToPayrollEmploymentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_employments', 'start_date_apn')) {
                $table->dropColumn('start_date_apn');
            };
            $table->string('years_apn', 10)->nullable()->comment('Años en otras instituciones públicas');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_employments', 'years_apn')) {
                $table->dropColumn('years_apn');
            };
            $table->date('start_date_apn')->nullable()->comment('Fecha de ingreso a la administración pública nacional');
        });
    }
}
