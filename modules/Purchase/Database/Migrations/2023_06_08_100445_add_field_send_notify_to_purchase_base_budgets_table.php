<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Purchase\Models\PurchaseBaseBudget;

/**
 * @class AddFieldSendNotifyToPurchaseBaseBudgetsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldSendNotifyToPurchaseBaseBudgetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("purchase_base_budgets")) {
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (!Schema::hasColumn("purchase_base_budgets", "send_notify")) {
                    $table->boolean('send_notify')->nullable()
                        ->comment("Estado del envio de la notificación para solicitar disponibilidad presupuiestaria");
                }
            });

            foreach (PurchaseBaseBudget::all() as $purchase_base_buget) {
                $purchase_base_buget->send_notify = strlen($purchase_base_buget->availability) != 0
                                                        ? true : false;
                $purchase_base_buget->save();
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('purchase_base_budgets')) {
            Schema::table('purchase_base_budgets', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_base_budgets', 'send_notify')) {
                    $table->dropColumn(['send_notify']);
                }
            });
        }
    }
}
