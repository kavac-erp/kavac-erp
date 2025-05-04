<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSalaryTabulatorTable
 * @brief Crear tabla de tabuladores salariales
 *
 * Gestiona la creación o eliminación de la tabla de tabuladores salariales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSalaryTabulatorsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_salary_tabulators')) {
            Schema::create('payroll_salary_tabulators', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');
                $table->string('name')->comment('Nombre del tabulador salarial');
                $table->text('description')->nullable()->comment('Descripción del tabulador salarial');
                $table->boolean('active')->default(true)->comment('Indica si el tabulador esta activo');

                $table->foreignId('payroll_position_type_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->unsignedBigInteger('payroll_horizontal_salary_scale_id')->nullable()
                      ->comment('Identificador único del escalafón salarial horizontal asociado al tabulador');
                $table->foreign(
                    'payroll_horizontal_salary_scale_id',
                    'payroll_salary_tabulators_horizontal_salary_scale_fk'
                )->references('id')->on('payroll_salary_scales')->onDelete('restrict')->onUpdate('cascade');


                $table->unsignedBigInteger('payroll_vertical_salary_scale_id')->nullable()
                      ->comment('Identificador único del escalafón vertical salarial asociado al tabulador');
                $table->foreign(
                    'payroll_vertical_salary_scale_id',
                    'payroll_salary_tabulators_vertical_salary_scale_fk'
                )->references('id')->on('payroll_salary_scales')->onDelete('restrict')->onUpdate('cascade');


                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        };
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_salary_tabulators');
    }
}
