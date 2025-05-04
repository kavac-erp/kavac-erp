<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetModificationsTable
 * @brief Crear tabla de modificaciones presupuestarias
 *
 * Gestiona la creación o eliminación de la tabla de modificaciones presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetModificationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_modifications')) {
            Schema::create('budget_modifications', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('approved_at')
                    ->comment("Fecha en la que se aprobó la modificación presupuestaria");
                $table->string('code', 20)->unique()
                    ->comment('Código único para el tipo de modifición presupuestaria');
                $table->enum('type', ['C', 'R', 'T'])
                    ->comment('Tipo de operación: (C)rédito, (R)educción o (T)raspaso');
                $table->text('description')
                    ->comment('Descripción del documento que avala la modificación presupuestaria');
                $table->string('document')
                    ->comment('Número del documento que avala la modificación presupuestaria');
                $table->foreignId('institution_id')->constrained()->onUpdate('cascade');
                $table->foreignId('document_status_id')->constrained('document_status')->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
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
        Schema::dropIfExists('budget_modifications');
    }
}
