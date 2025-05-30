<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseRequirementsTable
 * @brief Migración encargada de crear la tabla de requerimientos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseRequirementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_requirements')) {
            Schema::create('purchase_requirements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 20)->unique()->comment('Código único para el requerimiento de compra');
                $table->text('description')->comment("Descripción del requerimiento");

                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación del año fiscal
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación al año fiscal
                */
                $table->bigInteger('fiscal_year_id')->unsigned()
                          ->comment('Identificador del año fiscal');
                $table->foreign('fiscal_year_id')->references('id')
                          ->on('fiscal_years')->onDelete('restrict')
                          ->onUpdate('cascade');

                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación de la unidad contratante
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación a la unidad o departamento contratante del
                | requerimiento a registrar
                */
                $table->bigInteger('contracting_department_id')->unsigned()->nullable()
                          ->comment('Identificador de la unidad o departamento contratante. Opcional');
                $table->foreign('contracting_department_id')->references('id')
                          ->on('departments')->onDelete('restrict')
                          ->onUpdate('cascade');

                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación de la unidad usuaria
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación a la unidad o departamento usuaria del
                | requerimiento a registrar
                */
                $table->bigInteger('user_department_id')->unsigned()
                          ->comment('Identificador de la unidad o departamento usuaria del requerimiento');
                $table->foreign('user_department_id')->references('id')
                          ->on('departments')->onDelete('restrict')
                          ->onUpdate('cascade');

                /*
                | -----------------------------------------------------------------------
                | Clave foránea a la relación del tipo de proveedor
                | -----------------------------------------------------------------------
                |
                | Define la estructura de relación al tipo de proveedor según el requerimiento
                | a ser registrado
                */
                $table->bigInteger('purchase_supplier_type_id')->unsigned()
                        ->comment('Identificador del tipo de requerimiento (tipo de proveedor)');
                $table->foreign('purchase_supplier_type_id')->references('id')
                        ->on('purchase_supplier_types')->onDelete('restrict')
                        ->onUpdate('cascade');

                $table->bigInteger('purchase_base_budget_id')->unsigned()
                        ->comment('Identificador del presupuesto base');
                $table->foreign('purchase_base_budget_id')->references('id')
                        ->on('purchase_base_budgets')->onDelete('restrict')
                        ->onUpdate('cascade');

                $table->enum('requirement_status', ['WAIT', 'PROCESSED','QUOTED', 'BOUGHT'])
                    ->default('WAIT')
                    ->comment(
                        "Determina el estatus del requerimiento
                        (WAIT) - en espera.
                        (PROCESSED) - Procesado,
                        (BOUGHT) - comprado"
                    );

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
        Schema::dropIfExists('purchase_requirements');
    }
}
