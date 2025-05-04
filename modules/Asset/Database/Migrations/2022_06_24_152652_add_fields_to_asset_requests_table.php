<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToAssetRequestsTable
 * @brief Agrega campos adicionales a la tabla asset_requests
 *
 * Agrega campos adicionales a la tabla asset_requests
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToAssetRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_requests')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_requests', 'address')) {
                    $table->text('address')->nullable()->comment('Dirección fisíca a donde se prestará el bien');
                }
                if (!Schema::hasColumn('asset_requests', 'country_id')) {
                    $table->foreignId('country_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
                if (!Schema::hasColumn('asset_requests', 'estate_id')) {
                    $table->foreignId('estate_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
                if (!Schema::hasColumn('asset_requests', 'municipality_id')) {
                    $table->foreignId('municipality_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
                if (!Schema::hasColumn('asset_requests', 'parish_id')) {
                    $table->foreignId('parish_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        if (Schema::hasTable('asset_requests')) {
            Schema::table('asset_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_requests', 'address')) {
                    $table->text('address')->nullable()->comment('Dirección fisíca a donde se prestará el bien');
                }
                if (Schema::hasColumn('asset_requests', 'country_id')) {
                    $table->dropForeign(['country_id']);
                    $table->dropColumn('country_id');
                }
                if (Schema::hasColumn('asset_requests', 'estate_id')) {
                    $table->dropForeign(['estate_id']);
                    $table->dropColumn('estate_id');
                }
                if (Schema::hasColumn('asset_requests', 'municipality_id')) {
                    $table->dropForeign(['municipality_id']);
                    $table->dropColumn('municipality_id');
                }
                if (Schema::hasColumn('asset_requests', 'parish_id')) {
                    $table->dropForeign(['parish_id']);
                    $table->dropColumn('parish_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
            });
        }
    }
}
