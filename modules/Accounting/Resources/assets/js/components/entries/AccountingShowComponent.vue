<template>
    <div>
        <button
            type="button"
            @click="addRecord(modal_name + id, route_show, $event)"
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Visualizar registro"
            v-has-tooltip
            data-toggle="modal"
            :data-target="'#' + modal_name + id"
        >
            <i class="fa fa-eye"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="modal_name + id"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="reset"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            Asiento contable
                        </h6>
                    </div>
                    <!-- Fromulario -->
                    <div class="modal-body">
                        <accounting-show-errors ref="accountingAccountForm" />
                        <h6>INFORMACIÓN DEL ASIENTO CONTABLE</h6>
                        <br />
                        <div class="row">
                            <div class="col-2">
                                <strong>Código:</strong> {{ records.reference }}
                            </div>
                            <div class="col-2">
                                <strong>Fecha:</strong>
                                {{ format_date(records.from_date) }}
                            </div>
                            <div class="col-2">
                                <strong>Estatus:</strong>
                                <span
                                    class="badge badge-success"
                                    v-if="records.approved"
                                >
                                    <strong>APROBADO </strong></span
                                >
                                <span
                                    class="badge badge-danger"
                                    v-if="!records.approved"
                                >
                                    <strong>NO APROBADO</strong></span
                                >
                            </div>
                            <div class="col-2">
                                <strong>Categoria:</strong>
                                {{ accounting_entry_category }}
                            </div>
                            <div class="col-4">
                                <strong>Tipo de moneda:</strong>
                                {{ currency }} (<strong>{{
                                    currency_symbol
                                }}</strong
                                >)
                            </div>
                            <div class="col-4">
                                <strong>Institución:</strong> {{ institution }}
                            </div>
                            <div class="col-4">
                                <strong>Descripción ó concepto:</strong>
                                {{ concept }}
                            </div>
                            <div class="col-4">
                                <strong>Observaciones:</strong>
                                {{ observations }}
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr class="row">
                                            <th
                                                tabindex="0"
                                                class="col-2"
                                                style="
                                                    border: 1px solid #dee2e6;
                                                    position: relative;
                                                "
                                            >
                                                Código
                                            </th>
                                            <th
                                                tabindex="0"
                                                class="col-5"
                                                style="
                                                    border: 1px solid #dee2e6;
                                                    position: relative;
                                                "
                                            >
                                                Denominación
                                            </th>
                                            <th
                                                tabindex="0"
                                                class="col-2"
                                                style="
                                                    border: 1px solid #dee2e6;
                                                    position: relative;
                                                "
                                            >
                                                Debe
                                            </th>
                                            <th
                                                tabindex="0"
                                                class="col-2"
                                                style="
                                                    border: 1px solid #dee2e6;
                                                    position: relative;
                                                "
                                            >
                                                Haber
                                            </th>
                                            <th
                                                tabindex="0"
                                                class="col-1"
                                                style="
                                                    border: 1px solid #dee2e6;
                                                    position: relative;
                                                "
                                            >
                                                Referencia Bancaria
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                row, index
                                            ) in accounting_accounts"
                                            :key="index"
                                            class="row"
                                        >
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-2 text-left"
                                            >
                                                {{
                                                    row.account.group +
                                                    "." +
                                                    row.account.subgroup +
                                                    "." +
                                                    row.account.item +
                                                    "." +
                                                    row.account.generic +
                                                    "." +
                                                    row.account.specific +
                                                    "." +
                                                    row.account.subspecific +
                                                    "." +
                                                    row.account.institutional
                                                }}
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-5 text-left"
                                            >
                                                {{ row.account.denomination }}
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-2 text-right"
                                            >
                                                {{ addDecimals(row.debit) }}
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-2 text-right"
                                            >
                                                {{ addDecimals(row.assets) }}
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-1 text-left"
                                            >
                                                {{ row.bank_reference }}
                                            </td>
                                        </tr>
                                        <tr class="row">
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-7 text-left"
                                            >
                                                Totales Debe / Haber
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-2 text-right"
                                            >
                                                {{ currency_symbol }}
                                                {{
                                                    addDecimals(
                                                        records.tot_debit
                                                    )
                                                }}
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-2 text-right"
                                            >
                                                {{ currency_symbol }}
                                                {{
                                                    addDecimals(
                                                        records.tot_assets
                                                    )
                                                }}
                                            </td>
                                            <td
                                                style="
                                                    border: 1px solid #dee2e6;
                                                "
                                                tabindex="0"
                                                class="col-1"
                                            ></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["id", "modal_name"],
    data() {
        return {
            records: [],
            columns: ["account.denomination", "debit", "assets"],
        };
    },
    created() {
        this.table_options.headings = {
            "account.denomination": "Denominación",
            debit: "Debe",
            assets: "Haber",
        };
        this.table_options.columnsClasses = {
            "account.denomination": "col-xs-8",
            debit: "col-xs-2",
            assets: "col-xs-2",
        };
    },
    mounted() {},
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset() {},
        addDecimals(value) {
            return parseFloat(value).toFixed(this.currency_decimal_places);
        },
    },
    computed: {
        reference: function () {
            if (this.records.reference) {
                return this.records.reference;
            }
            return null;
        },
        accounting_entry_category: function () {
            if (this.records.accounting_entry_category) {
                return this.records.accounting_entry_category.name;
            }
            return null;
        },
        institution: function () {
            if (this.records.institution) {
                return this.records.institution.name;
            }
            return null;
        },
        currency: function () {
            if (this.records.currency) {
                return this.records.currency.name;
            }
            return null;
        },
        currency_decimal_places: function () {
            if (this.records.currency) {
                return this.records.currency.decimal_places;
            }
            return 0;
        },
        currency_symbol: function () {
            if (this.records.currency) {
                return this.records.currency.symbol;
            }
            return null;
        },
        concept: function () {
            if (this.records.concept) {
                const regexForStripHTML = /(<([^>]+)>)/gi;

                return this.records.concept.replaceAll(regexForStripHTML, "");
            }
            return null;
        },
        observations: function () {
            if (this.records.observations) {
                const regexForStripHTML = /(<([^>]+)>)/gi;

                return this.records.observations.replaceAll(
                    regexForStripHTML,
                    ""
                );
            }
            return null;
        },
        accounting_accounts: function () {
            if (this.records.accounting_accounts) {
                return this.records.accounting_accounts;
            }
            return [];
        },
    },
};
</script>
