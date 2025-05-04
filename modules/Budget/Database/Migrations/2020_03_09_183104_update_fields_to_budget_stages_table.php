<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToBudgetStagesTable
 * @brief Actualiza el tipo de dato de los campos de la tabla budget_stages
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToBudgetStagesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_stages', function (Blueprint $table) {
            $table->dropMorphs('sourceable');
        });

        Schema::table('budget_stages', function (Blueprint $table) {
            /* Relación para los documentos de origen que generan la etapa presupuestaria del compromiso,
            solo para los estados (CAU)sado y (PAG)ado */
            $table->nullableMorphs('stageable');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_stages', function (Blueprint $table) {
            $table->dropMorphs('stageable');
        });

        Schema::table('budget_stages', function (Blueprint $table) {
            $table->morphs('sourceable');
        });
    }
}
