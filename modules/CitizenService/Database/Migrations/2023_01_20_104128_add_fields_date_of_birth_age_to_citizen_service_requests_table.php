<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsDateOfBirthAgeToCitizenServiceRequestsTable
 * @brief Agrega los campos date_of_birth y age a la tabla de solicitudes de servicios
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsDateOfBirthAgeToCitizenServiceRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('citizen_service_requests')) {
            Schema::table('citizen_service_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('citizen_service_requests', 'birth_date')) {
                    $table->date('birth_date')->nullable()->comment('Fecha de nacimiento');
                }
                if (!Schema::hasColumn('citizen_service_requests', 'age')) {
                    $table->bigInteger('age')->nullable()->comment('Edad');
                }
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
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_requests', 'birth_date')) {
                $table->dropColumn(['birth_date']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'age')) {
                $table->dropColumn(['age']);
            }
        });
    }
}
