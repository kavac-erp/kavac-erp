<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class RenamePurchaseQuotationId
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RenamePurchaseQuotationId extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_budgetary_availabilities')) {
            Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_budgetary_availabilities', 'purchase_quotation_id')) {
                    $table->dropForeign(['purchase_quotation_id']);
                    $table->renameColumn('purchase_quotation_id', 'purchase_base_budgets_id');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
            $table->renameColumn('purchase_base_budgets_id', 'purchase_quotation_id');
            $table->foreign('purchase_quotation_id')->references('id')
                ->on('purchase_quotations')->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }
}
