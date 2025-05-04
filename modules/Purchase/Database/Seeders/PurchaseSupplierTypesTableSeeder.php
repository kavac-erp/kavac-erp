<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\PurchaseSupplierType;

/**
 * @class PurchaseSupplierTypesTableSeeder
 * @brief Información por defecto para datos iniciales de tipos de proveedores del módulo de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierTypesTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::transaction(function () {
            $types = [
                ['name' => 'Firma Personal'],
                ['name' => 'Compañía Anónima (C.A.)'],
                ['name' => 'Sociedad Anónima (S.A.)'],
                ['name' => 'Cooperativa'],
                ['name' => 'Comanditas'],
                ['name' => 'Sociedad de Responsabilidad Limitada (S.R.L.)'],
                ['name' => 'Asociaciones Civiles'],
                ['name' => 'Fundaciones'],
                ['name' => 'Organizaciones Socio-Productivas'],
                ['name' => 'Otra forma asociativa']
            ];

            foreach ($types as $type) {
                PurchaseSupplierType::updateOrCreate($type, $type);
            }
        });
    }
}
