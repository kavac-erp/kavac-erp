<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsPurchaseTypeIdDueDateAndHiringNumberToPurchaseDirectHiresTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsPurchaseTypeIdDueDateAndHiringNumberToPurchaseDirectHiresTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('purchase_direct_hires')) {
            Schema::table('purchase_direct_hires', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_direct_hires', 'purchase_type_id')) {
                    /*
                    | -----------------------------------------------------------------------
                    | Clave foránea a la relación con modalidades de compra
                    | -----------------------------------------------------------------------
                    */
                    $table->foreignId('purchase_type_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
                if (!Schema::hasColumn('purchase_direct_hires', 'due_date')) {
                    $table->text('due_date')->nullable()->comment('Plazo de entrega');
                }
                if (!Schema::hasColumn('purchase_direct_hires', 'hiring_number')) {
                    $table->text('hiring_number')->nullable()->comment('Número de contratación');
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
        Schema::table('purchase_direct_hires', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_direct_hires', 'purchase_type_id')) {
                $table->dropForeign(['purchase_type_id']);
                $table->dropColumn('purchase_type_id');
            }
            if (Schema::hasColumn('purchase_direct_hires', 'due_date')) {
                $table->dropColumn('due_date');
            }
            if (Schema::hasColumn('purchase_direct_hires', 'hiring_number')) {
                $table->dropColumn('hiring_number');
            }
        });
    }
}
