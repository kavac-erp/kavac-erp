<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldMorphToPayrollScalesTable
 * @brief Migración para agregar el campo morph a la tabla payroll_scales
 *
 * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldMorphToPayrollScalesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_scales')) {
            Schema::table('payroll_scales', function (Blueprint $table) {
                $table->nullableMorphs('relationable');
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_scales')) {
            Schema::table('payroll_scales', function (Blueprint $table) {
                $table->dropMorphs('relationable');
            });
        };
    }
}
