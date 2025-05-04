<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldInstitutionIdToAccountingEntryCategoriesTable
 * @brief Ejecuta la migración para agregar el campo institution_id a la tabla accounting_entry_categories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldInstitutionIdToAccountingEntryCategoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entry_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_entry_categories', 'institution_id')) {
                $table->foreignId('institution_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'id de la institucion a relacionar con el registro'
                );
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
        Schema::table('accounting_entry_categories', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_entry_categories', 'institution_id')) {
                $table->dropForeign(['institution_id']);
                $table->dropColumn('institution_id');
            }
        });
    }
}
