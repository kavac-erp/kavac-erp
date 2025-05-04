<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RenameAccountingEntryCategoriesIdInAccountingEntriesTable
 * @brief Ejecuta la migración para renombrar el campo accounting_entry_categories_id a accounting_entry_category_id
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RenameAccountingEntryCategoriesIdInAccountingEntriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entries', function (Blueprint $table) {
            $table->renameColumn('accounting_entry_categories_id', 'accounting_entry_category_id');
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
            if (Schema::hasColumn('accounting_entries', 'accounting_entry_category_id')) {
                $table->renameColumn('accounting_entry_category_id', 'accounting_entry_categories_id');
            }
        });
    }
}
