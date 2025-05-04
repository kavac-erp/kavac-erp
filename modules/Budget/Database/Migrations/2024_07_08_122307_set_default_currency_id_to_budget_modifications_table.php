<?php

use App\Models\Currency;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Budget\Models\BudgetModification;

/**
 * @class SetDefaultCurrencyIdToBudgetModificationsTable
 * @brief Cambia el valor predeterminado del campo 'currency_id' de la tabla 'budget_modifications'
 *
 * @author Natanael Rojo <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SetDefaultCurrencyIdToBudgetModificationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_modifications', function (Blueprint $table) {
            $defaultCurrency = Currency::query()
                ->where('default', true)
                ->first();
            $modifications = BudgetModification::query()
                ->whereNull('currency_id')
                ->get();
            foreach ($modifications as $modification) {
                $modification->currency_id = $defaultCurrency->id;
                $modification->save();
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
        Schema::table('budget_modifications', function (Blueprint $table) {
            $modifications = BudgetModification::query()
                ->whereNotNull('currency_id')
                ->get();
            foreach ($modifications as $modification) {
                $modification->currency_id = null;
            }
        });
    }
}
