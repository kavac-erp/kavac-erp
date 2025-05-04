<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldAccountingAccountIdToAccountablesTable
 * @brief Ejecuta la migración para agregar el campo accounting_account_id a la tabla accountables
 *
 * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldAccountingAccountIdToAccountablesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accountables', function (Blueprint $table) {
            if (Schema::hasColumn('accountables', 'accountable_id')) {
                $table->foreignId('accountable_id')
                      ->nullable()
                      ->change();
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
        Schema::table('accountables', function (Blueprint $table) {
            if (Schema::hasColumn('accountables', 'accountable_id')) {
                $table->foreignId('accountable_id')
                      ->change();
            }
        });
    }
}
