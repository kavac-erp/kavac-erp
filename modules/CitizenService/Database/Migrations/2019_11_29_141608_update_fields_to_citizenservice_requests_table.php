<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToCitizenserviceRequestsTable
 * @brief Actualizar campos de la tabla de solicitudes
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToCitizenserviceRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_requests', 'id_number')) {
                $table->dropUnique(['id_number']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'email')) {
                $table->dropUnique(['email']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'document_id')) {
                $table->bigInteger('document_id')->unsigned()->nullable()->comment(
                    'Identificador unico del archivo adjuntar'
                )->change();
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
        Schema::disableForeignKeyConstraints();
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_requests', 'id_number')) {
                $table->string('id_number', 12)->unique()->comment('Cédula de identidad del Solicitante')->change();
            }
            if (Schema::hasColumn('citizen_service_requests', 'email')) {
                $table->string('email')->unique()->nullable()->comment('Correo electrónico del Solicitante')->change();
            }
            if (Schema::hasColumn('citizen_service_requests', 'document_id')) {
                $table->dropForeign(['document_id']);
                $table->unsignedBigInteger('document_id')->comment(
                    'Identificador unico del archivo adjuntar'
                )->change();
                $table->foreign('document_id')->references('id')->on('documents')
                      ->onDelete('restrict')->onUpdate('cascade');
            }
        });
        Schema::enableForeignKeyConstraints();
    }
}
