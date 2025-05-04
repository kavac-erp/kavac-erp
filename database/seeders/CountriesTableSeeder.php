<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class CountriesTableSeeder
 * @brief Información por defecto para Países
 *
 * Gestiona la información por defecto a registrar inicialmente para los Países
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CountriesTableSeeder extends Seeder
{
    /**
     * Contador de países cargados
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
     * Método constructor de la clase
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
         * Permisos disponibles para la gestión de países
         */

        $permissions = [
            [
                'name' => 'Crear Países', 'slug' => 'country.create',
                'description' => 'Acceso al registro de países',
                'model' => Country::class, 'model_prefix' => '0general',
                'slug_alt' => 'pais.crear', 'short_description' => 'agregar pais'
            ],
            [
                'name' => 'Editar Países', 'slug' => 'country.edit',
                'description' => 'Acceso para editar países',
                'model' => Country::class, 'model_prefix' => '0general',
                'slug_alt' => 'pais.editar', 'short_description' => 'editar pais'
            ],
            [
                'name' => 'Eliminar Países', 'slug' => 'country.delete',
                'description' => 'Acceso para eliminar países',
                'model' => Country::class, 'model_prefix' => '0general',
                'slug_alt' => 'pais.eliminar', 'short_description' => 'eliminar pais'
            ],
            [
                'name' => 'Ver Países', 'slug' => 'country.list',
                'description' => 'Acceso para ver países',
                'model' => Country::class, 'model_prefix' => '0general',
                'slug_alt' => 'pais.ver', 'short_description' => 'ver países'
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando los Países</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions) {
            Country::withTrashed()->updateOrCreate(
                ['name' => 'Venezuela'],
                ['prefix' => '58', 'deleted_at' => null]
            );
            $this->count++;

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
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->count </><fg=green>Pais</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Países</>"
        );
        $this->command->line("");
    }
}
