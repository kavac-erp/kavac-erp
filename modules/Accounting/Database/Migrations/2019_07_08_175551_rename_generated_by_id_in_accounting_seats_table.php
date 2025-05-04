<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RenameGeneratedByIdInAccountingSeatsTable
 * @brief Ejecuta la migración para modificar el campo generado_by_id a la tabla accounting_seats
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RenameGeneratedByIdInAccountingSeatsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            $table->renameColumn('generated_by_id', 'accounting_seat_categories_id');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_seats', 'accounting_seat_categories_id')) {
                $table->renameColumn('accounting_seat_categories_id', 'generated_by_id');
            }
        });
    }
}
