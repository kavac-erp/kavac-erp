<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldInstitutionalToAccountingAccountsTable
 * @brief Ejecuta la migración para agregar el campo institutional a la tabla accounting_accounts
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldInstitutionalToAccountingAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('accounting_accounts')) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->char('institutional', 3)->default('000')->nullable()->comment(
                    'Numero para la cuenta utilizado por instituciones'
                );
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
        if (Schema::hasTable('accounting_accounts')) {
            Schema::table('accounting_accounts', function (Blueprint $table) {
                $table->dropColumn('institutional');
            });
        }
    }
}
