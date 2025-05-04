<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDocumentsIdToPurchaseTypesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDocumentsIdToPurchaseTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('purchase_types')) {
            Schema::table('purchase_types', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_types', 'documents_id')) {
                    $table->text('documents_id')->nullable()->comment('Lista de ids. de los documentos requeridos para modalidades de compras');
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
        if (Schema::hasTable('purchase_types')) {
            Schema::table('purchase_types', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_types', 'documents_id')) {
                    $table->dropColumn('documents_id');
                }
            });
        }
    }
}
