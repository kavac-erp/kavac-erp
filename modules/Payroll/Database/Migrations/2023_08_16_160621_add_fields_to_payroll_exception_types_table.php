<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPayrollExceptionTypesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToPayrollExceptionTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_exception_types', function (Blueprint $table) {
            $table->string('sign', 1)->nullable()->comment('Signo para el tipo de concepto');
            $table->foreignId('affect_id')
                ->nullable()
                ->constrained('payroll_exception_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_exception_types', function (Blueprint $table) {
            $table->dropForeign(['affect_id']);
            $table->dropColumn(['sign', 'affect_id']);
        });
    }
}
