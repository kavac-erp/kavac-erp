<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldsTotDebitTotAssetsToAccountingSeatsTable
 * @brief Ejecuta la migración para modificar los campos tot_debit y tot_assets a la tabla accounting_seats
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsTotDebitTotAssetsToAccountingSeatsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            $table->float('tot_debit', 30, 10)->comment('Monto asignado al Debe total del asiento')->change();
            $table->float('tot_assets', 30, 10)->comment('Monto asignado al Haber total del Asiento')->change();
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
                $table->dropColumn(['tot_debit', 'tot_assets']);
            });
        }
    }
}
