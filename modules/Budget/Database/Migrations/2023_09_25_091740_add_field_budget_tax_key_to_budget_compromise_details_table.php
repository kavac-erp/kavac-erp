<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldBudgetTaxKeyToBudgetCompromiseDetailsTable
 * @brief Agrega el campo 'budget_tax_key' a la tabla 'budget_compromise_details'
 *
 * @author Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldBudgetTaxKeyToBudgetCompromiseDetailsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_compromise_details', function (Blueprint $table) {
            if (!Schema::hasColumn('budget_compromise_details', 'budget_tax_key')) {
                $table
                    ->string('budget_tax_key')
                    ->nullable()
                    ->comment('Clave que vincula una cuenta presuestaria con una cuenta presupuestaria de impuesto');
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
        Schema::table('budget_compromise_details', function (Blueprint $table) {
            if (Schema::hasColumn('budget_compromise_details', 'budget_tax_key')) {
                $table->dropColumn('budget_tax_key');
            }
        });
    }
}
