<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSupervisedGroupsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSupervisedGroupsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_supervised_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique()->comment('Código del grupo');
            $table
                ->foreignId('supervisor_id')
                ->nullable()
                ->references('id')
                ->on('payroll_staffs')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Supervisor del grupo');
            $table
                ->foreignId('approver_id')
                ->nullable()
                ->references('id')
                ->on('payroll_staffs')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Aprobador del grupo');
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
        Schema::dropIfExists('payroll_supervised_groups');
    }
}
