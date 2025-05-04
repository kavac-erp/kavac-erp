<?php

namespace Modules\Sale\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class SaleDatabaseSeeder
 * @brief Gestiona la carga inicial de datos del módulo de comercialización
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        /** Seeder para roles y permisos disponibles en el módulo */
        $this->call(SaleRoleAndPermissionsTableSeeder::class);

        $this->call(SaleSettingProductTypeTableSeeder::class);
    }
}
