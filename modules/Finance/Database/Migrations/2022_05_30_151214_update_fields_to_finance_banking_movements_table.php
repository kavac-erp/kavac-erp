<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToFinanceBankingMovementsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToFinanceBankingMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'accounting_entry_id')) {
                $table->dropForeign('finance_banking_movements_accounting_entry_id_foreign');
                $table->dropColumn('accounting_entry_id');
            };
            if (Schema::hasColumn('finance_banking_movements', 'budget_compromise_id')) {
                $table->dropForeign('finance_banking_movements_budget_compromise_id_foreign');
                $table->dropColumn('budget_compromise_id');
            };
        });
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_banking_movements', 'code')) {
                $table->string('code', 20)->unique()->comment('Código identificador de la solicitud');
            };
            if (!Schema::hasColumn('finance_banking_movements', 'institution_id')) {
                $table->foreignId('institution_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')->comment('Institución');
            };
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                /** Relación a la acción específica en presupuesto */
                $table->foreignId('accounting_entry_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }

            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                /** Relación a la acción específica en presupuesto */
                $table->foreignId('budget_compromise_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }
        });
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'code')) {
                $table->dropColumn('code');
            };
            if (Schema::hasColumn('finance_banking_movements', 'institution_id')) {
                $table->dropForeign('finance_banking_movements_institution_id_foreign');
                $table->dropColumn('institution_id');
            };
        });
    }
}
