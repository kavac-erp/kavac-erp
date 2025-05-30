<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetDisincorporationsTable
 * @brief Crear tabla de las desincorporaciones de bienes institucionales
 *
 * Gestiona la creación o eliminación de la tabla de desincorporaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetDisincorporationsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_disincorporations')) {
            Schema::create('asset_disincorporations', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');
                $table->string('code', 20)->unique()->comment('Código identificador de la desincorporación');
                $table->foreignId('asset_disincorporation_motive_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->date('date')->nullable()->comment('Fecha de la desincorporación del bien');

                $table->string('observation')->nullable()
                      ->comment('Observaciones generales del estado del bien a desincorporar');

                $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_disincorporations');
    }
}
