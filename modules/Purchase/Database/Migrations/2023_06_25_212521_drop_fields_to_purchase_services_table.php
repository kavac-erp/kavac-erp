<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DropFieldsToPurchaseServicesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DropFieldsToPurchaseServicesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_services', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_services', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('purchase_services', 'institution_id')) {
                $table->dropForeign('purchase_services_institution_id_foreign');
                $table->dropColumn('institution_id');
            }
            if (Schema::hasColumn('purchase_services', 'history_tax_id')) {
                $table->dropForeign('purchase_services_history_tax_id_foreign');
                $table->dropColumn('history_tax_id');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_services', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_services', 'date')) {
                $table->date('date')->nullable()->comment('Fecha de generación');
            }
            if (!Schema::hasColumn('purchase_services', 'institution_id')) {
                $table->foreignId('institution_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('purchase_services', 'history_tax_id')) {
                $table->foreignId('history_tax_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
