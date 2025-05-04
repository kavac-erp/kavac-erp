<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingEntryAccountsTable
 * @brief Ejecuta la migración de la tabla accounting_entry_accounts
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingEntryAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_entry_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('accounting_entry_id')->constrained()->onDelete('cascade')
                  ->comment('id del asiento contable');
            $table->foreignId('accounting_account_id')->nullable()->constrained()->onDelete('cascade')->comment(
                'registro de cuentas patrimoniales en el asiento contable'
            );
            $table->float('debit', 30, 10)->comment('Monto asignado al Debe total del asiento');
            $table->float('assets', 30, 10)->comment('Monto asignado al Haber total del Asiento');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_entry_accounts');
    }
}
