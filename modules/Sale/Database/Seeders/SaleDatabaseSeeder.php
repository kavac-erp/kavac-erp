<?php

namespace Modules\Sale\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SaleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
