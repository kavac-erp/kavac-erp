<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateCompromiseableFieldsToBudgetCompromisesTable
 * @brief Actualiza los tipos de datos de los campos 'compromiseable_type' y 'compromiseable_id' de la tabla 'budget_compromises'
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateCompromiseableFieldsToBudgetCompromisesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_compromises', function (Blueprint $table) {
            if (Schema::hasColumn('budget_compromises', 'compromiseable_type')) {
                $table->string('compromiseable_type', 255)->nullable()->change();
            }
            if (Schema::hasColumn('budget_compromises', 'compromiseable_id')) {
                $table->bigInteger('compromiseable_id')->nullable()->change();
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
        Schema::table('budget_compromises', function (Blueprint $table) {
            if (Schema::hasColumn('budget_compromises', 'compromiseable_type')) {
                $table->string('compromiseable_type', 255)->change();
            }
            if (Schema::hasColumn('budget_compromises', 'compromiseable_id')) {
                $table->bigInteger('compromiseable_id')->change();
            }
        });
    }
}
