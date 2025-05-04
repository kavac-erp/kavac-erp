<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class CurrenciesTableSeeder
 * @brief Información por defecto para Monedas
 *
 * Gestiona la información por defecto a registrar inicialmente para las Monedas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CurrenciesTableSeeder extends Seeder
{
    /**
     * Contador de monedas cargadas
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

        $permissions = [
            [
                'name' => 'Crear Monedas', 'slug' => 'currency.create',
                'description' => 'Acceso al registro de monedas',
                'model' => Currency::class, 'model_prefix' => '0general',
                'slug_alt' => 'moneda.crear', 'short_description' => 'agregar moneda'
            ],
            [
                'name' => 'Editar Monedas', 'slug' => 'currency.edit',
                'description' => 'Acceso para editar monedas',
                'model' => Currency::class, 'model_prefix' => '0general',
                'slug_alt' => 'moneda.editar', 'short_description' => 'editar moneda'
            ],
            [
                'name' => 'Eliminar Monedas', 'slug' => 'currency.delete',
                'description' => 'Acceso para eliminar monedas',
                'model' => Currency::class, 'model_prefix' => '0general',
                'slug_alt' => 'moneda.eliminar', 'short_description' => 'eliminar moneda'
            ],
            [
                'name' => 'Ver Monedas', 'slug' => 'currency.list',
                'description' => 'Acceso para ver monedas',
                'model' => Currency::class, 'model_prefix' => '0general',
                'slug_alt' => 'moneda.ver', 'short_description' => 'ver monedas'
            ],
        ];

        /* Almacena información del pais */
        $country = Country::where('name', 'Venezuela')->first();

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando las Monedas</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions, $country) {
            Currency::withTrashed()->updateOrCreate(
                ['country_id' => $country->id, 'symbol' => 'Bs.'],
                ['name' => 'Bolívar', 'plural_name' => 'Bolívares', 'default' => true, 'deleted_at' => null]
            );
            $this->count = 1;

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
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->count </><fg=green>Monedas</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Monedas</>"
        );
        $this->command->line("");
    }
}
