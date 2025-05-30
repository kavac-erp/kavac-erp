<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDisaggregateTaxToBudgetAccountsTable
 * @brief Agrega el campo desagregar impuestos a la tabla de Cuentas presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDisaggregateTaxToBudgetAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_accounts', function (Blueprint $table) {
            $table->boolean('disaggregate_tax')->default(false)->comment(
                "Determina si la cuenta permite desagregar impuestos"
            );
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_accounts', function (Blueprint $table) {
            $table->dropColumn('disaggregate_tax');
        });
    }
}
