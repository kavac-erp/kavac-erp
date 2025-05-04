<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollPosition;

/**
 * @class PayrollUpdateNumberPositionsAssignedTableSeeder
 * @brief Actualiza datos del modelo PayrollPosition.
 *
 * Clase que actualiza datos del modelo PayrollPosition según conteo de tabla
 * intermedia.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollUpdateNumberPositionsAssignedTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        // Obtener todos los registros de la tabla payroll_employments.
        $employments = DB::table('payroll_employments')->get();

        /*
         | Se realiza consulta a la tabla intermedia para contar cuántos
         | registros están asociados a cada payroll_position_id. Luego, se agrupan
         | los resultados por cada payroll_position_id.
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
            PayrollPosition::where(
                'id',
                $employmentPosition->payroll_position_id
            )->update([
                'number_positions_assigned'
                    => $employmentPosition->employment_count
            ]);
        }
    }
}
