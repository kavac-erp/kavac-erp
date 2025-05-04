<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldRequirementTypeToPurchaseRequirementsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldRequirementTypeToPurchaseRequirementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requirements', 'requirement_type')) {
                $table->string('requirement_type', 100)->nullable()->comment('Tipo de requerimiento');
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
        Schema::table('purchase_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requirements', 'requirement_type')) {
                $table->dropColumn('requirement_type');
            }
        });
    }
}
