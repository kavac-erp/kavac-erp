<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollProfessionalsTable
 * @brief Crear tabla profesional del trabajador
 *
 * Gestiona la creación o eliminación de la tabla profesional del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollProfessionalsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_professionals')) {
            Schema::create('payroll_professionals', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('instruction_degree_name', 100)
                      ->nullable()
                      ->comment(
                          'Nombre en caso de elegir en Grado de Instrucción: Especialización, Maestría, Doctorado'
                      );
                $table->text('study_program_name')->nullable()->comment('Nombre del programa de estudio');
                $table->text('class_schedule')->nullable()->comment('Horario de clase');
                $table->boolean('is_student')->default(false)->comment('Establece si el trabajdor es estudiante o no');

                $table->foreignId('payroll_staff_id')->unique()->comment('Identificador del dato personal')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_instruction_degree_id')->comment('Identificador del grado de instrucción')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_study_type_id')->nullable()
                      ->comment('Identificador del tipo de estudio')->constrained()
                      ->onUpdate('cascade')->onDelete('restrict');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('payroll_professionals');
        Schema::enableForeignKeyConstraints();
    }
}
