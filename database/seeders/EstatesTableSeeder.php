<?php

namespace Database\Seeders;

use App\Models\Estate;
use App\Models\Country;
use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class EstatesTableSeeder
 * @brief Información por defecto para Estados
 *
 * Gestiona la información por defecto a registrar inicialmente para las Estados
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class EstatesTableSeeder extends Seeder
{
    /**
     * Contador de Estados cargados
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
         * Permisos disponibles para la gestión de estados
         */

        $permissions = [
            [
                'name' => 'Crear Estados', 'slug' => 'estate.create',
                'description' => 'Acceso al registro de estados',
                'model' => Estate::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.crear', 'short_description' => 'agregar estado'
            ],
            [
                'name' => 'Editar Estados', 'slug' => 'estate.edit',
                'description' => 'Acceso para editar estados',
                'model' => Estate::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.editar', 'short_description' => 'editar estado'
            ],
            [
                'name' => 'Eliminar Estados', 'slug' => 'estate.delete',
                'description' => 'Acceso para eliminar estados',
                'model' => Estate::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.eliminar', 'short_description' => 'eliminar estado'
            ],
            [
                'name' => 'Ver Estados', 'slug' => 'estate.list',
                'description' => 'Acceso para ver estados',
                'model' => Estate::class, 'model_prefix' => '0general',
                'slug_alt' => 'estado.ver', 'short_description' => 'ver estados'
            ],
        ];

        /* Almacena información del pais */
        $country_default = Country::where('name', 'Venezuela')->first();

        $estates = [
            "01" => "Distrito Capital",
            "02" => "Amazonas",
            "03" => "Anzoategui",
            "04" => "Apure",
            "05" => "Aragua",
            "06" => "Barinas",
            "07" => "Bolívar",
            "08" => "Carabobo",
            "09" => "Cojedes",
            "10" => "Delta Amacuro",
            "11" => "Falcón",
            "12" => "Guárico",
            "13" => "Lara",
            "14" => "Mérida",
            "15" => "Miranda",
            "16" => "Monagas",
            "17" => "Nueva Esparta",
            "18" => "Portuguesa",
            "19" => "Sucre",
            "20" => "Táchira",
            "21" => "Trujillo",
            "22" => "Yaracuy",
            "23" => "Zulia",
            "24" => "Vargas"
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando los Estados</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions, $country_default, $estates) {
            foreach ($estates as $code => $state) {
                Estate::withTrashed()->updateOrCreate(
                    ['code' => $code],
                    ['name' => $state, 'country_id' => $country_default->id, 'deleted_at' => null]
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
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->count </><fg=green>Estados</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Estados</>"
        );
        $this->command->line("");
    }
}
