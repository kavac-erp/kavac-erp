<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetSpecialtySupplierTable
 * @brief Crea la tabla de asset_specialty_suppliers
 *
 * Crea la tabla de asset_specialty_suppliers
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetSpecialtySupplierTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_specialty_supplier')) {
            Schema::create('purchase_specialty_supplier', function (Blueprint $table) {
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
                * Clave foránea a la relación de la especialidad del proveedor
                * -----------------------------------------------------------------------
                *
                * Define la estructura de relación a la información de la especialidad del proveedor
                */
                $table->foreignId('purchase_supplier_specialty_id')->constrained()
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
        Schema::dropIfExists('purchase_specialty_supplier');
    }
}
