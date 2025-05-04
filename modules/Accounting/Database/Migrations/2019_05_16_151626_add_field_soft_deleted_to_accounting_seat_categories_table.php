<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldSoftDeletedToAccountingSeatCategoriesTable
 * @brief Ejecuta la migración para agregar el campo deleted_at a la tabla accounting_seat_categories
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldSoftDeletedToAccountingSeatCategoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seat_categories', function (Blueprint $table) {
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('accounting_seat_categories')) {
            Schema::table('accounting_seat_categories', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
