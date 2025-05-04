<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldEmailToCitizenServiceRegistersTable
 * @brief Modifica el tipo de dato de la columna email de la tabla de registros de servicio
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldEmailToCitizenServiceRegistersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_registers', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_registers', 'email')) {
                $table->dropUnique(['email']);
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
            if (Schema::hasColumn('citizen_service_registers', 'email')) {
                $table->string('email')->unique()->nullable()->comment('Correo electrónico del responsable')->change();
            }
        });
    }
}
