<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldReversedAtToAccountingEntriesTable
 * @brief Ejecuta la migración para agregar el campo reversed_at a la tabla accounting_entries
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldReversedAtToAccountingEntriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('accounting_entries')) {
            Schema::table('accounting_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('accounting_entries', 'reversed_at')) {
                    $table->date('reversed_at')->nullable()->comment('Fecha en que se reversó el asiento contable');
                }
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('accounting_entries')) {
            Schema::table('accounting_entries', function (Blueprint $table) {
                if (Schema::hasColumn('accounting_entries', 'reversed_at')) {
                    $table->dropColumn('reversed_at');
                }
            });
        }
    }
}
