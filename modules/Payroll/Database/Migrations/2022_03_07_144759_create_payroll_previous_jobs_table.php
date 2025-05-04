<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollPreviousJobsTable
 * @brief Crear tabla de trabajos anteriores
 *
  * Gestiona la creación o eliminación de la tabla de trabajos anteriores
 *
  * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollPreviousJobsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_previous_jobs', function (Blueprint $table) {
            $table->id();

            $table->string('organization_name', 200)->comment('Nombre de la organización');
            $table->string('organization_phone', 20)->comment('Télefono de la organización');
            $table->foreignId('payroll_sector_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade')->comment('Identificador único del tipo de sector asociado al registro');
            $table->foreignId('payroll_position_id')->constrained()->onDelete('restrict')->onUpdate('cascade')->comment('Identificador único del cargo asociado al registro');
            $table->foreignId('payroll_staff_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade')->comment('Identificador único del tipo de personal asociado al registro');
            $table->date('start_date')->comment('Fecha de inicio en la organización');
            $table->date('end_date')->comment('Fecha de fin en la organización');
            $table->foreignId('payroll_employment_id')->constrained()->onDelete('restrict')->onUpdate('cascade')->comment('Identificador único de los datos laborales asociado al registro');
            
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
        Schema::dropIfExists('payroll_previous_jobs');
    }
}
