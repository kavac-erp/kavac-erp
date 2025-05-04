<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToCitizenServiceRequestsTable
 * @brief Agrega campos adicionales a la tabla de solicitudes de servicios
 *
 * Clase que añade campos adicionales a la tabla de solicitudes de servicios
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddALotOfFieldsToCitizenServiceRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_requests', 'gender_id')) {
                $table->foreignId('gender_id')->nullable()->references('id')->on('genders')->onDelete('cascade')->onUpdate('cascade')->comment('Género de la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'gender')) {
                $table->string('gender')->nullable()->comment('Género de la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'nationality_id')) {
                $table->foreignId('nationality_id')->nullable()->references('id')->on('payroll_nationalities')->onDelete('cascade')->onUpdate('cascade')->comment('Nacionalidad de la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'nacionality')) {
                $table->string('nationality')->nullable()->comment('Nacionalidad de la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'community')) {
                $table->string('community')->nullable()->comment('Si la persona solicitante es de una comunidad');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'location')) {
                $table->string('location')->nullable()->comment('Ubicación de la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'commune')) {
                $table->string('commune')->nullable()->comment('Comuna de la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'communal_council')) {
                $table->string('communal_council')->nullable()->comment('Consejo comunal al que pertenece la persona solicitante');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'population_size')) {
                $table->integer('population_size')->nullable()->comment('Cantidad de habitantes de la comunidad');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'director_id')) {
                $table->foreignId('director_id')->nullable()->references('id')->on('payroll_staffs')->onDelete('cascade')->onUpdate('cascade')->comment('Director y/o responsable de la solicitud');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_requests', 'gender_id')) {
                $table->dropColumn('gender_id');
            }
            if (Schema::hasColumn('citizen_service_requests', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('citizen_service_requests', 'nationality_id')) {
                $table->dropColumn('nationality_id');
            }
            if (Schema::hasColumn('citizen_service_requests', 'nationality')) {
                $table->dropColumn('nationality');
            }
            if (Schema::hasColumn('citizen_service_requests', 'community')) {
                $table->dropColumn('community');
            }
            if (Schema::hasColumn('citizen_service_requests', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('citizen_service_requests', 'commune')) {
                $table->dropColumn('commune');
            }
            if (Schema::hasColumn('citizen_service_requests', 'communal_council')) {
                $table->dropColumn('communal_council');
            }
            if (Schema::hasColumn('citizen_service_requests', 'population_size')) {
                $table->dropColumn('population_size');
            }
            if (Schema::hasColumn('citizen_service_requests', 'director_id')) {
                $table->dropColumn('director_id');
            }
        });
    }
}
