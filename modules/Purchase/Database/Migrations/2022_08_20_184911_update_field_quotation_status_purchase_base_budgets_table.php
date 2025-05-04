<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldQuotationStatusPurchaseBaseBudgetsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldQuotationStatusPurchaseBaseBudgetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('purchase_base_budgets')) {
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_base_budgets', 'status2')) {
                    $table->dropColumn(['status2']);
                }
            });

            Schema::table('purchase_base_budgets', function ($table) {
                $table->enum('status2', ['WAIT', 'QUOTED', 'PARTIALLY_QUOTED', 'WAIT_QUOTATION', 'BOUGHT'])->nullable();
            });
            DB::statement('UPDATE "purchase_base_budgets" SET status2 = status ');
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_base_budgets', 'status')) {
                    $table->dropColumn(['status']);
                }
            });

            Schema::table('purchase_base_budgets', function (Blueprint $table) {

                if (!Schema::hasColumn('purchase_base_budgets', 'status')) {
                    $table->enum('status', ['WAIT', 'QUOTED', 'PARTIALLY_QUOTED', 'WAIT_QUOTATION', 'BOUGHT'])->default('WAIT')
                        ->comment(
                            'Determina el estatus del presupuesto base
                              (WAIT) - espera por ser completado.
                              (WAIT_QUOTATION) - espera ser cotizado.
                              (PARTIALLY_QUOTED) - Cotizado Parcialmente,
                              (QUOTED) - Cotizado,
                              (BOUGHT) - comprado',
                        );
                }
            });
            DB::statement('UPDATE "purchase_base_budgets" SET status = status2');
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                $table->dropColumn(['status2']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('purchase_base_budgets')) {
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_base_budgets', 'status')) {
                    $table->enum('status', ['WAIT', 'QUOTED', 'WAIT_QUOTATION', 'BOUGHT'])->default('WAIT')
                        ->comment(
                            'Determina el estatus del presupuesto base
                                (WAIT) - espera por ser completado.
                                (WAIT_QUOTATION) - espera ser cotizado.
                                (QUOTED) - Cotizado,
                                (BOUGHT) - comprado',
                        );
                }
            });
        }
    }
}
