<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDirectorIdToCitizenServiceDepartmentsTable
 * @brief Agrega el campo director_id a la tabla de departamentos de servicios
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsDirectorIdAndCoordinatorIdToCitizenServiceDepartmentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_departments', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_departments', 'director_id')) {
                $table->foreignId('director_id')->nullable()->references('id')->on('payroll_staffs')->onDelete('cascade')->onUpdate('cascade')->comment('Director del departamento');
            }
            if (!Schema::hasColumn('citizen_service_departments', 'coordinator_id')) {
                $table->foreignId('coordinator_id')->nullable()->references('id')->on('payroll_staffs')->onDelete('cascade')->onUpdate('cascade')->comment('Coordinador del departamento');
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
        Schema::table('citizen_service_departments', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_departments', 'director_id')) {
                $table->dropColumn('director_id');
            }
            if (Schema::hasColumn('citizen_service_departments', 'coordinator_id')) {
                $table->dropColumn('coordinator_id');
            }
        });
    }
}
