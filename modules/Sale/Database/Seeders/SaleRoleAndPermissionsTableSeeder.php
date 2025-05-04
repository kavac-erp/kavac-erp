<?php

namespace Modules\Sale\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Roles\Models\Role;
use App\Roles\Models\Permission;

/**
 * @class SaleRoleAndPermissionsTableSeeder
 * @brief Inicializa los roles y permisos del módulo de comercialización
 *
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Método que registra los valores iniciales de los roles y permisos del módulo
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $saleRole = Role::updateOrCreate(
            ['slug' => 'sale'],
            ['name' => 'Comercialización', 'description' => 'Coordinador de comercialización']
        );

        $permissions = [
            /* Configuración General de Comercialización */
            [
                'name' => 'Configuración General del módulo de comercialización',
                'slug' => 'sale.setting',
                'description' => 'Acceso a la configuración general del módulo de comercialización',
                'model' => '', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.ver', 'short_description' => 'configuración general de comercialización'
            ],
            /* Configuración de Alamcenes */
            [
                'name' => 'Configuración de los Almacenes',
                'slug' => 'sale.setting.warehouse',
                'description' => 'Acceso a la configuración de los almacenes',
                'model' => 'Modules\Sale\Models\SaleWarehouse', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.almacen', 'short_description' => 'configuración de los almacenes'
            ],
            /* Configuración de los clientes */
            [
                'name' => 'Configuración de los clientes',
                'slug' => 'sale.setting.client',
                'description' => 'Acceso a la configuración de los clientes',
                'model' => 'Modules\Sale\Models\SaleClient', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.cliente',
                'short_description' => 'configuración de los clientes'
            ],
            /* Configuración de los productos */
            [
                'name' => 'Configuración de los productos',
                'slug' => 'sale.setting.product',
                'description' => 'Acceso a la configuración de los productos',
                'model' => 'Modules\Sale\Models\SaleSettingProduct', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.producto',
                'short_description' => 'Configuración de los productos'
            ],
            /* Configuración de los tipos de productos */
            [
                'name' => 'Configuración de los tipos de productos',
                'slug' => 'sale.setting.product.type',
                'description' => 'Acceso a la configuración de los productos',
                'model' => 'Modules\Sale\Models\SaleSettingProductType', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.producto.tipo',
                'short_description' => 'Configuración de los tipos de productos'
            ],
            /* Configuración de los descuentos */
            [
                'name' => 'Configuración de los descuentos',
                'slug' => 'sale.setting.discount',
                'description' => 'Acceso a la configuración de los desceuntos',
                'model' => 'Modules\Sale\Models\SaleDiscount', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.descuento',
                'short_description' => 'Configuración de los descuentos'
            ],
            /* Configuración de los tipos de bien */
            [
                'name' => 'Configuración de los tipos de bien',
                'slug' => 'sale.setting.type.good',
                'description' => 'Acceso a la configuración de los tipos de bien',
                'model' => 'Modules\Sale\Models\SaleTypeGood', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.bien.tipo',
                'short_description' => 'Configuración de los tipos de bien'
            ],
            /* Configuración de la lista de subservicios */
            [
                'name' => 'Configuración de la lista de subservicios',
                'slug' => 'sale.setting.subservices',
                'description' => 'Acceso a la configuración de la lista de subservicios',
                'model' => 'Modules\Sale\Models\SaleListSubservices', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.subservicios',
                'short_description' => 'Configuración de la lista de subservicios'
            ],
            /* Configuración de los costos fijos */
            [
                'name' => 'Configuración de los costos fijos',
                'slug' => 'sale.setting.periodic.cost',
                'description' => 'Acceso a la configuración de los costos fijos',
                'model' => 'Modules\Sale\Models\PeriodicCost', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.costos_fijos',
                'short_description' => 'Configuración de los costos fijos'
            ],
            /* Configuración de los métodos de cobro */
            [
                'name' => 'Configuración de los métodos de cobro',
                'slug' => 'sale.setting.charge.money',
                'description' => 'Acceso a la configuración de los métodos de cobro',
                'model' => 'Modules\Sale\Models\SaleChargeMoney', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.metodos_cobro',
                'short_description' => 'Configuración de los métodos de cobro'
            ],
            /* Configuración de las formas de cobro */
            [
                'name' => 'Configuración de las formas de cobro',
                'slug' => 'sale.setting.form.payment',
                'description' => 'Acceso a la configuración de las formas de cobro',
                'model' => 'Modules\Sale\Models\SaleFormPayment', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.formas_cobro',
                'short_description' => 'configuración de las formas de cobro'
            ],
            /* Configuración de los periodos de tiempo */
            [
                'name' => 'Configuración de los periodos de tiempo',
                'slug' => 'sale.setting.frecuency',
                'description' => 'Acceso a la configuración de los periodos de tiempo',
                'model' => 'Modules\Sale\Models\SaleSettingFrecuency', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.periodos_tiempo',
                'short_description' => 'Configuración de los periodos de tiempo'
            ],
            /* Configuración de los bienes a comercializar */
            [
                'name' => 'Configuración de los bienes a comercializar',
                'slug' => 'sale.setting.good.traded',
                'description' => 'Acceso a la configuración de los bienes a comercializar',
                'model' => 'Modules\Sale\Models\SaleGoodsToBeTraded', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'configuracion.bienes_comercializar',
                'short_description' => 'Configuración de los bienes a comercializar'
            ],
            /* Solicitud de servicios */
            [
                'name' => 'Ver solicitud de servicios',
                'slug' => 'sale.service.list',
                'description' => 'Acceso para ver las solicitudes de servicios',
                'model' => 'Modules\Sale\Models\SaleService', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.servicio.ver', 'short_description' => 'ver solicitud de servicio'
            ],
            [
                'name' => 'Crear solicitud de servicios',
                'slug' => 'sale.service.create',
                'description' => 'Acceso para crear las solicitudes de servicios',
                'model' => 'Modules\Sale\Models\SaleService', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.servicio.crear', 'short_description' => 'crear solicitud de servicio'
            ],
            [
                'name' => 'Editar solicitud de servicios',
                'slug' => 'sale.service.edit',
                'description' => 'Acceso para editar las solicitudes de servicios',
                'model' => 'Modules\Sale\Models\SaleService', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.servicio.editar', 'short_description' => 'editar solicitud de servicio'
            ],
            [
                'name' => 'Eliminar solicitud de servicios',
                'slug' => 'sale.service.delete',
                'description' => 'Acceso para eliminar las solicitudes de servicios',
                'model' => 'Modules\Sale\Models\SaleService', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.servicio.eliminar', 'short_description' => 'eliminar solicitud de servicio'
            ],
            /* Facturas */
            [
                'name' => 'Ver facturas',
                'slug' => 'sale.bill.list',
                'description' => 'Acceso para ver las facturas',
                'model' => 'Modules\Sale\Models\SaleBill', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.factura.ver', 'short_description' => 'ver facturas'
            ],
            [
                'name' => 'Crear facturas',
                'slug' => 'sale.bill.create',
                'description' => 'Acceso para crear las facturas',
                'model' => 'Modules\Sale\Models\SaleBill', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.factura.crear', 'short_description' => 'crear facturas'
            ],
            [
                'name' => 'Editar facturas',
                'slug' => 'sale.bill.edit',
                'description' => 'Acceso para editar las facturas',
                'model' => 'Modules\Sale\Models\SaleBill', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.factura.editar', 'short_description' => 'editar facturas'
            ],
            [
                'name' => 'Eliminar facturas',
                'slug' => 'sale.bill.delete',
                'description' => 'Acceso para eliminar las facturas',
                'model' => 'Modules\Sale\Models\SaleBill', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.factura.eliminar', 'short_description' => 'eliminar facturas'
            ],
            /* Recepció */
            [
                'name' => 'Ver recepción de almacén',
                'slug' => 'sale.warehouse.reception.list',
                'description' => 'Acceso para ver las recepciones de almacén',
                'model' => 'Modules\Sale\Models\SaleWarehouseInventoryProduct', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.recepcion.ver', 'short_description' => 'ver recepción de almacén'
            ],
            [
                'name' => 'Crear recepción de almacén',
                'slug' => 'sale.warehouse.reception.create',
                'description' => 'Acceso para crear las recepciones de almacén',
                'model' => 'Modules\Sale\Models\SaleWarehouseInventoryProduct', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.recepcion.crear', 'short_description' => 'crear recepción de almacén'
            ],
            [
                'name' => 'Editar recepción de almacén',
                'slug' => 'sale.warehouse.reception.edit',
                'description' => 'Acceso para editar las recepciones de almacén',
                'model' => 'Modules\Sale\Models\SaleWarehouseInventoryProduct', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.recepcion.editar', 'short_description' => 'editar recepción de almacén'
            ],
            [
                'name' => 'Eliminar recepción de almacén',
                'slug' => 'sale.warehouse.reception.delete',
                'description' => 'Acceso para eliminar las recepciones de almacén',
                'model' => 'Modules\Sale\Models\SaleWarehouseInventoryProduct', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.recepcion.eliminar', 'short_description' => 'eliminar recepción de almacén'
            ],
            /* Pagos */
            [
                'name' => 'Ver pagos',
                'slug' => 'sale.payment.list',
                'description' => 'Acceso para ver los pagos',
                'model' => 'Modules\Sale\Models\SaleRegisterPayment', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pago.ver', 'short_description' => 'ver pagos'
            ],
            [
                'name' => 'Crear pagos',
                'slug' => 'sale.payment.create',
                'description' => 'Acceso para crear los pagos',
                'model' => 'Modules\Sale\Models\SaleRegisterPayment', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pago.crear', 'short_description' => 'crear pagos'
            ],
            [
                'name' => 'Editar pagos',
                'slug' => 'sale.payment.edit',
                'description' => 'Acceso para editar los pagos',
                'model' => 'Modules\Sale\Models\SaleRegisterPayment', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pago.editar', 'short_description' => 'editar pagos'
            ],
            [
                'name' => 'Eliminar pagos',
                'slug' => 'sale.payment.delete',
                'description' => 'Acceso para eliminar los pagos',
                'model' => 'Modules\Sale\Models\SaleRegisterPayment', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pago.eliminar', 'short_description' => 'eliminar pagos'
            ],
            /* Pedidos */
            [
                'name' => 'Ver pedidos',
                'slug' => 'sale.order.list',
                'description' => 'Acceso para ver los pedidos',
                'model' => 'Modules\Sale\Models\SaleOrderManagement', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pedido.ver', 'short_description' => 'ver pedidos'
            ],
            [
                'name' => 'Crear pedidos',
                'slug' => 'sale.order.create',
                'description' => 'Acceso para crear los pedidos',
                'model' => 'Modules\Sale\Models\SaleOrderManagement', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pedido.crear', 'short_description' => 'crear pedidos'
            ],
            [
                'name' => 'Editar pedidos',
                'slug' => 'sale.order.edit',
                'description' => 'Acceso para editar los pedidos',
                'model' => 'Modules\Sale\Models\SaleOrderManagement', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pedido.editar', 'short_description' => 'editar pedidos'
            ],
            [
                'name' => 'Eliminar pedidos',
                'slug' => 'sale.order.delete',
                'description' => 'Acceso para eliminar los pedidos',
                'model' => 'Modules\Sale\Models\SaleOrderManagement', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.pedido.eliminar', 'short_description' => 'eliminar pedidos'
            ],
            /* Cotizaciones */
            [
                'name' => 'Ver cotizaciones',
                'slug' => 'sale.quote.list',
                'description' => 'Acceso para ver las cotizaciones',
                'model' => 'Modules\Sale\Models\SaleQuote', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.cotizacion.ver', 'short_description' => 'ver cotizaciones'
            ],
            [
                'name' => 'Crear cotizaciones',
                'slug' => 'sale.quote.create',
                'description' => 'Acceso para crear las cotizaciones',
                'model' => 'Modules\Sale\Models\SaleQuote', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.cotizacion.crear', 'short_description' => 'crear cotizaciones'
            ],
            [
                'name' => 'Editar cotizaciones',
                'slug' => 'sale.quote.edit',
                'description' => 'Acceso para editar las cotizaciones',
                'model' => 'Modules\Sale\Models\SaleQuote', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.cotizacion.editar', 'short_description' => 'editar cotizaciones'
            ],
            [
                'name' => 'Eliminar cotizaciones',
                'slug' => 'sale.quote.delete',
                'description' => 'Acceso para eliminar las cotizaciones',
                'model' => 'Modules\Sale\Models\SaleQuote', 'model_prefix' => 'Comercialización',
                'slug_alt' => 'comercializacion.cotizacion.eliminar', 'short_description' => 'eliminar cotizaciones'
            ],
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de comercialización',
                'slug'              => 'sale.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'Comercialiación',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de comercialización'
            ],
        ];

        $saleRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'], 'short_description' => $permission['short_description']
                ]
            );

            $saleRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
