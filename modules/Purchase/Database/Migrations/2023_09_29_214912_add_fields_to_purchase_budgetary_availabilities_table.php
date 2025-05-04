<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPurchaseBudgetaryAvailabilitiesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToPurchaseBudgetaryAvailabilitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
            $table->date('date')->nullable()->comment('Fecha de la disponibilidad presupuestaria');
            $table->text('spac_description')->nullable()->comment('Descripción de la accioń específica');
            $table->foreignId('budget_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('budget_specific_action_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('spac_description');
            $table->dropForeign(['budget_account_id']);
            $table->dropColumn('budget_account_id');
            $table->dropForeign(['budget_specific_action_id']);
            $table->dropColumn('budget_specific_action_id');
        });
    }
}
