<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetDepreciationAssetsTable
 * @brief Crea la tabla que almacena los bienes depreciados
 *
 * Crea la tabla que almacena los bienes depreciados
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetDepreciationAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_depreciation_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_depreciation_id')
                    ->nullable()
                    ->comment('Identificador asociado al bien')
                    ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('asset_id')
                    ->nullable()
                    ->comment('Identificador asociado al bien')
                    ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('amount', 20, 10)->default(0)->comment('Monto de la depreciación');
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
        Schema::dropIfExists('asset_depreciation_assets');
    }
}
