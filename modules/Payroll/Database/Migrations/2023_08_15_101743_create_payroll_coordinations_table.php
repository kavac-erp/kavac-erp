<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollCoordinationsTable
 *
 * @brief Gestión de campos de las Coordinaciones.
 *
 * Clase que gestiona los métodos para la gestión de las Coordinaciones.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollCoordinationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_coordinations')) {
            Schema::create('payroll_coordinations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 200)
                    ->nullable()
                    ->unique()
                    ->comment('Nombre de la coordinación');
                $table->string('description', 200)
                    ->nullable()
                    ->comment('Descripción la coordinación');
                $table->foreignId('department_id')
                    ->constrained()
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->comment('Departamenta de adscripción');
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
        Schema::dropIfExists('payroll_coordinations');
    }
}
