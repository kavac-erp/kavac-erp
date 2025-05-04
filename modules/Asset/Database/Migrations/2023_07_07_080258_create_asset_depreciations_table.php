<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetDepreciationsTable
 * @brief Crea la tabla de depreciaciones de bienes
 *
 * Crea la tabla de depreciaciones de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetDepreciationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Código de la depreciación');
            $table->string('year', 4)->comment('Año de la depreciación');
            $table->decimal('amount', 20, 10)->default(0)->comment('Monto de la depreciación');
            $table->foreignId('document_status_id')
                    ->references('id')
                    ->on('document_status')
                    ->nullable()
                    ->comment('Identificador asociado al estatus de la depreciación')
                    ->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('asset_depreciations');
    }
}
