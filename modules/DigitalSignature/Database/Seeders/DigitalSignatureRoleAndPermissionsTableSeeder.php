<?php

namespace Modules\DigitalSignature\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Roles\Models\Role;
use App\Roles\Models\Permission;

/**
 * @class   DigitalSignatureRoleAndPermissionsTableSeeder
 * @brief   Gestiona la inserción de permisos en la base de datos
 *
 * Clase que gestiona la inserción de permisos en la base de datos.
 *
 * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DigitalSignatureRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Ejecute el seeder de la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $digitalSignatureRole = Role::updateOrCreate(
            [
                'slug' => 'digitalsignature'
            ],
            [
                'name' => 'Firma Electrónica',
                'description' => 'Coordinador de firma electrónica'
            ]
        );

        $permissions = [
            [
                'name' => 'Acceder al módulo de Firma Electrónica',
                'slug' => 'digitalsignature.index',
                'description' => 'Acceso al módulo de Firma Electrónica',
                'model' => '',
                'model_prefix' => 'firma_electronica',
                'slug_alt' => 'firma_electronica.acceso',
                'short_description' => 'Acceder al módulo de Firma Electrónica'
            ],
            [
                'name' => 'Cargar certificado digital p12',
                'slug' => 'digitalsignature.store',
                'description' => 'Cargar certificado digital p12',
                'model' => '',
                'model_prefix' => 'firma_electronica',
                'slug_alt' => 'firma_electronica.carga',
                'short_description' => 'Cargar certificado digital p12'
            ],
            [
                'name' => 'Actualizar certificado digital p12',
                'slug' => 'digitalsignature.update',
                'description' => 'Actualizar certificado digital p12',
                'model' => '',
                'model_prefix' => 'firma_electronica',
                'slug_alt' => 'firma_electronica.actualizar',
                'short_description' => 'Actualizar certificado digital p12'
            ],
            [
                'name' => 'Eliminar certificado digital p12 cargado',
                'slug' => 'digitalsignature.destroy',
                'description' => 'Eliminar certificado digital p12 cargado',
                'model' => '',
                'model_prefix' => 'firma_electronica',
                'slug_alt' => 'firma_electronica.eliminar',
                'short_description' => 'Eliminar certificado digital p12 cargado'
            ],
            [
                'name' => 'Firmar electrónicamente un documento',
                'slug' => 'digitalsignature.sign',
                'description' => 'Firmar electrónicamente un documento',
                'model' => '',
                'model_prefix' => 'firma_electronica',
                'slug_alt' => 'firma_electronica.sign',
                'short_description' => 'Firmar electrónicamente un documento'
            ],
            [
                'name' => 'Verificar la firma electrónica de un documento',
                'slug' => 'digitalsignature.verify',
                'description' => 'Verificar la firma electrónica de un documento',
                'model' => '',
                'model_prefix' => 'firma_electronica',
                'slug_alt' => 'firma_electronica.verify',
                'short_description' => 'Verificar la firma electrónica de un documento'
            ],
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de firma electrónica',
                'slug'              => 'digitalsignature.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'firma_electronica',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de firma electrónica'
            ],
        ];

        $digitalSignatureRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'model' => $permission['model'],
                    'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                    'short_description' => $permission['short_description']
                ]
            );

            $digitalSignatureRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
