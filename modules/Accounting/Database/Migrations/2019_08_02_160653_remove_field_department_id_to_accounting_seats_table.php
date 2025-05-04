<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RemoveFieldDepartmentIdToAccountingSeatsTable
 * @brief Ejecuta la migración para eliminar el campo department_id a la tabla accounting_seats
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveFieldDepartmentIdToAccountingSeatsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            $table->dropForeign('accounting_seats_department_id_foreign');
            $table->dropColumn('department_id');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('accounting_seats')) {
            Schema::table('accounting_seats', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'id del departamento de institución que genero el asiento contable'
                );
            });
        }
    }
}
