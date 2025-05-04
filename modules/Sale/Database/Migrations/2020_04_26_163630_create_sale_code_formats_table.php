<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
/**
 * @class CreateSaleCodeFormatsTable
 * @brief Migración encargada de crear la tabla de formatos de código
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleCodeFormatsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_code_formats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('formatcode', 17)->unique()->comment('Format code');
            $table->string('type_formatcode', 50)->comment('Type of Format code');
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
        Schema::dropIfExists('sale_code_formats');
    }
}
