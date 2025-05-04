import BudgetAccountList from './components/BudgetAccountsListComponent.vue';
import BudgetProjectList from './components/BudgetProjectsListComponent.vue';
import BudgetFinancementTypes from './components/BudgetFinancementTypesComponent.vue';
import BudgetFinancementSources from './components/BudgetFinancementSourcesComponent.vue';
import BudgetCentralizedActionsList from './components/BudgetCentralizedActionsListComponent.vue';
import BudgetCentralizedActionsInfo from './components/BudgetCentralizedActionsInfoComponent.vue';
import BudgetSpecificActionsList from './components/BudgetSpecificActionsListComponent.vue';
import BudgetSpecificActionsInfo from './components/BudgetSpecificActionsInfoComponent.vue';
import BudgetModificationListData from './components/BudgetModificationListComponentsData.vue';
import BudgetReductionList from './components/BudgetReductionListComponent.vue';
import BudgetReductionListModal from './components/BudgetReductionListModalComponents.vue';
import BudgetTransferList from './components/BudgetTransferListComponent.vue';
import BudgetTransferListModal from './components/BudgetTransferListModalComponents.vue';
import BudgetSubSpecificFormulationList from './components/BudgetSubSpecificFormulationListComponent.vue';
import BudgetSubSpecificFormulation from './components/BudgetSubSpecificFormulationComponent.vue';
import BudgetModification from './components/BudgetModificationComponent.vue';
import BudgetModificationList from './components/BudgetModificationListComponent.vue';
import BudgetCompromisesList from './components/BudgetCompromisesListComponent.vue';
import BudgetCompromise from './components/BudgetCompromiseComponent.vue';
import BudgetCompromiseInfo from './components/BudgetCompromiseInfoComponent.vue';
import BudgetAvailability from './components/BudgetAvailabilityComponent.vue';
import BudgetProjectsReport from './components/reports/BudgetProjectsReportComponent.vue';
import BudgetFormulatedReport from './components/reports/BudgetFormulatedReportComponent.vue';
import BudgetAnalyticalMajor from './components/BudgetAnalyticalMajorComponent.vue';
import BudgetCancelCompromise from './components/BudgetCancelCompromiseComponent.vue'

/**
 * Componente para mostrar listado del clasificador de cuentas presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-accounts-list', BudgetAccountList);

/**
 * Componente para la gestión de los tipos de financiamiento.
 *
 * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
Vue.component('budget-financement-types', BudgetFinancementTypes);

/**
 * Componente para la gestión de las fuentes de financiamiento.
 *
 * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
Vue.component('budget-financement-sources', BudgetFinancementSources);

/**
 * Componente para mostrar listado de proyectos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-projectlist', BudgetProjectList);

/**
 * Componente para mostrar listado de acciones centralizadas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-centralized-actions-list', BudgetCentralizedActionsList);

/**
 * Componente para mostrar listado de acciones centralizadas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-centralized-actions-info', BudgetCentralizedActionsInfo);

/**
 * Componente para mostrar listado de acciones centralizadas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-actsplist', BudgetSpecificActionsList);

/**
 * Componente para detallar la información de acciones específicas
 *
 * @author Pedro Contreras <pdrocont@gmail.com>
 */
Vue.component('budget-info-specific-actions', BudgetSpecificActionsInfo);

/**
 * Componente para detallar la información de acciones específicas
 *
 * @author Jesús Paredes <danielparedessotillo13@gmail.com>
 */
Vue.component('budget-modinfo', BudgetModificationListData);

/**
 * Componente para detallar la información de acciones específicas
 *
 * @author Oscar Gonzales <>
 */
Vue.component('budget-modreduction', BudgetReductionList);

Vue.component('budget-reduction-modal', BudgetReductionListModal);

/**
 * Componente para detallar la información de acciones específicas
 *
 * @author Daniel Ordaz <danielordaz61@gmail.com>
 */
Vue.component('budget-modtransfer', BudgetTransferList);

Vue.component('budget-modtinfo', BudgetTransferListModal);

/**
 * Componente para mostrar listado de formulaciones de presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-formulation-list', BudgetSubSpecificFormulationList);

/**
 * Componente para mostrar formulario de formulación de presupuesto por sub específica
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-formulation-subspecific', BudgetSubSpecificFormulation);

/**
 * Componente para getionar las modificaciones presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budgetmod', BudgetModification);

/**
 * Componente para mostrar listado de créditos adicionales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budgetmodlist', BudgetModificationList);

/**
 * Componente para agregar cuentas al registro o actualización de créditos adicionales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
//Vue.component('budget-aditional-credit-add', require('./components/BudgetAditionalCreditAddComponent.vue').default);

/**
 * Componente para mostrar listado de compromisos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-compromise-list', BudgetCompromisesList);

/**
 * Componente para getionar los compromisos presupuestarios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('budget-compromise', BudgetCompromise);

/**
 * Componente para mostrar listado de compromisos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 */
Vue.component('budget-compromise-info', BudgetCompromiseInfo);

/**
 * Componente para mostrar el formulario de disponibilidad presupuestaria
 *
 * @author Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
 */
Vue.component('budget-availability', BudgetAvailability);

/**
 * Componente para mostrar lista de proyectos
 *
 * @author Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
 */
Vue.component('budget-projects-report', BudgetProjectsReport);

/**
 * Componente para mostrar lista de proyectos
 *
 * @author Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
 */
Vue.component('budget-formulated-report', BudgetFormulatedReport);

/**
 * Componente para mostrar lista de proyectos
 *
 * @author José Briceño <josejorgebriceno9@gmail.com> | <jonathanalvarado1407@gmail.com>
 */
Vue.component('budget-analytical-major', BudgetAnalyticalMajor);

/**
 * Componente para realizar el proceso de aunulación de un compromiso
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 */
Vue.component('budget-cancel-compromise', BudgetCancelCompromise);

/**
 * Opciones de configuración global del módulo de presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.mixin({
    data() {
        return {
            /** @type {String} Especifica el año de ejercicio presupuestario en curso */
            execution_year: ''
        };
    },
    methods: {
        /**
         * Obtiene la lista con los detalles de las acciones específicas.
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getSpecificActionDetail(id) {
            const response = await axios.get(
                `${window.app_url}/budget/detail-specific-actions/${id}`
            );
            return response.data;
        },
        /**
         * Obtiene la lista con los detalles de las cuentas.
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAccountDetail(id) {
            const response = await axios.get(
                `${window.app_url}/budget/detail-accounts/${id}`
            );
            return response.data;
        },
        /**
         * Obtiene la lista de los tipos de financiamiento.
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getFinancementTypes() {
            const vm = this;
            await axios.get(`${vm.app_url}/budget/get-financement-types`).then(response => {
                vm.financementTypes = response.data;
            }).catch(error => {
                vm.logs('Budget/Resources/assets/js/_all.js', 90, error, 'getFinancementTypes');
            });
        },
        /**
         * Obtiene la lista de los tipos de financiamiento.
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getFinancementSources() {
            const vm = this;
            vm.financementSources = [];
            const financement_type_id = this.record.financement_type_id || '';
            if (financement_type_id) {
            await axios.get(`${vm.app_url}/budget/get-financement-sources/${financement_type_id}`).then(response => {
                    vm.financementSources = response.data;
                }).catch(error => {
                    vm.logs('Finance/Resources/assets/js/_all.js', 90, error, 'getFinancementSources');
                });
            }
        },
    },
    mounted() {
        // Agregar instrucciones para determinar el año de ejecución
    }
});
