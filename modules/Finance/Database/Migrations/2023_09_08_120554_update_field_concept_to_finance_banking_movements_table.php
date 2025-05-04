<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldConceptToFinanceBankingMovementsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldConceptToFinanceBankingMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'concept')) {
                $table->longText('concept')->nullable()->comment('Concepto')->change();
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
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'concept')) {
                $table->string('concept')->comment('Concepto')->change();
            }
        });
    }
}
