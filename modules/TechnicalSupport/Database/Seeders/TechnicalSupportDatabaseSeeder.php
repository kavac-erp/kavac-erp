<?php

namespace Modules\TechnicalSupport\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TechnicalSupportDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        /** Roles y permisos disponibles para el acceso al módulo de soporte técnico */
        $this->call(TechnicalSupportRoleAndPermissionsTableSeeder::class);
    }
}
