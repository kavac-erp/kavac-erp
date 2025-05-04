<?php

namespace Modules\DigitalSignature\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class   DigitalSignatureDatabaseSeeder
 * @brief   Gestiona la inserción de datos por defecto del módulo de firma electrónica
 *
 * Clase que gestiona la inserción de datos por defecto del módulo de firma electrónica.
 *
 * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DigitalSignatureDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(DigitalSignatureRoleAndPermissionsTableSeeder::class);
    }
}
