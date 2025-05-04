<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSchoolingLevelsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [José Briceño] [josejorgebriceno9@gmail.com]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSchoolingLevelsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_schooling_levels')) {
            Schema::create('payroll_schooling_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique()->comment('Nombre del nivel de escolaridad');
                $table->string('description', 200)->nullable()->comment('Descripción del nivel de escolaridad');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
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
        Schema::dropIfExists('payroll_schooling_levels');
    }
}
