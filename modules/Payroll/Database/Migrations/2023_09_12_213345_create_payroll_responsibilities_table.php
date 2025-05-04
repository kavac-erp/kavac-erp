<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollResponsibilitiesTable
 *
 * @brief Gestión de campos de las Responsabilidades.
 *
 * Clase que gestiona los métodos para la gestión de las Responsabilidades.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollResponsibilitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_responsibilities')) {
            Schema::create('payroll_responsibilities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('department_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->comment('Id del departamento');
                $table->foreignId('payroll_staff_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->comment('Id del trabajador');
                $table->foreignId('payroll_position_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->comment('Id del cargo');
                $table->foreignId('payroll_coordination_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->comment('Id de la coordinación');
                $table->timestamps();
                $table->softDeletes()
                    ->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_responsibilities');
    }
}
