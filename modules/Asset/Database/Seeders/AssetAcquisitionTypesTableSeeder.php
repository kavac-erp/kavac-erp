<?php

namespace Modules\Asset\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Asset\Models\AssetAcquisitionType;

/**
 * @class AssetAcquisitionTypesTableSeeder
 * @brief Inicializar los tipos de adquisición de un bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAcquisitionTypesTableSeeder extends Seeder
{
    /**
     * Método que registra los valores iniciales de las formas de adquisición de un bien
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $assetAcquisitionTypes = [
            ['name' => 'Compra Directa (Consulta de Precio)'],
            ['name' => 'Permuta'],
            ['name' => 'Dación en Pago'],
            ['name' => 'Donación'],
            ['name' => 'Transferencia'],
            ['name' => 'Expropiación'],
            ['name' => 'Confiscación'],
            ['name' => 'Compra por Concurso Abierto'],
            ['name' => 'Compra por Concurso Cerrado'],
            ['name' => 'Adjudicación']

        ];

        $types = AssetAcquisitionType::select('name')->withTrashed()->get()->toArray();
        foreach ($assetAcquisitionTypes as $assetAcquisitionType) {
            if (array_search($assetAcquisitionType['name'], array_column($types, 'name')) !== false) {
                continue;
            }
            AssetAcquisitionType::create([
                'name' => $assetAcquisitionType['name']
            ]);
        }
    }
}
