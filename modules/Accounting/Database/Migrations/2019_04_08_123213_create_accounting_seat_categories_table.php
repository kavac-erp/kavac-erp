<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingSeatCategoriesTable
 * @brief Ejecuta la migración de categorías de asientos contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingSeatCategoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accounting_seat_categories')) {
            Schema::create('accounting_seat_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('name')->comment('Nombre de la categoria u origen del cual se genero el asiento contable');
                $table->text('acronym')->comment('acrónimo utilizado al generar un asiento de manera automatica');
                $table->timestamps();
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
        Schema::dropIfExists('accounting_seat_categories');
    }
}
