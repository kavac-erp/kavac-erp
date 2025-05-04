<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetCentralizedActionsTable
 * @brief Crear tabla de acciones centralizadas
 *
 * Gestiona la creación o eliminación de la tabla de acciones centralizadas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetCentralizedActionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_centralized_actions')) {
            Schema::create('budget_centralized_actions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('custom_date')->comment('Fecha de creación personalizada indicada por el usuario');
                $table->string('name')->comment('Nombre de la Acción Centralizada');
                $table->string('code')->unique()->comment('Código de la Acción Centralizada');
                $table->boolean('active')->default(true)->comment('Indica si la acción centralizada esta activa');
                $table->foreignId('department_id')->constrained()->onUpdate('cascade');
                $table->foreignId('payroll_position_id')->constrained()->onUpdate('cascade');
                $table->foreignId('payroll_staff_id')->constrained()->onUpdate('cascade');
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
        Schema::dropIfExists('budget_centralized_actions');
    }
}
