<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetAditionalCreditsTable
 * @brief Crear tabla de créditos adicionales
 *
 * Gestiona la creación o eliminación de la tabla de créditos adicionales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetAditionalCreditsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_aditional_credits')) {
            Schema::create('budget_aditional_credits', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 20)->unique()->comment('Código único para el crédito adicional');
                $table->date('credit_date')->comment('Fecha en la que se otorgó el crédito');
                $table->text('description')->comment('Descripción del documento que avala el crédito');
                $table->string('document')->unique()->comment('Número del documento que avala el crédito');
                $table->foreignId('institution_id')->nullable()->constrained()->onUpdate('cascade');
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
        Schema::dropIfExists('budget_aditional_credits');
    }
}
