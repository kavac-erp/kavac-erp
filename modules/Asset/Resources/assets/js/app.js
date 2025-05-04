/**
 *--------------------------------------------------------------------------
 * App Scripts
 *--------------------------------------------------------------------------
 *
 * Scripts del Modulo de Bienes a compilar por la aplicación
 */

/**
 * Componente para mostrar listado del clasificador de Bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-clasifications", () =>
    import(
        /* webpackChunkName: "asset-clasifications" */
        "./components/settings/AssetClasificationComponent.vue"
    )
);

/**
 * Componente para la gestión de Tipos de Bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-types", () =>
    import(
        /* webpackChunkName: "asset-types" */
        "./components/settings/AssetTypesComponent.vue"
    )
);

/**
 * Componente para la gestión de las Categorías de Bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-categories", () =>
    import(
        /* webpackChunkName: "asset-categories" */
        "./components/settings/AssetCategoriesComponent.vue"
    )
);

/**
 * Componente para la gestión de las Subcategorías de Bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-subcategories", () =>
    import(
        /* webpackChunkName: "asset-subcategories" */
        "./components/settings/AssetSubcategoriesComponent.vue"
    )
);

/**
 * Componente para la gestión de las Categorías Específicas de Bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-specific-categories", () =>
    import(
        /* webpackChunkName: "asset-specific-categories" */
        "./components/settings/AssetSpecificCategoriesComponent.vue"
    )
);

/**
 * Componente para la gestión de las Condiciones Físicas de un Bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-conditions", () =>
    import(
        /* webpackChunkName: "asset-conditions" */
        "./components/settings/AssetConditionsComponent.vue"
    )
);

/**
 * Componente para la gestión de los Status de Uso de un Bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-status", () =>
    import(
        /* webpackChunkName: "asset-status" */
        "./components/settings/AssetStatusComponent.vue"
    )
);

/**
 * Componente para la gestión de la Función de Uso de un Bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-use-functions", () =>
    import(
        /* webpackChunkName: "asset-use-functions" */
        "./components/settings/AssetUseFunctionsComponent.vue"
    )
);

/**
 * Componente para la gestión del Tipo de Adquisición de un Bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-acquisition-types", () =>
    import(
        /* webpackChunkName: "asset-acquisition-types" */
        "./components/settings/AssetAcquisitionTypesComponent.vue"
    )
);

/**
 * Componente para la gestión del método de depreciación de un Bien
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 */
Vue.component("asset-depreciation-methods", () =>
    import(
        /* webpackChunkName: "asset-depreciation-methods" */
        "./components/settings/AssetDepreciationMethodsComponent.vue"
    )
);

/**
 * Componente para la gestión de los Depósitos de bienes
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
 */
Vue.component("asset-storages", () =>
    import(
        /* webpackChunkName: "asset-storages" */
        "./components/settings/AssetStoragesComponent.vue"
    )
);

/**
 * Componente para la gestion de edificaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component("asset-buildings", () =>
    import(
        /* webpackChunkName: "asset-buildings" */
        "./components/settings/AssetBuildingsComponent.vue"
    )
);

/**
 * Componente para la gestion de pisos o niveles de edificaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component("asset-floors", () =>
    import(
        /* webpackChunkName: "asset-floors" */
        "./components/settings/AssetFloorsComponent.vue"
    )
);

/**
 * Componente para la gestion de secciones de pisos o niveles de edificaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 */
Vue.component("asset-sections", () =>
    import(
        /* webpackChunkName: "asset-sections" */
        "./components/settings/AssetSectionsComponent.vue"
    )
);

/**
 * Componente para gestionar las solicitudes de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-request-create", () =>
    import(
        /* webpackChunkName: "asset-request-create" */
        "./components/requests/AssetRequestCreateComponent.vue"
    )
);
/**
 * Componente para mostrar la información de una solicitud registrada
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-show", () =>
    import(
        /* webpackChunkName: "asset-show" */
        "./components/requests/AssetRequestInfoComponent.vue"
    )
);

/**
 * Componente para gestionar las asignaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-asignation-create", () =>
    import(
        /* webpackChunkName: "asset-asignation-create" */
        "./components/asignations/AssetAsignationCreateComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de las asignaciones registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-asignation-list", () =>
    import(
        /* webpackChunkName: "asset-asignation-list" */
        "./components/asignations/AssetAsignationListComponent.vue"
    )
);

/**
 * Componente para mostrar la información de una asignacion registrada
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-asignation-info", () =>
    import(
        /* webpackChunkName: "asset-assignation-info" */
        "./components/asignations/AssetAsignationInfoComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de entregas registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-asignation-delivery-list", () =>
    import(
        /* webpackChunkName: "asset-asignation-delivery-list" */
        "./components/asignations/AssetAsignationDeliveryListComponent.vue"
    )
);

/**
 * Componente para gestinar una solicitud de entrega de bienes asignados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-asignation-deliver-equipment", () =>
    import(
        /* webpackChunkName: "asset-asignation-deliver-equipment" */
        "./components/asignations/AssetAsignationDeliverEquipment.vue"
    )
);

/**
 * Componente para gestinar las actas de asignación, entrega o desincorporación de un bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-manage-record-component", () =>
    import(
        /* webpackChunkName: "asset-manage-record-component" */
        "./components/manageRecord/AssetManageRecordComponent.vue"
    )
);

/**
 * Componente para gestionar las desincorporaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-disincorporation-create", () =>
    import(
        /* webpackChunkName: "asset-desincorporation-create" */
        "./components/disincorporations/AssetDisincorporationCreateComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de las desincorporaciones registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-disincorporation-list", () =>
    import(
        /* webpackChunkName: "asset-desincorporation-list" */
        "./components/disincorporations/AssetDisincorporationListComponent.vue"
    )
);

/**
 * Componente para mostrar la información de una desincorporación registrada
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-disincorporation-info", () =>
    import(
        /* webpackChunkName: "asset-desincorporation-info" */
        "./components/disincorporations/AssetDisincorporationInfoComponent.vue"
    )
);

/**
 * Componente para gestionar el ingreso manual de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-register", () =>
    import(
        /* webpackChunkName: "asset-register" */
        "./components/registers/AssetRegisterComponent.vue"
    )
);

/**
 * Componente para gestionar el ingreso manual de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-create", () =>
    import(
        /* webpackChunkName: "asset-create" */
        "./components/registers/AssetCreateComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de bienes institucionales registrados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-list", () =>
    import(
        /* webpackChunkName: "asset-list" */
        "./components/registers/AssetListComponent.vue"
    )
);

/**
 * Componente para mostrar la información de un bien registrado
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-info", () =>
    import(
        /* webpackChunkName: "asset-info" */
        "./components/registers/AssetInfoComponent.vue"
    )
);

/**
 * Componente para solicitar prorroga en la entrega de solicitudes registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-extension", () =>
    import(
        /* webpackChunkName: "asset-extension" */
        "./components/requests/AssetRequestExtensionComponent.vue"
    )
);

/**
 * Componente para la gestion de eventos ocurridos en equipos solicitados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-events", () =>
    import(
        /* webpackChunkName: "asset-events" */
        "./components/requests/AssetRequestEventComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de las solicitudes registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-request-list", () =>
    import(
        /* webpackChunkName: "asset-request-list" */
        "./components/requests/AssetRequestListComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de solicitudes pendientes registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-request-list-pending", () =>
    import(
        /* webpackChunkName: "asset-request-list-pending" */
        "./components/requests/AssetRequestListPendingComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de solicitudes de entregas registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-request-delivery-list", () =>
    import(
        /* webpackChunkName: "asset-request-delivery-list" */
        "./components/requests/AssetRequestDeliveryListComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de depreciación de bienes institucionales registrados
 *
 * @author Fabian Palmera <fpalmera@cenditel.gob.ve>
 */
Vue.component("asset-depreciation-list", () =>
    import(
        /* webpackChunkName: "asset-depreciation-list" */
        "./components/depreciations/AssetDepreciationListComponent.vue"
    )
);

/**
 * Componente para mostrar la información de la depreciación de un bien registrado
 *
 * @author Fabian Palmera <fpalmera@cenditel.gob.ve>
 */
Vue.component("asset-depreciation-info", () =>
    import(
        /* webpackChunkName: "asset-depreciation-info" */
        "./components/depreciations/AssetDepreciationInfoComponent.vue"
    )
);

/**
 * Componente para registrar la depreciación de un bien registrado
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component("asset-depreciation-create", () =>
    import(
        /* webpackChunkName: "asset-depreciation-create" */
        "./components/depreciations/AssetDepreciationCreateComponent.vue"
    )
);

/**
 * Componente para gestionar la creación de reportes de inventario
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-report-create", () =>
    import(
        /* webpackChunkName: "asset-report-create" */
        "./components/reports/AssetReportCreateComponent.vue"
    )
);

/**
 * Componente para gestionar la creación de reportes de depreciacion acumulad
 *
 * @author Manuel Zambrano
 */
Vue.component("asset-report-depreciation", () =>
    import("./components/reports/AssetReportDepreciationComponent.vue")
);

/**
 * Componente para gestionar la creación de reportes de depreciacion acumulad
 *
 * @author Manuel Zambrano
 */
Vue.component("asset-report-depreciation-table", () =>
    import("./components/reports/AssetReportDepreciationTableComponent.vue")
);

/**
 * Componente para mostrar un listado del historico del inventario
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-inventory-history-list", () =>
    import(
        /* webpackChunkName: "asset-inventory-history-list" */
        "./components/inventories/AssetInventoryHistoryListComponent.vue"
    )
);

/**
 * Componente para mostrar los gráficos del panel de control asociados al módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-dashboard-graphs", () =>
    import(
        /* webpackChunkName: "asset-dashboard-graphs" */
        "./components/dashboard/AssetDashboardGraphsComponent.vue"
    )
);

/**
 * Componente para mostrar la información de una operación asociada al módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-operations-history-info", () =>
    import(
        /* webpackChunkName: "asset-operations-history-info" */
        "./components/dashboard/AssetOperationsHistoryInfoComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de las operaciones registradas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component("asset-operations-history-list", () =>
    import(
        /* webpackChunkName: "asset-operations-history-list" */
        "./components/dashboard/AssetOperationsHistoryListComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de los bienes registrados para ajustes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component("asset-adjustment-list", () =>
    import(
        /* webpackChunkName: "asset-adjustment-list" */
        "./components/adjustments/AssetAdjustmentsListComponent.vue"
    )
);

/**
 * Componente para mostrar un listado de los bienes registrados para ajustes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component("asset-adjustment-create", () =>
    import(
        /* webpackChunkName: "asset-adjustment-create" */
        "./components/adjustments/AssetAdjustmentCreateComponent.vue"
    )
);

/**
 * Componente para la gestión de gráficos estadísticos del módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.component(
    "asset-graph-charts",
    require("./components/dashboard/AssetGraphChartsComponent.vue").default
);

/**
 * Componente para la gestión de las ramas de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('asset-supplier-branches-fix', () =>
    import (
        /* webpackChunkName: "asset-supplier-branches-fix" */
        './components/settings/AssetSupplierBranchComponent.vue'));

/**
 * Componente para la gestión de los objetos de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('asset-supplier-objects-fix', () =>
    import (
        /* webpackChunkName: "asset-supplier-objects-fix" */
        './components/settings/AssetSupplierObjectsComponent.vue'));

/**
 * Componente para la gestión de las especialidades de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('asset-supplier-specialties-fix', () =>
    import (
        /* webpackChunkName: "asset-supplier-specialties-fix" */
        './components/settings/AssetSupplierSpecialtyComponent.vue'));

/**
 * Componente para la gestión de los tipos de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('asset-supplier-types-fix', () =>
    import (
        /* webpackChunkName: "asset-supplier-types-fix" */
        './components/settings/AssetSupplierTypeComponent.vue'));

/**
 * Componente para la gestión de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('asset-suppliers-list', () =>
    import (
        /* webpackChunkName: "asset-suppliers-list" */
        './components/suppliers/AssetSupplierListComponent.vue'));

/**
 * Componente para la visualizacion proveedor
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('asset-suppliers-show', () =>
    import (
        /* webpackChunkName: "asset-suppliers-show" */
        './components/suppliers/AssetSupplierShowComponent.vue'));

/**
 *  Componente generico del modulo para mostrar errores
 *
 * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 */
Vue.component('asset-show-errors', () =>
    import (
        /* webpackChunkName: "asset-show-errors" */
        './components/settings/AssetShowErrorsComponent.vue'));

/**
 * Opciones de configuración global del módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 */
Vue.mixin({
    methods: {
        /**
         * Obtiene los datos de los tipos de bienes institucionales registrados
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getAssetTypes() {
            const vm = this;
            await axios.get(`${window.app_url}/asset/get-types`).then((response) => {
                vm.asset_types = response.data;
            });
            if (vm.record.asset_type && vm.record.id) {
                if (vm.record.asset_type_id == "") {
                    vm.record.asset_type_id = vm.record.asset_type.id;
                }
            }
        },
        /**
         * Obtiene los datos de las categorias generales de los bienes institucionales registrados
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getAssetCategories() {
            var vm = this;
            vm.asset_categories = [];

            if (vm.record.asset_type_id) {
                await axios
                    .get(
                        `${window.app_url}/asset/get-categories/${vm.record.asset_type_id}`
                    )
                    .then(function (response) {
                        vm.asset_categories = response.data;
                    });
                if (
                    vm.record.asset_category &&
                    (vm.record.id || vm.editIndex == true)
                ) {
                    vm.record.asset_category_id = vm.record.asset_category.id;
                }
            }
        },
        /**
         * Obtiene los datos de las subcategorias de los bienes institucionales registrados
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getAssetSubcategories() {
            var vm = this;
            vm.asset_subcategories = [];

            if (vm.record.asset_category_id) {
                await axios
                    .get(
                        `${window.app_url}/asset/get-subcategories/${vm.record.asset_category_id}`
                    )
                    .then(function (response) {
                        vm.asset_subcategories = response.data;
                    });
                if (
                    vm.record.asset_subcategory &&
                    (vm.record.id || vm.editIndex == true)
                ) {
                    vm.record.asset_subcategory_id = vm.record.asset_subcategory.id;
                }
            }
        },
        /**
         * Obtiene los datos de las categorias específicas de los bienes institucionales registrados
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        async getAssetSpecificCategories() {
            var vm = this;
            vm.asset_specific_categories = [];

            if (vm.record.asset_subcategory_id) {
                await axios
                    .get(
                        `${window.app_url}/asset/get-specific-categories/${vm.record.asset_subcategory_id}`
                    )
                    .then(function (response) {
                        vm.asset_specific_categories = response.data;
                    });
                if (
                    vm.record.asset_specific_category &&
                    (vm.record.id || vm.editIndex == true)
                ) {
                    vm.record.asset_specific_category_id =
                        vm.record.asset_specific_category.id;
                }
            }
        },
        /**
         *--------------------------------------------------------------------------
         * Módulo Payroll
         *--------------------------------------------------------------------------
         *
         * Operaciones del modulo de talento humano requeridas por el módulo de bienes
         */

        getPayrollStaffs() {
            this.payroll_staffs = [];
            axios
                .get(`${window.app_url}/asset/get-payroll-staffs`)
                .then((response) => {
                    this.payroll_staffs = response.data;
                });
        },

        getPayrollStaffInfo(id) {
            this.payroll_staff_info = [];
            axios
                .get(`${window.app_url}/asset/get-payroll-staffs-info/${id}`)
                .then((response) => {
                    this.payroll_positions = [response.data[0]];
                    this.payroll_position_types = [response.data[1]];
                    this.departments = [response.data[2]];
                });
        },
        /**
         * Método que carga la información de edificaciones
         *
         * @author  Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        async getBuildings() {
            const vm = this;
            await axios.get(`${window.app_url}/asset/get-buildings`).then(response => {
                vm.buildings = response.data;
            }).catch(error => {
                console.error(error);
            });
        },
        /**
         * Método que carga la información de los niveles asociados a una edificación
         *
         * @author  Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         * @param building_id id de la edificación
         */
        async getBuildingFloors() {
            const vm = this;
            if (vm.record.building_id !== '') {
                await axios
                    .get(`${window.app_url}/asset/get-building-floors/${vm.record.building_id}`)
                    .then((response) => {
                        vm.floors = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
            }
        },
        /**
         * Método que carga la información de las secciones asociadas a un nivel de una edificación
         *
         * @author  Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         * @param floor_id id del nivel de la edificación
         */
        async getFloorSections() {
            const vm = this;
            if (vm.record.floor_id !== '') {
                await axios
                    .get(`${window.app_url}/asset/get-floor-sections/${vm.record.floor_id}`)
                    .then((response) => {
                        vm.sections = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
            }
        },
        /**
         * Método que obtiene la cantidad de secciones para un codigo dado
         *
         * @param {string} code El codigo del cual se retorna la cantidad de secciones asociadas a el
         *
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        async getSectionAmount(code) {
            const vm = this;
            if (typeof (code != 'undefined')) {
                await axios
                    .get(`${window.app_url}/asset/get-section-amount/${code}`)
                    .then((response) => {
                        if (typeof (response.data.records != 'undefined')) {
                            vm.currentSectionAmount = response.data.amount;
                        }
                    }).catch(error => {
                        console.error(error);
                    });
            }
        },
    },
});
