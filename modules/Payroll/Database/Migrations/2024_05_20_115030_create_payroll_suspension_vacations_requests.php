<?php

use App\Models\Document;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Payroll\Models\PayrollVacationRequest;

/**
 * @class CreatePayrollSuspensionVacationsRequests
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSuspensionVacationsRequests extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_suspension_vacation_requests')) {
            Schema::create('payroll_suspension_vacation_requests', function (Blueprint $table) {
                $table->id()->comment('Identificador único del registro');
                $table->string('status')
                    ->comment('Estatus de la solicitud de suspension de vacaciones');
                $table->longText('suspended_years')
                    ->comment('Años suspendidos de la solicitud de vacaciones');
                $table->unsignedInteger('enjoyed_days')
                    ->comment('Días efectivamente disfrutados');
                $table->unsignedInteger('pending_days')
                    ->comment('Días pendientes por disfrutar');
                $table->text('suspension_reason')
                    ->comment('Motivo de la suspensión de vacaciones');
                $table->foreignIdFor(PayrollVacationRequest::class)
                    ->constrained()->onUpdate('cascade')->onDelete('restrict')
                    ->comment('Identificador único asociado a una solicitud de vacaciones');
                $table->date('date_request')->comment('Fecha de la solicitud de suspension de vacaciones');
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
        Schema::dropIfExists('payroll_suspension_vacation_requests');
    }
}
