<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountingEntryCategory;

/**
 * @class AccountingSeatCategoriesTableSeeder
 * @brief Información por defecto de las categorias de origen de asientos contables
 *
 * Gestiona la información por defecto a registrar inicialmente de las categorias de origen de asientos contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingEntryCategoriesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de categorías de asientos contables
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $categories = [
            [
                'name' => 'Solicitud de pago',
                'acronym' => 'SOP',
            ],
            [
                'name' => 'Emisión de cheques',
                'acronym' => 'CHQ',
            ],
            [
                'name' => 'Movimientos bancarios',
                'acronym' => 'DEP',
            ],
            [
                'name' => 'Emisiones de Pago',
                'acronym' => 'PAG',
            ],
            [
                'name' => 'Estado de resultado',
                'acronym' => 'EDR',
            ],
            [
                'name' => 'Ajustes de resultados acumulados',
                'acronym' => 'ARA',
            ]
        ];

        DB::transaction(function () use ($categories) {
            foreach ($categories as $category) {
                AccountingEntryCategory::updateOrCreate(
                    [
                        "name" => $category["name"],
                        "acronym" => $category["acronym"],
                    ],
                    []
                );
            }
        });
    }
}
