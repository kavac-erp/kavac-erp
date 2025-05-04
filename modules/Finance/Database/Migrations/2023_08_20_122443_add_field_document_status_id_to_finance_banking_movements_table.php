<?php

use App\Models\DocumentStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Symfony\Component\Console\Output\ConsoleOutput;

class AddFieldDocumentStatusIdToFinanceBankingMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_banking_movements', 'document_status_id')) {
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
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'document_status_id')) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
            }
        });
    }
}
