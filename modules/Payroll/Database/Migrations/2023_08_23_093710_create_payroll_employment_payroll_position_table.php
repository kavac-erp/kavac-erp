<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollPosition;

/**
 * @class CreatePayrollEmploymentPayrollPositionTable
 *
 * @brief Gestión de campos de la tabla intermedia payroll_employment_payroll_position.
 *
 * Clase que gestiona los métodos para la gestión de los datos de la tabla
 * payroll_employment_payroll_position.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollEmploymentPayrollPositionTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_employment_payroll_position')) {
            Schema::create('payroll_employment_payroll_position', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_employment_id')
                    ->constrained('payroll_employments')
                    ->onUpdate('cascade')
                    ->onDelete('restrict')
                    ->nullable();
                $table->foreignId('payroll_position_id')
                    ->constrained('payroll_positions')
                    ->onUpdate('cascade')
                    ->onDelete('restrict')
                    ->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }

        // Obtener todos los registros de la tabla payroll_employments.
        $employments = DB::table('payroll_employments')->get();

        // Recorrer cada registro de la tabla payroll_employments.
        foreach ($employments as $employment) {
            // Obtener el ID del trabajador, el ID del cargo y el valor de "active".
            $employeeId = $employment->id;
            $positionId = $employment->payroll_position_id;
            $isActive = $employment->active; // Obtener el valor de "active"

            // Verificar si el registro está eliminado (tiene deleted_at no nulo).
            if ($employment->deleted_at !== null) {
                // Si está eliminado, establecer $isActive en false.
                $isActive = false;
            }

            /* Insertar un nuevo registro en la tabla intermedia
            payroll_employment_payroll_position. */
            DB::table('payroll_employment_payroll_position')->insert([
                'payroll_employment_id' => $employeeId,
                'payroll_position_id' => $positionId,
                'active' => $isActive, // Almacenar el valor de "active"
                'created_at' => now(),
            ]);
        }

        // Eliminar el campo payroll_position_id de la tabla payroll_employments.
        if (Schema::hasTable('payroll_employments')) {
            Schema::table('payroll_employments', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_employments', 'payroll_position_id')) {
                    $table->dropColumn('payroll_position_id');
                }
            });
        }

        /* Se realiza consulta a la tabla intermedia para contar cuántos
        * registros están asociados a cada payroll_position_id. Luego, se agrupan
        * los resultados por cada payroll_position_id.
        */
        $employmentPositions = DB::table('payroll_employment_payroll_position')
            ->select('payroll_position_id', DB::raw('count(*) as employment_count'))
            ->groupBy('payroll_position_id')
            ->where('active', true)
            ->get();

        /* Actualizar el campo number_positions_assigned en la tabla
        payroll_positions en función de la consulta almacenada en la variable
        $employmentPositions */
        foreach ($employmentPositions as $employmentPosition) {
            PayrollPosition::withTrashed()  // Considera registros eliminados
                ->where('id', $employmentPosition->payroll_position_id)
                ->update(['number_positions_assigned' => $employmentPosition->employment_count]);
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            $table->foreignId('payroll_position_id')
                ->nullable()
                ->constrained('payroll_positions')
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->comment('Identificador del cargo');
        });

        // Obtener todos los registros de la tabla intermedia.
        $intermediateRecords = DB::table('payroll_employment_payroll_position')->get();

        foreach ($intermediateRecords as $record) {
            /* Actualizar el registro correspondiente en la tabla
            payroll_employments solo si payroll_position_id no es NULL. */
            if (!is_null($record->payroll_position_id)) {
                DB::table('payroll_employments')
                    ->where('id', $record->payroll_employment_id)
                    ->update(['payroll_position_id' => $record->payroll_position_id]);
            }
        }

        // Eliminar la tabla intermedia payroll_employment_payroll_position.
        Schema::dropIfExists('payroll_employment_payroll_position');
    }
}
