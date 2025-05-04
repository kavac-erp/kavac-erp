<?php

namespace Database\Seeders;

use App\Roles\Models\Role;
use App\Models\InstitutionType;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class InstitutionTypesTableSeeder
 * @brief Información por defecto para tipos de Organizaciones
 *
 * Gestiona la información por defecto a registrar inicialmente para los tipos de Organizaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InstitutionTypesTableSeeder extends Seeder
{
    /**
     * Contador de tipos de instituciones cargados
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
         * Permisos disponibles para la gestión de tipos de organizaciones
         */

        $permissions = [
            [
                'name' => 'Crear Tipo de Organización', 'slug' => 'institution.type.create',
                'description' => 'Acceso al registro de tipos de organizaciones',
                'model' => InstitutionType::class, 'model_prefix' => '0general',
                'slug_alt' => 'tipo.institucion.crear', 'short_description' => 'agregar tipo de Organización'
            ],
            [
                'name' => 'Editar Tipo de Organización', 'slug' => 'institution.type.edit',
                'description' => 'Acceso para editar tipos de organizaciones',
                'model' => InstitutionType::class, 'model_prefix' => '0general',
                'slug_alt' => 'tipo.institucion.editar', 'short_description' => 'editar tipo de Organización'
            ],
            [
                'name' => 'Eliminar Tipo de Organización', 'slug' => 'institution.type.delete',
                'description' => 'Acceso para eliminar tipos de organizaciones',
                'model' => InstitutionType::class, 'model_prefix' => '0general',
                'slug_alt' => 'tipo.institucion.eliminar', 'short_description' => 'eliminar tipo de Organización'
            ],
            [
                'name' => 'Ver Tipo de Organización', 'slug' => 'institution.type.list',
                'description' => 'Acceso para ver tipos de organizaciones',
                'model' => InstitutionType::class, 'model_prefix' => '0general',
                'slug_alt' => 'tipo.institucion.ver', 'short_description' => 'ver tipos de organizaciones'
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando los Tipos de Organismos</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions) {
            InstitutionType::withTrashed()->updateOrCreate(
                ['acronym' => 'EDSF'],
                ['name' => 'Ente Desentralizado sin fines empresariales', 'deleted_at' => null]
            );
            InstitutionType::withTrashed()->updateOrCreate(
                ['acronym' => 'ALCD'],
                ['name' => 'Alcaldía', 'deleted_at' => null]
            );
            InstitutionType::withTrashed()->updateOrCreate(
                ['acronym' => 'MINS'],
                ['name' => 'Ministerio', 'deleted_at' => null]
            );
            InstitutionType::withTrashed()->updateOrCreate(
                ['acronym' => 'GOBR'],
                ['name' => 'Gobernación', 'deleted_at' => null]
            );

            $this->count = 4;

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
            "<fg=green>Tipos de Organismos</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Tipos de Organismos</>"
        );
        $this->command->line("");
    }
}
