<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTotalYearAmountMToBudgetAccountOpensTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTotalYearAmountMToBudgetAccountOpensTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_account_opens', function (Blueprint $table) {
            $table->float('total_year_amount_m', 30, 10)->nullable()->comment('Monto o cantidad total formulada para mostrar en los selectores');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_account_opens', function (Blueprint $table) {
            $table->dropColumn('total_year_amount_m');
        });
    }
}
