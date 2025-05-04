<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldNameToPayrollAcknowledgmentFilesTable
 * @brief Migración para cambiar el campo name de la tabla de archivos de reconocimiento
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldNameToPayrollAcknowledgmentFilesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_acknowledgment_files', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_acknowledgment_files', 'name')) {
                $table->string('name', 200)->nullable()->comment('Nombre del reconocimiento')->change();
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
        Schema::table('payroll_acknowledgment_files', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_acknowledgment_files', 'name')) {
                $table->string('name', 200)->comment('Nombre del reconocimiento')->change();
            };
        });
    }
}
