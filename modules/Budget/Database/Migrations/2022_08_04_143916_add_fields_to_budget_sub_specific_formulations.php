<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToBudgetSubSpecificFormulations
 *
 * @brief Agrega campos a las formulacioces de presupuesto.
 *
 * Clase que gestiona los métodos para las formulacioces de presupuesto.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToBudgetSubSpecificFormulations extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            $table->foreignId('budget_financement_type_id')->nullable()->constrained()->onUpdate('cascade');
            $table->foreignId('budget_financement_source_id')->nullable()->constrained()->onUpdate('cascade');
            $table->float('financement_amount', 30, 10)->nullable()->comment('Monto del financiamiento');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            if (Schema::hasColumn('budget_sub_specific_formulations', 'financement_type_id')) {
                $table->dropColumn('financement_type_id');
            }
            if (Schema::hasColumn('budget_sub_specific_formulations', 'financement_source_id')) {
                $table->dropColumn('financement_source_id');
            }
            if (Schema::hasColumn('budget_sub_specific_formulations', 'financement_amount')) {
                $table->dropColumn('financement_amount');
            }
        });
    }
}
