<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteTablesSeatTable
 * @brief Ejecuta la migración para eliminar las tablas accounting_seat_accounts, accounting_seats y accounting_seat_categories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteTablesSeatTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('accounting_seat_accounts');
        Schema::dropIfExists('accounting_seats');
        Schema::dropIfExists('accounting_seat_categories');
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        // TODO: Se debe indicar las migraciones a revertir
    }
}
