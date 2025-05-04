<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetStoragesTable
 * @brief Crear tabla para los depósitos registrados
 *
 * Gestiona la creación o eliminación de la tabla de depósitos
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetStoragesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @author  Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_storages')) {
            Schema::create('asset_storages', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->string('name', 100)->comment('Nombre o descripción del depósito');

                $table->boolean('active')->default(true)
                      ->comment('Estatus del depósito. (true) activo, (false) inactivo');

                $table->text('address')->comment('Dirección física del depósito');

                $table->foreignId('parish_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('asset_storages');
    }
}
