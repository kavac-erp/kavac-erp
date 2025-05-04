<template>
    <div class="form-horizontal">
        <div class="card-body pb-5" v-if="!results">
            <accounting-show-errors ref="accountingConverterForm" />
            <div class="row" v-if="pair_arrays.length == 0">
                <label class="control-label"
                    >Cantidad de cuentas a convertir</label
                >
            </div>
            <div class="row" v-if="pair_arrays.length == 0">
                <div
                    class="col-6 mb-6"
                    id="helpTotalToConvert"
                    style="padding-top: 0.5rem"
                >
                    <input
                        type="number"
                        class="form-control"
                        placeholder="1"
                        min="1"
                        v-model="totalToConvert"
                        title="Indique el número de cuentas a convertir"
                        data-toggle="tooltip"
                    />
                </div>
                <div class="col-6 mb-6">
                    <button
                        type="button"
                        @click="addPair"
                        title="Agregar más cuentas a convertir"
                        data-toggle="tooltip"
                        v-has-tooltip
                        class="btn btn-primary btn-icon btn-round"
                        data-original-title="Guardar registro"
                    >
                        <i class="fa fa-plus-circle cursor-pointer"></i>
                    </button>
                </div>
            </div>
            <div class="row" v-if="pair_arrays.length > 0">
                <h6 class="card-title">
                    Cuentas a convertir
                    <i
                        class="fa fa-plus-circle cursor-pointer"
                        @click="addPairSimple"
                    ></i>
                </h6>
            </div>
            <div v-for="(pair, index) in pair_arrays" :key="index" class="row">
                <div
                    class="col-xs-10 col-sm-10 col-md-5 is-required"
                    id="helpSelectBudget"
                >
                    <label class="control-label">Cuentas Presupuestarias</label>
                    <select2
                        :options="budgetOptions"
                        v-model="pair.budgetSelect"
                        :id="'budgetSelect' + index"
                        :name="'budgetSelect' + index"
                        data-toggle="tooltip"
                        title="Seleccione una cuenta presupuestaria"
                    ></select2>
                </div>
                <div
                    class="col-xs-10 col-sm-10 col-md-5 is-required"
                    id="helpSelectAccounting"
                >
                    <label class="control-label">Cuentas Patrimoniales</label>
                    <select2
                        :options="accountingOptions"
                        :id="'accountingSelect' + index"
                        :name="'accountingSelect' + index"
                        v-model="pair.accountingSelect"
                        data-toggle="tooltip"
                        title="Seleccione una cuenta patrimonial"
                    ></select2>
                </div>
                <div
                    class="col-xs-10 col-sm-10 col-md-2"
                    style="padding-top: 1.25rem"
                >
                    <button
                        @click="deletePair(index)"
                        title=""
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        data-original-title="Eliminar registro"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-footer text-right" v-if="!results">
            <buttonsDisplay :route_list="app_url + '/q'" display="false" />
        </div>
        <div class="card-body pb-5" v-if="results">
            <div
                :class="'alert alert-success'"
                role="alert"
                v-if="nonDuplicates.length > 0"
            >
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Exito!</strong> Debe verificar los siguiente antes
                    de continuar:
                    <button
                        type="button"
                        @click="this.return"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="success in nonDuplicates" :key="success">
                            {{ success }}
                        </li>
                    </ul>
                </div>
            </div>
            <div
                :class="'alert alert-danger'"
                role="alert"
                v-if="duplicates.length > 0"
            >
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
                    <button
                        type="button"
                        @click="this.return"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in duplicates" :key="error">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        accounting_list: {
            type: Array,
            default() {
                return [];
            },
        },
        budget_list: {
            type: Array,
            default() {
                return [];
            },
        },
        account_to_edit: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            totalToConvert: 1,
            budgetOptions: [],
            accountingOptions: [],
            budgetSelect: "",
            results: false,
            duplicates: [],
            nonDuplicates: [],
            results: false,
            pair_arrays: [],
            accountingSelect: "",
            urlPrevious: `${window.app_url}/accounting/converter`,
        };
    },
    created() {
        this.budgetOptions = this.budget_list;
        this.accountingOptions = this.accounting_list;

        /**
         * si existe account_to_edit, el formulario esta en modo editar
         */
        if (this.account_to_edit) {
            this.budgetSelect = this.account_to_edit.accountable_id;
            this.accountingSelect = this.account_to_edit.accounting_account_id;
        }
    },
    mounted() {
        if (this.budget_list.length < 2) {
            this.$refs.accountingConverterForm.showAlertMessages(
                "No se encontraron registros de cuentas presupuestarias."
            );
        }
        if (this.accounting_list.length < 2) {
            this.$refs.accountingConverterForm.showAlertMessages(
                "No se encontraron registros de cuentas patrimoniales."
            );
        }
    },
    methods: {
        reset() {
            this.pair_arrays = [];
        },
        return() {
            const vm = this;
            location.href = vm.urlPrevious;
        },

        /**
         * Método que agrega upar de cuentas.
         *
         * addPair
         *
         * @author Francisco Escala <fjescala@gmail.com>
         */
        addPair() {
            for (let i = 0; i < this.totalToConvert; i++) {
                this.pair_arrays.push({
                    budgetSelect: "",
                    accountingSelect: "",
                });
            }
        },
        deletePair(index) {
            // Elimina el par correspondiente al índice proporcionado
            this.pair_arrays.splice(index, 1);
        },
        /**
         * Método que agrega upar de cuentas.
         *
         * addPairSimple
         *
         * @author Francisco Escala <fjescala@gmail.com>
         */
        addPairSimple() {
            this.pair_arrays.push({
                budgetSelect: "",
                accountingSelect: "",
            });
        },

        /**
         * enviar la información de las cuentas a convertir para ser almacenada
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param {int} $indexToConvertion [posición en el array de cuentas a convertir]
         */
        createRecord: function () {
            const vm = this;
            let checkArray = (arrayElement) =>
                arrayElement.budgetSelect == "" ||
                arrayElement.accountingSelect == "";
            let conditionArray = vm.pair_arrays.some(checkArray);
            if (conditionArray == true) {
                vm.$refs.accountingConverterForm.showAlertMessages(
                    "Los campos de selección de cuenta son obligatorios."
                );
                return;
            }
            if (vm.pair_arrays.length == 0) {
                vm.$refs.accountingConverterForm.showAlertMessages(
                    "Debe agregar al menos una cuenta a convertir."
                );
            } else {
                vm.loading = true;
                if (vm.account_to_edit == null) {
                    axios
                        .post(`${window.app_url}/accounting/converter`, {
                            module: "Budget",
                            model: "Modules\\Accounting\\Models\\BudgetAccount",
                            accountable_id: vm.budgetSelect,
                            pair: vm.pair_arrays,
                            accounting_account_id: vm.accountingSelect,
                        })
                        .then((response) => {
                            if (response.data.message !== "Success") {
                                vm.showMessage(
                                    "custom",
                                    "Error",
                                    "danger",
                                    "fa-ban",
                                    response.data.message
                                );
                            } else {
                                vm.$refs.accountingConverterForm.reset();
                                vm.showMessage("store");
                                vm.results = true;
                                vm.budgetSelect = "";
                                vm.accountingSelect = "";
                                vm.accountingOptions = [];
                                vm.budgetOptions = [];
                                vm.pair_arrays = [];
                                vm.nonDuplicates = response.data.non_duplicates;
                                vm.duplicates = response.data.duplicates;
                            }
                            vm.loading = false;
                        })
                        .catch((error) => {
                            vm.loading = false;

                            for (let index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.$refs.accountingConverterForm.showAlertMessages(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        });
                } else {
                    axios
                        .put(
                            `${window.app_url}/accounting/converter/${vm.account_to_edit.id}`,
                            {
                                module: "Budget",
                                model: "Modules\\Accounting\\Models\\BudgetAccount",
                                accountable_id: vm.budgetSelect,
                                accounting_account_id: vm.accountingSelect,
                            }
                        )
                        .then((response) => {
                            if (response.data.message !== "Success") {
                                vm.showMessage(
                                    "custom",
                                    "Error",
                                    "danger",
                                    "fa-ban",
                                    response.data.message
                                );
                            } else {
                                vm.showMessage("update");
                                location.href = vm.urlPrevious;
                            }
                            vm.loading = false;
                        })
                        .catch((error) => {
                            vm.loading = false;

                            for (let index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.$refs.accountingConverterForm.showAlertMessages(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        });
                }
            }
        },
    },
};
</script>
