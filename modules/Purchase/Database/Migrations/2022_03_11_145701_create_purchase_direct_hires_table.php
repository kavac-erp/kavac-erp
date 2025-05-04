<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseDirectHiresTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseDirectHiresTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_direct_hires', function (Blueprint $table) {
            $table->id();

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
            | Clave foránea a la relación con proveedor
            | -----------------------------------------------------------------------
            */
            $table->foreignId('purchase_supplier_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

            /*
            | -----------------------------------------------------------------------
            | Clave foránea a la relación al tipo de moneda
            | -----------------------------------------------------------------------
            */
            $table->foreignId('currency_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

            $table->text('funding_source')->comment('Fuente de financiamiento');
            $table->text('description')->comment('Denominación especifica del requerimiento');

            $table->timestamps();
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
        Schema::dropIfExists('purchase_direct_hires');
    }
}
