<template>
    <div>
        <accounting-show-errors ref="AccountingEntryGenerator" />
        <table class="table table-formulation">
            <thead>
                <tr>
                    <th class="text-uppercase">
                        CÓDIGO DE CUENTA - DENOMINACIÓN
                    </th>
                    <th class="text-uppercase" width="20%">DEBE</th>
                    <th class="text-uppercase" width="20%">HABER</th>
                    <th
                        class="text-uppercase"
                        width="10%"
                        v-if="showBankReference"
                    >
                        REFERENCIA BANCARIA
                    </th>
                    <th
                        class="text-uppercase"
                        width="10%"
                        v-if="showEdit == false"
                    >
                        ACCIÓN
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(record, index) in recordsAccounting" :key="index">
                    <td>
                        <select2
                            :disabled="showEdit != false"
                            :options="accounting_accounts"
                            v-model="record.id"
                            @input="changeSelectingTable(record)"
                        ></select2>
                    </td>
                    <td>
                        <input
                            :disabled="record.assets != 0 || showEdit != false"
                            type="number"
                            class="form-control input-sm"
                            :step="cualculateLimitDecimal()"
                            v-model="record.debit"
                            @change="calculateTot()"
                            @input="$emit('inputDebit', $event.target.value)"
                        />
                    </td>
                    <td>
                        <input
                            :disabled="record.debit != 0 || showEdit != false"
                            type="number"
                            class="form-control input-sm"
                            :step="cualculateLimitDecimal()"
                            v-model="record.assets"
                            @change="calculateTot()"
                        />
                    </td>
                    <td v-if="showBankReference">
                        <input
                            v-if="defaultBankReference != ''"
                            :disabled="showEdit != false"
                            type="text"
                            class="form-control input-sm"
                            v-model="defaultBankReferenceComputed"
                        />
                        <input
                            v-else
                            :disabled="showEdit != false"
                            type="text"
                            class="form-control input-sm"
                            v-model="record.bank_reference"
                        />
                    </td>
                    <td v-if="showEdit == false">
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
                    <td v-if="showBankReference"></td>
                    <td v-if="showEdit == false"></td>
                </tr>
                <tr>
                    <td id="helpEntriesAccountSelect">
                        <select2
                            :options="accounting_accounts"
                            id="select2"
                            @input="addAccountingAccount()"
                            v-if="showEdit == false"
                        ></select2>
                    </td>
                    <td id="helpEntriesTotDebit">
                        <div class="form-group text-center">
                            Total Debe:
                            <h6>
                                <span>{{ data.currency.symbol }}</span>
                                <span
                                    v-if="
                                        parseFloat(data.totDebit).toFixed(
                                            data.currency.decimal_places
                                        ) ==
                                            parseFloat(data.totAssets).toFixed(
                                                data.currency.decimal_places
                                            ) &&
                                        parseFloat(data.totDebit).toFixed(
                                            data.currency.decimal_places
                                        ) >= 0
                                    "
                                    style="color: #18ce0f"
                                >
                                    <strong>{{
                                        addDecimals(data.totDebit)
                                    }}</strong>
                                </span>
                                <span v-else style="color: #ff3636">
                                    <strong>{{
                                        addDecimals(data.totDebit)
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
                                        parseFloat(data.totDebit).toFixed(
                                            data.currency.decimal_places
                                        ) ==
                                            parseFloat(data.totAssets).toFixed(
                                                data.currency.decimal_places
                                            ) &&
                                        parseFloat(data.totAssets).toFixed(
                                            data.currency.decimal_places
                                        ) >= 0
                                    "
                                    style="color: #18ce0f"
                                >
                                    <strong>{{
                                        addDecimals(data.totAssets)
                                    }}</strong>
                                </span>
                                <span v-else style="color: #ff3636">
                                    <strong>{{
                                        addDecimals(data.totAssets)
                                    }}</strong>
                                </span>
                            </h6>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
export default {
    props: {
        recordToConverter: {
            type: Array,
            default: null,
        },
        date: {
            type: String,
            default: null,
        },
        showEdit: {
            type: Boolean,
            default: false,
        },
        showBankReference: {
            type: Boolean,
            default: false,
        },
        defaultBankReference: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            recordsAccounting: [],
            accounting_accounts: [],
            rowsToDelete: [],
            columns: ["code", "debit", "assets", "bank_reference", "id"],
            data: {
                date: "",
                reference: "",
                concept: "",
                observations: "",
                category: "",
                totDebit: 0,
                totAssets: 0,
                institution: {
                    id: "",
                    rif: "",
                    acronym: "",
                    name: 0,
                },
                currency: {
                    id: "",
                    symbol: "",
                    name: "",
                    decimal_places: 0,
                },
            },
            categories: [],
        };
    },
    computed: {
        defaultBankReferenceComputed() {
            return this.defaultBankReference;
        },
    },
    methods: {
        reset() {
            //
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
            /**
             * se cargan los errores
             */
            var errors = [];
            var res = false;

            if (!this.data.date) {
                errors.push("El campo fecha es obligatorio.");
                res = true;
            }
            if (!this.data.currency.id) {
                errors.push("El tipo de moneda es obligatorio.");
                res = true;
            }
            if (this.recordsAccounting.length < 1) {
                errors.push(
                    "No está permitido registrar asientos contables vacíos"
                );
                res = true;
            }
            if (this.data.totDebit != this.data.totAssets) {
                errors.push(
                    "El asiento no esta balanceado, Por favor verifique."
                );
                res = true;
            }
            if (this.data.totDebit < 0 || this.data.totAssets < 0) {
                errors.push(
                    "Los valores en la columna del DEBE y el HABER deben ser positivos."
                );
                res = true;
            }

            this.$refs.AccountingEntryGenerator.showAlertMessages(errors);
            return res;
        },

        /**
         * Vacia la información del debe y haber de la columna sin cuenta seleccionada
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        changeSelectingTable: function (record) {
            // si asigna un select en vacio, vacia los valores del debe y haber de esa fila
            if (record.id == "") {
                record.debit = 0;
                record.assets = 0;
                this.calculateTot();
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
        calculateTot: function () {
            this.data.totDebit = 0;
            this.data.totAssets = 0;
            let recordAcc = Object.values(this.recordsAccounting);

            /** Se recorren todo el arreglo que tiene todas las filas de las cuentas y saldos para el asiento y se calcula el total */
            for (var i = recordAcc.length - 1; i >= 0; i--) {
                if (recordAcc[i].id != "") {
                    var debit =
                        recordAcc[i].debit != "" &&
                        typeof recordAcc[i].debit != "undefined"
                            ? recordAcc[i].debit
                            : 0;
                    var assets =
                        recordAcc[i].assets != "" &&
                        typeof recordAcc[i].assets != "undefined"
                            ? recordAcc[i].assets
                            : 0;

                    recordAcc[i].debit = parseFloat(debit).toFixed(
                        this.data.currency.decimal_places
                    );
                    recordAcc[i].assets = parseFloat(assets).toFixed(
                        this.data.currency.decimal_places
                    );

                    if (recordAcc[i].debit < 0 || recordAcc[i].assets < 0) {
                        this.$refs.AccountingEntryGenerator.showAlertMessages(
                            "Los valores en la columna del DEBE y el HABER deben ser positivos."
                        );
                    }

                    this.data.totDebit =
                        recordAcc[i].debit != "" &&
                        typeof recordAcc[i].debit != "undefined"
                            ? parseFloat(
                                  parseFloat(this.data.totDebit) +
                                      parseFloat(recordAcc[i].debit)
                              ).toFixed(this.data.currency.decimal_places)
                            : 0;
                    this.data.totAssets =
                        recordAcc[i].assets != "" &&
                        typeof recordAcc[i].assets != "undefined"
                            ? parseFloat(
                                  parseFloat(this.data.totAssets) +
                                      parseFloat(recordAcc[i].assets)
                              ).toFixed(this.data.currency.decimal_places)
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
                this.recordsAccounting = Object.values(this.recordsAccounting);

                for (var i = this.accounting_accounts.length - 1; i >= 0; i--) {
                    if (this.accounting_accounts[i].id == $("#select2").val()) {
                        this.recordsAccounting.push({
                            id: $("#select2").val(),
                            entryAccountId: null,
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
         * cambia el tipo de moneda en el que se expresa el asiento contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        changeCurrency(currency_id) {
            if (currency_id) {
                axios
                    .get(`${window.app_url}/currencies/info/${currency_id}`)
                    .then((response) => {
                        this.data.currency = response.data.currency;
                        this.data.currency_id = response.data.currency.id;
                    });
            } else {
                this.data.currency = {
                    id: "",
                    symbol: "",
                    name: "",
                    decimal_places: 0,
                };
                this.data.currency_id = "";
            }
            this.calculateTot();
        },

        /**
         * Elimina la fila de la cuenta y vuelve a calcular el total del asiento
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        deleteAccount: function (index, id) {
            this.rowsToDelete.push(id);
            this.recordsAccounting.splice(index, 1);
            this.calculateTot();
        },

        /**
         * vacia los valores del debe y del haber de la fila de la cuenta y vuelve a calcular el total del asiento
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        clearValues: function (index) {
            this.recordsAccounting[index].assets = 0.0;
            this.recordsAccounting[index].debit = 0.0;
            this.calculateTot();
        },

        /**
         * carga las cuentas previamente registradas para generar el asiento contable
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
         */
        chargeAccounts(recordToConverter) {
            const vm = this;
            if (recordToConverter) {
                axios
                    .post(
                        `${window.app_url}/accounting/entries/converterToEntry`,
                        {
                            objectsList: recordToConverter,
                        }
                    )
                    .then((response) => {
                        vm.loading = true;
                        vm.accounting_accounts =
                            response.data.accountingAccounts;

                        // En caso de no haber seleccionado un tipo de moneda le asignara el
                        // que tenga por defecto en el sistema
                        if (!vm.data.currency.id && response.data.currency) {
                            vm.data.currency = response.data.currency;
                        }

                        const timeOpen = setTimeout(addRecordsAccounting, 1000);
                        function addRecordsAccounting() {
                            vm.recordsAccounting = Object.values(
                                response.data.recordsAccounting
                            ).map(function ($acc) {
                                return {
                                    amount: $acc.amount
                                        ? parseFloat($acc.amount).toFixed(2)
                                        : 0,
                                    assets: $acc.assets
                                        ? parseFloat($acc.assets).toFixed(2)
                                        : 0,
                                    debit: $acc.debit
                                        ? parseFloat($acc.debit).toFixed(2)
                                        : 0,
                                    id: $acc.id,
                                    is_retention: $acc.is_retention,
                                    text: $acc.text,
                                };
                            });
                            vm.calculateTot();
                            vm.loading = false;
                        }
                    });
            }
        },
    },
    created() {
        this.table_options.headings = {
            code: "CÓDIGO PATRIMONIAL - DENOMINACIÓN",
            debit: "BEDE",
            assets: "HABER",
            bank_reference: "REFERENCIA BANCARIA",
            id: "ACCIÓN",
        };

        $("#select2").val("");
    },
    mounted() {
        this.chargeAccounts(this.recordToConverter);
        if (this.date) {
            this.data.date = this.date;
        }
    },
};
</script>
