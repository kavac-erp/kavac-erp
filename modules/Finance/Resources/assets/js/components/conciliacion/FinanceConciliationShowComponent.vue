<template>
    <div>
        <button type="button" @click="addRecord(modal_name + id, route_show, $event)"
            class="btn btn-info btn-xs btn-icon btn-action" title="Visualizar registro" v-has-tooltip data-toggle="modal"
            :data-target="'#' + modal_name + id">
            <i class="fa fa-eye"></i>
        </button>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="modal_name + id">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            Conciliación Bancaria
                        </h6>
                    </div>
                    <div class="modal-body">
                        <h6>INFORMACIÓN DE CONCILIACIÓN BANCARIA</h6>
                        <br>
                        <div class="row">
                            <div class="col-4 my-2"><strong>Código:</strong> {{ records.code }}</div>
                            <div class="col-4 my-2"><strong>Periodo:</strong>
                                {{ `${format_date(records.start_date)} a ${format_date(records.end_date)}` }}</div>
                            <div class="col-4 my-2" v-if="records.document_status?.action"><strong>Estatus:</strong>
                                <span class="text-success" v-if="records.document_status.action === 'AP'">
                                    {{ records.document_status.name }}
                                </span>
                                <span class="text-warning" title="Este registro puede ser aprobado desde asientos contables"
                                    v-else-if="records.document_status.action === 'PR'">
                                    Pendiente
                                </span>
                                <span class="text-danger" v-else-if="records.document_status.action === 'AN'">
                                    {{ records.document_status.name }}
                                </span>
                            </div>
                            <div class="col-4 my-2">
                                <strong>Tipo de moneda:</strong> {{ currency }} (<strong>{{ currency_symbol }}</strong>)
                            </div>
                            <div class="col-4 my-2">
                                <strong>Saldo en banco:</strong> {{ records.bank_balance }} {{ currency_symbol }}
                            </div>
                            <div class="col-4 my-2" v-if="records.system_balance">
                                <strong>Saldo en sistema:</strong> {{ records.system_balance }} {{ currency_symbol }}
                            </div>
                            <div class="col-4 my-2"><strong>Institución:</strong> {{ institution }}</div>
                            <div class="col-4 my-2"><strong>Banco:</strong> {{ bankDescription }}</div>
                            <div class="col-4 my-2"><strong>Nro. de cuenta:</strong> {{ bankAccountNumber }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr class="row">
                                            <th tabindex="0" class="col-1"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Fecha
                                            </th>
                                            <th tabindex="0" class="col-1"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Código de movimiento
                                            </th>
                                            <th tabindex="0" class="col-3"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Concepto en sistema
                                            </th>
                                            <th tabindex="0" class="col-1"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Concepto Bancario
                                            </th>
                                            <th tabindex="0" class="col-2"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Débito
                                            </th>
                                            <th tabindex="0" class="col-2"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Crédito
                                            </th>
                                            <th tabindex="0" class="col-2"
                                                style="border: 1px solid #dee2e6; position: relative;">
                                                Saldo Final
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(mov, index) in records.finance_conciliation_bank_movements" :key="index" class="row">
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-1 text-left">
                                                {{ mov?.accounting_entry_account?.entries?.from_date ?? '' }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-1 text-left">
                                                {{ mov?.accounting_entry_account?.entries?.reference ?? '' }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-3 text-left">
                                                {{ mov?.accounting_entry_account?.entries?.concept ?? '' }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-1 text-left">
                                                {{ mov.concept }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ addDecimals(mov.debit) }} {{ currency_symbol }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ addDecimals(mov.assets) }} {{ currency_symbol }}
                                            </td>
                                            <td class="col-2 text-right">
                                                {{ addDecimals(mov.current_balance) }} {{ currency_symbol }}
                                            </td>
                                        </tr>
                                        <tr class="row">
                                            <td class="col-1 text-left"></td>
                                            <td class="col-1 text-left"></td>
                                            <td class="col-3 text-left"></td>
                                            <td class="col-1 text-left"></td>
                                            <td class="col-2 text-center font-weight-bold">
                                                Total Débito
                                            </td>
                                            <td class="col-2 text-center font-weight-bold">
                                                Total Crédito
                                            </td>
                                            <td class="col-2 text-right"></td>
                                        </tr>
                                        <tr class="row">
                                            <td class="col-1 text-left"></td>
                                            <td class="col-1 text-left"></td>
                                            <td class="col-3 text-left"></td>
                                            <td class="col-1 text-left"></td>
                                            <td class="col-2 text-right">
                                                {{ calculateTotalDebit }} {{ currency_symbol }}
                                            </td>
                                            <td class="col-2 text-right">
                                                {{ calculateTotalAssets }} {{ currency_symbol }}
                                            </td>
                                            <td class="col-2 text-right"></td>
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
    props: ['id', 'modal_name'],
    data() {
        return {
            records: [],
            columns: [
                'accounting_entry_account.entries.from_date',
                'accounting_entry_account.entries.code',
                'accounting_entry_account.entries.concept',
                'concept',
                'debit',
                'assets'
            ],
            total: {
                debit: 0,
                assets: 0
            }
        };
    },
    created() {
        this.table_options.headings = {
            'accounting_entry_account.entries.from_date': 'Fecha',
            'accounting_entry_account.entries.code': 'Código de movimiento',
            'accounting_entry_account.entries.concept': 'Concepto en sistema',
            'concept': 'Concepto Bancario',
            'debit': 'Débito',
            'assets': 'Crédito',
        };
        this.table_options.columnsClasses = {
            'accounting_entry_account.entries.from_date': 'col-xs-1',
            'accounting_entry_account.entries.code': 'col-xs-1',
            'accounting_entry_account.entries.concept': 'Concepto en sistema',
            'concept': 'Concepto Bancario',
            'debit': 'col-xs-2',
            'assets': 'col-xs-2',
        };
    },
    mounted() {

    },
    methods: {

        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset() {

        },
        addDecimals(value) {
            value = !value || value == 'NaN' ? 0.00 : value;
            return parseFloat(value).toFixed(this.currency_decimal_places);
        },
    },
    computed: {
        calculateTotalDebit() {
            let total = 0;

            if (this.records.finance_conciliation_bank_movements) {
                this.records.finance_conciliation_bank_movements.forEach(element => {
                    element.debit = !element.debit || element.debit == 'NaN' ? 0 : element.debit;
    
                    total += parseFloat(element.debit);
                });
            }

            return total;
        },
        calculateTotalAssets() {
            let total = 0;

            if (this.records.finance_conciliation_bank_movements) {
                this.records.finance_conciliation_bank_movements.forEach(element => {
                    element.assets = !element.assets || element.assets == 'NaN' ? 0 : element.assets;
                    console.log(element.assets);
                    total += parseFloat(element.assets);
                });
            }

            return total;
        },
        bankDescription: function () {
            // Crea un elemento div temporal
            const tempDiv = document.createElement('div');

            // Asigna el HTML con etiquetas al contenido del div
            tempDiv.innerHTML = this.records.finance_bank_account?.description ?? '';

            // Devuelve el texto sin etiquetas
            return tempDiv.textContent || tempDiv.innerText || '';
        },
        bankAccountNumber: function() {
            return this.records.finance_bank_account?.ccc_number ?? ''
        },
        reference: function () {
            if (this.records.reference) {
                return this.records.reference;
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

                return this.records.concept.replaceAll(regexForStripHTML, '');
            }
            return null;
        },
        observations: function () {
            if (this.records.observations) {
                const regexForStripHTML = /(<([^>]+)>)/gi;

                return this.records.observations.replaceAll(regexForStripHTML, '');
            }
            return null;
        },
        accounting_accounts: function () {
            if (this.records.accounting_accounts) {
                return this.records.accounting_accounts;
            }
            return [];
        },

    }
};
</script>
