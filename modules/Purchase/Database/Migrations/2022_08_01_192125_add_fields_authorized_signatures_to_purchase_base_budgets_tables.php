<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsAuthorizedSignaturesToPurchaseBaseBudgetsTables
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsAuthorizedSignaturesToPurchaseBaseBudgetsTables extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    void
     */
    public function up()
    {

        if (Schema::hasTable('purchase_base_budgets')) {
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_base_budgets', 'prepared_by_id')) {
                    $table->foreignId('prepared_by_id')->nullable()
                    ->constrained('payroll_employments')->onUpdate('cascade')->comment('Preparado por');
                }

                if (!Schema::hasColumn('purchase_base_budgets', 'reviewed_by_id')) {
                    $table->foreignId('reviewed_by_id')->nullable()
                        ->constrained('payroll_employments')->onUpdate('cascade')->comment('Revisado por');
                }

                if (!Schema::hasColumn('purchase_base_budgets', 'verified_by_id')) {
                    $table->foreignId('verified_by_id')->nullable()
                        ->constrained('payroll_employments')->onUpdate('cascade')->comment('Verificado por');
                }

                if (!Schema::hasColumn('purchase_base_budgets', 'first_signature_id')) {
                    $table->foreignId('first_signature_id')->nullable()
                        ->constrained('payroll_employments')->onUpdate('cascade')->comment('Firmado por');
                }

                if (!Schema::hasColumn('purchase_base_budgets', 'second_signature_id')) {
                    $table->foreignId('second_signature_id')->nullable()
                        ->constrained('payroll_employments')->onUpdate('cascade')->comment('Firmado por');
                }
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    void
     */
    public function down()
    {
        if (Schema::hasTable('purchase_base_budgets')) {
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_base_budgets', 'prepared_by_id')) {
                    $table->dropForeign(['prepared_by_id']);
                    $table->dropColumn('prepared_by_id');
                };
                if (Schema::hasColumn('purchase_base_budgets', 'reviewed_by_id')) {
                            $table->dropForeign(['reviewed_by_id']);
                            $table->dropColumn('reviewed_by_id');
                };
                if (Schema::hasColumn('purchase_base_budgets', 'verified_by_id')) {
                            $table->dropForeign(['verified_by_id']);
                            $table->dropColumn('verified_by_id');
                };
                if (Schema::hasColumn('purchase_base_budgets', 'first_signature_id')) {
                            $table->dropForeign(['first_signature_id']);
                            $table->dropColumn('first_signature_id');
                };
                if (Schema::hasColumn('purchase_base_budgets', 'second_signature_id')) {
                            $table->dropForeign(['second_signature_id']);
                            $table->dropColumn('second_signature_id');
                };
            });
        };
    }
}
