<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class    AddNewFieldToBudgetModificationsTable
 *
 * @brief    Agregado nuevo campo a la migración de la tabla
 * budget_modifications.
 *
 * Clase que gestiona la actualización de campos de la tabla
 * budget_modifications.
 *
 * @author   Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddNewFieldToBudgetModificationsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones, se agrega nuevo campos para
     * la gestión de los datos de la tabla budget_modifications.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('budget_modifications')) {
            Schema::table('budget_modifications', function (Blueprint $table) {
                $table->string('status')
                    ->default('PE')
                    ->nullable()
                    ->comment('Estatus del registro (PE=Pendiente y AP=Aprobado)');
            });
        }
    }

    /**
     * Método que la operación del método up..
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        // Eliminar el campo status de la tabla budget_modifications.
        if (Schema::hasTable('budget_modifications')) {
            Schema::table('budget_modifications', function (Blueprint $table) {
                if (Schema::hasColumn('budget_modifications', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
}
