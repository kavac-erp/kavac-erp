<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldAccountingAccountIdToFinanceBankAccounts
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAccountingAccountIdToFinanceBankAccounts extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return    void
     */
    public function up()
    {
        if (Schema::hasTable('finance_bank_accounts')) {
            Schema::table('finance_bank_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_bank_accounts', 'accounting_account_id')) {
                    $table->foreignId('accounting_account_id')->nullable()
                          ->comment('Identificador único asociado a la cuenta contable')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };
            });
        };
    }

    /**
     * Método que elimina las migraciones
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return    void
     */
    public function down()
    {
        if (Schema::hasTable('finance_bank_accounts')) {
            Schema::table('finance_bank_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('finance_bank_accounts', 'accounting_account_id')) {
                    $table->dropForeign(['accounting_account_id']);
                    $table->dropColumn('accounting_account_id');
                };
            });
        };
    }
}
