<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetBranchSupplierTable
 * @brief Crea la tabla de asset_branch_suppliers
 *
 * Crea la tabla de asset_branch_suppliers
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetBranchSupplierTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_branch_supplier')) {
            Schema::create('purchase_branch_supplier', function (Blueprint $table) {

                $table->bigIncrements('id');
                /*
                * -----------------------------------------------------------------------
                * Clave foránea a la relación del proveedor
                * -----------------------------------------------------------------------
                *
                * Define la estructura de relación a la información del proveedor
                */
                $table->foreignId('purchase_supplier_id')->constrained()
                        ->onDelete('restrict')->onUpdate('cascade');
                /*
                * -----------------------------------------------------------------------
                * Clave foránea a la relación de la rama del proveedor
                * -----------------------------------------------------------------------
                *
                * Define la estructura de relación a la información de la rama del proveedor
                */
                $table->foreignId('purchase_supplier_branch_id')->constrained()
                        ->onDelete('restrict')->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
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
        Schema::dropIfExists('purchase_branch_supplier');
    }
}
