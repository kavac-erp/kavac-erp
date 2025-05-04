<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToFinanceSettingBankReconciliationFiles
 *
 * @brief Agrega campos a las configuraciones de los archivos de conciliación
 * bancaria.
 *
 * Clase que gestiona los métodos para la gestión de la configuración de
 * archivos de conciliación bancaria.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class AddFieldsToFinanceSettingBankReconciliationFiles extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_setting_bank_reconciliation_files', function (Blueprint $table) {
            $table->integer('balance_according_bank')->nullable()->comment('Saldo según banco');
            $table->integer('position_balance_according_bank')->nullable()->comment('Posición del saldo según banco en el archivo');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_setting_bank_reconciliation_files', function (Blueprint $table) {
            $table->dropColumn('balance_according_bank');
            $table->dropColumn('position_balance_according_bank');
        });
    }
}
