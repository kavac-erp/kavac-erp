<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldStatusToPurchaseDirectHiresTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldStatusToPurchaseDirectHiresTable extends Migration
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
                if (!Schema::hasColumn('purchase_direct_hires', 'status')) {
                    $table->enum('status', ['WAIT', 'APPROVED'])->default('WAIT')->comment(
                        'Determina el estatus de la orden de compra
                        (WAIT) - en espera de ser aprobado.
                        (APPROVED) - Aprobado',
                    );
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
        if (Schema::hasTable('purchase_direct_hires')) {
            Schema::table('purchase_direct_hires', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_direct_hires', 'status')) {
                    $table->dropColumn(['status']);
                }
            });
        }
    }
}
