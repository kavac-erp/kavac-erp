<template>
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardSearchCloseFiscalYearForm">
                <div class="card-header">
                    <h6 class="card-title">
                        Configuración de la cuenta para el resultado del ejercicio
                        <a href="javascript:void(0)" title="haz click para ver la ayuda guiada de este elemento"
                           data-toggle="tooltip" class="btn-help" @click="initUIGuide(helpFile)">
                            <i class="ion ion-ios-help-outline cursor-pointer"></i>
                        </a>
                    </h6>
                    <div class="card-btns">
                        <div class="d-inline-flex">
                            <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
                               title="Ir atrás" data-toggle="tooltip">
                                <i class="fa fa-reply"></i>
                            </a>
                            <a href="javascript:void(0)" class="card-minimize btn btn-card-action btn-round"
                               title="Minimizar" data-toggle="tooltip">
                                <i class="now-ui-icons arrows-1_minimal-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <form-errors :listErrors="errors"></form-errors>
                <div class="card-body">
                    <div class="row">
                        <!-- Cuentas contable -->
                            <div class="col-md-4" id="helpSelectAccounting">
                                <div class=" form-group is-required">
                                    <label>Cuenta contable:</label>
                                    <select2 :options="accounting_accounts" v-model="record.accounting_account_id"></select2>
                                </div>
                            </div>
                        <!-- ./Cuentas contable -->
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button
                        type="reset"
                        class="btn btn-default btn-icon btn-round"
                        data-toggle="tooltip"
                        title="Borrar datos del formulario"
                        @click="reset()"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-warning btn-icon btn-round"
                        data-toggle="tooltip"
                        title="Cancelar y regresar"
                        @click="redirect_back(route_list)"
                    >
                        <i class="fa fa-ban"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-success btn-icon btn-round"
                        data-toggle="tooltip"
                        title="Guardar registro"
                        @click="createRecord('close-fiscal-year/settings/add-account')"
                    >
                        <i class="fa fa-save"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            record: {
                accounting_account_id: '',
            },
            errors: [],
            accounting_accounts: []
        }
    },
    methods: {
        /**
         * Limpia los campos del formulario
         *
         * @method     reset
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        reset() {
            const vm = this;
            vm.record = {
                accounting_account_id: '',
            }
            errors = [];
        },

        /**
         * Obtiene un listado de cuentas patrimoniales
         *
         * @method     getAccountingAccounts
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getAccountingAccounts() {
            const vm = this;
            vm.accounting_accounts = [];
            axios.get('/accounting/accounts').then(response => {
                if (response.data.records.length > 0) {
                    vm.accounting_accounts.push({
                        id: '',
                        text: 'Seleccione...'
                    });
                    $.each(response.data.records, function() {
                        vm.accounting_accounts.push({
                            id: this.id,
                            text: `${this.code} - ${this.denomination}`
                        });
                    });
                }
            }).catch(error => {
                vm.logs('CloseFiscalYearAccountSettingComponent', 258, error, 'getAccountingAccounts');
            });
        },
        /**
         * Obtiene la cuenta patrimonial ya agregada
         *
         * @method     getAccountingAccount
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        getAccountingAccount() {
            const vm = this;
            axios.get('close-fiscal-year/settings/get-account').then(response => {
                for (let account of vm.accounting_accounts) {
                    if (account.id == response.data.id) {
                        vm.record.accounting_account_id = response.data.id;
                        console.log(vm.record.accounting_account_id)
                    }
                }
            }).catch(error => {
                vm.logs('CloseFiscalYearAccountSettingComponent', 258, error, 'getAccountingAccounts');
            });
        }
    },
    async mounted() {
        const vm = this;
        await vm.getAccountingAccounts();
        await vm.getAccountingAccount();
    },
};
</script>
