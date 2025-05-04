<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingEntryablesTable
 * @brief Ejecuta la migración de la tabla accounting_entryables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingEntryablesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        // Relacion N-M para asientos contables
        if (!Schema::hasTable('accounting_entryables')) {
            Schema::create('accounting_entryables', function (Blueprint $table) {
                $table->id();

                $table->foreignId('accounting_entry_id')->constrained()->onDelete('cascade')->comment(
                    'llave foranea a registro en la tabla accounting_entries'
                );

                $table->morphs('accounting_entryable', 'accounting_entryable_index');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_entryables');
    }
}
