<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToAssetTable
 * @brief Actualiza los campos de la tabla assets
 *
 * Actualiza los campos de la tabla assets
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('assets', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('assets', 'inventory_serial')) {
                $table->dropColumn('inventory_serial');
            }
            if (Schema::hasColumn('assets', 'serial')) {
                $table->dropColumn('serial');
            }
            if (Schema::hasColumn('assets', 'marca')) {
                $table->dropColumn('marca');
            }
            if (Schema::hasColumn('assets', 'model')) {
                $table->dropColumn('model');
            }
            if (Schema::hasColumn('assets', 'parish_id')) {
                $table->dropForeign(['parish_id']);
                $table->dropColumn('parish_id');
            }

            if (!Schema::hasColumn('assets', 'asset_details')) {
                $table->longText('asset_details')->nullable();
            }
            if (Schema::hasColumn('assets', 'value')) {
                $table->renameColumn('value', 'acquisition_value');
            }
            if (Schema::hasColumn('assets', 'specifications')) {
                $table->renameColumn('specifications', 'description');
            }
            if (!Schema::hasColumn('assets', 'headquarter_id')) {
                $table->foreignId('headquarter_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('assets', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('assets', 'code_sigecof')) {
                $table->string('code_sigecof')->nullable();
            }
            if (!Schema::hasColumn('assets', 'document_num')) {
                $table->string('document_num')->nullable();
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
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'color')) {
                $table->text('color')->nullable()->comment('Color del bien institucional');
            }
            if (!Schema::hasColumn('assets', 'address')) {
                $table->text('address')->nullable()->comment('Dirección fisíca de bien');
            }
            if (!Schema::hasColumn('assets', 'inventory_serial')) {
                $table->string('inventory_serial', 50)->nullable()
                      ->comment('Código que coloca el organismo en el bien para identificarlo');
            }
            if (!Schema::hasColumn('assets', 'serial')) {
                $table->string('serial', 50)->nullable()->comment('Serial del fabricante');
            }
            if (!Schema::hasColumn('assets', 'marca')) {
                $table->string('marca', 50)->nullable()->comment('Marca del bien');
            }
            if (!Schema::hasColumn('assets', 'model')) {
                $table->string('model', 50)->nullable()->comment('Modelo del bien');
            }
            if (!Schema::hasColumn('assets', 'parish_id')) {
                $table->foreignId('parish_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
