<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceRequestTypesTable
 * @brief Modifica los tipos de dato de los campos de la tabla de solicitudes
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsToCitizenServiceRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('citizen_service_requests', 'information')) {
                $table->dropColumn(['information']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'payroll_sector_type_id')) {
                $table->dropForeign(['payroll_sector_type_id']);
                $table->dropColumn(['payroll_sector_type_id']);
            }
            if (!Schema::hasColumn('citizen_service_requests', 'rif')) {
                $table->string('rif', 12)->unique()->nullable()->comment('Rif de la institución');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'address')) {
                $table->string('address', 100)->nullable()->comment('Dirección');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'motive_request')) {
                $table->string('motive_request', 200)->nullable()->comment('Motivo de la solicitud');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'state')) {
                $table->string('state', 200)->nullable()->comment('Estado de la solicitud');
            }
            if (Schema::hasColumn('citizen_service_requests', 'institution_name')) {
                $table->string('institution_name', 200)->nullable()->comment('Nombre de la institución')->change();
            }
            if (Schema::hasColumn('citizen_service_requests', 'institution_address')) {
                $table->string('institution_address', 200)->nullable()->comment('Dirección de la institución')
                ->change();
            }
            if (Schema::hasColumn('citizen_service_requests', 'web')) {
                $table->string('web', 200)->nullable()->comment('Dirección Web')->change();
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
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('citizen_service_requests', 'information')) {
                $table->string('information', 200)->nullable()->comment('Información Adicional');
            }
            if (!Schema::hasColumn('citizen_service_requests', 'payroll_sector_type_id')) {
                $table->foreignId('payroll_sector_type_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }
            if (Schema::hasColumn('citizen_service_requests', 'rif')) {
                $table->dropUnique(['rif']);
                $table->dropColumn(['rif']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'address')) {
                $table->dropColumn(['address']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'motive_request')) {
                $table->dropColumn(['motive_request']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'state')) {
                $table->dropColumn(['state']);
            }
            if (Schema::hasColumn('citizen_service_requests', 'institution_name')) {
                $table->string('institution_name', 200)->comment('Nombre de la institución')->change();
            }
            if (Schema::hasColumn('citizen_service_requests', 'institution_address')) {
                $table->string('institution_address', 200)->comment('Dirección de la institución')->change();
            }
            if (Schema::hasColumn('citizen_service_requests', 'web')) {
                $table->string('web', 200)->comment('Dirección Web')->change();
            }
        });
    }
}
