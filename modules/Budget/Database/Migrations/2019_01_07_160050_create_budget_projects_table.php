<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetProjectsTable
 * @brief Crear tabla de Proyectos en presupuesto
 *
 * Gestiona la creación o eliminación de la tabla de Proyectos en presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_projects')) {
            Schema::create('budget_projects', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->comment('Nombre del Proyecto');
                $table->string('code')->unique()->comment('Código del Proyecto');
                $table->string('onapre_code')->nullable()->comment('Código otorgado por la ONAPRE');
                $table->boolean('active')->default(true)->comment('Indica si el proyecto esta activo');
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
        Schema::dropIfExists('budget_projects');
    }
}
