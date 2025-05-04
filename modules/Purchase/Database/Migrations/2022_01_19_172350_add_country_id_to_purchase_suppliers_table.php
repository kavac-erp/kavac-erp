<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddCountryIdToPurchaseSuppliersTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddCountryIdToPurchaseSuppliersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_suppliers', 'country_id')) {
                $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'id del pais a relacionar con el registro'
                );
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
        Schema::table('purchase_suppliers', function (Blueprint $table) {
            $table->dropColumn('country_id');
        });
    }
}
