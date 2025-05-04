<?php

namespace Modules\Finance\Database\Seeders;

use App\Roles\Models\Permission;
use App\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * @class FinanceRoleAndPermissionsTableSeeder
 * @brief Carga de datos de roles y permisos del módulo de finanzas
 *
 * Clase seeder para cargar datos de roles y permisos del módulo de finanzas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $financeRole = Role::updateOrCreate(
            ['slug' => 'finance'],
            ['name' => 'Finanza', 'description' => 'Coordinador de finanza']
        );

        $permissions = [
            [
                'name' => 'Configuración del módulo de finanzas',
                'slug' => 'finance.setting.create',
                'description' => 'Acceso a la configuración del módulo de finanzas',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'finanza.configuracion.crear',
                'short_description' => 'Configuración de finanzas',
            ],
            [
                'name' => 'Crear banco',
                'slug' => 'finance.bank.create',
                'description' => 'Acceso para crear banco',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.crear',
                'short_description' => 'Agregar banco',
            ],
            [
                'name' => 'Editar banco',
                'slug' => 'finance.bank.edit',
                'description' => 'Acceso para editar banco',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.editar',
                'short_description' => 'Editar banco',
            ],
            [
                'name' => 'Eliminar banco',
                'slug' => 'finance.bank.delete',
                'description' => 'Acceso para eliminar banco',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.eliminar',
                'short_description' => 'Eliminar banco',
            ],
            [
                'name' => 'Ver bancos',
                'slug' => 'finance.bank.list',
                'description' => 'Acceso para ver bancos',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.ver',
                'short_description' => 'Ver banco',
            ],
            [
                'name' => 'Crear agencia bancaria',
                'slug' => 'finance.bankagency.create',
                'description' => 'Acceso para crear agencia bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.crear',
                'short_description' => 'Agregar agencia bancaria',
            ],
            [
                'name' => 'Editar agencia bancaria',
                'slug' => 'finance.bankagency.edit',
                'description' => 'Acceso para editar agencia bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.editar',
                'short_description' => 'Editar agencia bancaria',
            ],
            [
                'name' => 'Eliminar agencia bancaria',
                'slug' => 'finance.bankagency.delete',
                'description' => 'Acceso para eliminar agencia bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.eliminar',
                'short_description' => 'Eliminar agencia bancaria',
            ],
            [
                'name' => 'Ver agencia bancarias',
                'slug' => 'finance.bankagency.list',
                'description' => 'Acceso para ver agencia bancarias',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.ver',
                'short_description' => 'Ver agencia bancaria',
            ],
            [
                'name' => 'Crear tipo de cuenta',
                'slug' => 'finance.accounttype.create',
                'description' => 'Acceso para crear tipo de cuenta',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.crear',
                'short_description' => 'Agregar tipo de cuenta',
            ],
            [
                'name' => 'Editar tipo de cuenta',
                'slug' => 'finance.accounttype.edit',
                'description' => 'Acceso para editar tipo de cuenta',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.editar',
                'short_description' => 'Editar tipo de cuenta',
            ],
            [
                'name' => 'Eliminar tipo de cuenta',
                'slug' => 'finance.accounttype.delete',
                'description' => 'Acceso para eliminar tipo de cuenta',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.eliminar',
                'short_description' => 'Eliminar tipo de cuenta',
            ],
            [
                'name' => 'Ver tipo de cuentas',
                'slug' => 'finance.accounttype.list',
                'description' => 'Acceso para ver tipo de cuentas',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.ver',
                'short_description' => 'Ver tipo de cuenta',
            ],
            [
                'name' => 'Crear cuenta bancaria',
                'slug' => 'finance.bankaccount.create',
                'description' => 'Acceso para crear cuenta bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.crear',
                'short_description' => 'Agregar cuenta bancaria',
            ],
            [
                'name' => 'Editar cuenta bancaria',
                'slug' => 'finance.bankaccount.edit',
                'description' => 'Acceso para editar cuenta bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.editar',
                'short_description' => 'Editar cuenta bancaria',
            ],
            [
                'name' => 'Eliminar cuenta bancaria',
                'slug' => 'finance.bankaccount.delete',
                'description' => 'Acceso para eliminar cuenta bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.eliminar',
                'short_description' => 'Eliminar cuenta bancaria',
            ],
            [
                'name' => 'Ver cuenta bancarias',
                'slug' => 'finance.bankaccount.list',
                'description' => 'Acceso para ver cuenta bancarias',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.ver',
                'short_description' => 'Ver cuenta bancaria',
            ],
            [
                'name' => 'Crear chequera',
                'slug' => 'finance.checkbook.create',
                'description' => 'Acceso para crear chequera',
                'model' => 'Modules\Finance\Models\FinanceCheckBook',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'chequera.crear',
                'short_description' => 'Agregar chequera',
            ],
            [
                'name' => 'Editar chequera',
                'slug' => 'finance.checkbook.edit',
                'description' => 'Acceso para editar chequera',
                'model' => 'Modules\Finance\Models\FinanceCheckBook',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'chequera.editar',
                'short_description' => 'Editar chequera',
            ],
            [
                'name' => 'Eliminar chequera',
                'slug' => 'finance.checkbook.delete',
                'description' => 'Acceso para eliminar chequera',
                'model' => 'Modules\Finance\Models\FinanceCheckBook',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'chequera.eliminar',
                'short_description' => 'Eliminar chequera',
            ],
            [
                'name' => 'Ver chequeras',
                'slug' => 'finance.checkbook.list',
                'description' => 'Acceso para ver chequeras',
                'model' => 'Modules\Finance\Models\FinanceCheckBook',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'chequera.ver',
                'short_description' => 'Ver chequera',
            ],
            /* Orden de pagos. */
            [
                'name' => 'Crear orden de pago',
                'slug' => 'finance.payorder.create',
                'description' => 'Acceso para crear orden de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.crear',
                'short_description' => 'Agregar orden de pago',
            ],
            [
                'name' => 'Editar orden de pago',
                'slug' => 'finance.payorder.edit',
                'description' => 'Acceso para editar orden de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.editar',
                'short_description' => 'Editar orden de pago',
            ],
            [
                'name' => 'Ver orden de pago',
                'slug' => 'finance.payorder.list',
                'description' => 'Acceso para ver órdenes de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.ver',
                'short_description' => 'Ver orden de pago',
            ],
            [
                'name' => 'Eliminar orden de pago',
                'slug' => 'finance.payorder.delete',
                'description' => 'Acceso para eliminar la orden de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.eliminar',
                'short_description' => 'Eliminar orden de pago',
            ],
            [
                'name' => 'Aprobar orden de pago',
                'slug' => 'finance.payorder.approve',
                'description' => 'Acceso para aprobar la orden de pago',
                'model' => 'Modules\Finance\Models\FinancePayOrder',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.aprobar',
                'short_description' => 'Aprobar orden de pago',
            ],
            [
                'name' => 'Anular órdenes de pago',
                'slug' => 'finance.payorder.cancel',
                'description' => 'Acceso para anular una orden de pago',
                'model' => 'Modules\Finance\Models\FinancePayOrder',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.anular',
                'short_description' => 'Anular órdenes de pago',
            ],
            [
                'name' => 'Crear un movimiento bancario',
                'slug' => 'finance.movements.create',
                'description' => 'Acceso para crear un movimiento bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.crear',
                'short_description' => 'Agregar un movimiento bancario',
            ],
            [
                'name' => 'Editar un movimiento bancario',
                'slug' => 'finance.movements.edit',
                'description' => 'Acceso para editar un movimiento bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.editar',
                'short_description' => 'Editar un movimiento bancario',
            ],
            [
                'name' => 'Ver movimientos bancario',
                'slug' => 'finance.movements.list',
                'description' => 'Acceso para ver los movimientos bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.ver',
                'short_description' => 'Ver los movimientos bancario',
            ],
            [
                'name' => 'Eliminar un movimiento bancario',
                'slug' => 'finance.movements.delete',
                'description' => 'Acceso para eliminar un movimiento bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.eliminar',
                'short_description' => 'Eliminar un movimiento bancario',
            ],
            [
                'name' => 'Aprobar movimientos bancarios',
                'slug' => 'finance.movements.approve',
                'description' => 'Acceso para aprobar movimientos bancarios',
                'model' => 'Modules\Finance\Models\FinanceBankingMovement',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.aprobar',
                'short_description' => 'Aprobar movimientos bancarios',
            ],
            [
                'name' => 'Anular movimientos bancarios',
                'slug' => 'finance.movements.cancel',
                'description' => 'Acceso para anular un movimiento bancario',
                'model' => 'Modules\Finance\Models\FinanceBankingMovement',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.anular',
                'short_description' => 'Anular movimientos bancarios',
            ],

            /* Configuraciones de los archivos de conciliación bancaria. */
            [
                'name' => 'Obtener listado de configuraciones de archivos de conciliación bancaria',
                'slug' => 'finance.settingbankreconciliationfiles.index',
                'description' => 'Acceso para obtener listado de configuraciones de archivos de conciliación bancaria',
                'model' => 'Modules\Finance\Models\FinanceSettingBankReconciliationFiles',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'config_archivos_conciliacion_bancaria.listado',
                'short_description' => 'Acceder al listado de configuraciones de archivos de conciliación bancaria',
            ],
            [
                'name' => 'Registrar una configuración de archivo de conciliación bancaria',
                'slug' => 'finance.settingbankreconciliationfiles.store',
                'description' => 'Acceso para registrar un configuració de archivo de conciliación bancaria',
                'model' => 'Modules\Finance\Models\FinanceSettingBankReconciliationFiles',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'config_archivos_conciliacion_bancaria.crear',
                'short_description' => 'Registrar una configuración de archivo de conciliación bancaria',
            ],
            [
                'name' => 'Actualizar una configuración de archivo de conciliación bancaria',
                'slug' => 'finance.settingbankreconciliationfiles.update',
                'description' => 'Acceso para actualizar un configuración de archivo de conciliación bancaria',
                'model' => 'Modules\Finance\Models\FinanceSettingBankReconciliationFiles',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'config_archivos_conciliacion_bancaria.actualizar',
                'short_description' => 'Actualizar una configuración de archivo de conciliación bancaria',
            ],
            [
                'name' => 'Eliminar una configuración de archivo de conciliación bancaria',
                'slug' => 'finance.settingbankreconciliationfiles.destroy',
                'description' => 'Acceso para eliminar un configuración de archivo de conciliación bancaria',
                'model' => 'Modules\Finance\Models\FinanceSettingBankReconciliationFiles',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'config_archivos_conciliacion_bancaria.eliminar',
                'short_description' => 'Eliminar una configuración de archivo de conciliación bancaria',
            ],
            /* Emisión de pagos. */
            [
                'name' => 'Obtener listado de emisión de pagos',
                'slug' => 'finance.paymentexecute.index',
                'description' => 'Acceso para obtener listado de emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pagos.listado',
                'short_description' => 'Acceder al listado de emisión de pagos',
            ],
            [
                'name' => 'Registrar una emisión de pagos',
                'slug' => 'finance.paymentexecute.store',
                'description' => 'Acceso para registrar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pagos.crear',
                'short_description' => 'Registrar una emisión de pagos',
            ],
            [
                'name' => 'Actualizar una emisión de pagos',
                'slug' => 'finance.paymentexecute.update',
                'description' => 'Acceso para actualizar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.actualizar',
                'short_description' => 'Actualizar una emisión de pagos',
            ],
            [
                'name' => 'Eliminar una emisión de pagos',
                'slug' => 'finance.paymentexecute.destroy',
                'description' => 'Acceso para eliminar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.eliminar',
                'short_description' => 'Eliminar una emisión de pagos',
            ],
            [
                'name' => 'Aprobar una emisión de pagos',
                'slug' => 'finance.paymentexecute.approve',
                'description' => 'Acceso para aprobar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.aprobar',
                'short_description' => 'Aprobar una emisión de pagos',
            ],
            [
                'name' => 'Anular emisiones de pagos',
                'slug' => 'finance.paymentexecute.cancel',
                'description' => 'Acceso para anular una emisión de pago',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.anular',
                'short_description' => 'Anular emisiones de pagos',
            ],
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de finanzas',
                'slug'              => 'finance.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'finanzas',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de finanza'
            ],
        ];

        $financeRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'model' => $permission['model'],
                    'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                    'short_description' => $permission['short_description'],
                ]
            );

            $financeRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
