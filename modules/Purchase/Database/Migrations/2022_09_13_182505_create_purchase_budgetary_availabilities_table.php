<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class CreatePurchaseBudgetaryAvailabilitiesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseBudgetaryAvailabilitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_budgetary_availabilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('item_code')->nullable()->comment('Codigo de Partida');
            $table->text('item_name')->nullable()->comment('Nombre de Partida');
            $table->text('amount')->nullable()->comment('Monto de Partida');
            $table->text('description')->nullable()
                ->comment('Descripción o comentario');
            $table->string('availability')->nullable()->comment('Disponibilidad');



            /*
             | -----------------------------------------------------------------------
             | Clave foránea a la relación del requerimiento
             | -----------------------------------------------------------------------
             |
             | Define la estructura de relación al requerimiento
             */
            $table->bigInteger('purchase_quotation_id')->unsigned()
                ->comment('Identificador del cotizacion de compra');
            $table->foreign('purchase_quotation_id')->references('id')
                ->on('purchase_quotations')->onDelete('restrict')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('purchase_budgetary_availabilities');
    }
}
