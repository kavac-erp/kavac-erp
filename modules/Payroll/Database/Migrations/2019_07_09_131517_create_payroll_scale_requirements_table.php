<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollScaleRequirementTable
 * @brief Crear tabla de los requisitos de una escala de nómina
 *
 * Gestiona la creación o eliminación de la tabla de los requisitos de una escala de nómina
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollScaleRequirementsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_scale_requirements')) {
            Schema::create('payroll_scale_requirements', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->integer('scale_years_minimum')->unsigned()->nullable()
                      ->comment('Cantidad minima de años requeridas por la escala');
                $table->integer('scale_years_maximum')->unsigned()->nullable()
                      ->comment('Cantidad minima de años requeridas por la escala');

                $table->foreignId('payroll_scale_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->nullableMorphs('clasificable', 'payroll_scale_requirements_clasificable_index');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_scale_requirements');
    }
}
