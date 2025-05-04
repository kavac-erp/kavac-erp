<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldActiveToAccountingAccountConvertersTable
 * @brief Ejecuta la migración para agregar el campo active a la tabla accounting_account_converters
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldActiveToAccountingAccountConvertersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('accounting_account_converters', 'active')) {
            Schema::table('accounting_account_converters', function (Blueprint $table) {
                $table->boolean('active')->default(true)->comment('Indica si la conversión esta activa');
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
        if (Schema::hasColumn('accounting_account_converters', 'active')) {
            Schema::table('accounting_account_converters', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }
    }
}
