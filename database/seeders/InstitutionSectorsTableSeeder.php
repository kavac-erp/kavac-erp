<?php

namespace Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use App\Models\InstitutionSector;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class InstitutionSectorsTableSeeder
 * @brief Información por defecto para sectores de las Organizaciones
 *
 * Gestiona la información por defecto a registrar inicialmente para los sectores de las Organizaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InstitutionSectorsTableSeeder extends Seeder
{
    /**
     * Contador de sectores de instituciones cargados
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
     * Crea una nueva instancia de la clase
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
         * Permisos disponibles para la gestión de sectores de organizaciones
         */

        $permissions = [
            [
                'name' => 'Crear Sector de Organización', 'slug' => 'institution.sector.create',
                'description' => 'Acceso al registro de sectores de organizaciones',
                'model' => InstitutionSector::class, 'model_prefix' => '0general',
                'slug_alt' => 'sector.institucion.crear', 'short_description' => 'agregar sector de Organización'
            ],
            [
                'name' => 'Editar Sector de Organización', 'slug' => 'institution.sector.edit',
                'description' => 'Acceso para editar sectores de organizaciones',
                'model' => InstitutionSector::class, 'model_prefix' => '0general',
                'slug_alt' => 'sector.institucion.editar', 'short_description' => 'editar sector de Organización'
            ],
            [
                'name' => 'Eliminar Sector de Organización', 'slug' => 'institution.sector.delete',
                'description' => 'Acceso para eliminar sectores de organizaciones',
                'model' => InstitutionSector::class, 'model_prefix' => '0general',
                'slug_alt' => 'sector.institucion.eliminar', 'short_description' => 'eliminar sector de Organización'
            ],
            [
                'name' => 'Ver Sector de Organización', 'slug' => 'institution.sector.list',
                'description' => 'Acceso para ver sectores de organizaciones',
                'model' => InstitutionSector::class, 'model_prefix' => '0general',
                'slug_alt' => 'sector.institucion.ver', 'short_description' => 'ver sectores de organizaciones'
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando los Sectores de Organismos</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions) {
            InstitutionSector::withTrashed()->updateOrCreate(
                ['name' => 'Desarrollo'],
                ['deleted_at' => null]
            );
            InstitutionSector::withTrashed()->updateOrCreate(
                ['name' => 'Ciencia y Tecnología'],
                ['deleted_at' => null]
            );
            InstitutionSector::withTrashed()->updateOrCreate(
                ['name' => 'Gobierno'],
                ['deleted_at' => null]
            );

            $this->count = 3;

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
            "<fg=green>Se cargó y/o actualizó un total de</>" .
            "<fg=yellow> $this->count </>" .
            "<fg=green>Sectores de Organismos</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</>" .
            "<fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Sectores de Organismos</>"
        );
        $this->command->line("");
    }
}
