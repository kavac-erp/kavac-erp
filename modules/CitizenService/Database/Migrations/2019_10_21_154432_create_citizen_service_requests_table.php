<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceRequestsTable
 * @brief Crear tabla de solicitudes
 *
 * Gestiona la creación o eliminación de la tabla de solicitudes
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('citizen_service_requests')) {
            Schema::create('citizen_service_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('first_name', 100)->comment('Nombre del Solicitante');
                $table->string('last_name', 100)->comment('Apellido del Solicitante');
                $table->string('id_number', 12)->unique()->comment('Cédula de identidad del Solicitante');
                $table->string('email')->unique()->nullable()->comment('Correo electrónico del Solicitante');
                $table->string('phone', 20)->nullable()->comment('Teléfono del Solicitante');
                $table->date('date')->comment('Fecha de Solicitud');
                $table->string('institution_name', 200)->comment('Nombre de la institución');
                $table->string('institution_address', 200)->comment('Dirección de la institución');
                $table->string('web', 200)->comment('Dirección Web');
                $table->string('information', 200)->comment('Información Adicional');
                $table->foreignId('city_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('municipality_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('payroll_sector_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('citizen_service_request_type_id')->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('document_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('citizen_service_requests');
    }
}
