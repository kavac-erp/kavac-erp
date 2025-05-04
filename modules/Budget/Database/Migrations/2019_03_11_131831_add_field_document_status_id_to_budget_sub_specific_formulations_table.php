<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDocumentStatusIdToBudgetSubSpecificFormulationsTable
 * @brief Agrega el campo document_status_id a la tabla budget_sub_specific_formulations
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDocumentStatusIdToBudgetSubSpecificFormulationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('budget_sub_specific_formulations', 'document_status_id')) {
            Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
                $table->foreignId('document_status_id')->nullable()->constrained('document_status')
                      ->onUpdate('cascade');
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
            $table->dropForeign(['document_status_id']);
            $table->dropColumn('document_status_id');
        });
    }
}
