<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSupervisedGroupStaffTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSupervisedGroupStaffTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_supervised_group_staff', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('payroll_supervised_group_id')
                ->nullable()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Grupo al que el trabajador está asociado');
            $table
                ->foreignId('payroll_staff_id')
                ->nullable()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Trabajador asociado al grupo');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_supervised_group_staff');
    }
}
