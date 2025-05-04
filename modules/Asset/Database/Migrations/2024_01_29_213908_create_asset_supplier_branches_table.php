<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetSupplierBranchesTable
 * @brief Crea la tabla de purchase_supplier_branches
 *
 * Crea la tabla de purchase_supplier_branches
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetSupplierBranchesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_supplier_branches')) {
            Schema::create('purchase_supplier_branches', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->comment('Nombre de la rama del proveedor');
                $table->text('description')->nullable()
                        ->comment('Descripción de la rama del proveedor. Opcional');
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
        Schema::dropIfExists('purchase_supplier_branches');
    }
}
