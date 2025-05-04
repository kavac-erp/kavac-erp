<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingSeatAccountsTable
 * @brief Ejecuta la migración de cuentas contables para asientos contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingSeatAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accounting_seat_accounts')) {
            Schema::create('accounting_seat_accounts', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->foreignId('accounting_seat_id')->constrained()->onDelete('cascade')
                      ->comment('id del asiento contable');

                $table->foreignId('accounting_account_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'registro de cuentas patrimoniales en el asiento contable'
                );

                $table->float('debit', 30, 2)->comment('Monto asignado al Debe total del asiento');
                $table->float('assets', 30, 2)->comment('Monto asignado al Haber total del Asiento');

                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
                $table->timestamps();
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
        Schema::dropIfExists('accounting_seat_accounts');
    }
}
