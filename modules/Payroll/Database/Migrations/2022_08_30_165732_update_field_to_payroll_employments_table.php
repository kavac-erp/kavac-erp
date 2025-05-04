<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPayrollEmploymentsTable
 * @brief Actualiza los campos de la tabla de contrataciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToPayrollEmploymentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            $table->string('years_apn', 50)->nullable()->comment('Años en otras instituciones públicas')->change();
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
            $table->string('years_apn', 50)->nullable()->comment('Años en otras instituciones públicas')->change();
        });
    }
}
