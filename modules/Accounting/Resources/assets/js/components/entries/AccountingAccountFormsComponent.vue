<template>
    <div>
        <accounting-show-errors ref="AccountingAccountsInForm" />
        <table class="table table-formulation">
            <thead>
                <tr>
                    <th class="text-uppercase" width="50%">
                        CÓDIGO DE CUENTA - DENOMINACIÓN
                    </th>
                    <th class="text-uppercase" width="15%">DEBE</th>
                    <th class="text-uppercase" width="15%">HABER</th>
                    <th class="text-uppercase" width="10%">
                        REFERENCIA BANCARIA
                    </th>
                    <th class="text-uppercase" width="10%">ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(record, index) in recordsAccounting" :key="index">
                    <td>
                        <select2
                            :options="accounting_accounts"
                            v-model="record.id"
                            @input="changeSelectinTable(record)"
                        ></select2>
                    </td>
                    <td>
                        <input
                            :disabled="record.assets != 0"
                            type="number"
                            data-toggle="tooltip"
                            class="form-control input-sm"
                            :step="0.01"
                            v-model="record.debit"
                            @change="CalculateTot()"
                        />
                    </td>
                    <td>
                        <input
                            :disabled="record.debit != 0"
                            type="number"
                            data-toggle="tooltip"
                            class="form-control input-sm"
                            :step="0.01"
                            v-model="record.assets"
                            @change="CalculateTot()"
                        />
                    </td>
                    <td>
                        <input
                            type="text"
                            data-toggle="tooltip"
                            class="form-control input-sm"
                            v-model="record.bank_reference"
                        />
                    </td>
                    <td>
                        <div class="text-center">
                            <button
                                @click="
                                    clearValues(
                                        recordsAccounting.indexOf(record)
                                    )
                                "
                                class="btn btn-default btn-xs btn-icon btn-action"
                                title="Vaciar valores"
                                data-toggle="tooltip"
                                v-has-tooltip
                            >
                                <i class="fa fa-eraser"></i>
                            </button>
                            <button
                                @click="
                                    deleteAccount(
                                        recordsAccounting.indexOf(record),
                                        record.entryAccountId
                                    )
                                "
                                class="btn btn-danger btn-xs btn-icon btn-action"
                                title="Eliminar registro"
                                data-toggle="tooltip"
                                v-has-tooltip
                            >
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td id="helpEntriesAccountSelect">
                        <select2
                            :disabled="!enableInput"
                            :options="accounting_accounts"
                            id="select2"
                            @input="addAccountingAccount()"
                        ></select2>
                    </td>
                    <td id="helpEntriesTotDebit">
                        <div class="form-group text-center">
                            Total Debe:
                            <h6>
                                <span>{{ data.currency.symbol }}</span>
                                <span
                                    v-if="
                                        data.totDebit.toFixed(
                                            data.currency.decimal_places
                                        ) ==
                                            data.totAssets.toFixed(
                                                data.currency.decimal_places
                                            ) &&
                                        data.totDebit.toFixed(
                                            data.currency.decimal_places
                                        ) >= 0
                                    "
                                    style="color: #18ce0f"
                                >
                                    <strong>{{
                                        parseFloat(data.totDebit).toFixed(
                                            data.currency &&
                                                data.currency.decimal_places
                                                ? data.currency.decimal_places
                                                : 2
                                        )
                                    }}</strong>
                                </span>
                                <span v-else style="color: #ff3636">
                                    <strong>{{
                                        parseFloat(data.totDebit).toFixed(
                                            data.currency &&
                                                data.currency.decimal_places
                                                ? data.currency.decimal_places
                                                : 2
                                        )
                                    }}</strong>
                                </span>
                            </h6>
                        </div>
                    </td>
                    <td id="helpEntriesTotAssets">
                        <div class="form-group text-center">
                            Total Haber:
                            <h6>
                                <span>{{ data.currency.symbol }}</span>
                                <span
                                    v-if="
                                        data.totDebit.toFixed(
                                            data.currency.decimal_places
                                        ) ==
                                            data.totAssets.toFixed(
                                                data.currency.decimal_places
                                            ) &&
                                        data.totAssets.toFixed(
                                            data.currency.decimal_places
                                        ) >= 0
                                    "
                                    style="color: #18ce0f"
                                >
                                    <strong>{{
                                        parseFloat(data.totAssets).toFixed(
                                            data.currency &&
                                                data.currency.decimal_places
                                                ? data.currency.decimal_places
                                                : 2
                                        )
                                    }}</strong>
                                </span>
                                <span v-else style="color: #ff3636">
                                    <strong>{{
                                        parseFloat(data.totAssets).toFixed(
                                            data.currency &&
                                                data.currency.decimal_places
                                                ? data.currency.decimal_places
                                                : 2
                                        )
                                    }}</strong>
                                </span>
                            </h6>
                        </div>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="card-footer text-right">
            <div class="row">
                <div class="col-md-3 offset-md-9" id="helpParamButtons">
                    <button
                        type="button"
                        @click="reset()"
                        class="btn btn-default btn-icon btn-round"
                        data-toggle="tooltip"
                        title="Borrar datos del formulario"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>

                    <button
                        type="button"
                        @click="redirect_back(route_list)"
                        class="btn btn-warning btn-icon btn-round btn-modal-close"
                        data-dismiss="modal"
                        title="Cancelar y regresar"
                    >
                        <i class="fa fa-ban"></i>
                    </button>

                    <button
                        type="button"
                        @click="createRecord()"
                        class="btn btn-success btn-icon btn-round btn-modal-save"
                        title="Guardar registro"
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
    props: {
        accounting_accounts: {
            type: Array,
            default() {
                return [];
            },
        },
        entries: {
            type: Object,
            default: null,
        },
        close_fiscal_year: {
            type: Boolean,
            default() {
                return false;
            },
        },
    },
    data() {
        return {
            recordsAccounting: [],
            recordsBudget: [],
            rowsToDelete: [],
            urlPrevious: `${window.app_url}/accounting/entries`,
            data: {
                date: "",
                reference: "",
                concept: "",
                observations: "",
                category: "",
                totDebit: 0,
                totAssets: 0,
                institution_id: null,
                currency_id: null,
                currency: {
                    id: "",
                    symbol: "",
                    name: "",
                    decimal_places: 0,
                },
            },
            enableInput: false,
            accountingOptions: [],
            optionIdBudget: "",
            type: "debit",
        };
    },
    created() {
        this.table_options.headings = {
            code: "CÓDIGO PATRIMONIAL - DENOMINACIÓN",
            debit: "BEDE",
            assets: "HABER",
            id: "ACCIÓN",
        };

        $("#select2").val("");

        EventBus.$on("enableInput:entries-account", (data) => {
            this.enableInput = data.value;
            this.data.date = data.date;
            this.data.reference = data.reference;
            this.data.concept = data.concept;
            this.data.observations = data.observations;
            this.data.category = data.category;
            this.data.institution_id = data.institution_id;
            this.data.currency_id = data.currency_id;
        });

        EventBus.$on("change:currency", (data) => {
            if (data != "") {
                axios
                    .get(`${window.app_url}/currencies/info/${data}`)
                    .then((response) => {
                        this.data.currency = response.data.currency;
                    });
            } else {
                this.data.currency = {
                    id: "",
                    symbol: "",
                    name: "",
                    decimal_places: 0,
                };
            }
        });
    },
    mounted() {
        if (this.entries) {
            for (var i = 0; i < this.entries.accounting_accounts.length; i++) {
                this.recordsAccounting.push({
                    id: this.entries.accounting_accounts[i]
                        .accounting_account_id,
                    entryAccountId: this.entries.accounting_accounts[i].id,
                    bank_reference:
                        this.entries.accounting_accounts[i].bank_reference,
                    debit: this.entries.accounting_accounts[i].debit,
                    assets: this.entries.accounting_accounts[i].assets,
                });
            }
            this.data.totDebit = parseFloat(this.entries.tot_debit);
            this.data.totAssets = parseFloat(this.entries.tot_assets);
        }
        EventBus.$emit("validate-required:accounting-entry-edit-create");
    },
    beforeDestroy() {
        EventBus.$off("enableInput:entries-account");
    },
    methods: {
        reset() {
            EventBus.$emit("reset:accounting-entry-edit-create");
        },

        addDecimals(value) {
            return parseFloat(value).toFixed(this.data.currency.decimal_places);
        },

        /**
         * [validateTotals valida que los totales sean positivos]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {boolean}
         */
        validateTotals: function () {
            return !(
                this.data.totDebit.toFixed(this.data.currency.decimal_places) >=
                    0 &&
                this.data.totAssets.toFixed(
                    this.data.currency.decimal_places
                ) >= 0
            );
        },

        /**
         * Validación de errores
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        validateErrors: function () {
            const vm = this;
            /** se cargan los errores */
            var errors = [];
            var res = false;

            if (!vm.data.date) {
                errors.push("El campo fecha es obligatorio.");
                res = true;
            }
            if (!vm.data.concept) {
                errors.push("El campo concepto ó descripción es obligatorio.");
                res = true;
            }
            if (!vm.data.category) {
                errors.push("El campo categoria es obligatorio.");
                res = true;
            }
            if (!vm.data.institution_id) {
                errors.push("El campo institución es obligatorio.");
                res = true;
            }
            if (!vm.data.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
                res = true;
            }
            if (vm.recordsAccounting.length < 1) {
                errors.push(
                    "No está permitido registrar asientos contables vacíos"
                );
                res = true;
            }
            if (
                vm.addDecimals(vm.data.totDebit) !=
                vm.addDecimals(vm.data.totAssets)
            ) {
                errors.push(
                    "El asiento no esta balanceado, Por favor verifique."
                );
                res = true;
            }
            if (
                vm.addDecimals(vm.data.totDebit) < 0 ||
                vm.addDecimals(vm.data.totAssets) < 0
            ) {
                errors.push(
                    "Los valores en la columna del DEBE y el HABER deben ser positivos."
                );
                res = true;
            }
            vm.$refs.AccountingAccountsInForm.showAlertMessages(errors);
            return res;
        },

        /**
         * Vacia la información del debe y haber de la columna sin cuenta seleccionada
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        changeSelectinTable: function (record) {
            // si asigna un select en vacio, vacia los valores del debe y haber de esa fila
            if (record.id == "") {
                record.debit = 0;
                record.assets = 0;
                this.CalculateTot();
            }
        },

        /**
         * Establece la cantidad de decimales correspondientes a la moneda que se maneja
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        cualculateLimitDecimal() {
            var res = "0.";
            for (var i = 0; i < this.data.currency.decimal_places - 1; i++) {
                res += "0";
            }
            res += "1";
            return res;
        },

        /**
         * Calcula el total del debe y haber del asiento contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        CalculateTot: function () {
            this.data.totDebit = 0;
            this.data.totAssets = 0;

            /** Se recorren todo el arreglo que tiene todas las filas de las cuentas y saldos para el asiento y se calcula el total */
            for (var i = this.recordsAccounting.length - 1; i >= 0; i--) {
                if (this.recordsAccounting[i].id != "") {
                    var debit =
                        this.recordsAccounting[i].debit != ""
                            ? this.recordsAccounting[i].debit
                            : 0;
                    var assets =
                        this.recordsAccounting[i].assets != ""
                            ? this.recordsAccounting[i].assets
                            : 0;

                    this.recordsAccounting[i].debit = parseFloat(debit).toFixed(
                        this.data.currency.decimal_places
                    );
                    this.recordsAccounting[i].assets = parseFloat(
                        assets
                    ).toFixed(this.data.currency.decimal_places);

                    if (
                        this.recordsAccounting[i].debit < 0 ||
                        this.recordsAccounting[i].assets < 0
                    ) {
                        this.enableInput = false;
                        this.$refs.AccountingAccountsInForm.showAlertMessages(
                            "Los valores en la columna del DEBE y el HABER deben ser positivos."
                        );
                    } else {
                        this.enableInput = true;
                    }

                    this.data.totDebit +=
                        this.recordsAccounting[i].debit != ""
                            ? parseFloat(this.recordsAccounting[i].debit)
                            : 0;
                    this.data.totAssets +=
                        this.recordsAccounting[i].assets != ""
                            ? parseFloat(this.recordsAccounting[i].assets)
                            : 0;
                }
            }
        },

        /**
         * Establece la información base para cada fila de cuentas
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        addAccountingAccount: function () {
            if ($("#select2").val() != "") {
                for (var i = this.accounting_accounts.length - 1; i >= 0; i--) {
                    if (this.accounting_accounts[i].id == $("#select2").val()) {
                        this.recordsAccounting.push({
                            id: $("#select2").val(),
                            entryAccountId: null,
                            bank_reference: "",
                            debit: 0,
                            assets: 0,
                        });
                        $("#select2").val("");
                        break;
                    }
                }
            }
        },

        /**
         * [createRecord se valida si el asiento sera actualizado o creado]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {[type]} [description]
         */
        createRecord: function () {
            if (this.entries == null) {
                this.storeEntry();
            } else {
                this.updateRecord();
            }
        },

        /**
         * [storeEntry Guarda la información del asiento contable]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        storeEntry() {
            const vm = this;

            if (vm.validateErrors()) {
                return;
            }

            vm.data["currency_id"] = vm.data.currency.id;
            vm.data["tot"] = vm.addDecimals(vm.data.totDebit);
            vm.data["tot_confirmation"] = vm.addDecimals(vm.data.totAssets);
            vm.data["accountingAccounts"] = vm.recordsAccounting;

            if (vm.close_fiscal_year) {
                vm.data["close_fiscal_year"] = true;
            }

            vm.loading = true;

            axios
                .post(`${window.app_url}/accounting/entries`, vm.data)
                .then((response) => {
                    vm.loading = false;
                    vm.showMessage("store");
                    setTimeout(function () {
                        location.href = vm.urlPrevious;
                    }, 2000);
                })
                .catch((error) => {
                    var errors = [];
                    if (typeof error.response != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                "custom",
                                "Acceso Denegado",
                                "danger",
                                "screen-error",
                                error.response.data.message
                            );
                        }

                        if (
                            error.response.data.result == false &&
                            error.response.status != 403
                        ) {
                            vm.showMessage(
                                error.response.data.message.type,
                                error.response.data.message.title,
                                error.response.data.message.class,
                                error.response.data.message.icon,
                                error.response.data.message.text
                            );
                        } else {
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        }
                    }
                    /**
                     * se cargan los errores
                     */
                    vm.$refs.AccountingAccountsInForm.showAlertMessages(errors);
                    vm.loading = false;
                });
        },

        /**
         * Actualiza la información del asiento contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        updateRecord: function () {
            const vm = this;
            if (vm.validateErrors()) {
                return;
            }
            vm.data["tot"] = vm.data.totDebit;
            vm.data["tot_confirmation"] = vm.data.totAssets;
            vm.data["accountingAccounts"] = vm.recordsAccounting;
            vm.data["rowsToDelete"] = vm.rowsToDelete;

            vm.loading = true;

            axios
                .put(
                    `${window.app_url}/accounting/entries/${vm.entries.id}`,
                    vm.data
                )
                .then(() => {
                    vm.loading = false;
                    vm.showMessage("update");
                    setTimeout(function () {
                        location.href = vm.route_list;
                    }, 2000);
                })
                .catch((error) => {
                    var errors = [];
                    if (typeof error.response != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                "custom",
                                "Acceso Denegado",
                                "danger",
                                "screen-error",
                                error.response.data.message
                            );
                        }

                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                    }
                    /**
                     * se cargan los errores
                     */
                    vm.$refs.AccountingAccountsInForm.showAlertMessages(errors);
                    vm.loading = false;
                });
        },

        /**
         * Elimina la fila de la cuenta y vuelve a calcular el total del asiento
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        deleteAccount: function (index, id) {
            this.rowsToDelete.push(id);
            this.recordsAccounting.splice(index, 1);
            this.CalculateTot();
        },

        /**
         * vacia los valores del debe y del haber de la fila de la cuenta y vuelve a calcular el total del asiento
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        clearValues: function (index) {
            this.recordsAccounting[index].assets = 0.0;
            this.recordsAccounting[index].debit = 0.0;
            this.CalculateTot();
        },
    },
};
</script>
