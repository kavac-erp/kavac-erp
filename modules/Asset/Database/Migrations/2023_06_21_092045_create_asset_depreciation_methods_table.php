<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetDepreciationMethondsTable
 * @brief Crea la tabla asset_depreciation_methods
 *
 * Crea la tabla asset_depreciation_methods
 *
 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetDepreciationMethodsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_depreciation_methods')) {
            Schema::create('asset_depreciation_methods', function (Blueprint $table) {
                $table->id();
                $table->smallInteger('depreciation_type_id')->comment('Tipo de depreciación');
                $table->date('activation_date')->nullable()->comment('Fecha de activación');
                $table->boolean('active')->default(false)->comment('Activo');
                $table->foreignId('institution_id')
                    ->nullable()
                    ->comment('Identificador único asociado a la institución')
                    ->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('asset_depreciation_methods');
    }
}
