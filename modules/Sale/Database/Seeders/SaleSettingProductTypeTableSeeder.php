<?php

namespace Modules\Sale\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use Modules\Sale\Models\SaleSettingProductType;

/**
 * @class SaleSettingProductTypeTableSeeder
 * @brief Inicializar los tipos de producto
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class SaleSettingProductTypeTableSeeder extends Seeder
{
     /**
     * Método que registra los valores de los tipos de producto
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return boolean|void
     */
    public function run()
    {
        if (!Module::isEnabled('Sale')) {
            return true;
        }
        Model::unguard();

        $saleSettingProductTypes = [
            [
                'name' => 'Producto'
            ],
            [
                'name' => 'Servicio'
            ],
        ];

        DB::transaction(function () use ($saleSettingProductTypes) {
            foreach ($saleSettingProductTypes as $saleSettingProductType) {
                saleSettingProductType::updateOrCreate(
                    ['name' => $saleSettingProductType['name']],
                    ['name' => $saleSettingProductType['name']],
                );
            }
        });
    }
}
