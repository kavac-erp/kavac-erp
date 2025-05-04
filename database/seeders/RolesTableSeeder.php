<?php

namespace Database\Seeders;

use Exception;
use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * @class RolesTableSeeder
 * @brief Información por defecto para Roles
 *
 * Gestiona la información por defecto a registrar inicialmente para los Roles
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RolesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeers de base de datos
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando Roles</>");
        $this->command->line("");

        $roles = [
            [
                'slug' => 'dev',
                'name' => 'Desarrollador',
                'description' => 'Desarrollador de la aplicación',
                'level' => 2,
            ],
            [
                'slug' => 'admin',
                'name' => 'Administrador',
                'description' => 'Administrador de la aplicación',
                'level' => 1,
            ],
            [
                'slug' => 'user',
                'name' => 'Usuario',
                'description' => 'Usuario de la aplicación',
                'level' => 1,
            ],
            /*[
                'slug' => 'support',
                'name' => 'Soporte',
                'description' => 'Soporte técnico de la aplicación',
                'level' => 2,
            ],*/
            [
                'slug' => 'audit',
                'name' => 'Auditor',
                'description' => 'Auditor del Sistema',
                'level' => 2,
            ]
        ];
        $rolesText = '';
        DB::transaction(function () use ($roles, $rolesText) {
            foreach ($roles as $roleData) {
                $role = Role::updateOrCreate(['slug' => $roleData['slug']], $roleData);
                if (!$role) {
                    throw new Exception("Error creando el rol por defecto para {$roleData['name']}");
                }
                $rolesText .= $role->name . ', ';
            }
        });

        $rolesText = trim($rolesText, ', ');
        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</><fg=yellow> " . count($roles) . " </>" .
            "<fg=green>Roles</><fg=yellow> [$rolesText] </>"
        );
        $this->command->line("");
    }
}
