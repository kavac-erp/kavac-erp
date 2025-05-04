<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangePurchaseSuppliersTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangePurchaseSuppliersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('purchase_suppliers')) {
            Schema::table('purchase_suppliers', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_suppliers', 'purchase_supplier_branch_id')) {
                    $table->foreignId('purchase_supplier_branch_id')->nullable()->change();
                }
                if (Schema::hasColumn('purchase_suppliers', 'purchase_supplier_specialty_id')) {
                    $table->foreignId('purchase_supplier_specialty_id')->nullable()->change();
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
        if (Schema::hasTable('purchase_suppliers')) {
            Schema::table('purchase_suppliers', function (Blueprint $table) {
                /*if (Schema::hasColumn('purchase_suppliers', 'purchase_supplier_branch_id')) {
                    $table->foreignId('purchase_supplier_branch_id')->constrained()
                        ->onDelete('restrict')->onUpdate('cascade')->change();

                }
                if (Schema::hasColumn('purchase_suppliers', 'purchase_supplier_specialty_id')) {
                    $table->foreignId('purchase_supplier_specialty_id')->constrained()
                        ->onDelete('restrict')->onUpdate('cascade')->change();

                }*/
            });
        }
    }
}
