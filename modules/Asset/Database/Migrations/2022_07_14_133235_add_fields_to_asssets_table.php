<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToAsssetsTable
 * @brief Agrega campos adicionales a la tabla assets
 *
 * Agrega campos adicionales a la tabla assets
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToAsssetsTable extends Migration
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
                if (!Schema::hasColumn('assets', 'color')) {
                    $table->text('color')->nullable()->comment('Color del bien institucional');
                }
                if (!Schema::hasColumn('assets', 'asset_institutional_code')) {
                    $table->string('asset_institutional_code', 150)->unique()->nullable()->comment('Color del bien institucional');
                }
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
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (Schema::hasColumn('assets', 'color')) {
                    $table->dropColumn('color');
                }
                if (Schema::hasColumn('asssets', 'asset_institutional_code')) {
                    $table->dropColumn('asset_institutional_code');
                }
            });
        }
    }
}
