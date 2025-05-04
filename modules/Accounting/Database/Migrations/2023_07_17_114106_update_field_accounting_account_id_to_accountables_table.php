<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldAccountingAccountIdToAccountablesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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
