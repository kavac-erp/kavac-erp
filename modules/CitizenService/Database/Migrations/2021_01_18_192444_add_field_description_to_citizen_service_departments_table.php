<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDescriptionToCitizenServiceDepartmentsTable
 * @brief Agrega el campo description a la tabla de departamentos de solicitudes de servicios
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDescriptionToCitizenServiceDepartmentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_departments', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_departments', 'description')) {
                $table->string('description', 300)->nullable()->comment('Descripción del departamento');
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
            if (Schema::hasColumn('citizen_service_departments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}
