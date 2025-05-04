<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class UpdateFieldReviewedByIdDirectHireTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldReviewedByIdDirectHireTable extends Migration
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
                if (Schema::hasColumn('purchase_direct_hires', 'reviewed_by_id')) {
                    $table->bigInteger('reviewed_by_id')->nullable()->change();
                }
                if (Schema::hasColumn('purchase_direct_hires', 'first_signature_id')) {
                    $table->bigInteger('first_signature_id')->nullable()->change();
                }
                if (Schema::hasColumn('purchase_direct_hires', 'second_signature_id')) {
                    $table->bigInteger('second_signature_id')->nullable()->change();
                }

                if (Schema::hasColumn('purchase_direct_hires', 'verified_by_id')) {
                    $table->bigInteger('verified_by_id')->nullable()->change();
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
        Schema::table('', function (Blueprint $table) {
        });
    }
}
