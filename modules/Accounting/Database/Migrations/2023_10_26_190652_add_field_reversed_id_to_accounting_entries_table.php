<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldReversedIdToAccountingEntriesTable
 * @brief Ejecuta la migración para agregar el campo reversed_id a la tabla accounting_entries
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldReversedIdToAccountingEntriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entries', function (Blueprint $table) {
            $table->foreignId('reversed_id')->nullable()->constrained('accounting_entries')->onDelete('cascade')
                ->comment('id del asiento contable al que le esta aplicando reverse');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_entries', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_entries', 'reversed_id')) {
                $table->dropForeign(['reversed_id']);
                $table->dropColumn('reversed_id');
            }
        });
    }
}
