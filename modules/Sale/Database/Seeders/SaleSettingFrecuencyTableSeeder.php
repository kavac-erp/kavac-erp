<?php

namespace Modules\Sale\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use Modules\Sale\Models\SaleSettingFrecuency;

/**
 * @class SaleSettingFrecuencyTableSeeder
 * @brief Agrega los valores iniciales de los periodos de tiempo
 *
 * Agrega los valores iniciales de los periodos de tiempo
 *
 * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleSettingFrecuencyTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return boolean|void
     */
    public function run()
    {
        if (!Module::isEnabled('Sale')) {
            return true;
        }
        Model::unguard();

        $Frecuencies = [
            [
                'name' => 'Semanal'
            ],
            [
                'name' => 'Quincenal'
            ],
            [
                'name' => 'Mensual'
            ],
            [
                'name' => 'Bimensual'
            ],
            [
                'name' => 'Trimestral'
            ],
            [
                'name' => 'Semestral'
            ],
            [
                'name' => 'Anual'
            ],
        ];

        DB::transaction(function () use ($Frecuencies) {
            foreach ($Frecuencies as $frecuency) {
                SaleSettingFrecuency::Create(
                    ['name' => $frecuency['name']],
                );
            }
        });
    }
}
