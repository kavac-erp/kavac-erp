<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldUniformSizeToPayrollStaffsTable
 * @brief Migración para eliminar el campo de talla de uniforme de la tabla de personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldUniformSizeToPayrollStaffsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_staffs', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_staffs', 'uniform_size')) {
                $table->dropColumn('uniform_size');
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
        Schema::table('payroll_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_staffs', 'uniform_size')) {
                $table->integer('uniform_size')->default(0)->comment('Talla de uniforme');
            };
        });
    }
}
