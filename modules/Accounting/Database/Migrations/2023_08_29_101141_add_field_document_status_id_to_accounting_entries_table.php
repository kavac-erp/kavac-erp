<?php

use App\Models\DocumentStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDocumentStatusIdToAccountingEntriesTable
 * @brief Ejecuta la migración para agregar el campo document_status_id a la tabla accounting_entries
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDocumentStatusIdToAccountingEntriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_entries', 'document_status_id')) {
                $table->foreignId('document_status_id')->nullable()
                    ->default(default_document_status()->id)
                    ->comment('Identificador único asociado al estatus del documento')->constrained('document_status')
                    ->onDelete('restrict')->onUpdate('cascade');
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
        Schema::table('accounting_entries', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_entries', 'document_status_id')) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
            }
        });
    }
}
