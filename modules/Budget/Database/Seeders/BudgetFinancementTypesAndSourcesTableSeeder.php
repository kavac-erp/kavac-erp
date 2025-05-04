<?php

namespace Modules\Budget\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\BudgetFinancementTypes;
use Modules\Budget\Models\BudgetFinancementSources;

/**
 * @class BudgetFinancementTypesAndSourcesTableSeeder
 * @brief Información por defecto para tipos de financiamiento y fuentes de financiamiento
 *
 * Gestiona la información por defecto a registrar inicialmente para los tipos de financiamiento y fuentes de financiamiento
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetFinancementTypesAndSourcesTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $finance_types = [
            [
                'name' => 'Ingresos de la República',
                'BudgetFinancementSources' => [
                    'Recursos Ordinarios',
                    'Servicio de la deuda Pública',
                    'Gestión Fiscal',
                    'Proyectos por endeudamiento',
                    'Banca Comercial',
                    'Bilateral',
                    'Otros ingresos extraordinarios',
                ],
            ],
            [
                'name' => 'Ingresos Propios',
                'BudgetFinancementSources' => [
                    'Venta de Bienes y/o Servicios',
                    'Disminución de Saldo en Caja y Banco',
                    'Cotizaciones de Afiliados',
                    'Fuentes Financieras',
                    'Otras fuentes por Ingresos Propios',
                ],
            ],
            [
                'name' => 'Ingresos Especiales',
                'BudgetFinancementSources' => [
                    'Banco de Desarrollo Económico y Social de Venezuela',
                    'Convenio Venezuela Argentin',
                    'Convenio Venezuela Belarús',
                    'Convenio Venezuela Iran',
                    'Convenio Venezuela Uruguay',
                    'Excedentes de la Oficina Nacional del Tesoro',
                    'Fondo Bicentenario',
                    'Fondo de Ahorro Obligatorio para la Vivienda (FAOV)',
                    'Fondo Conjunto Chino Venezolano (FCCV)',
                    'Fondo de Aportaciones para la Seguridad Pública (FASP)',
                    'Fondo de Compensación Interterritorial (FCI)',
                    'Fondo para el Desarrollo Nacional (FONDEN)',
                    'Fondo de Eficiencia Socialista',
                    'Fondo Eléctrico Nacional (FEN)',
                    'Fondo Especial Ezequiel Zamora',
                    'Fondo Gran Volumen Largo Plazo',
                    'Fondo Independencia',
                    'Fondo Miranda',
                    'Fondo para el Desarrollo Económico y Social del País (Fondespa)',
                    'Fondo Siembra Petrolera',
                    'Fondo Simón Bolívar para la Reconstrucción',
                    'Transferencias de otros entes descentralizados',
                    'Otras Fuentes',
                ],
            ],
        ];

        DB::transaction(function () use ($finance_types) {
            foreach ($finance_types as $type) {
                $a = BudgetFinancementTypes::updateOrCreate(
                    [
                        'name' => $type['name']
                    ],
                    [
                        'name' => $type['name'],
                    ]
                );
                foreach ($type['BudgetFinancementSources'] as $source) {
                    BudgetFinancementSources::updateOrCreate(
                        [
                            'name' => $source,
                            'budget_financement_type_id' => $a->id
                        ],
                    );
                }
            }
        });
    }
}
