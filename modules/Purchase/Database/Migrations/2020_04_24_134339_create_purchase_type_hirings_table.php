<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseTypeHiringsTable
 * @brief Migración encargada de crear la tabla de tipos de contratación
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseTypeHiringsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_type_hirings')) {
            Schema::create('purchase_type_hirings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('date')->nullable()->comment('Fecha del tipo de contratación');
                $table->boolean('active')->default(true)->comment('Indica si el tipo de contratación esta activo');

                $table->foreignId('purchase_type_operation_id')->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->float('ut', 15, 2)->comment('Monto de unidades tributarias');
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
        Schema::dropIfExists('purchase_type_hirings');
    }
}
