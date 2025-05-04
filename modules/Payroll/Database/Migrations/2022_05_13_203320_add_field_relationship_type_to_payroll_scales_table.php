<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldRelationshipTypeToPayrollScalesTable
 * @brief Migración para agregar el campo de relationship_type a la tabla payroll_scales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldRelationshipTypeToPayrollScalesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_scales', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_scales', 'relationship_type')) {
                $table->string('relationship_type')->nullable()
                ->comment('variable para identificar la relacion del modelo al cual pertenece para ser usado en caso de filtrar');
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
        Schema::table('payroll_scales', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_scales', 'relationship_type')) {
                $table->dropColumn('relationship_type');
            };
        });
    }
}
