<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldsDebitAndAssetsToAccountingSeatAccountsTable
 * @brief Ejecuta la migración para modificar los campos de Debe y Haber a la tabla accounting_seat_accounts
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsDebitAndAssetsToAccountingSeatAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seat_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_seat_accounts', 'debit')) {
                $table->float('debit', 30, 10)->comment('Monto asignado al Debe')->change();
            }
            if (Schema::hasColumn('accounting_seat_accounts', 'assets')) {
                $table->float('assets', 30, 10)->comment('Monto asignado al Haber')->change();
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
        if (Schema::hasTable('accounting_seat_accounts')) {
            Schema::table('accounting_seat_accounts', function (Blueprint $table) {
                $table->dropColumn(['debit', 'assets']);
            });
        }
    }
}
