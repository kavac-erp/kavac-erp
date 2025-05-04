<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateUniqueFieldSerialToAssetsTable
 * @brief Agrega unica clave única al campo serial de la tabla de bienes
 *
 * Agrega unica clave única al campo serial de la tabla de bienes
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateUniqueFieldSerialToAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {

                $table->unique(['serial'])->comment('Serial del fabricante');
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
        DB::statement("ALTER TABLE assets DROP CONSTRAINT IF EXISTS assets_serial_unique");
    }
}
