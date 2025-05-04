<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddBankReferenceToAccountingEntryAccountsTable
 * @brief Ejecuta la migración para agregar el campo bank_reference a la tabla accounting_entry_accounts
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddBankReferenceToAccountingEntryAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entry_accounts', function (Blueprint $table) {
            $table->string('bank_reference')->nullable()->comment('Referencia bancaria de la transacción');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_entry_accounts', function (Blueprint $table) {
            $table->dropColumn('bank_reference');
        });
    }
}
