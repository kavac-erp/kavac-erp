<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetAdjustmentAssetsTable
 * @brief Crea la tabla de ajustes de bienes
 *
 * Crea la tabla de ajustes de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetAdjustmentAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_adjustment_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                    ->nullable()
                    ->comment('Identificador asociado al bien')
                    ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->text('description')->nullable()->comment('Descripción del ajuste del bien');
            $table->decimal('adjustment_value', 20, 10)->nullable()->comment('Valor de adquisición del bien');
            $table->decimal('residual_value', 20, 10)->nullable()->comment('Valor de residual del bien');
            $table->integer('depresciation_years')->nullable()->comment('Años de depreciación del bien');
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
        Schema::dropIfExists('asset_adjustment_assets');
    }
}
