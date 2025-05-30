<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetStagesTable
 * @brief Crear tabla de estados presupuestarios
 *
 * Gestiona la creación o eliminación de la tabla de estados presupuestarios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetStagesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_stages')) {
            Schema::create('budget_stages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 20)->unique()
                      ->comment("Código único que identifica la etapa presupuestaria del compromiso");
                $table->date('registered_at');
                $table->enum('type', ['PRE', 'PRO', 'COM', 'CAU', 'PAG'])
                      ->comment(
                          'Identifica las etapas presupuestarias del compromiso. Ej. (PRE)compromiso, (PRO)gramado, ' .
                          '(COM)prometido, (CAU)sado, (PAG)ado'
                      );
                $table->float('amount', 30, 10)
                      ->comment('Monto por el que se establece la etapa presupuestaria del compromiso');
                $table->foreignId('budget_compromise_id')->constrained()->onUpdate('cascade');
                /** Relación para los documentos de origen que generan la etapa presupuestaria del compromiso */
                $table->morphs('sourceable');
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
        Schema::dropIfExists('budget_stages');
    }
}
