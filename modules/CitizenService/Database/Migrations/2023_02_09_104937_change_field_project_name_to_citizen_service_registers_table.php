<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldProjectNameToCitizenServiceRegistersTable
 * @brief Modifica el tipo de dato de la columna project_name de la tabla de registros de servicio
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldProjectNameToCitizenServiceRegistersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_registers', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_registers', 'project_name')) {
                $table->dropColumn('project_name');
                $table->string('code')->nullable()->comment('Codigo de la solicitud');
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
        Schema::table('citizen_service_registers', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_registers', 'code')) {
                $table->dropColumn('code');
                $table->string('project_name', 100)->comment('Nombre del proyecto');
            }
        });
    }
}
