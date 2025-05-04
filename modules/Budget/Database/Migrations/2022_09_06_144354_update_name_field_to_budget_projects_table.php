<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateNameFieldToBudgetProjectsTable
 * @brief Actualiza el tipo de dato del campo 'name' de la tabla 'budget_projects'
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateNameFieldToBudgetProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_projects', function (Blueprint $table) {
            $table->text('name')->comment('Nombre del proyecto')->change();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_projects', function (Blueprint $table) {
            $table->string('name')->comment('Nombre del proyecto')->change();
        });
    }
}
