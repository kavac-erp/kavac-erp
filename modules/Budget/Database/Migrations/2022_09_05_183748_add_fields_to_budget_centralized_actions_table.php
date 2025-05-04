<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToBudgetCentralizedActionsTable
 * @brief Agrega campos adicionales a la tabla budget_centralized_actions
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToBudgetCentralizedActionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_centralized_actions', function (Blueprint $table) {
            $table->date('from_date')->nullable()->comment('Fecha de inicio del proyecto');
            $table->date('to_date')->nullable()->comment('Fecha de fin del proyecto');
            $table->text('ca_description')->nullable()->comment('Descripción del Proyecto');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_centralized_actions', function (Blueprint $table) {
            $table->dropColumn('from_date');
            $table->dropColumn('to_date');
            $table->dropColumn('ca_description');
        });
    }
}
