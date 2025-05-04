<?php

namespace Modules\Warehouse\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Roles\Models\Role;
use App\Roles\Models\Permission;

/**
 * @class WarehouseRoleAndPermissionsTableSeeder
 * @brief Inicializa los roles y permisos del módulo de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseRoleAndPermissionsTableSeeder extends Seeder
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

        $warehouseRole = Role::updateOrCreate(
            ['slug' => 'warehouse'],
            ['name' => 'Almacén', 'description' => 'Coordinador de almacenes']
        );

        $permissions = [
            /* Panel de Control */
            [
                'name' => 'Acceso al panel de control de almacén',
                'slug' => 'warehouse.dashboard',
                'description' => 'Acceso al panel de control del módulo de almacén',
                'model' => '', 'model_prefix' => 'Almacén',
                'slug_alt' => 'panel.control.ver', 'short_description' => 'panel de control de almacén'
            ],
            /* Configuración General de Bienes */
            [
                'name' => 'Configuración General del módulo de almacén',
                'slug' => 'warehouse.setting',
                'description' => 'Acceso a la configuración general del módulo de almacén',
                'model' => '', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.ver', 'short_description' => 'configuración general de almacén'
            ],
            /* Configuración de Almacenes */
            [
                'name' => 'Configuración de los Almacenes',
                'slug' => 'warehouse.setting.warehouse',
                'description' => 'Acceso a la configuración de los almacenes',
                'model' => 'Modules\Warehouse\Models\Warehouse', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen', 'short_description' => 'Configuración de los almacenes'
            ],
            [
                'name' => 'Crear almacenes',
                'slug' => 'warehouse.setting.warehouse.create',
                'description' => 'Acceso para crear almacenes',
                'model' => 'Modules\Warehouse\Models\Warehouse', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.crear', 'short_description' => 'Crear almacenes'
            ],
            [
                'name' => 'Editar almacenes',
                'slug' => 'warehouse.setting.warehouse.edit',
                'description' => 'Acceso para editar almacenes',
                'model' => 'Modules\Warehouse\Models\Warehouse', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.editar', 'short_description' => 'Editar almacenes'
            ],
            [
                'name' => 'Eliminar almacenes',
                'slug' => 'warehouse.setting.warehouse.delete',
                'description' => 'Acceso para eliminar almacenes',
                'model' => 'Modules\Warehouse\Models\Warehouse', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.eliminar', 'short_description' => 'Eliminar almacenes'
            ],
            /* Configuración de los productos almacenables */
            [
                'name' => 'Configuración de los productos almacenables',
                'slug' => 'warehouse.setting.product',
                'description' => 'Acceso a la configuración de los productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto',
                'short_description' => 'Configuración de los productos almacenables'
            ],
            [
                'name' => 'Crear insumos',
                'slug' => 'warehouse.setting.product.create',
                'description' => 'Acceso para crear productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto.crear',
                'short_description' => 'Crear productos almacenables'
            ],
            [
                'name' => 'Editar insumos',
                'slug' => 'warehouse.setting.product.edit',
                'description' => 'Acceso para editar productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto.editar',
                'short_description' => 'Editar productos almacenables'
            ],
            [
                'name' => 'Eliminar insumos',
                'slug' => 'warehouse.setting.product.delete',
                'description' => 'Acceso para eliminar productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto.eliminar',
                'short_description' => 'Eliminar productos almacenables'
            ],
            [
                'name' => 'Importar insumos',
                'slug' => 'warehouse.setting.product.import',
                'description' => 'Acceso para importar productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto.importar',
                'short_description' => 'Importar productos almacenables'
            ],
            [
                'name' => 'Exportar insumos',
                'slug' => 'warehouse.setting.product.export',
                'description' => 'Acceso para exportar productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto.exportar',
                'short_description' => 'Exportar productos almacenables'
            ],
            [
                'name' => 'Entregar insumos',
                'slug' => 'warehouse.setting.product.delivery',
                'description' => 'Acceso para entregar productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.almacen.producto.entrega',
                'short_description' => 'Entregar productos almacenables'
            ],
            /* Configuración de los atributos de los productos almacenables */
            [
                'name' => 'Configuración de los atributos de los productos almacenables',
                'slug' => 'warehouse.setting.attribute',
                'description' => 'Acceso a la configuración de los atributos de los productos almacenables',
                'model' => 'Modules\Warehouse\Models\WarehouseProductAttribute', 'model_prefix' => 'Almacén',
                'slug_alt' => 'configuracion.producto.atributo',
                'short_description' => 'Configuración de los atributos de los productos almacenables'
            ],
            // /**
            //  * Configuración de las unidades métricas de productos almacenables
            //  **/
            // [
            //     'name' => 'Configuración de las unidades métricas de productos almacenables',
            //     'slug' => 'warehouse.setting.unit',
            //     'description' => 'Acceso a la configuración de las unidades métricas de productos almacenables',
            //     'model' => 'Modules\Warehouse\Models\WarehouseProductUnit', 'model_prefix' => 'Almacén',
            //     'slug_alt' => 'configuracion.almacen.unidad',
            //     'short_description' => 'configuración de las unidades métricas de productos almacenables'
            // ],
            // /**
            //  * Configuración de las reglas de almacén
            //  **/
            // [
            //     'name' => 'Configuración de las reglas de almacén', 'slug' => 'warehouse.setting.rule',
            //     'description' => 'Acceso a la configuración de las reglas de almacén',
            //     'model' => 'Modules\Warehouse\Models\WarehouseProductRule', 'model_prefix' => 'Almacén',
            //     'slug_alt' => 'configuracion.almacen.regla',
            //     'short_description' => 'configuración de las reglas de almacén'
            // ],
            // /**
            //  * Cierres de Almacen
            //  **/
            // [
            //     'name' => 'Configuración de los cierres de almacén', 'slug' => 'warehouse.setting.close',
            //     'description' => 'Acceso a la configuración de cierres de almacén',
            //     'model' => 'Modules\Warehouse\Models\WarehouseClose', 'model_prefix' => 'Almacén',
            //     'slug_alt' => 'configuracion.almacen.cierre',
            //     'short_description' => 'configuración de los cierres de almacén'
            // ],

            /* Solicitudes de Almacén */
            [
                'name' => 'Ver solicitud de almacén',
                'slug' => 'warehouse.request.list',
                'description' => 'Acceso para ver las solicitudes de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseRequest', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.solicitud.ver', 'short_description' => 'ver solicitud de almacén'
            ],
            [
                'name' => 'Crear solicitud de almacén',
                'slug' => 'warehouse.request.create',
                'description' => 'Acceso para crear solicitud de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseRequest', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.solicitud.crear', 'short_description' => 'agregar solicitud de almacén'
            ],
            [
                'name' => 'Editar solicitud de almacén',
                'slug' => 'warehouse.request.edit',
                'description' => 'Acceso para editar solicitud de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseRequest', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.solicitud.editar', 'short_description' => 'editar solicitud de almacén'
            ],
            [
                'name' => 'Eliminar solicitud de almacén',
                'slug' => 'warehouse.request.delete',
                'description' => 'Acceso para eliminar solicitud de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseRequest', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.solicitud.eliminar', 'short_description' => 'eliminar solicitud de bienes'
            ],
            [
                'name' => 'Visualizar solicitudes por departamento',
                'slug' => 'warehouse.request.deparment',
                'description' => 'Acceso para visualizar las solicitudes de almacén por departamento',
                'model' => 'Modules\Warehouse\Models\WarehouseRequest', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.solicitud.departamento', 'short_description' => 'Ver solicitudes por departamento'
            ],
            [
                'name' => 'Visualizar solicitudes por usuario',
                'slug' => 'warehouse.request.user',
                'description' => 'Acceso para visualizar lsa solicitudes de almacén por usuario',
                'model' => 'Modules\Warehouse\Models\WarehouseRequest', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.solicitud.usuario', 'short_description' => 'Ver solicitudes por usuario'
            ],
            /* Movimientos de Almacén */
            [
                'name' => 'Ver movimiento de artículos de almacén',
                'slug' => 'warehouse.movement.list',
                'description' => 'Acceso para ver los movimientos de artículos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movimiento.ver', 'short_description' => 'ver movimiento de artículos de almacén'
            ],
            [
                'name' => 'Crear movimiento de artículos de almacén',
                'slug' => 'warehouse.movement.create',
                'description' => 'Acceso para crear movimientos de artículos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movimiento.crear',
                'short_description' => 'Agregar movimiento de artículos de almacén'
            ],
            [
                'name' => 'Editar movimiento de artículos de almacén',
                'slug' => 'warehouse.movement.edit',
                'description' => 'Acceso para editar los movimientos de artículos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movimiento.editar',
                'short_description' => 'Editar movimiento de artículos de almacén'
            ],
            [
                'name' => 'Eliminar movimiento de artículos de almacén',
                'slug' => 'warehouse.movement.delete',
                'description' => 'Acceso para eliminar los movimientos de artículos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movement.eliminar',
                'short_description' => 'Eliminar movimiento de artículos de almacén'
            ],
            [
                'name' => 'Aprobar movimientos de almacén',
                'slug' => 'warehouse.movement.approve',
                'description' => 'Acceso para aprobar movimientos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movimiento.crear',
                'short_description' => 'Aprobar movimientos de almacén'
            ],
            [
                'name' => 'Rechazar movimientos de almacén',
                'slug' => 'warehouse.movement.decline',
                'description' => 'Acceso para rechazar movimientos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movimiento.rechazar',
                'short_description' => 'Rechazar movimientos de almacén'
            ],
            [
                'name' => 'Confirmar movimientos de almacén',
                'slug' => 'warehouse.movement.confirm',
                'description' => 'Acceso para confirmar movimientos de almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseMovement', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.movimiento.confirmar',
                'short_description' => 'Confirmar movimientos de almacén'
            ],
            /* Ingresos de Almacén */
            [
                'name' => 'Crear ingreso de almacén',
                'slug' => 'warehouse.inventory.create',
                'description' => 'Acceso para crear ingresos del almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseInventoryProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.ingreso.crear', 'short_description' => 'Crear ingreso de almacén'
            ],
            [
                'name' => 'Ver ingreso de almacén',
                'slug' => 'warehouse.inventory.show',
                'description' => 'Acceso para ver ingresos del almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseInventoryProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.ingreso.ver', 'short_description' => 'Ver ingreso de almacén'
            ],
            [
                'name' => 'Editar ingreso de almacén',
                'slug' => 'warehouse.inventory.edit',
                'description' => 'Acceso para editar ingresos del almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseInventoryProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.ingreso.editar', 'short_description' => 'Editar ingreso de almacén'
            ],
            [
                'name' => 'Eliminar ingreso de almacén',
                'slug' => 'warehouse.inventory.delete',
                'description' => 'Acceso para eliminar ingresos del almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseInventoryProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.ingreso.eliminar', 'short_description' => 'Eliminar ingreso de almacén'
            ],
            [
                'name' => 'Aprobar ingreso de almacén',
                'slug' => 'warehouse.inventory.approve',
                'description' => 'Acceso para aprobar ingresos del almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseInventoryProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.ingreso.aprobar', 'short_description' => 'Aprobrar ingreso de almacén'
            ],
            [
                'name' => 'Rechazar ingreso al almacén',
                'slug' => 'warehouse.inventory.decline',
                'description' => 'Acceso para rechazar ingresos al almacén',
                'model' => 'Modules\Warehouse\Models\WarehouseInventoryProduct', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.ingreso.rechazar', 'short_description' => 'Rechazar ingreso de almacén'
            ],
            /* Reportes de Bienes */
            [
                'name' => 'Crear reporte de inventario',
                'slug' => 'warehouse.report.create',
                'description' => 'Acceso para crear reportes de inventario',
                'model' => '', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.reporte.crear', 'short_description' => 'generar reporte de inventario'
            ],
            [
                'name' => 'Generar reporte de solicitudes de productos',
                'slug' => 'warehouse.report.product.request',
                'description' => 'Acceso para generar reportes de solicitudes de productos',
                'model' => '', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.reporte.producto.solicitud', 'short_description' => 'Generar reporte de solicitudes de producto'
            ],
            [
                'name' => 'Generar reporte de mínimo inventario',
                'slug' => 'warehouse.report.least.inventory',
                'description' => 'Acceso para generar reportes de mínimo inventario',
                'model' => '', 'model_prefix' => 'Almacén',
                'slug_alt' => 'almacen.reporte.mínimo.inventario', 'short_description' => 'Generar reporte de mínimo inventario'
            ],
        ];

        $warehouseRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'], 'short_description' => $permission['short_description']
                ]
            );

            $warehouseRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
        $deleteUnitPer = Permission::where('slug', 'warehouse.setting.unit');
        $deleteUnitPer ? $deleteUnitPer->delete() : null;
        $deleteRulePer = Permission::where('slug', 'warehouse.setting.rule');
        $deleteRulePer ? $deleteRulePer->delete() : null;
        $deleteClosePer = Permission::where('slug', 'warehouse.setting.close');
        $deleteClosePer ? $deleteClosePer->delete() : null;
    }
}
