<template>
    <div class="form-horizontal">
        <!-- card-body -->
        <div class="card-body" style="min-height: 5px !important">
            <accounting-show-errors ref="accountingConverter" />
            <div class="row">
                <div
                    class="col-xs-6 col-sm-6 col-lg-2"
                    id="helpSearchSelectBudget"
                >
                    <label for="sel_budget_acc" class="control-label">
                        Por código presupuestario
                    </label>
                    <div class="custom-control custom-switch">
                        <input
                            type="radio"
                            name="sel_account_type"
                            class="custom-control-input"
                            id="sel_budget_acc"
                            @click="loadAccounts('budget')"
                        />
                        <label
                            class="custom-control-label"
                            for="sel_budget_acc"
                        ></label>
                    </div>
                </div>
                <div
                    class="col-xs-6 col-sm-6 col-lg-2"
                    id="helpSearchSelectAccounting"
                >
                    <label for="sel_account_type" class="control-label">
                        Por código patrimonial
                    </label>
                    <div class="custom-control custom-switch">
                        <input
                            type="radio"
                            name="sel_account_type"
                            class="custom-control-input"
                            id="sel_accounting_acc"
                            @click="loadAccounts('accounting')"
                            checked
                        />
                        <label
                            class="custom-control-label"
                            for="sel_accounting_acc"
                        ></label>
                    </div>
                </div>
                <div
                    class="col-xs-12 col-sm-12 col-lg-8 row"
                    id="helpSearchRangeAccount"
                >
                    <div class="col-xs-12 col-sm-12 col-lg-5">
                        <label class="control-label">Desde</label>
                        <select2
                            id="sel_acc_init"
                            :options="accountOptions[0]"
                            v-model="accountSelect.init_id"
                            :disabled="SelectAll"
                        ></select2>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-lg-5">
                        <label class="control-label">Hasta</label>
                        <select2
                            id="sel_acc_end"
                            :options="accountOptions[1]"
                            v-model="accountSelect.end_id"
                            :disabled="SelectAll"
                        ></select2>
                    </div>
                    <div
                        class="col-xs-12 col-sm-12 col-lg-2"
                        id="helpSearchRangeAll"
                    >
                        <label class="control-label" style="font-size: 0.78em">
                            Seleccionar todas
                        </label>
                        <div class="custom-control custom-switch">
                            <input
                                type="radio"
                                name="sel_account_type"
                                class="custom-control-input"
                                id="sel_all_acc"
                                @click="checkAll()"
                            />
                            <label
                                class="custom-control-label"
                                for="sel_all_acc"
                            ></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Final card-body -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <button
                class="btn btn-info btn-sm"
                :disabled="!searchActive"
                title="Buscar conversiones de cuentas"
                data-toggle="tooltip"
                v-has-tooltip
                v-on:click="getRecords()"
            >
                <i class="fa fa-search"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </div>
</template>
<script>
export default {
    data() {
        return {
            records: [],
            budgetAccounts: null,
            accountingAccounts: null,
            accountOptions: [[], []],
            accountSelect: {
                init_id: "",
                end_id: "",
                type: "budget",
                all: false,
            },
            searchActive: false,
            searchBudgetAccount: true, //true: para cuentas presupuestarias, false para cuentas patrimoniales
            convertionId: null,
        };
    },
    created() {
        /** Se realiza la primera busqueda en base a cuentas patrimoniales para los selects */
        this.getAllRecords_selects_vuejs(
            "getAllRecordsAccounting_vuejs",
            "accounting",
            false
        );
    },
    mounted() {
        const vm = this;
        vm.errors = [];
    },
    methods: {
        /**
         * Cambia la lista de cuantas a mostrar en los selectores
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        loadAccounts(type) {
            const vm = this;
            vm.accountSelect.type = type;
            if (!document.getElementById(`sel_${type}_acc`).checked) {
                return false;
            }
            if (vm.accountSelect.type == "budget") {
                vm.getAllRecords_selects_vuejs(
                    "getAllRecordsBudget_vuejs",
                    vm.accountSelect.type,
                    true
                );
                vm.accountSelect.all = false;
            } else if (vm.accountSelect.type == "accounting") {
                vm.getAllRecords_selects_vuejs(
                    "getAllRecordsAccounting_vuejs",
                    vm.accountSelect.type,
                    false
                );
                vm.accountSelect.all = false;
            }
        },

        /**
         * Selecciona todo el rango de registros de cuantas
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        checkAll() {
            const vm = this;
            vm.accountSelect.all = true;
            if (!document.getElementById("sel_all_acc").checked) {
                return false;
            }
            if (vm.accountSelect.type == "budget") {
                vm.getAllRecords_selects_vuejs(
                    "getAllRecordsBudget_vuejs",
                    vm.accountSelect.type,
                    true
                );
            } else if (vm.accountSelect.type == "accounting") {
                vm.getAllRecords_selects_vuejs(
                    "getAllRecordsAccounting_vuejs",
                    vm.accountSelect.type,
                    false
                );
            }
            if (!$("#sel_all_acc").prop("checked")) {
                vm.accountSelect.init_id = "";
                vm.accountSelect.end_id = "";
                vm.accountSelect.all = false;
            }
        },

        /**
         * Asigna los valores a las variables de los selects
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        setValues: function (records, type_select, type_search) {
            this.accountOptions = [[], []];
            this.accountOptions[0] = records;
            this.accountOptions[1] = records;
            this.searchBudgetAccount = type_search;
            this.accountSelect.type = type_select;
            this.searchActive = true;
            if (type_select == "accounting") {
                this.accountingAccounts = records;
            }
            if (type_select == "budget") {
                this.budgetAccounts = records;
            }
            if (records.length > 1) {
                this.accountSelect.init_id = records[1].id;
                this.accountSelect.end_id = records[records.length - 1].id;
            }
        },

        /**
         * varifica y realiza la consulta de las cuentas de ser necesario
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        getAllRecords_selects_vuejs: function (
            name_route,
            type_select,
            type_search
        ) {
            const vm = this;
            vm.loading = true;
            /** Array que almacenara los registros de las cuentas para los selects */
            var records = null;
            /** Boolean que determina si es necesario realizar la consulta de los registros */
            var query = true;
            if (type_select == "accounting" && vm.accountingAccounts) {
                records = vm.accountingAccounts;
                query = false;
            } else if (type_select == "budget" && vm.budgetAccounts) {
                records = vm.budgetAccounts;
                query = false;
            }
            if (query) {
                axios
                    .post(
                        `${window.app_url}/accounting/converter/${name_route}`
                    )
                    .then((response) => {
                        vm.setValues(
                            response.data.records,
                            type_select,
                            type_search
                        );
                        vm.loading = false;
                    });
            } else {
                vm.setValues(records, type_select, type_search);
                vm.loading = false;
            }
        },

        /**
         * Obtiene los registros de las cuentas que tienen conversión activa
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        getRecords: function () {
            let vm = this;
            if (
                vm.accountSelect.init_id != "" &&
                vm.accountSelect.end_id != ""
            ) {
                vm.loading = true;
                axios
                    .post(
                        `${window.app_url}/accounting/converter/get-Records`,
                        vm.accountSelect
                    )
                    .then((response) => {
                        vm.records = [];
                        vm.records = response.data.records;
                        vm.showMessage(
                            "custom",
                            "Éxito",
                            "success",
                            "screen-ok",
                            "Consulta realizada de manera existosa."
                        );
                        if (vm.records.length == 0) {
                            vm.$refs.accountingConverter.showAlertMessages(
                                "No se encontraron registros de conversiones en el rango dado",
                                "primary"
                            );
                        } else {
                            vm.$refs.accountingConverter.reset();
                        }
                        EventBus.$emit(
                            "list:conversions",
                            response.data.records
                        );
                        vm.loading = false;
                    });
            } else {
                vm.$refs.accountingConverter.showAlertMessages(
                    "Los campos de selección de cuenta son obligatorios"
                );
            }
        },
    },
    computed: {
        SelectAll: function () {
            return this.accountSelect.all;
        },
    },
};
</script>
