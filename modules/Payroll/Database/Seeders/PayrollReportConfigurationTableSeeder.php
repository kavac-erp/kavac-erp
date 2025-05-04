<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\Parameter;

/**
 * @class $CLASS$
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollReportConfigurationTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @method run
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        $PayrollReportConfigurations = [
            [
                'p_key' => 'number_decimals',
                'p_value' => 2
            ],
            [
                'p_key' => 'round',
                'p_value' => 'true'
            ],
            [
                'p_key' => 'zero_concept',
                'p_value' => 'true'
            ],
        ];

        DB::transaction(function () use ($PayrollReportConfigurations) {
            foreach ($PayrollReportConfigurations as $PayrollReportConfiguration) {
                Parameter::updateOrCreate(
                    [
                        'p_key' => $PayrollReportConfiguration['p_key'],
                        'required_by' => 'payroll',
                        'active' => 'true'
                    ],
                    [
                        'p_value' => $PayrollReportConfiguration['p_value'],
                    ]
                );
            }
        });
    }
}
