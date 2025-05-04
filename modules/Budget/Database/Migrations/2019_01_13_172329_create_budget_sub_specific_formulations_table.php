<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetSubSpecificFormulationsTable
 * @brief Crear tabla de formulación de presupuesto por subespecífica
 *
 * Gestiona la creación o eliminación de la tabla de formulación de presupuesto por subespecífica
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetSubSpecificFormulationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_sub_specific_formulations')) {
            Schema::create('budget_sub_specific_formulations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('year', 4)->comment('Año de formulación');
                $table->float('total_formulated', 30, 10)->comment('Monto total formulado');
                $table->boolean('assigned')->default(false)
                      ->comment('Establece si la formulación fue asignada para su ejecución');
                $table->foreignId('currency_id')->constrained()->onUpdate('cascade');
                $table->unsignedBigInteger('budget_specific_action_id')->comment(
                    'Identificador asociado a la acción específica de la formulación'
                );
                $table->foreign('budget_specific_action_id', 'budget_sub_specific_formulations_specific_action_fk')
                      ->references('id')->on('budget_specific_actions')->onUpdate('cascade');

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
        Schema::dropIfExists('budget_sub_specific_formulations');
    }
}
