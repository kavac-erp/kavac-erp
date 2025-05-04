<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldPercentToCitizenServiceRegistersTable
 * @brief Modifica el tipo de dato de la columna percent de la tabla de registros de servicio
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldPercentToCitizenServiceRegistersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_registers', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_registers', 'percent')) {
                $table->dropColumn('percent');
            }
        });
        Schema::table('citizen_service_registers', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_registers', 'percent')) {
                $table->integer('percent')->nullable()->comment('Porcentaje de cumplimiento');
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
            if (Schema::hasColumn('citizen_service_registers', 'percent')) {
                $table->dropColumn('percent');
            }
        });
        Schema::table('citizen_service_registers', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_registers', 'percent')) {
                $table->string('percent', 10)->nullable()->comment('Porcentaje de cumplimiento');
            }
        });
    }
}
