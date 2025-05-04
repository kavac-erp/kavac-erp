<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPurchasePlansTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToPurchasePlansTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('purchase_plans')) {
            Schema::table('purchase_plans', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_plans', 'user_id')) {
                    $table->dropColumn('user_id');
                }
                if (!Schema::hasColumn('purchase_plans', 'payroll_staff_id')) {
                    $table->foreignId('payroll_staff_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        if (Schema::hasTable('purchase_plans')) {
            Schema::table('purchase_plans', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_plans', 'payroll_staff_id')) {
                    $table->dropForeign(['payroll_staff_id']);
                    $table->dropColumn('payroll_staff_id');
                }
                if (!Schema::hasColumn('purchase_plans', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
            });
        }
    }
}
