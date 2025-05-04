<?php

namespace Database\Seeders;

use App\Models\Profession;
use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class ProfessionsTableSeeder
 * @brief Información por defecto para Profesiones
 *
 * Gestiona la información por defecto a registrar inicialmente para las Profesiones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProfessionsTableSeeder extends Seeder
{
    /**
     * Contador de Profesiones cargadas
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
         * Permisos disponibles para la gestión de profesiones
         */

        $permissions = [
            [
                'name' => 'Crear Profesiones', 'slug' => 'profession.create',
                'description' => 'Acceso al registro de profesiones',
                'model' => Profession::class, 'model_prefix' => '0general',
                'slug_alt' => 'profesion.crear', 'short_description' => 'agregar profesión'
            ],
            [
                'name' => 'Editar Profesiones', 'slug' => 'profession.edit',
                'description' => 'Acceso para editar profesiones',
                'model' => Profession::class, 'model_prefix' => '0general',
                'slug_alt' => 'profesion.editar', 'short_description' => 'editar profesión'
            ],
            [
                'name' => 'Eliminar Profesiones', 'slug' => 'profession.delete',
                'description' => 'Acceso para eliminar profesiones',
                'model' => Profession::class, 'model_prefix' => '0general',
                'slug_alt' => 'profesion.eliminar', 'short_description' => 'eliminar profesión'
            ],
            [
                'name' => 'Ver Profesiones', 'slug' => 'profession.list',
                'description' => 'Acceso para ver profesiones',
                'model' => Profession::class, 'model_prefix' => '0general',
                'slug_alt' => 'profesion.ver', 'short_description' => 'ver profesiones'
            ],
        ];

        $professions = [
            ['name' => 'Abogado(a)', 'acronym' => 'Abg'],
            ['name' => 'Arquitecto(a)', 'acronym' => 'Arq'],
            ['name' => 'Bachiller', 'acronym' => 'Bach'],
            ['name' => 'Criminólogo(a)', 'acronym' => ''],
            ['name' => 'Doctor(a)', 'acronym' => 'Dr'],
            ['name' => 'Doctor(a) en Ciencias Computacionales', 'acronym' => 'Dr'],
            ['name' => 'Economista', 'acronym' => 'Eco'],
            ['name' => 'Ingeniero(a) Civil', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) de Sistemas', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) Electricista', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) en Computación', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) en Electrónica', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) en Informática', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) Industrial', 'acronym' => 'Ing'],
            ['name' => 'Ingeniero(a) Mecánico', 'acronym' => 'Ing'],
            ['name' => 'Licenciado(a) en Administración', 'acronym' => 'Lic'],
            ['name' => 'Licenciado(a) en Ciencias Gerenciales', 'acronym' => 'Lic'],
            ['name' => 'Licenciado(a) en Comunicación Social', 'acronym' => 'Lic'],
            ['name' => 'Licenciado(a) en Contaduría Pública', 'acronym' => 'Lic'],
            ['name' => 'Licenciado(a) en Estadística', 'acronym' => 'Lic'],
            ['name' => 'Ninguna', 'acronym' => ''],
            ['name' => 'Politologo(a)', 'acronym' => 'Pol'],
            ['name' => 'T.S.U. en Administración', 'acronym' => 'T.S.U.'],
            ['name' => 'T.S.U. en Contaduría', 'acronym' => 'T.S.U.'],
            ['name' => 'T.S.U. en Informática', 'acronym' => 'T.S.U.'],
            ['name' => 'T.S.U. en Diseño', 'acronym' => 'T.S.U.'],
            ['name' => 'T.S.U. en Electrónica', 'acronym' => 'T.S.U.'],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando las Profesiones</>");
        $this->command->line("");

        DB::transaction(function () use ($adminRole, $permissions, $professions) {
            foreach ($professions as $profession) {
                Profession::withTrashed()->updateOrCreate(
                    ['name' => $profession['name']],
                    [
                        'acronym' => ($profession['acronym']) ? $profession['acronym'] : null,
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
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->count </><fg=green>Profesiones</>"
        );
        $this->command->line("");
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> $this->countP </>" .
            "<fg=green>Permisos para la gestión de Profesiones</>"
        );
        $this->command->line("");
    }
}
