<?php

namespace Modules\CitizenService\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Roles\Models\Role;
use App\Roles\Models\Permission;

/**
 * @class CitizenServiceRoleAndPermissionsTableSeeder
 * @brief Inicializa los roles y permisos del módulo de atención al ciudadano
 *
 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $citizenServiceRole = Role::updateOrCreate(
            ['slug' => 'CitizenService'],
            ['name' => 'OAC', 'description' => 'Coordinador de atención al ciudadano']
        );

        $permissions = [
            [
                'name' => 'Configuración del módulo de atención al ciudadano',
                'slug' => 'citizenservice.setting.index',
                'description' => 'Acceso a la configuración del módulo de atención al ciudadano',
                'model' => '', 'model_prefix' => 'OAC',
                'slug_alt' => 'configuracion.ver'
            ],
            /* Request (Solicitudes) */
            [
                'name' => 'Ver gestión de atención al ciudadano',
                'slug' => 'citizenservice.requests.list',
                'description' => 'Acceso para ver solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.ver'
            ],
            [
                'name' => 'Crear solicitud',
                'slug' => 'citizenservice.requests.create',
                'description' => 'Acceso para crear solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.crear'
            ],
            [
                'name' => 'Editar solicitud',
                'slug' => 'citizenservice.requests.edit',
                'description' => 'Acceso para editar solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.editar'
            ],
            [
                'name' => 'Eliminar solicitud',
                'slug' => 'citizenservice.requests.delete',
                'description' => 'Acceso para eliminar solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.eliminar'
            ],
            [
                'name' => 'Aprobar solicitud',
                'slug' => 'citizenservice.requests.approved',
                'description' => 'Acceso para aprobar solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.aprobar'
            ],
            [
                'name' => 'Rechazar solicitud',
                'slug' => 'citizenservice.requests.rejected',
                'description' => 'Acceso para rechazar solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.rechazar'
            ],
            [
                'name' => 'Agregar indicador a la solicitud',
                'slug' => 'citizenservice.requests.addindicator',
                'description' => 'Acceso para agragar un indicador a la solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.indicador'
            ],
            [
                'name' => 'Ver información de la solicitud',
                'slug' => 'citizenservice.requests.info',
                'description' => 'Acceso para ver la información de la solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.ver-info'
            ],
            /* Report (Reportes)*/
            [
                'name' => 'Crear reporte de atención al ciudadano',
                'slug' => 'citizenservice.report.create',
                'description' => 'Acceso para crear reportes de atención al ciudadano',
                'model' => '',
                'model_prefix' => 'OAC',
                'slug_alt' => 'reporte.crear',
                'short_description' => 'generar reporte de atención al ciudadano'
            ],
            [
                'name' => 'Ver reporte de atención al ciudadano',
                'slug' => 'citizenservice.report.list',
                'description' => 'Acceso para ver reportes de atención al ciudadano',
                'model' => '', 'model_prefix' => 'OAC',
                'slug_alt' => 'reporte.ver',
                'short_description' => 'generar reporte de atención al ciudadano'
            ],
            /* Register (Cronograma) */
            [
                'name' => 'Ver gestión de cronograma de actividades',
                'slug' => 'citizenservice.registers.list',
                'description' => 'Acceso para ver cronograma de actividades',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRegister',
                'model_prefix' => 'OAC',
                'slug_alt' => 'cronograma.ver'
            ],
            [
                'name' => 'Crear cronograma de actividades',
                'slug' => 'citizenservice.registers.create',
                'description' => 'Acceso para crear cronograma de actividades',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRegister',
                'model_prefix' => 'OAC',
                'slug_alt' => 'cronograma.crear'
            ],
            [
                'name' => 'Editar cronograma de actividades',
                'slug' => 'citizenservice.registers.edit',
                'description' => 'Acceso para editar cronograma de actividades',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRegister',
                'model_prefix' => 'OAC',
                'slug_alt' => 'cronograma.editar'
            ],
            [
                'name' => 'Eliminar cronograma de actividades',
                'slug' => 'citizenservice.registers.delete',
                'description' => 'Acceso para eliminar cronograma de actividades',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRegister',
                'model_prefix' => 'OAC',
                'slug_alt' => 'cronograma.eliminar'
            ],
            /* request-type (Tipo de solicitud) */
            [
                'name' => 'Crear tipo de solicitud',
                'slug' => 'citizenservice.request.types.create',
                'description' => 'Acceso para crear tipo de solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequestType',
                'model_prefix' => 'OAC',
                'slug_alt' => 'tipo_de_solicitud.crear'
            ],
            [
                'name' => 'Editar tipo de solicitud',
                'slug' => 'citizenservice.request.types.edit',
                'description' => 'Acceso para editar tipo de solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequestType',
                'model_prefix' => 'OAC',
                'slug_alt' => 'tipo_de_solicitud.editar'
            ],
            [
                'name' => 'Eliminar tipo de solicitud',
                'slug' => 'citizenservice.request.types.delete',
                'description' => 'Acceso para eliminar tipo de solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequestType',
                'model_prefix' => 'OAC',
                'slug_alt' => 'tipo-de-solicitud.eliminar'
            ],
            /* departament (Departamento) */
            [
                'name' => 'Crear departamento',
                'slug' => 'citizenservice.departaments.create',
                'description' => 'Acceso para crear un departamento',
                'model' => 'Modules\CitizenService\Models\CitizenServiceDepartment',
                'model_prefix' => 'OAC',
                'slug_alt' => 'departamento.crear'
            ],
            [
                'name' => 'Editar departamento',
                'slug' => 'citizenservice.departaments.edit',
                'description' => 'Acceso para editar un departamento',
                'model' => 'Modules\CitizenService\Models\CitizenServiceDepartment',
                'model_prefix' => 'OAC',
                'slug_alt' => 'departamento.editar'
            ],
            [
                'name' => 'Eliminar departamento',
                'slug' => 'citizenservice.departaments.delete',
                'description' => 'Acceso para eliminar un departamento',
                'model' => 'Modules\CitizenService\Models\CitizenServiceDepartment',
                'model_prefix' => 'OAC',
                'slug_alt' => 'departamento.eliminar'
            ],
            /* effect-types (Tipo de impacto) */
            [
                'name' => 'Crear tipo de impacto',
                'slug' => 'citizenservice.effect.types.create',
                'description' => 'Acceso para crear un tipo de impacto',
                'model' => 'Modules\CitizenService\Models\CitizenServiceEffectType',
                'model_prefix' => 'OAC',
                'slug_alt' => 'tipo_de_impacto.crear'
            ],
            [
                'name' => 'Editar tipo de impacto',
                'slug' => 'citizenservice.effect.types.edit',
                'description' => 'Acceso para editar un tipo de impacto',
                'model' => 'Modules\CitizenService\Models\CitizenServiceEffectType',
                'model_prefix' => 'OAC',
                'slug_alt' => 'tipo_de_impacto.editar'
            ],
            [
                'name' => 'Eliminar tipo de impacto',
                'slug' => 'citizenservice.effect.types.delete',
                'description' => 'Acceso para eliminar un tipo de impacto',
                'model' => 'Modules\CitizenService\Models\CitizenServiceEffectType',
                'model_prefix' => 'OAC',
                'slug_alt' => 'tipo_de_impacto.eliminar'
            ],
            /* indicators (Indicador) */
            [
                'name' => 'Crear indicador',
                'slug' => 'citizenservice.indicators.create',
                'description' => 'Acceso para crear un indicador',
                'model' => 'Modules\CitizenService\Models\CitizenServiceIndicator',
                'model_prefix' => 'OAC',
                'slug_alt' => 'indicador.crear'
            ],
            [
                'name' => 'Editar indicador',
                'slug' => 'citizenservice.indicators.edit',
                'description' => 'Acceso para editar un indicador',
                'model' => 'Modules\CitizenService\Models\CitizenServiceIndicator',
                'model_prefix' => 'OAC',
                'slug_alt' => 'indicador.editar'
            ],
            [
                'name' => 'Eliminar indicador',
                'slug' => 'citizenservice.indicators.delete',
                'description' => 'Acceso para eliminar un indicador',
                'model' => 'Modules\CitizenService\Models\CitizenServiceIndicator',
                'model_prefix' => 'OAC',
                'slug_alt' => 'indicador.eliminar'
            ],
            /* request-close (Cierre de solicitud) */
            [
                'name' => 'Ver cierre de solicitudes',
                'slug' => 'citizenservice.requests.close.list',
                'description' => 'Acceso para ver cierre de solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.ver-cierre'
            ],
            [
                'name' => 'Cerrar solicitudes',
                'slug' => 'citizenservice.requests.close',
                'description' => 'Acceso para cerrar solicitud',
                'model' => 'Modules\CitizenService\Models\CitizenServiceRequest',
                'model_prefix' => 'OAC',
                'slug_alt' => 'solicitud.cerrar'
            ],
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de OAC',
                'slug'              => 'citizenservice.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'OAC',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de OAC'
            ],
        ];

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt']
                ]
            );

            $citizenServiceRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
