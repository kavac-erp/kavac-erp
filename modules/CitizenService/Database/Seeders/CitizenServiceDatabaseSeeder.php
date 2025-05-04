<?php

namespace Modules\CitizenService\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class CitizenServiceDatabaseSeeder
 * @brief Ejecuta las migraciones del módulo de la oficina de atención al ciudadano
 *
 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(CitizenServiceRequestTypesTableSeeder::class);
        $this->call(CitizenServiceRoleAndPermissionsTableSeeder::class);
    }
}
