<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleSettingDepositsTable
 * @brief Migración encargada de crear la tabla de los tipos de depósitos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleSettingDepositsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_setting_deposits', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name')->comment('Nombre');
            $table->string('description')->comment('Descripción');
            $table->string('deposit_attributes')->comment('Atributos');

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
        Schema::dropIfExists('sale_setting_deposits');
    }
}
