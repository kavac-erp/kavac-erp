<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceRegistersTable
 * @brief Crear tabla de registros de servicios
 *
 * Gestiona la creación o eliminación de la tabla de registros de servicios
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceRegistersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_registers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date_register')->comment('Fecha del registro');
            $table->string('first_name', 100)->comment('Nombre del director');
            $table->string('project_name', 100)->comment('Nombre del proyecto');
            $table->string('activities', 100)->comment('Actividades');
            $table->date('start_date')->comment('Fecha de inicio');
            $table->date('end_date')->comment('Fecha de culminación');
            $table->string('email')->unique()->nullable()->comment('Correo electrónico del responsable');
            $table->string('percent', 10)->comment('Porcentaje de cumplimiento');

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
        Schema::dropIfExists('citizen_service_registers');
    }
}
