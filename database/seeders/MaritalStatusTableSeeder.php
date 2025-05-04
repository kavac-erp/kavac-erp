<?php

namespace Database\Seeders;

use App\Roles\Models\Role;
use App\Models\MaritalStatus;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class MaritalStatusTableSeeder
 * @brief Información por defecto para Estados Civiles
 *
 * Gestiona la información por defecto a registrar inicialmente para los Estados Civiles
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class MaritalStatusTableSeeder extends Seeder
{
    /**
     * Contador de estados civiles cargados
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
         * Permisos disponibles para la gestión de estados civiles
         */

        $permissions = [
            [
                'name' => 'Crear Estados Civiles', 'slug' => 'marital.status.create',
                'description' => 'Acceso al registro de estados civiles',
                'model' => MaritalStatus::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.civil.crear', 'short_description' => 'agregar estado civil'
            ],
            [
                'name' => 'Editar Estados Civiles', 'slug' => 'marital.status.edit',
                'description' => 'Acceso para editar estados civiles',
                'model' => MaritalStatus::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.civil.editar', 'short_description' => 'editar estado civil'
            ],
            [
                'name' => 'Eliminar Estados Civiles', 'slug' => 'marital.status.delete',
                'description' => 'Acceso para eliminar estados civiles',
                'model' => MaritalStatus::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.civil.eliminar', 'short_description' => 'eliminar estado civil'
            ],
            [
                'name' => 'Ver Estados Civiles', 'slug' => 'marital.status.list',
                'description' => 'Acceso para ver estados civiles',
                'model' => MaritalStatus::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.civil.ver', 'short_description' => 'ver estados civiles'
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando los Estados Civiles</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions) {
            MaritalStatus::withTrashed()->updateOrCreate(
                ['name' => 'Soltero(a)'],
                ['deleted_at' => null]
            );
            MaritalStatus::withTrashed()->updateOrCreate(
                ['name' => 'Casado(a)'],
                ['deleted_at' => null]
            );
            MaritalStatus::withTrashed()->updateOrCreate(
                ['name' => 'Divorciado(a)'],
                ['deleted_at' => null]
            );
            MaritalStatus::withTrashed()->updateOrCreate(
                ['name' => 'Viudo(a)'],
                ['deleted_at' => null]
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
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->count </><fg=green>Estados Civiles</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Estados Civiles</>"
        );
        $this->command->line("");
    }
}
