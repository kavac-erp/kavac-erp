<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DocumentStatus;

class AddFieldDocumentStatusIdToAssetDisincorporationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('asset_disincorporations')) {
            Schema::table('asset_disincorporations', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_disincorporations', 'document_status_id')) {
                    $table->foreignId('document_status_id')->default(default_document_status()->id)->nullable()
                        ->comment('Identificador único asociado al estatus del documento')
                        ->constrained('document_status')
                        ->onDelete('restrict')->onUpdate('cascade');
                };
            });
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('asset_disincorporations')) {
            Schema::table('asset_disincorporations', function (Blueprint $table) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
            });
        }
    }
}
