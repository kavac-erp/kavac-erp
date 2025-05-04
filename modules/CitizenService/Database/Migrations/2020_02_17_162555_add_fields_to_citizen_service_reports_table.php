<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToCitizenServiceReportsTable
 * @brief Agrega campos a la tabla de reportes de servicio
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToCitizenServiceReportsTable extends Migration
{
    /**
     * Ejecuta las migraciones
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_reports', 'type_search')) {
                $table->string('type_search', 20)->nullable()->comment('Tipo de búsqueda');
            }

            if (!Schema::hasColumn('citizen_service_reports', 'date')) {
                $table->date('date')->comment('Fecha de Solicitud');
            }

            if (!Schema::hasColumn('citizen_service_reports', 'start_date')) {
                $table->date('start_date')->nullable()->comment('Fecha inicial de busqueda');
            }

            if (!Schema::hasColumn('citizen_service_reports', 'end_date')) {
                $table->date('end_date')->nullable()->comment('Fecha final de busqueda');
            }
        });
    }

    /**
     * Revierte las migraciones
     *
     * @return void
     */
    public function down()
    {
        Schema::table('citizen_service_reports', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_reports', 'type_search')) {
                $table->dropColumn('type_search');
            }

            if (Schema::hasColumn('citizen_service_reports', 'date')) {
                $table->dropColumn('date');
            }

            if (Schema::hasColumn('citizen_service_reports', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('citizen_service_reports', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
}
