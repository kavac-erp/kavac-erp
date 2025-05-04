<?php

namespace Database\Seeders;

use App\Roles\Models\Role;
use App\Models\MeasurementUnit;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class MeasurementUnitsTableSeeder
 * @brief Información por defecto para Unidades de medida
 *
 * Gestiona la información por defecto a registrar inicialmente para las unidades de medida
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class MeasurementUnitsTableSeeder extends Seeder
{
    /**
     * Contador de Unidades de Medidas cargadas
     *
     * @var int $count
     */
    protected $count;

    /**
     * Contador de permisos cargados
     *
     * @var int $countP
     */
    protected $countP;

    /**
     * Método constructor de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        $this->count = 0;
        $this->countP = 0;
    }

    /**
     * Ejecuta los seeers de base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        /*
         * Permisos disponibles para la gestión de Unidades de Medidas
         */

        $permissions = [
            [
                'name' => 'Crear Unidades de Medida', 'slug' => 'measurement.units.create',
                'description' => 'Acceso al registro de Unidades de Medidas',
                'model' => MeasurementUnit::class, 'model_prefix' => '0general',
                'slug_alt' => 'unidades.medida.crear', 'short_description' => 'agregar Unidades de Medidas'
            ],
            [
                'name' => 'Editar Unidades de Medida', 'slug' => 'measurement.units.edit',
                'description' => 'Acceso para editar Unidades de Medidas',
                'model' => MeasurementUnit::class, 'model_prefix' => '0general',
                'slug_alt' => 'unidades.medida.editar', 'short_description' => 'editar Unidades de Medidas'
            ],
            [
                'name' => 'Eliminar Unidades de Medida', 'slug' => 'measurement.units.delete',
                'description' => 'Acceso para eliminar Unidades de Medidas',
                'model' => MeasurementUnit::class, 'model_prefix' => '0general',
                'slug_alt' => 'unidades.medida.eliminar', 'short_description' => 'eliminar Unidades de Medidas'
            ],
            [
                'name' => 'Ver Unidades de Medida', 'slug' => 'measurement.units.list',
                'description' => 'Acceso para ver Unidades de Medidas',
                'model' => MeasurementUnit::class, 'model_prefix' => '0general',
                'slug_alt' => 'unidades.medida.ver', 'short_description' => 'ver Unidades de Medidas'
            ],
            [
                'name' => 'Notificar gestión de unidades de medida',
                'slug' => 'measurement.unit.notify',
                'description' => 'Notificar sobre gestión de datos de unidades de medida',
                'model' => MeasurementUnit::class, 'model_prefix' => '0general',
                'slug_alt' => 'unidad.medida.notificar',
                'short_description' => 'notificar la gestión de unidades de medida'
            ],
        ];

        $measurement_units = [
            [
                'name' => 'Bulto',
                'description' => 'Bultos',
                'acronym' => 'bulto',
            ],
            [
                'name' => 'Caja',
                'description' => 'Recipiente de varias formas y tamaños que sirve para albergar objetos',
                'acronym' => 'caja',
            ],
            [
                'name' => 'Galón',
                'description' => 'Galones',
                'acronym' => 'gal',
            ],
            [
                'name' => 'Litro',
                'description' => 'Producto por litros',
                'acronym' => 'lts',
            ],
            [
                'name' => 'Metros cuadrados',
                'description' => 'Metros cuadrados',
                'acronym' => 'mt2',
            ],
            [
                'name' => 'Metros lineales',
                'description' => 'Metros lineales',
                'acronym' => 'm',
            ],
            [
                'name' => 'Paquete',
                'description' => 'Embalaje comercial o envase de un producto',
                'acronym' => 'pkg',
            ],
            [
                'name' => 'Quintal',
                'description' => 'Quintal como medida de peso',
                'acronym' => 'qq',
            ],
            [
                'name' => 'Resma',
                'description' => 'Conjunto de 500 pliegos de papel',
                'acronym' => 'res',
            ],
            [
                'name' => 'Servicio',
                'description' => 'Servicio',
                'acronym' => 'srv',
            ],
            [
                'name' => 'Unidad',
                'description' => 'Unidad de un artículo',
                'acronym' => 'und',
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando las Unidades de Medida</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions, $measurement_units) {
            foreach ($measurement_units as $unit) {
                MeasurementUnit::withTrashed()->updateOrCreate(
                    ['acronym' => $unit['acronym']],
                    [
                        'name' => $unit['name'],
                        'description' => $unit['description'],
                        'deleted_at' => null
                    ]
                );
                $this->count++;
            }

            foreach ($permissions as $permission) {
                $per = Permission::updateOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $permission['name'], 'description' => $permission['description'],
                        'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                        'slug_alt' => $permission['slug_alt'], 'short_description' => $permission['short_description']
                    ]
                );
                if ($adminRole) {
                    $adminRole->attachPermission($per);
                }
                $this->countP++;
            }
        });

        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->count </>" .
            "<fg=green>Unidades de Medida</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Unidades de Medida</>"
        );
        $this->command->line("");
    }
}
