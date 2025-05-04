<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToBudgetSubSpecificFormulations
 * @brief Agrega el campo 'date' a la tabla 'budget_sub_specific_formulations'
 *
 * Clase que gestiona la creación de un nuevo campo para la tabla
 * de la formulación de presupuesto.
 *
 * @author Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldToBudgetSubSpecificFormulations extends Migration
{
    /**
     * Método que ejecuta las migraciones para haceptar datos nulos en los
     * campos de la tabla.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            $table->date('date')
                ->nullable()
                ->comment('Fecha de generación');
        });
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            if (Schema::hasColumn('budget_sub_specific_formulations', 'date')) {
                $table->dropColumn('date');
            }
        });
    }
}
