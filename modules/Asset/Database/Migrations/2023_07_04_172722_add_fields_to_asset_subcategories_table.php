<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToAssetSubcategoriesTable
 * @brief Agrega campos a la tabla asset_subcategories
 *
 * Agrega campos a la tabla asset_subcategories
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToAssetSubcategoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_subcategories')) {
            Schema::table('asset_subcategories', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_subcategories', 'accounting_account_debit')) {
                    $table
                        ->foreignId('accounting_account_debit')
                        ->nullable()
                        ->references('id')
                        ->on('accounting_accounts')
                        ->onDelete('restrict')
                        ->onUpdate('cascade');
                }
                if (!Schema::hasColumn('asset_subcategories', 'accounting_account_asset')) {
                    $table
                        ->foreignId('accounting_account_asset')
                        ->nullable()
                        ->references('id')
                        ->on('accounting_accounts')
                        ->onDelete('restrict')
                        ->onUpdate('cascade');
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
        if (Schema::hasTable('asset_subcategories')) {
            Schema::table('asset_subcategories', function (Blueprint $table) {
                if (Schema::hasColumn('asset_subcategories', 'accounting_account_debit')) {
                    $table->dropForeign('asset_subcategories_accounting_account_debit_foreign');
                    $table->dropColumn('accounting_account_debit');
                }
                if (Schema::hasColumn('asset_subcategories', 'accounting_account_asset')) {
                    $table->dropForeign('asset_subcategories_accounting_account_asset_foreign');
                    $table->dropColumn('accounting_account_asset');
                }
            });
        }
    }
}
