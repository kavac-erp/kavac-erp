<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateTaxIdFieldToBudgetCompromiseDetailsTable
 * @brief Actualiza el tipo de dato del campo 'tax_id' de la tabla 'budget_compromise_details'
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateTaxIdFieldToBudgetCompromiseDetailsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_compromise_details', function (Blueprint $table) {
            if (Schema::hasColumn('budget_compromise_details', 'tax_id')) {
                $table->dropForeign(['tax_id']);
                $table->bigInteger('tax_id')->nullable()->change();
                $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('restrict')->onUpdate('cascade');
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
            if (Schema::hasColumn('budget_compromise_details', 'tax_id')) {
                $table->dropForeign(['tax_id']);
                $table->bigInteger('tax_id')->change();
                $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
