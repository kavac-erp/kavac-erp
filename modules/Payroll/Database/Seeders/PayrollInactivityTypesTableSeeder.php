<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollInactivityType;

/**
 * @class PayrollInactivityTipesTableSeeder
 * @brief Inicializar los tipos de inactividad
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollInactivityTypesTableSeeder extends Seeder
{
    /**
     * Método que registra los valores de los tipos de inactividad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $payrollInactivityTypes = [
            [
                'name' => 'Permiso no remunerado'
            ],
            [
                'name' => 'Comisión de servicio'
            ],
            [
                'name' => 'Año sabático'
            ],
            [
                'name' => 'Renuncia'
            ],
            [
                'name' => 'Jubilado'
            ]
        ];

        DB::transaction(function () use ($payrollInactivityTypes) {
            foreach ($payrollInactivityTypes as $payrollInactivityType) {
                PayrollInactivityType::updateOrCreate(
                    ['name' => $payrollInactivityType['name']]
                );
            }
        });
    }
}
