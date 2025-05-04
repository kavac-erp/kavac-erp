<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldsToBudgetCompromiseDetailsTable
 * @brief Actualiza los tipos de dato de los campos de la tabla budget_compromise_details
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsToBudgetCompromiseDetailsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_compromise_details', function (Blueprint $table) {
            if (Schema::hasColumn('budget_compromise_details', 'budget_account_id')) {
                $table->dropForeign(['budget_account_id']);
                $table->bigInteger('budget_account_id')->nullable()->change();
                $table->foreign('budget_account_id')->references('id')->on('budget_accounts')
                      ->onDelete('restrict')->onUpdate('cascade');
            }
            if (Schema::hasColumn('budget_compromise_details', 'budget_sub_specific_formulation_id')) {
                $table->dropForeign('budget_compromise_details_formulation_fk');
                $table->bigInteger('budget_sub_specific_formulation_id')->nullable()->change();
                $table->foreign('budget_sub_specific_formulation_id')->references('id')->on('budget_sub_specific_formulations')
                      ->onDelete('restrict')->onUpdate('cascade');
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
            if (Schema::hasColumn('budget_compromise_details', 'budget_account_id')) {
                $table->dropForeign(['budget_account_id']);
                $table->bigInteger('budget_account_id')->change();
                $table->foreign('budget_account_id')->references('id')->on('budget_accounts')
                      ->onDelete('restrict')->onUpdate('cascade');
            }
            if (Schema::hasColumn('budget_compromise_details', 'budget_sub_specific_formulation_id')) {
                $table->dropForeign(['budget_sub_specific_formulation_id']);
                $table->bigInteger('budget_sub_specific_formulation_id')->change();
                $table->foreign('budget_sub_specific_formulation_id')->references('id')->on('budget_sub_specific_formulations')
                      ->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
