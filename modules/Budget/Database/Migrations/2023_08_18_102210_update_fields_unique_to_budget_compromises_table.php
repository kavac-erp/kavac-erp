<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsUniqueToBudgetCompromisesTable
 * @brief [descripción detallada]
 *
 * Quita la uniquidad de campos 'document_number' y estables la uniquidad en conjunto
 * de los campos 'created_at', 'document_number' y 'document_status_id'
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsUniqueToBudgetCompromisesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('budget_compromises')) {
            Schema::table('budget_compromises', function (Blueprint $table) {
                if (Schema::hasColumn('budget_compromises', 'document_number')){
                     $table->dropUnique('budget_compromises_document_number_unique');
                    // $table->dropUnique(['document_number']);
                }
                if (Schema::hasColumns('budget_compromises',
                    ['created_at', 'document_number', 'document_status_id'])
                ) {
                    $table->unique(['created_at', 'document_number', 'document_status_id']);
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
        Schema::table('budget_compromises', function (Blueprint $table) {
            if (Schema::hasColumns('budget_compromises',
                ['created_at','document_number', 'document_status_id'])
            ) {
                $table->dropUnique(['created_at', 'document_number', 'document_status_id']);
            }
            if (Schema::hasColumn('budget_compromises', 'document_number')) {
                $table->unique('document_number');
            }
        });
    }
}
