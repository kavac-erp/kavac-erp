<?php

namespace Modules\CitizenService\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\CitizenService\Models\CitizenServiceRequestType;

/**
 * @class CitizenServiceRequestTypesTableSeeder
 * @brief Ejecuta las migraciones de los tipos de solicitudes
 *
 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestTypesTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $citizenServiceRequestTypes = [
            ['name' => 'Soporte técnico'],
            ['name' => 'Migración a software libre'],
            ['name' => 'Talleres de formación - asesorias'],
            ['name' => 'Desarrollo de software libre']

        ];



        foreach ($citizenServiceRequestTypes as $citizenServiceRequestType) {
            CitizenServiceRequestType::updateOrCreate(
                ['name' => $citizenServiceRequestType['name']]
            );
        }
    }
}
