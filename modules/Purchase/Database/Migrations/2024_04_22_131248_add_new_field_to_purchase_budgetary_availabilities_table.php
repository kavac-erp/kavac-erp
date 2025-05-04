<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class    AddNewFieldToPurchaseBudgetaryAvailabilitiesTable
 * @brief    Agregado nuevo campo a la migración de la tabla purchase_budgetary_availabilities.
 *
 * Clase que gestiona la actualización de campos de la tabla purchase_budgetary_availabilities.
 *
 * @author   Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddNewFieldToPurchaseBudgetaryAvailabilitiesTable extends Migration
{
    /**
     * Método que ejecuta las migraciones, se agrega nuevo campos "código"
     * en la tabla de disponibilidad presupuestaria.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {

        });
        if (Schema::hasTable('purchase_budgetary_availabilities')) {
            Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
                $table->string('code', 20)
                    ->nullable()
                    ->unique()
                    ->comment('Código para la disponibilidad presupuestaria manual');
            });
        }
    }

    /**
     * Método que revierte la operación del método up.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {

        });
        // Eliminar el campo code de la tabla budget_modifications.
        if (Schema::hasTable('purchase_budgetary_availabilities')) {
            Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_budgetary_availabilities', 'code')) {
                    $table->dropColumn('code');
                }
            });
        }
    }
}
