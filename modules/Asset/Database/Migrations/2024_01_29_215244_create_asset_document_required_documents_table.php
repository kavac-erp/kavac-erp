<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetDocumentRequiredDocumentsTable
 * @brief Crea la tabla de asset_document_required_documents
 *
 * Crea la tabla de asset_document_required_documents
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetDocumentRequiredDocumentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_document_required_documents')) {
            Schema::create('purchase_document_required_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('documents')->onDelete('cascade')->onUpdate('cascade');
                $table->foreignId('required_document_id')->constrained('required_documents')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('purchase_document_required_documents');
    }
}
