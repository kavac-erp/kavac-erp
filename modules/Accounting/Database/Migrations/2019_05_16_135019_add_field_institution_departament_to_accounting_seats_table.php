<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldInstitutionDepartamentToAccountingSeatsTable
 * @brief Ejecuta la migración para agregar el campo institution_id y department_id a la tabla accounting_seats
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldInstitutionDepartamentToAccountingSeatsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->constrained()->onDelete('cascade')->comment(
                'id de la institución que genero el asiento contable'
            );

            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade')->comment(
                'id del departamento de institución que genero el asiento contable'
            );
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
                $table->dropColumn(['institution_id', 'department_id']);
            });
        }
    }
}
