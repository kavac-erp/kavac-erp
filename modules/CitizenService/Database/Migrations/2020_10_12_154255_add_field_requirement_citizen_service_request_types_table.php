<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldRequirementCitizenServiceRequestTypesTable
 * @brief Agrega el campo requirement a la tabla de tipos de solicitudes de servicios
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldRequirementCitizenServiceRequestTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_request_types', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_request_types', 'requirement')) {
                $table->string('requirement', 300)->nullable()->comment('Descripción de requerimientos de solicitud');
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
        Schema::table('citizen_service_request_types', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_request_types', 'requirement')) {
                $table->dropColumn('requirement');
            }
        });
    }
}
