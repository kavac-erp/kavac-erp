/**
 * Componente para la gestión de tipos de productos
 *
 * @author Francisco Escala <fjescala@gmail.com>
 */

Vue.component("project-tracking-type-products", () =>
    import(
        /* webpackChunkName: "project-tracking-type-products" */
        "./components/settings/ProjectTrackingTypeProductsComponent.vue"
    )
);

/**
 * Componente para la gestión de Actividades
 *
 * @author Francisco Escala <fjescala@gmail.com>
 */

Vue.component("project-tracking-activity-config", () =>
    import(
        /* webpackChunkName: "project-tracking-activity-config" */
        "./components/settings/ProjectTrackingActivityConfigComponent.vue"
    )
);
/**
 *  Componente para la gestión de tipos de proyectos
 *  @author José Jorge Briceño <josejorgebriceno9@gmail.com>
 */
Vue.component("project-tracking-project-type", () =>
    import("./components/settings/ProjectTrackingProjectTypeComponent.vue")
);

/**
 *  Componente para la gestión de configuración de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-projects", () =>
    import("./components/settings/ProjectTrackingProjectsComponent.vue")
);

/**
 *  Componente para detallar los registros de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-project-info", () =>
    import("./components/settings/ProjectTrackingProjectInfoComponent.vue")
);

/**
 *  Componente para la gestión de configuración de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com>
 */
Vue.component("project-tracking-staff-classification", () =>
    import(
        "./components/settings/ProjectTrackingStaffClassificationsComponent.vue"
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de cargos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component("project-tracking-positions", () =>
    import(
        /* webpackChunkName: "projecttracking-positions" */
        "./components/settings/ProjectTrackingPositionsComponent.vue"
    )
);

/**
 *  Componente para la gestión de configuración de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com>
 */
Vue.component("project-tracking-personal-register", () =>
    import(
        /* webpackChunkName: "project-tracking-personal-register" */
        "./components/settings/ProjectTrackingPersonalRegisterComponent.vue"
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de las dependencias
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component("project-tracking-dependencies", () =>
    import(
        /* webpackChunkName: "projecttracking-dependencies" */
        "./components/settings/ProjectTrackingDependencyComponent.vue"
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de las prioridades
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component("project-tracking-priorities", () =>
    import(
        /* webpackChunkName: "projecttracking-priorities" */
        "./components/settings/ProjectTrackingPriorityComponent.vue"
    )
);

/**
 * Componente para listar, crear, actualizar y borrar datos de los estatus de actividades
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 */
Vue.component("project-tracking-activity-status", () =>
    import(
        /* webpackChunkName: "projecttracking-activity-status" */
        "./components/settings/ProjectTrackingActivityStatusComponent.vue"
    )
);

Vue.component("project-tracking-subprojects", () =>
    import(
        /* webpackChunkName: "projecttracking-subprojects" */
        "./components/settings/ProjectTrackingSubProjectComponent.vue"
    )
);

/**
 * Componente para mostrar listado de plan de actividades
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component("project-tracking-activity-plan-list", () =>
    import(
        /* webpackChunkName: "project-tracking-activity-plan-list" */
        "./components/activity_plans/ProjectTrackingActivityPlanListComponent.vue"
    )
);

/**
 * Componente para registrar o actualizar plan de actividades
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 */
Vue.component("project-tracking-activity-plan", () =>
    import(
        /* webpackChunkName: "project-tracking-activity-plan" */
        "./components/activity_plans/ProjectTrackingActivityPlanComponent.vue"
    )
);

/**
 * Componente para mostrar la información detallada de plan de actividades
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component("project-tracking-activity-plan-info", () =>
    import(
        /* webpackChunkName: "project-tracking-activity-plan-info" */
        "./components/activity_plans/ProjectTrackingActivityPlanInfoComponent.vue"
    )
);

Vue.component("project-tracking-activity-plan-team-info", () =>
    import(
        /* webpackChunkName: "project-tracking-activity-plan-team-info" */
        "./components/activity_plans/ProjectTrackingActivityPlanTeamInfoComponent.vue"
    )
);

Vue.component("project-tracking-activity-plan-activity-info", () =>
    import(
        /* webpackChunkName: "project-tracking-activity-plan-activity-info" */
        "./components/activity_plans/ProjectTrackingActivityPlanActivityInfoComponent.vue"
    )
);

/**
 *  Componente para la gestión de configuración de productos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-products", () =>
    import("./components/settings/ProjectTrackingProductsComponent.vue")
);

/**
 *  Componente para detallar los registros de productos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-product-info", () =>
    import("./components/settings/ProjectTrackingProductInfoComponent.vue")
);

/**
 *  Componente para la gestión de configuración de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-tasks", () =>
    import(
        /* webpackChunkName: "project-tracking-tasks" */
        "./components/tasks/ProjectTrackingTasksComponent.vue"
    )
);

/**
 *  Componente para la gestión de configuración de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-tasks-list", () =>
    import(
        /* webpackChunkName: "project-tracking-tasks-list" */
        "./components/tasks/ProjectTrackingTasksListComponent.vue"
    )
);

/**
 *  Componente para la gestión de configuración de proyectos
 *  @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 */
Vue.component("project-tracking-task-info", () =>
    import(
        /* webpackChunkName: "project-tracking-task-info" */
        "./components/tasks/ProjectTrackingTaskInfoComponent.vue"
    )
);

/*  Componente para detallar los registros de productos
 *  @author Wilmari Vielma<wilmari.vielma@gmail.com>
 */
Vue.component("project-tracking-delivery-status", () =>
    import(
        /* webpackChunkName: "project-tracking-delivery-status" */
        "./components/settings/ProjectTrackingDeliveryStatusComponent.vue"
    )
);

/**
 * Opciones de configuración Seguimieto
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.mixin({
    methods: {
        async getTypesProducts() {
            const vm = this;
            await axios
                .get(`${window.app_url}/projecttracking/get-type-products`)
                .then((response) => {
                    vm.type_products = response.data;
                });
            if (vm.record.type_products && vm.record.id) {
                if (vm.record.type_product_id == "") {
                    vm.record.type_product_id = vm.record.type_products.id;
                }
            }
        },
        /**
         * Obtiene los datos de los trabajadores registrados
         *
         * @author William Páez <wpaez@cenditel.gob.ve>
         * @author Ing. Roldan Vargas <rvargas at cenditel.gob.ve>
         *
         * @param string filter establece una condición bajo la cual filtrar los resultados
         */
        async getPayrollStaffs(type = "") {
            this.payroll_staffs = [];
            await axios
                .get(`${window.app_url}/payroll/get-staffs/${type}`)
                .then((response) => {
                    this.payroll_staffs = response.data;
                });
        },
    },
});
