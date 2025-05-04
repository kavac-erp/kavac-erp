<?php

namespace Modules\Asset\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @class AssetDatabaseSeeder
 * @brief Inicializa el módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDatabaseSeeder extends Seeder
{
    /**
     * Método que realiza el llamado a los seeders del modulo de bienes
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        /* Roles disponibles para el acceso al módulo de bienes */
        $this->call(AssetRoleAndPermissionsTableSeeder::class);

        /* Registros de la clasificacíón de bienes según SUDEBIP */
        $this->call(AssetClasificationTableSeeder::class);

        /* Registros de tipos de adquisición de bienes */
        $this->call(AssetAcquisitionTypesTableSeeder::class);

        /* Registros de condiciones físicas de bienes */
        $this->call(AssetConditionsTableSeeder::class);

        /* Registros de estatus de uso de bienes */
        $this->call(AssetStatusTableSeeder::class);

        /* Registros de funciones de uso de bienes */
        $this->call(AssetUseFunctionsTableSeeder::class);

        /* Registros de motivos de desincorporación de bienes */
        $this->call(AssetDisincorporationMotivesTableSeeder::class);
    }
}
