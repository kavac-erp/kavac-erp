<?php

namespace Modules\Asset\Database\Seeders;

use App\Roles\Models\Permission;
use App\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Nwidart\Modules\Facades\Module;

/**
 * @class AssetRoleAndPermissionsTableSeeder
 * @brief Inicializa los roles y permisos del módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Método que registra los valores iniciales de los roles y permisos del módulo
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();
        $accountingRole = Role::where('slug', 'accounting')->first();

        $assetRole = Role::updateOrCreate(
            ['slug' => 'asset'],
            ['name' => 'Bienes', 'description' => 'Coordinador de bienes']
        );

        $permissions = [
            /* Panel de Control */
            [
                'name' => 'Acceso al panel de control de bienes',
                'slug' => 'asset.dashboard',
                'description' => 'Acceso al panel de control del módulo de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'panel.control.ver', 'short_description' => 'panel de control de bienes',
            ],
            /* Configuración General de Bienes */
            [
                'name' => 'Configuración General del módulo de bienes',
                'slug' => 'asset.setting',
                'description' => 'Acceso a la configuración general del módulo de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.ver', 'short_description' => 'configuración general de bienes',
            ],
            /* Configuración de Tipos de Bienes */
            [
                'name' => 'Configuración de los tipos de bienes',
                'slug' => 'asset.setting.type',
                'description' => 'Acceso a la configuración de los tipos de bienes',
                'model' => 'Modules\Asset\Models\AssetType', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.tipo', 'short_description' => 'configuración de los tipos de bienes',
            ],
            /* Configuración de las Categorías Generales de Bienes */
            [
                'name' => 'Configuración de las Categorías Generales de bienes',
                'slug' => 'asset.setting.category',
                'description' => 'Acceso a la configuración de las categorías de bienes',
                'model' => 'Modules\Asset\Models\AssetCategory', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.categoria',
                'short_description' => 'configuración de las categorías de bienes',
            ],
            /* Configuración de las Subcategorías de Bienes */
            [
                'name' => 'Configuración de las Subcategorías de bienes',
                'slug' => 'asset.setting.subcategory',
                'description' => 'Acceso a la configuración de las subcategorías de bienes',
                'model' => 'Modules\Asset\Models\AssetSubcategory', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.subcategoria',
                'short_description' => 'configuración de las subcategorías de bienes',
            ],
            /* Configuración de las Categorías Específicas de Bienes */
            [
                'name' => 'Configuración de las categorías específicas de bienes',
                'slug' => 'asset.setting.specific',
                'description' => 'Acceso a la configuración de las categorías específicas de bienes',
                'model' => 'Modules\Asset\Models\AssetSpecificCategory', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.categoria.especifica',
                'short_description' => 'configuración de las categorías específicas de bienes',
            ],
            /* Configuración de las Edificaciones */
            [
                'name' => 'Configuración de las edificaciones',
                'slug' => 'asset.setting.building',
                'description' => 'Acceso a la configuración de las edificaciones',
                'model' => 'Modules\Asset\Models\AssetBuilding', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.edificacion',
                'short_description' => 'configuración de las edificaciones',
            ],
            /* Configuración de los niveles */
            [
                'name' => 'Configuración de los niveles',
                'slug' => 'asset.setting.floor',
                'description' => 'Acceso a la configuración de los niveles',
                'model' => 'Modules\Asset\Models\AssetFloor', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.nivel',
                'short_description' => 'configuración de los niveles',
            ],
            /* Configuración de las secciones */
            [
                'name' => 'Configuración de las secciones',
                'slug' => 'asset.setting.section',
                'description' => 'Acceso a la configuración de las secciones',
                'model' => 'Modules\Asset\Models\AssetSection', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.seccion',
                'short_description' => 'configuración de las secciones',
            ],
            /* Ingreso de Bienes */
            [
                'name' => 'Ver bienes',
                'slug' => 'asset.list',
                'description' => 'Acceso a descripción del módulo de bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.ver', 'short_description' => 'ver bienes',
            ],
            [
                'name' => 'Crear bienes',
                'slug' => 'asset.create',
                'description' => 'Acceso al registro de bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.crear', 'short_description' => 'agregar bienes',
            ],
            [
                'name' => 'Editar bienes',
                'slug' => 'asset.edit',
                'description' => 'Acceso para editar bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.editar', 'short_description' => 'editar bienes',
            ],
            [
                'name' => 'Eliminar bienes',
                'slug' => 'asset.delete',
                'description' => 'Acceso para eliminar bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.eliminar', 'short_description' => 'eliminar bienes',
            ],
            [
                'name' => 'Importar registro',
                'slug' => 'asset.import',
                'description' => 'Acceso para importar registro',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.importar', 'short_description' => 'importar registro',
            ],
            [
                'name' => 'Exportar registro',
                'slug' => 'asset.export',
                'description' => 'Acceso para exportar registro',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.exportar', 'short_description' => 'exportar registro',
            ],
            /* Asignación de Bienes */
            [
                'name' => 'Ver asignación de bienes',
                'slug' => 'asset.asignation.list',
                'description' => 'Acceso para ver las asignaciones de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.ver', 'short_description' => 'ver asignación de bienes',
            ],
            [
                'name' => 'Crear asignación de bienes',
                'slug' => 'asset.asignation.create',
                'description' => 'Acceso para crear asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.crear', 'short_description' => 'agregar asignacion de bienes',
            ],
            [
                'name' => 'Editar asignación de bienes',
                'slug' => 'asset.asignation.edit',
                'description' => 'Acceso para editar asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.editar', 'short_description' => 'editar asignación de bienes',
            ],
            [
                'name' => 'Eliminar asignación de bienes',
                'slug' => 'asset.asignation.delete',
                'description' => 'Acceso para eliminar asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.eliminar', 'short_description' => 'eliminar asignación de bienes',
            ],
            [
                'name' => 'Imprimir acta de asignación de bienes',
                'slug' => 'asset.download',
                'description' => 'Acceso para imprimir acta de asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.imprimir.actas', 'short_description' => 'Imprimir acta de asignación de bienes',
            ],
            [
                'name' => 'Aprobar y rechazar asignación de bienes',
                'slug' => 'asset.asignation.approvereject',
                'description' => 'Acceso para aprobar y rechazar asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignationDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.aprobar_rechazar', 'short_description' => 'aprobar y rechazar asignación de bienes',
            ],
            /* Desincorporación de Bienes */
            [
                'name' => 'Ver desincorporación de bienes',
                'slug' => 'asset.disincorporation.list',
                'description' => 'Acceso para ver las desincorporaciones de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.ver', 'short_description' => 'ver desincorporación de bienes',
            ],
            [
                'name' => 'Crear desincorporación de bienes',
                'slug' => 'asset.disincorporation.create',
                'description' => 'Acceso para crear desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.crear',
                'short_description' => 'agregar desincorporación de bienes',
            ],
            [
                'name' => 'Editar desincorporación de bienes',
                'slug' => 'asset.disincorporation.edit',
                'description' => 'Acceso para editar desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.editar',
                'short_description' => 'editar desincorporación de bienes',
            ],
            [
                'name' => 'Eliminar desincorporación de bienes',
                'slug' => 'asset.disincorporation.delete',
                'description' => 'Acceso para eliminar desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.eliminar',
                'short_description' => 'eliminar desincorporación de bienes',
            ],
            [
                'name' => 'Imprimir acta de desincorporación de bienes',
                'slug' => 'asset.desincorporation.download',
                'description' => 'Acceso para imprimir acta de desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.actas', 'short_description' => 'Imprimir acta de desincorporación de bienes',
            ],
            /* Registro de Bienes */
            [
                'name' => 'Ver listado de bienes',
                'slug' => 'asset.request.register',
                'description' => 'Acceso para ver los bienes registrados',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.registro.ver', 'short_description' => 'ver registro de bienes',
            ],
            /* Solicitudes de Bienes */
            [
                'name' => 'Ver solicitud de bienes',
                'slug' => 'asset.request.list',
                'description' => 'Acceso para ver las solicitudes de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.ver', 'short_description' => 'ver solicitud de bienes',
            ],
            [
                'name' => 'Crear solicitud de bienes',
                'slug' => 'asset.request.create',
                'description' => 'Acceso para crear solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.crear', 'short_description' => 'agregar solicitud de bienes',
            ],
            [
                'name' => 'Editar solicitud de bienes',
                'slug' => 'asset.request.edit',
                'description' => 'Acceso para editar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.editar', 'short_description' => 'editar solicitud de bienes',
            ],
            [
                'name' => 'Eliminar solicitud de bienes',
                'slug' => 'asset.request.delete',
                'description' => 'Acceso para eliminar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.eliminar', 'short_description' => 'eliminar solicitud de bienes',
            ],
            [
                'name' => 'Aprobar solicitud de bienes',
                'slug' => 'asset.request.approve',
                'description' => 'Acceso para aprobar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.aprobar', 'short_description' => 'aprobar solicitud de bienes',
            ],
            [
                'name' => 'Rechazar solicitud de bienes',
                'slug' => 'asset.request.reject',
                'description' => 'Acceso para rechazar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.rechazar', 'short_description' => 'rechazar solicitud de bienes',
            ],
            [
                'name' => 'Crear solicitud de prórroga',
                'slug' => 'asset.request.extension',
                'description' => 'Acceso para crear solicitud de prórroga',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.prorroga', 'short_description' => 'crear solicitud de prórroga',
            ],
            [
                'name' => 'Aprobar solicitud de prórroga',
                'slug' => 'asset.request.extension.approved',
                'description' => 'Acceso para aprobar solicitud de prórroga',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.prorroga.aprobar', 'short_description' => 'aprobar solicitud de prórroga',
            ],
            [
                'name' => 'Rechazar solicitud de prórroga',
                'slug' => 'asset.request.extension.rejected',
                'description' => 'Acceso para rechazar solicitud de prórroga',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.prorroga.rechazar', 'short_description' => 'Rechazar solicitud de prórroga',
            ],
            [
                'name' => 'Entregar equipos prestados',
                'slug' => 'asset.request.deliver',
                'description' => 'Acceso para entrega de equipos prestados',
                'model' => 'Modules\Asset\Models\AssetRequestDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.entrega.equipo.prestado', 'short_description' => 'Entregar equipos prestados',
            ],
            [
                'name' => 'Aprobar y rechazar entrega de equipos prestados',
                'slug' => 'asset.request.delivery.approvereject',
                'description' => 'Acceso para aprobar y rechazar entrega de equipos prestados',
                'model' => 'Modules\Asset\Models\AssetRequestDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.entrega.aprobar_rechazar', 'short_description' => 'aprobar y rechazar entrega de equipos prestados',
            ],
            [
                'name' => 'Eliminar entrega de equipos prestados',
                'slug' => 'asset.request.delivery.delete',
                'description' => 'Acceso para eliminar entrega de equipos prestados',
                'model' => 'Modules\Asset\Models\AssetRequestDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.entrega.eliminar', 'short_description' => 'eliminar entrega de equipos prestados',
            ],
            [
                'name' => 'Registrar evento',
                'slug' => 'asset.request.event.create',
                'description' => 'Acceso para registrar evento',
                'model' => 'Modules\Asset\Models\AssetRequestEvent', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.evento.crear', 'short_description' => 'registrar evento',
            ],
            [
                'name' => 'Eliminar evento',
                'slug' => 'asset.request.event.delete',
                'description' => 'Acceso para eliminar evento',
                'model' => 'Modules\Asset\Models\AssetRequestEvent', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.evento.eliminar', 'short_description' => 'eliminar evento',
            ],
            [
                'name' => 'Vista inventario de bienes',
                'slug' => 'asset.inventory.history.index',
                'description' => 'Acceso a la vista de inventario de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.inventory-history.view', 'short_description' => 'vista inventario de bienes',
            ],
            /* disincorporations */
            [
                'name' => 'Vista de desincorporación de Bienes',
                'slug' => 'asset.disincorporation.index',
                'description' => 'Acceso a la vista de desincorporación de Bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.disincorporation.index', 'short_description' => 'vista Desincorporación de Bienes',
            ],
            [
                'name' => 'Crear una desincorporación de Bienes',
                'slug' => 'asset.disincorporation.create',
                'description' => 'crear una desincorporación de Bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.disincorporation.create', 'short_description' => 'crear una Desincorporación de Bienes',
            ],
            /* Reportes de Bienes */
            [
                'name' => 'Vista de reporte de bienes',
                'slug' => 'asset.report.view',
                'description' => 'Acceso a la vista de reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.view', 'short_description' => 'vista reporte de bienes',
            ],
            [
                'name' => 'Crear reporte de bienes',
                'slug' => 'asset.report.create',
                'description' => 'Acceso para crear reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.crear', 'short_description' => 'generar reporte de bienes',
            ],
            [
                'name' => 'Imprimir reporte de bienes',
                'slug' => 'asset.report.print',
                'description' => 'Acceso para imprimir reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.imprimir', 'short_description' => 'imprimir reporte de bienes',
            ],
            [
                'name' => 'Descargar reporte de bienes',
                'slug' => 'asset.report.download',
                'description' => 'Acceso para descargar reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.descargar', 'short_description' => 'descargar reporte de bienes',
            ],
            /* Entrega de bienes */
            [
                'name' => 'Vista de equipos asignados',
                'slug' => 'asset.asignations.view',
                'description' => 'Acceso a la vista de equipos asignados',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.equipos', 'short_description' => 'vista de equipos asignados',
            ],
            [
                'name' => 'Vista de creación de equipos asignados',
                'slug' => 'asset.asignations.create',
                'description' => 'Acceso a la vista de creación de  equipos asignados',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.creacion', 'short_description' => 'vista de creacion de equipos asignados',
            ],
            [
                'name' => 'Entregar equipos asignados',
                'slug' => 'asset.deliver',
                'description' => 'Acceso para entregar equipos asignados',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.entregar.equipos', 'short_description' => 'Entregar equipos asignados',
            ],
            /* Depósito de bienes */
            [
                'name' => 'Crear depósitos de bienes',
                'slug' => 'asset.setting.storage.create',
                'description' => 'Acceso para crear depósitos de bienes',
                'model' => 'Modules\Asset\Models\AssetStorage', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.depósitos.crear', 'short_description' => 'Crear depósitos',
            ],
            [
                'name' => 'Modificar depósitos de bienes',
                'slug' => 'asset.setting.storage.edit',
                'description' => 'Acceso para modificar depósitos de bienes',
                'model' => 'Modules\Asset\Models\AssetStorage', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.depósitos.modificar', 'short_description' => 'Modificar depósitos',
            ],
            [
                'name' => 'Eliminar depósitos de bienes',
                'slug' => 'asset.setting.storage.delete',
                'description' => 'Acceso para eliminar depósitos de bienes',
                'model' => 'Modules\Asset\Models\AssetStorage', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.depósitos.eliminar', 'short_description' => 'Eliminar depósitos',
            ],
        ];

        $depreciationPermissions = [
            /* Depreciación */
            [
                'name'              => 'Ver depreciación de bienes',
                'slug'              => 'asset.depreciation.list',
                'description'       => 'Acceso para ver depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.ver',
                'short_description' => 'listar depreciación de bienes'
            ],
            [
                'name'              => 'Crear depreciación de bienes',
                'slug'              => 'asset.depreciation.create',
                'description'       => 'Acceso para crear depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.crear',
                'short_description' => 'agregar depreciación de bienes'
            ],
            [
                'name'              => 'Anular depreciación de bienes',
                'slug'              => 'asset.depreciation.cancel',
                'description'       => 'Acceso para anular depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.anular',
                'short_description' => 'anular depreciación de bienes'
            ],
            [
                'name'              => 'Generar reportes de depreciación de bienes',
                'slug'              => 'asset.depreciation.report',
                'description'       => 'Acceso para generar reportes de depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.reportes',
                'short_description' => 'Generar reportes de depreciación de bienes'
            ]
        ];

        $suppliersPermissions = [
            /* Proveedores */
            [
                'name' => 'Crear especialidad de proveedor',
                'slug' => 'asset.supplierspecialty.create',
                'description' => 'Acceso para crear especialidad de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.crear',
                'short_description' => 'agregar especialidad de proveedor'
            ],
            [
                'name' => 'Editar especialidad de proveedor',
                'slug' => 'asset.supplierspecialty.edit',
                'description' => 'Acceso para editar especialidad de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.editar',
                'short_description' => 'editar especialidad de proveedor'
            ],
            [
                'name' => 'Eliminar especialidad de proveedor',
                'slug' => 'asset.supplierspecialty.delete',
                'description' => 'Acceso para eliminar especialidad de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.eliminar',
                'short_description' => 'eliminar especialidad de proveedor'
            ],
            [
                'name' => 'Ver especialidades de proveedores',
                'slug' => 'asset.supplierspecialty.list',
                'description' => 'Acceso para ver especialidades de proveedores',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.ver',
                'short_description' => 'ver especialidad de proveedor'
            ],
            [
                'name' => 'Crear tipo de proveedor',
                'slug' => 'asset.suppliertype.create',
                'description' => 'Acceso para crear tipo de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.crear',
                'short_description' => 'Agregar tipo de proveedor'
            ],
            [
                'name' => 'Editar tipo de proveedor',
                'slug' => 'asset.suppliertype.edit',
                'description' => 'Acceso para editar tipo de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.editar',
                'short_description' => 'Editar tipo de proveedor'
            ],
            [
                'name' => 'Eliminar tipo de proveedor',
                'slug' => 'asset.suppliertype.delete',
                'description' => 'Acceso para eliminar tipo de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.eliminar',
                'short_description' => 'Eliminar tipo de proveedor'
            ],
            [
                'name' => 'Ver tipos de proveedores',
                'slug' => 'asset.suppliertype.list',
                'description' => 'Acceso para ver tipos de proveedores',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.ver',
                'short_description' => 'Ver tipo de proveedor'
            ],
            [
                'name' => 'Crear proveedor',
                'slug' => 'asset.supplier.create',
                'description' => 'Acceso para crear proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.crear',
                'short_description' => 'Agregar proveedor'
            ],
            [
                'name' => 'Editar proveedor',
                'slug' => 'asset.supplier.edit',
                'description' => 'Acceso para editar proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.editar',
                'short_description' => 'Editar proveedor'
            ],
            [
                'name' => 'Eliminar proveedor',
                'slug' => 'asset.supplier.delete',
                'description' => 'Acceso para eliminar proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.eliminar',
                'short_description' => 'Eliminar proveedor'
            ],
            [
                'name' => 'Ver tipos de proveedores',
                'slug' => 'asset.supplier.list',
                'description' => 'Acceso para ver tipos de proveedores',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.ver',
                'short_description' => 'Ver proveedor'
            ],
        ];

        $assetRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'], 'short_description' => $permission['short_description'],
                ]
            );

            $assetRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }

        foreach ($depreciationPermissions as $depreciationPermission) {
            $per = Permission::updateOrCreate(
                ['slug' => $depreciationPermission['slug']],
                [
                    'name' => $depreciationPermission['name'], 'description' => $depreciationPermission['description'],
                    'model' => $depreciationPermission['model'], 'model_prefix' => $depreciationPermission['model_prefix'],
                    'slug_alt' => $depreciationPermission['slug_alt'], 'short_description' => $depreciationPermission['short_description'],
                ]
            );

            if ($accountingRole) {
                $accountingRole->attachPermission($per);
            }
        }

        if (!Module::has('Purchase') && !Module::isEnabled('Purchase')) {
            foreach ($suppliersPermissions as $suppliersPermission) {
                $per = Permission::updateOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $suppliersPermission['name'], 'description' => $suppliersPermission['description'],
                        'model' => $suppliersPermission['model'], 'model_prefix' => $suppliersPermission['model_prefix'],
                        'slug_alt' => $suppliersPermission['slug_alt'], 'short_description' => $suppliersPermission['short_description'],
                    ]
                );

                $assetRole->attachPermission($per);

                if ($adminRole) {
                    $adminRole->attachPermission($per);
                }
            }
        }
    }
}
