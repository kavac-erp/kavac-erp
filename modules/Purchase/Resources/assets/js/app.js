/**
 * Componente para la gestión de las ramas de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('purchase-supplier-branches-fix', () =>
    import (
        /* webpackChunkName: "purchase-supplier-branches-fix" */
        './components/PurchaseSupplierBranchComponent.vue'));

/**
 * Componente para la gestión de los objetos de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('purchase-supplier-objects-fix', () =>
    import (
        /* webpackChunkName: "purchase-supplier-objects-fix" */
        './components/PurchaseSupplierObjectsComponent.vue'));

/**
 * Componente para la gestión de las especialidades de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('purchase-supplier-specialties-fix', () =>
    import (
        /* webpackChunkName: "purchase-supplier-specialties-fix" */
        './components/PurchaseSupplierSpecialtyComponent.vue'));

/**
 * Componente para la gestión de los tipos de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('purchase-supplier-types-fix', () =>
    import (
        /* webpackChunkName: "purchase-supplier-types-fix" */
        './components/PurchaseSupplierTypeComponent.vue'));

/**
 * Componente para la gestión de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('purchase-suppliers-list', () =>
    import (
        /* webpackChunkName: "purchase-suppliers-list" */
        './components/PurchaseSupplierListComponent.vue'));

/**
 * Componente para la visualizacion proveedor
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-suppliers-show', () =>
    import (
        /* webpackChunkName: "purchase-suppliers-show" */
        './components/PurchaseSupplierShowComponent.vue'));

/**
 * Componente para la gestión de procesos de compras
 */
Vue.component('purchase-processes', () =>
    import (
        /* webpackChunkName: "purchase-processes" */
        './components/PurchaseProcessComponent.vue'));

/**
 * Componente para la gestión de creacion y actualización de requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-requirements', () =>
    import (
        /* webpackChunkName: "purchase-requirements" */
        './components/requirements/PurchaseIndexComponent.vue'));

/**
 * Componente para la gestión de creacion y actualización de requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-requirements-form', () =>
    import (
        /* webpackChunkName: "purchase-requirements-form" */
        './components/requirements/PurchaseFormComponent.vue'));

/**
 * Componente para la visualizacion requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-requirements-show', () =>
    import (
        /* webpackChunkName: "purchase-requirements-show" */
        './components/requirements/PurchaseShowComponent.vue'));

/**
 * Componente para registrar productos en el modulo de almacen
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-warehouse-products', () =>
    import (
        /* webpackChunkName: "purchase-warehouse-products" */
        './components/requirements/PurchaseWarehouseProductoComponent.vue'));

/**
 * Componente para listar los presupuesto base
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-base-budget', () =>
    import (
        /* webpackChunkName: "purchase-base-budget" */
        './components/requirements/base_budget/PurchaseIndexComponent.vue'));

/**
 * Componente para la gestión de creacion y actualización de requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-base-budget-form', () =>
    import (
        /* webpackChunkName: "purchase-budget-form" */
        './components/requirements/base_budget/PurchaseFormComponent.vue'));

/**
 * Componente para la visualizacion requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-base-budget-show', () =>
    import (
        /* webpackChunkName: "purchase-base-budget-show" */
        './components/requirements/base_budget/PurchaseShowComponent.vue'));

/**
 * Componente para la gestión de plan de compras
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-plan-list', () =>
    import (
        /* webpackChunkName: "purchase-plan-list" */
        './components/purchase_plans/PurchaseIndexComponent.vue'));

/**
 * Componente para la gestión de plan de compras
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-plan-form', () =>
    import (
        /* webpackChunkName: "purchase-plan-form" */
        './components/purchase_plans/PurchaseFormComponent.vue'));

/**
 * Componente para la visualizacion requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-plan-show', () =>
    import (
        /* webpackChunkName: "purchase-plan-show" */
        './components/purchase_plans/PurchaseShowComponent.vue'));

/**
 * Componente para la visualizacion requerimientos
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-plan-start-diagnosis', () =>
    import (
        /* webpackChunkName: "purchase-plan-show" */
        './components/purchase_plans/PurchaseStartDiagnosisComponent.vue'));

/**
 * Componente para la gestión de ordend e compra
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *  @author Francisco Escala <fjescala@gmail.com>
 */
Vue.component('purchase-quotation-form-update', () =>
    import (
        /* webpackChunkName: "purchase-quotation-form" */
        './components/quotation/PurchaseFormComponent.vue'));

/**
* Componente para la gestión de  cotizaciones parciales
*
* @author Francisco Escala <fjescala@gmail.com>
*/
Vue.component('purchase-quotation-form-edit', () =>
    import(
        /* webpackChunkName: "purchase-quotation-form-edit" */
        './components/quotation/PurchaseEditFormComponent.vue'));
/**
 * Componente para la gestión de ordend e compra
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-quotation-list', () =>
    import (
        /* webpackChunkName: "purchase-quotation-list" */
        './components/quotation/PurchaseListComponent.vue'));


/**
 * Componente para visualización la orden de compra
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
 */
Vue.component('purchase-quotation-show', () =>
    import (
        /* webpackChunkName: "purchase-quotation-show" */
        './components/quotation/PurchaseShowComponent.vue'));



/**
 * Componente para la gestión de tipos de compras
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-type', () =>
    import (
        /* webpackChunkName: "purchase-type" */
        './components/PurchaseTypeComponent.vue'));

/**
 * Componente para la gestión de tipos de contratacion
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-type-hiring', () =>
    import (
        /* webpackChunkName: "purchase-type-hiring" */
        './components/PurchaseTypeHiringComponent.vue'));

/**
 * Componente para la gestión de tipos de operacion
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-type-operations', () =>
    import (
        /* webpackChunkName: "purchase-type-operations" */
        './components/PurchaseTypeOperationComponent.vue'));

/**
 *  Componente para gestionar la disponibilidad presupuestaria para una orden de compra
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-budgetary-availability', () =>
    import (
        /* webpackChunkName: "purchase-budgetary-availability" */
        './components/budgetary_availability/PurchaseIndexComponent.vue'));

/**
 *  Componente para gestionar el listado de ordenes de compra
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-order-list', () =>
    import (
        /* webpackChunkName: "purchase-order-list" */
        './components/purchase_order/PurchaseListComponent.vue'));

/**
 *  Componente para gestionar el registro y edicion de ordenes de compra
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-order-form', () =>
    import (
        /* webpackChunkName: "purchase-order-form" */
        './components/purchase_order/PurchaseFormComponent'));

/**
 *  Componente para gestionar el listado contrataciones directas de ordenes de compra
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-order-direct-hire-list', () =>
    import (
        /* webpackChunkName: "purchase-order-direct-hire-list" */
        './components/purchase_order/DirectHireListComponent.vue'));

/**
 *  Componente para gestionar la creacion y edicion de contrataciones directas de ordenes de compra
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-order-direct-hire-form', () =>
    import (
        /* webpackChunkName: "purchase-order-direct-hire-form" */
        './components/purchase_order/DirectHireFormComponent.vue'));

/**
 *  Componente para detallar contrataciones directas de ordenes de compra
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-order-direct-hire-show', () =>
    import (
        /* webpackChunkName: "purchase-order-direct-hire-show" */
        './components/purchase_order/DirectHireShowComponent.vue'));

/**
 *  Componente generico del modulo para mostrar errores
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('purchase-show-errors', () =>
    import (
        /* webpackChunkName: "purchase-show-errors" */
        './components/PurchaseShowErrorsComponent.vue'));

/**
 *  Componente para el registro de servicios
 *
 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('purchase-services', () =>
    import (
        /* webpackChunkName: "purchase-services" */
        './components/PurchaseServicesComponent.vue'));

/**
 *  Componente para el registro de productos e insumos
 *
 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('purchase-products', () =>
    import (
        /* webpackChunkName: "purchase-products" */
        './components/PurchaseProductsComponent.vue'));

Vue.component('purchase-general-conditions', () =>
    import (
        /* webpackChunkName: "purchase-general-conditions" */
        './components/PurchaseGeneralConditionsComponent.vue'));

/**
 * Opciones de configuración global del módulo de compras
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.mixin({
    data() {
        return {}
    },
    mounted() {

    },
    methods: {

    }
});