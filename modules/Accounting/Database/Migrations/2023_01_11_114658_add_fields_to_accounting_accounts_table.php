<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToAccountingAccountsTable
 * @brief Ejecuta la migración para agregar los campos resource y egress a la tabla accounting_accounts
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToAccountingAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->boolean('resource')->nullable()->comment('Indica si es una cuenta de reursos (ingresos)');
            $table->boolean('egress')->nullable()->comment('Indica si es una cuenta de egresos (gastos)');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropColumn('resource');
            $table->dropColumn('egress');
        });
    }
}
