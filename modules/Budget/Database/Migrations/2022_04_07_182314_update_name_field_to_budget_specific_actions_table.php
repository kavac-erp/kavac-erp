<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateNameFieldToBudgetSpecificActionsTable
 * @brief Actualiza el tipo de dato del campo name de la tabla budget_specific_actions
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateNameFieldToBudgetSpecificActionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_specific_actions', function (Blueprint $table) {
            $table->text('name')->comment('Nombre de la acción específica')->change();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_specific_actions', function (Blueprint $table) {
            $table->string('name')->comment('Nombre de la acción específica')->change();
        });
    }
}
