<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetSupplierSpecialtiesTable
 * @brief Crea la tabla de especialidades de proveedores de bienes
 *
 * Crea la tabla de especialidades de proveedores de bienes
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetSupplierSpecialtiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_supplier_specialties')) {
            Schema::create('purchase_supplier_specialties', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->unique()->comment('Nombre de la especialidad de proveedores');
                $table->text('description')->nullable()->comment('Descripción de la especialidad de proveedores');
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
        Schema::dropIfExists('purchase_supplier_specialties');
    }
}
