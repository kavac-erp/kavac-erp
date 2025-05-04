const { default: axios } = require('axios');

/**
 * Componente para la gestión de bancos
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-banks', () => import(
    /* webpackChunkName: "finance-banks" */
    './components/FinanceBankComponent.vue'
));

/**
 * Componente para la gestión de archivos de conciliación bancaria.
 *
 * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
Vue.component('finance-bank-reconciliation-files', () => import(
    /* webpackChunkName: "finance-bank-reconciliation-files" */
    './components/FinanceBankReconciliationFilesComponent.vue'
));

/**
 * Componente para la gestión de conciliación bancaria.
 *
 * @author  Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
Vue.component('finance-conciliation', () => import(
    /* webpackChunkName: "finance-conciliation" */
    './components/conciliacion/FinanceConciliationComponent.vue'
));

/**
 * Componente para listar de conciliación bancaria.
 *
 * @author  Juan Rosas <juan.rosasr@gmail.com>
 */
Vue.component('finance-conciliation-list', () => import(
    /* webpackChunkName: "finance-conciliation-list" */
    './components/conciliacion/FinanceConciliationListComponent.vue'
));


/**
 * Componente para la gestión de agencias bancarias
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-payment-methods', () => import(
    /* webpackChunkName: "finance-banking-agencies" */
    './components/FinancePaymentMethodsComponent.vue'
));

/**
 * Componente para la gestión de agencias bancarias
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-banking-agencies', () => import(
    /* webpackChunkName: "finance-banking-agencies" */
    './components/FinanceBankingAgencyComponent.vue'
));

/**
 * Componente para la gestión de tipos de cuenta bancaria
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-account-types', () => import(
    /* webpackChunkName: "finance-account-types" */
    './components/FinanceAccountTypeComponent.vue'
));

/**
 * Componente para la gestión de cuentas bancarias
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-bank-accounts', () => import(
    /* webpackChunkName: "finance-bank-accounts" */
    './components/FinanceBankAccountComponent.vue'
));

/**
 * Componente para la gestión de chequeras
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-checkbooks', () => import(
    /* webpackChunkName: "finance-checkbooks" */
    './components/FinanceCheckBookComponent.vue'
));

/**
 * Componente para gestionar y configurar el diseño del voucher para la impresión de cheques
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-voucher-design', () => import(
    /* webpackChunkName: "finance-voucher-design" */
    './components/FinanceVoucherDesignComponent.vue'
));

/**
 * Componente para listar registros de órdenes de pago
 * 
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-pay-order-list', () => import(
    /* webpackChunkName: "finance-pay-order-list" */
    './components/FinancePayOrderListComponent.vue'
));

/**
 * Componente para gestionar registros de órdenes de pago
 * 
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.component('finance-pay-order', () => import(
    /* webpackChunkName: "finance-pay-order" */
    './components/FinancePayOrderComponent.vue'
));

Vue.component('finance-payment-execute-list', () => import(
    /* webpackChunkName: "finance-payment-execute-list" */
    './components/FinancePaymentExecuteListComponent.vue'
));

Vue.component('finance-payment-execute', () => import(
    /* webpackChunkName: "finance-payment-execute" */
    './components/FinancePaymentExecuteComponent.vue'
));

/**
 * Componente para aprobar de una Emisión de pago
 * 
 * 
 *  @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 */
Vue.component('finance-approve-payment-execute', () => import(
    /* webpackChunkName: "finance-cancel-payment-execute" */
    './components/FinanceApprovePaymentExecuteComponent.vue'
));

/**
 * Componente para anular de una Emisión de pago
 * 
 * 
 *  @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 */
Vue.component('finance-cancel-payment-execute', () => import(
    /* webpackChunkName: "finance-cancel-payment-execute" */
    './components/FinanceCancelPaymentExecuteComponent.vue'
));

/**
 * Componente para anular de una Orden de pago
 * 
 * 
 *  @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 */
Vue.component('finance-cancel-pay-order', () => import(
    /* webpackChunkName: "finance-cancel-payment-execute" */
    './components/FinanceCancelPayOrderComponent.vue'
));

/**
 * Componente para la creación de movimientos bancarios.
 *
 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('finance-bank-movements-create', () => import(
    /* webpackChunkName: "finance-bank-movements-create" */
    './components/bank/movements/FinanceBankMovementCreateComponent.vue'
));

/**
 * Componente para visualizar modal de conciliacion bancaria.
 *
 * @author  Juan Rosas <juan.rosasr01@gmail.com>
 */
Vue.component('finance-conciliacion-show-modal', () => import(
    /* webpackChunkName: "finance-conciliacion-show-modal" */
    './components/conciliacion/FinanceConciliationShowComponent.vue'
));

/**
 * Componente para la creación de conciliacion bancaria.
 *
 * @author  Juan Rosas <juan.rosasr01@gmail.com>
 */
Vue.component('finance-conciliacion-form', () => import(
    /* webpackChunkName: "finance-conciliacion-form" */
    './components/conciliacion/FinanceConciliationFormComponent.vue'
));

/**
 * Componente para listar los movimientos bancarios.
 *
 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('finance-bank-movements-list', () => import(
    /* webpackChunkName: "finance-bank-movements-list" */
    './components/bank/movements/FinanceBankMovementListComponent.vue'
));

/**
 * Componente para detallar la información de los movimientos bancarios.
 *
 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('finance-bank-movements-info', () => import(
    /* webpackChunkName: "finance-bank-movements-info" */
    './components/bank/movements/FinanceBankMovementInfoComponent.vue'
));

/**
 * Componente para anular movimientos bancarios.
 * 
 *  @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 */
Vue.component('finance-cancel-bank-movements', () => import(
    /* webpackChunkName: "finance-cancel-bank-movements" */
    './components/bank/movements/FinanceCancelBankMovementComponent.vue'
));

/**
 * Componente para generar reportes financieros.
 * 
 *  @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 */
Vue.component('finance-general-reports', () => import(
    /* webpackChunkName: "finance-general-reports" */
    './components/reports/FinanceGeneralReportComponent.vue'
));

/**
 * Opciones de configuración global del módulo de finanzas
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */
Vue.mixin({
    methods: {
        /**
         * Permite formatear la cadena de la cuenta bancaria
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string}  account  Número de cuenta bancaria
         * @param  {boolean} formated Indica si se desea obtener o no el número de cuenta bancaria formateada
         *
         * @return {string}           Número de cuenta formateado
         */
        format_bank_account(account, formated=true) {
            if (account.includes('Seleccione')) {
                return account;
            }
            var formated = (typeof(formated) !== "undefined") ? formated : true;

            var account_formated = '';
            for (var i = 0; i < account.length; i++) {
                if (formated && [4, 8, 10].includes(i) && account.charAt(i) !== "-") {
                    account_formated += '-';
                }
                account_formated += account.charAt(i);
            }

            return account_formated;
        },
        /**
         * Obtiene los datos de las entidades bancarias registradas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getBanks() {
            const vm = this;
            await axios.get(`${vm.app_url}/finance/get-banks`).then(response => {
                vm.banks = response.data;
            }).catch(error => {
                vm.logs('Finance/Resources/assets/js/_all.js', 90, error, 'getBanks');
            });
        },
        /**
         * Obtiene las agencias bancarias registradas.
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAgencies() {
            const vm = this;
            vm.agencies = [];
            const bank_id = this.record.finance_bank_id || '';

            if (bank_id) {
                axios.get(`${vm.app_url}/finance/get-agencies/${bank_id}`).then(response => {
                    vm.agencies = response.data;
                }).catch(error => {
                    vm.logs('Finance/Resources/assets/js/_all.js', 90, error, 'getAgencies');
                });

                if ($("#bank_code").length) {
                    axios.get(`${vm.app_url}/finance/get-bank-info/${bank_id}`).then(response => {
                        if (response.data.result) {
                            vm.record.bank_code = response.data.bank.code;
                        }
                    }).catch(error => {
                        vm.logs('Finance/Resources/assets/js/_all.js', 97, error, 'getAgencies');
                    });
                }
            }
        },
        /**
         * Obtiene los datos de los tipos de cuenta bancaria
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAccountTypes() {
            const vm = this;
            await axios.get(`${vm.app_url}/finance/get-account-types`).then(response => {
                vm.account_types = response.data;
            }).catch(error => {
                console.log(error);
            });
        },
        /**
         * Obtiene los datos de las cuentas asociadas a una entidad bancaria
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getBankAccounts() {
            const vm = this;
            const bank_id = vm.record.finance_bank_id || '';

            if (bank_id) {
                await axios.get(`${vm.app_url}/finance/get-accounts/${bank_id}`).then(response => {
                    if (response.data.result) {
                        vm.accounts = response.data.accounts;
                    }
                }).catch(error => {
                    vm.logs('Budget/Resources/assets/js/_all.js', 127, error, 'getBankAccounts');
                });
            }
        },
        /**
         * Obtiene los datos de los métodos de pago
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getPaymentMethods() {
            const vm = this;

            await axios.get(`${vm.app_url}/finance/get-payment-methods`).then(response => {
                vm.paymentMethods = response.data || [];
            }).catch(error => {
                vm.logs('Finance/Resources/assets/js/_all.js', 127, error, 'getPaymentMethods');
            });
        },

        /**
         * Obtine el conjunto de deducciones a ser pagadas de una manera de tallada.
         *
         * @return {void} void.
         */
        async getDeductionsToPay(deductions_ids) {
            const vm = this;
            vm.loading = true;
            vm.deductionsToPay = [];
            const url = vm.setUrl('finance/deductions-to-pay');
            await axios.post(
                url,
                {deductions_ids: deductions_ids}
            ).then(response => {
                vm.deductionsToPay = response.data.records || [];
            }).catch(error => {
                console.error(error);
            });
            vm.loading = false;
        },
    },
    mounted() {
        // Agregar instrucciones para determinar el año de ejecución
    }
});
