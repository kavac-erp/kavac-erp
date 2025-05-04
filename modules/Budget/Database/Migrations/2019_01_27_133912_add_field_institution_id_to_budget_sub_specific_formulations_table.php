<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldInstitutionIdToBudgetSubSpecificFormulationsTable
 * @brief Agrega el campo institution_id a la tabla budget_sub_specific_formulations
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldInstitutionIdToBudgetSubSpecificFormulationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('budget_sub_specific_formulations', 'institution_id')) {
            Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
                $table->foreignId('institution_id')->nullable()->constrained()->onUpdate('cascade');
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            $table->dropForeign('budget_sub_specific_formulations_institution_id_foreign');
            $table->dropColumn('institution_id');
        });
    }
}
