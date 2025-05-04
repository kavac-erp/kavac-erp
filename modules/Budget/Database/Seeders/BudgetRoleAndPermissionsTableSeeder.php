<?php

namespace Modules\Budget\Database\Seeders;

use App\Roles\Models\Permission;
use App\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Budget\Models\BudgetAccount;
use Modules\Budget\Models\BudgetCentralizedAction;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetModification;
use Modules\Budget\Models\BudgetProject;
use Modules\Budget\Models\BudgetSpecificAction;
use Modules\Budget\Models\BudgetStage;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class BudgetRoleAndPermissionsTableSeeder
 * @brief Información por defecto para Roles y Permisos del módulo de presupuesto
 *
 * Gestiona la información por defecto a registrar inicialmente para los Roles y Permisos del módulo de presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetRoleAndPermissionsTableSeeder extends Seeder
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

        $budgetRole = Role::updateOrCreate(
            ['slug' => 'budget'],
            ['name' => 'Presupuesto', 'description' => 'Coordinador de presupuesto']
        );

        /* Listado de permisos a registrar */
        $permissions = [
            [
                'name' => 'Inicio del módulo de presupuesto',
                'slug' => 'budget.home',
                'description' => 'Acceso a descripción del módulo de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetAccount',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'presupuesto.inicio',
                'short_description' => 'página de inicio',
            ],
            [
                'name' => 'Configuración del módulo de presupuesto',
                'slug' => 'budget.setting.create',
                'description' => 'Acceso a la configuración del módulo de presupuesto',
                'model' => '',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'configuracion.crear',
                'short_description' => 'agregar configuración',
            ],
            [
                'name' => 'Editar configuración del módulo de presupuesto',
                'slug' => 'budget.setting.edit',
                'description' => 'Acceso para editar la configuración del módulo de presupuesto',
                'model' => '',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'configuracion.editar',
                'short_description' => 'editar configuración',
            ],
            [
                'name' => 'Ver configuración del módulo de presupuesto',
                'slug' => 'budget.setting.list',
                'description' => 'Acceso para ver la configuración del módulo de presupuesto',
                'model' => '',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'configuracion.ver',
                'short_description' => 'ver configuración',
            ],
            [
                'name' => 'Eliminar configuración del módulo de presupuesto',
                'slug' => 'budget.setting.delete',
                'description' => 'Acceso para eliminar la configuración del módulo de presupuesto',
                'model' => '',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'configuracion.eliminar',
                'short_description' => 'eliminar configuración',
            ],
            [
                'name' => 'Crear cuenta presupuestaria',
                'slug' => 'budget.account.create',
                'description' => 'Acceso para crear cuenta presupuestaria',
                'model' => 'Modules\Budget\Models\BudgetAccount',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'clasificador.crear',
                'short_description' => 'agregar clasificador',
            ],
            [
                'name' => 'Editar cuenta presupuestaria',
                'slug' => 'budget.account.edit',
                'description' => 'Acceso para editar cuenta presupuestaria',
                'model' => 'Modules\Budget\Models\BudgetAccount',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'clasificador.editar',
                'short_description' => 'editar clasificador',
            ],
            [
                'name' => 'Eliminar cuenta presupuestaria',
                'slug' => 'budget.account.delete',
                'description' => 'Acceso para eliminar cuenta presupuestaria',
                'model' => 'Modules\Budget\Models\BudgetAccount', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'clasificador.eliminar', 'short_description' => 'eliminar clasificador',
            ],
            [
                'name' => 'Ver cuentas presupuestarias',
                'slug' => 'budget.account.list',
                'description' => 'Acceso para ver cuentas presupuestarias',
                'model' => 'Modules\Budget\Models\BudgetAccount', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'clasificador.ver', 'short_description' => 'ver clasificador',
            ],
            [
                'name' => 'Crear proyecto',
                'slug' => 'budget.project.create',
                'description' => 'Acceso para crear proyecto',
                'model' => 'Modules\Budget\Models\BudgetProject', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'proyecto.crear', 'short_description' => 'agregar proyecto',
            ],
            [
                'name' => 'Editar proyecto',
                'slug' => 'budget.project.edit',
                'description' => 'Acceso para editar proyectos',
                'model' => 'Modules\Budget\Models\BudgetProject', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'proyecto.editar', 'short_description' => 'editar proyecto',
            ],
            [
                'name' => 'Eliminar proyecto',
                'slug' => 'budget.project.delete',
                'description' => 'Acceso para eliminar proyectos',
                'model' => 'Modules\Budget\Models\BudgetProject', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'proyecto.eliminar', 'short_description' => 'eliminar proyecto',
            ],
            [
                'name' => 'Ver proyectos',
                'slug' => 'budget.project.list',
                'description' => 'Acceso para ver proyectos',
                'model' => 'Modules\Budget\Models\BudgetProject', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'proyecto.ver', 'short_description' => 'ver proyecto',
            ],

            [
                'name' => 'Crear acción centralizada',
                'slug' => 'budget.centralizedaction.create',
                'description' => 'Acceso para crear acción centralizada',
                'model' => 'Modules\Budget\Models\BudgetCentralizedAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_centralizada.crear', 'short_description' => 'agregar acción centralizada',
            ],
            [
                'name' => 'Editar acción centralizada',
                'slug' => 'budget.centralizedaction.edit',
                'description' => 'Acceso para editar acción centralizada',
                'model' => 'Modules\Budget\Models\BudgetCentralizedAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_centralizada.editar', 'short_description' => 'editar acción centralizada',
            ],
            [
                'name' => 'Eliminar acción centralizada',
                'slug' => 'budget.centralizedaction.delete',
                'description' => 'Acceso para eliminar acción centralizada',
                'model' => 'Modules\Budget\Models\BudgetCentralizedAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_centralizada.eliminar', 'short_description' => 'eliminar acción centralizada',
            ],
            [
                'name' => 'Ver acciones centralizadas',
                'slug' => 'budget.centralizedaction.list',
                'description' => 'Acceso para ver acciones centralizadas',
                'model' => 'Modules\Budget\Models\BudgetCentralizedAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_centralizada.ver', 'short_description' => 'ver acción centralizada',
            ],
            [
                'name' => 'Crear acción específica',
                'slug' => 'budget.specificaction.create',
                'description' => 'Acceso para crear acción específica',
                'model' => 'Modules\Budget\Models\BudgetSpecificAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_especifica.crear', 'short_description' => 'agregar acción específica',
            ],
            [
                'name' => 'Editar acción específica',
                'slug' => 'budget.specificaction.edit',
                'description' => 'Acceso para editar acciones específicas',
                'model' => 'Modules\Budget\Models\BudgetSpecificAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_especifica.editar', 'short_description' => 'editar acción específica',
            ],
            [
                'name' => 'Eliminar acción específica',
                'slug' => 'budget.specificaction.delete',
                'description' => 'Acceso para eliminar acciones específicas',
                'model' => 'Modules\Budget\Models\BudgetSpecificAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_especifica.eliminar', 'short_description' => 'eliminar acción específica',
            ],
            [
                'name' => 'Ver acciones específicas',
                'slug' => 'budget.specificaction.list',
                'description' => 'Acceso para ver acciones específicas',
                'model' => 'Modules\Budget\Models\BudgetSpecificAction', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'accion_especifica.ver', 'short_description' => 'ver acción específica',
            ],
            [
                'name' => 'Crear formulación de presupuesto',
                'slug' => 'budget.formulation.create',
                'description' => 'Acceso para crear formulación de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetFormulation', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'formulacion.crear', 'short_description' => 'agregar formulación',
            ],
            [
                'name' => 'Editar formulación de presupuesto',
                'slug' => 'budget.formulation.edit',
                'description' => 'Acceso para editar formulaciones de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetFormulation', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'formulacion.editar', 'short_description' => 'editar formulación',
            ],
            [
                'name' => 'Eliminar formulación de presupuesto',
                'slug' => 'budget.formulation.delete',
                'description' => 'Acceso para eliminar formulaciones de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetFormulation', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'formulacion.eliminar', 'short_description' => 'eliminar formulación',
            ],
            [
                'name' => 'Ver formulaciones de presupuesto',
                'slug' => 'budget.formulation.list',
                'description' => 'Acceso para ver formulaciones de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetFormulation', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'formulacion.ver', 'short_description' => 'ver formulación',
            ],
            [
                'name' => 'Crear crédito adicional',
                'slug' => 'budget.aditionalcredit.create',
                'description' => 'Acceso para crear crédito adicional',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'credito_adicional.crear', 'short_description' => 'agregar crédito adicional',
            ],
            [
                'name' => 'Editar crédito adicional',
                'slug' => 'budget.aditionalcredit.edit',
                'description' => 'Acceso para editar créditos adicionales',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'credito_adicional.editar', 'short_description' => 'editar crédito adicional',
            ],
            [
                'name' => 'Eliminar crédito adicional',
                'slug' => 'budget.aditionalcredit.delete',
                'description' => 'Acceso para eliminar créditos adicionales',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'credito_adicional.eliminar', 'short_description' => 'eliminar crédito adicional',
            ],
            [
                'name' => 'Ver créditos adicionales',
                'slug' => 'budget.aditionalcredit.list',
                'description' => 'Acceso para ver créditos adicionales',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'credito_adicional.ver', 'short_description' => 'ver crédito adicional',
            ],
            [
                'name' => 'Crear reducción de presupuesto',
                'slug' => 'budget.reduction.create',
                'description' => 'Acceso para crear reducción de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'reduccion.crear', 'short_description' => 'agregar reducción de presupuesto',
            ],
            [
                'name' => 'Editar reducción de presupuesto',
                'slug' => 'budget.reduction.edit',
                'description' => 'Acceso para editar reducciones de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'reduccion.editar', 'short_description' => 'editar reducción de presupuesto',
            ],
            [
                'name' => 'Eliminar reducción de presupuesto',
                'slug' => 'budget.reduction.delete',
                'description' => 'Acceso para eliminar reducciones de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'reduccion.eliminar', 'short_description' => 'eliminar reducción de presupuesto',
            ],
            [
                'name' => 'Ver reducciones de presupuesto',
                'slug' => 'budget.reduction.list',
                'description' => 'Acceso para ver reducciones de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'reduccion.ver',
                'short_description' => 'ver reducción de presupuesto',
            ],
            [
                'name' => 'Crear traspaso de presupuesto',
                'slug' => 'budget.transfer.create',
                'description' => 'Acceso para crear traspaso de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'traspaso.crear',
                'short_description' => 'agregar traspaso de presupuesto',
            ],
            [
                'name' => 'Editar traspaso de presupuesto',
                'slug' => 'budget.transfer.edit',
                'description' => 'Acceso para editar traspasos de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'traspaso.editar',
                'short_description' => 'editar traspaso de presupuesto',
            ],
            [
                'name' => 'Eliminar traspaso de presupuesto',
                'slug' => 'budget.transfer.delete',
                'description' => 'Acceso para eliminar traspasos de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'traspaso.eliminar',
                'short_description' => 'eliminar traspaso de presupuesto',
            ],
            [
                'name' => 'Ver traspasos de presupuesto',
                'slug' => 'budget.transfer.list',
                'description' => 'Acceso para ver traspasos de presupuesto',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'traspaso.ver',
                'short_description' => 'ver traspaso de presupuesto',
            ],
            [
                'name' => 'Crear modificación presupuestaria',
                'slug' => 'budget.modifications.create',
                'description' => 'Acceso para crear modificación presupuestaria',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'modificacion.crear',
                'short_description' => 'agregar modificación presupuestaria',
            ],
            [
                'name' => 'Editar modificación presupuestaria',
                'slug' => 'budget.modifications.edit',
                'description' => 'Acceso para editar modificaciones presupuestarias',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'modificacion.editar',
                'short_description' => 'editar modificación presupuestaria',
            ],
            [
                'name' => 'Eliminar modificación presupuestaria',
                'slug' => 'budget.modifications.delete',
                'description' => 'Acceso para eliminar modificaciones presupuestarias',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'modificacion.eliminar',
                'short_description' => 'eliminar modificación presupuestaria',
            ],
            [
                'name' => 'Ver modificaciones presupuestarias',
                'slug' => 'budget.modifications.list',
                'description' => 'Acceso para ver modificaciones presupuestarias',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'modificacion.ver',
                'short_description' => 'ver modificación presupuestaria',
            ],
            [
                'name' => 'Aprobar modificaciones presupuestarias',
                'slug' => 'budget.modifications.approve',
                'description' => 'Acceso para aprobar modificaciones presupuestarias',
                'model' => 'Modules\Budget\Models\BudgetModification',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'modificacion.aprobar',
                'short_description' => 'Aprobar modificación presupuestaria',
            ],

            /**
             * Permisos de los Registros comúnes > Tipos de financiamiento.
             */
            [
                'name' => 'Obtener listado de los tipos de financiamiento',
                'slug' => 'budget.financementtypes.index',
                'description' => 'Acceso para obtener listado de los tipos de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementTypes',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'tipos_financiamiento.listado',
                'short_description' => 'Acceder al listado de tipos de financiamiento',
            ],
            [
                'name' => 'Registrar un tipo de financiamiento',
                'slug' => 'budget.financementtypes.store',
                'description' => 'Acceso para registrar un tipo de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementTypes',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'tipos_financiamiento.crear',
                'short_description' => 'Registrar un tipo de financiamiento',
            ],
            [
                'name' => 'Actualizar un tipo de financiamiento',
                'slug' => 'budget.financementtypes.update',
                'description' => 'Acceso para actualizar un tipo de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementTypes',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'tipos_financiamiento.actualizar',
                'short_description' => 'Actualizar un tipo de financiamiento',
            ],
            [
                'name' => 'Eliminar un tipo de financiamiento',
                'slug' => 'budget.financementtypes.destroy',
                'description' => 'Acceso para eliminar un tipo de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementTypes',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'tipos_financiamiento.eliminar',
                'short_description' => 'Eliminar un tipo de financiamiento',
            ],

            /* Permisos de los Registros comúnes > Fuentes de financiamiento. */
            [
                'name' => 'Obtener listado de las fuentes de financiamiento',
                'slug' => 'budget.financementsources.index',
                'description' => 'Acceso para obtener listado de las fuentes de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementSources',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'fuentes_financiamiento.listado',
                'short_description' => 'Acceder al listado de fuentes de financiamiento',
            ],
            [
                'name' => 'Registrar una fuente de financiamiento',
                'slug' => 'budget.financementsources.store',
                'description' => 'Acceso para registrar una fuente de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementSources',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'fuentes_financiamiento.crear',
                'short_description' => 'Registrar una fuente de financiamiento',
            ],
            [
                'name' => 'Actualizar una fuente de financiamiento',
                'slug' => 'budget.financementsources.update',
                'description' => 'Acceso para actualizar una fuente de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementSources',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'fuentes_financiamiento.actualizar',
                'short_description' => 'Actualizar una fuente de financiamiento',
            ],
            [
                'name' => 'Eliminar una fuente de financiamiento',
                'slug' => 'budget.financementsources.destroy',
                'description' => 'Acceso para eliminar una fuente de financiamiento',
                'model' => 'Modules\Budget\Models\BudgetFinancementSource',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'fuentes_financiamiento.eliminar',
                'short_description' => 'Eliminar una fuente de financiamiento',
            ],
            /* Permisos para Compromisos. */
            [
                'name' => 'Obtener listado de los Compromisos',
                'slug' => 'budget.compromise.index',
                'description' => 'Acceso para obtener listado de los compromisos',
                'model' => BudgetCentralizedAction::class, 'model_prefix' => 'presupuesto',
                'slug_alt' => 'compromisos.listado',
                'short_description' => 'Acceder al listado de los compromisos',
            ],
            [
                'name' => 'Registrar Compromisos',
                'slug' => 'budget.compromise.store',
                'description' => 'Acceso para registrar Compromisos',
                'model' => BudgetCentralizedAction::class, 'model_prefix' => 'presupuesto',
                'slug_alt' => 'compromisos.crear',
                'short_description' => 'Registrar Compromisos',
            ],
            [
                'name' => 'Actualizar Compromisos',
                'slug' => 'budget.compromise.update',
                'description' => 'Acceso para actualizar Compromisos',
                'model' => BudgetCentralizedAction::class, 'model_prefix' => 'presupuesto',
                'slug_alt' => 'compromisos.actualizar',
                'short_description' => 'Actualizar Compromisos',
            ],
            [
                'name' => 'Eliminar Compromisos',
                'slug' => 'budget.compromise.destroy',
                'description' => 'Acceso para eliminar Compromisos',
                'model' => BudgetCentralizedAction::class, 'model_prefix' => 'presupuesto',
                'slug_alt' => 'compromisos.eliminar',
                'short_description' => 'Eliminar Compromisos',
            ],
            [
                'name' => 'Aprobar Compromisos',
                'slug' => 'budget.compromise.approve',
                'description' => 'Acceso para aprobar Compromisos',
                'model' => BudgetCompromise::class, 'model_prefix' => 'presupuesto',
                'slug_alt' => 'compromisos.aprobar',
                'short_description' => 'Aprobar Compromisos',
            ],
            [
                'name' => 'Anular Compromisos',
                'slug' => 'budget.compromise.cancel',
                'description' => 'Acceso para anular Compromisos',
                'model' => BudgetCompromise::class, 'model_prefix' => 'presupuesto',
                'slug_alt' => 'compromisos.anular',
                'short_description' => 'Eliminar Compromisos',
            ],
            /* Permisos de reportes. */
            [
                'name' => 'Obtener Mayor analítico',
                'slug' => 'budget.analyticalmajor.index',
                'description' => 'Acceso para obtener Mayor analítico',
                'model' => '',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'AnalyticalMajor.listado',
                'short_description' => 'Acceder al Mayor analítico',
            ],
            [
                'name' => 'Obtener Disponibilidad Presupuestaria',
                'slug' => 'budget.budgetavailability.index',
                'description' => 'Acceso para obtener Disponibilidad Presupuestaria',
                'model' => '',
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'budgetAvailability.listado',
                'short_description' => 'Acceder al Disponibilidad Presupuestaria',
            ],
            [
                'name' => 'Obtener presupuesto formulado',
                'slug' => 'budget.formulated.index',
                'description' => 'Acceso para obtener presupuesto formulado',
                'model' => '', 'model_prefix' => 'presupuesto',
                'slug_alt' => 'formulated.index',
                'short_description' => 'Acceder al Mayor presupuesto formulado',
            ],
            /* Permisos para el panel de control de presupuesto */
            [
                'name'              => 'Vista principal del dashboard del módulo de presupuesto',
                'slug'              => 'budget.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'presupuesto',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de presupuesto'
            ],
        ];

        $permissions = array_merge($permissions, [
            [
                'name' => 'Notificar gestión de cuentas presupuestarias',
                'slug' => 'notify.budget.account',
                'description' => 'Notificar sobre gestión de datos de cuentas presupuestarias',
                'model' => BudgetAccount::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.cuentas',
                'short_description' => 'notificar la gestión de cuentas presupuestarias',
            ],
            [
                'name' => 'Notificar gestión de acciones centralizadas',
                'slug' => 'notify.budget.centralizedactions',
                'description' => 'Notificar sobre gestión de datos de acciones centralizadas',
                'model' => BudgetCentralizedAction::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.acciones.centralizadas',
                'short_description' => 'notificar la gestión de acciones centralizadas',
            ],
            [
                'name' => 'Notificar gestión de compromisos',
                'slug' => 'notify.budget.compromise',
                'description' => 'Notificar sobre gestión de datos de compromisos',
                'model' => BudgetCompromise::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.compromiso',
                'short_description' => 'notificar la gestión de compromisos',
            ],
            [
                'name' => 'Notificar etapa de compromiso',
                'slug' => 'notify.budget.stage',
                'description' => 'Notificar sobre etapas de compromisos. Precomprometido, Programado, Comprometido, Causado o Pagado',
                'model' => BudgetStage::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.etapa',
                'short_description' => 'notificar la etapa de compromisos',
            ],
            [
                'name' => 'Notificar gestión de modificaciones presupuestarias',
                'slug' => 'notify.budget.modification',
                'description' => 'Notificar sobre gestión de datos de modificaciones presupuestarias',
                'model' => BudgetModification::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.modificacion',
                'short_description' => 'notificar la gestión de modificaciones presupuestarias',
            ],
            [
                'name' => 'Notificar gestión de proyectos',
                'slug' => 'notify.budget.project',
                'description' => 'Notificar sobre gestión de datos de proyectos',
                'model' => BudgetProject::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.proyecto',
                'short_description' => 'notificar la gestión de proyectos',
            ],
            [
                'name' => 'Notificar gestión de acciones específicas',
                'slug' => 'notify.budget.specificaction',
                'description' => 'Notificar sobre gestión de datos de acciones específicas',
                'model' => BudgetSpecificAction::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.accion.especifica',
                'short_description' => 'notificar la gestión de acciones específicas',
            ],
            [
                'name' => 'Notificar gestión de formulaciones de presupuesto',
                'slug' => 'notify.budget.subspecificformulation',
                'description' => 'Notificar sobre gestión de datos de formulaciones de presupuesto',
                'model' => BudgetSubSpecificFormulation::class,
                'model_prefix' => 'presupuesto',
                'slug_alt' => 'notificar.presupuesto.formulacion',
                'short_description' => 'notificar la gestión de formulaciones de presupuesto',
            ],
        ]);

        $budgetRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'], 'short_description' => $permission['short_description'],
                ]
            );

            $budgetRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
