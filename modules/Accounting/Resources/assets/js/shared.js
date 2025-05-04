/**
 * Componente para generar asientos contable partiendo de otros registros relacionados a cuentas patrimoniales
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-generator", () =>
    import(
        /* webpackChunkName: "accounting-entry-generator" */
        "./components/entries/AccountingEntryGeneratorComponent.vue"
    )
);

/**
 * Componente generico del modulo de contabilidad para mostrar errores
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-show-errors", () =>
    import(
        /* webpackChunkName: "accounting-show-errors" */
        "./components/AccountingErrorsComponent.vue"
    )
);

/**
 * Componente para la creación de asientos contable
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-form", () =>
    import(
        /* webpackChunkName: "accounting-entry-form" */
        "./components/entries/AccountingFormComponent.vue"
    )
);

/**
 * Componente para cargar la tabla de cuentas patrimoniales para el asiento contable
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component("accounting-entry-form-account", () =>
    import(
        /* webpackChunkName: "accounting-entry-form-account" */
        "./components/entries/AccountingAccountFormsComponent.vue"
    )
);

/**
 * Evento global Bus del modulo de Contabilidad
 */
window.EventBus = new Vue();
