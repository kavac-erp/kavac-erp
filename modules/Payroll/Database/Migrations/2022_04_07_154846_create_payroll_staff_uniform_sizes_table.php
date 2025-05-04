<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollStaffUniformSizesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollStaffUniformSizesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_staff_uniform_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->nullable()->comment('Nombre del uniforme');
            $table->string('size', 200)->nullable()->comment('Talla del uniforme');
            $table->foreignId('payroll_staff_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')->comment('Personal asociado al uniforme');
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
        Schema::dropIfExists('payroll_staff_uniform_sizes');
    }
}
