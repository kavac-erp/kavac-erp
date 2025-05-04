<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldStatusToBudgetCompromiseDetailsTable
 * @brief Agrega el campo de estatus al detalle de compromiso
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldStatusToBudgetCompromiseDetailsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_compromise_details', function (Blueprint $table) {
            if (!Schema::hasColumn('budget_compromise_details', 'document_status_id')) {
                $table
                    ->foreignId('document_status_id')
                    ->nullable()
                    ->constrained('document_status')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
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
            if (Schema::hasColumn('budget_compromise_details', 'document_status_id')) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
            }
        });
    }
}
