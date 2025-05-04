<template>
    <section id="PayOrderForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-4">
                    <h6>Datos de la Orden</h6>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6" id="helpFinanceInstitution">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Institución</label>
                        <select2 :disabled="record.is_payroll_contribution"
                            :options="institutions"
                            v-model="record.institution_id" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3" id="helpFinanceOrderDate">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Fecha</label>
                        <input type="date" class="form-control input-sm fiscal-year-restrict" data-toggle="tooltip"
                            @input="record.documentType != 'T' ? getSourceDocuments() : '';"
                            title="Fecha de la órden de pago" v-model="record.ordered_at">
                    </div>
                </div>
                <div class="col-md-3" id="helpFinanceOrderType">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Tipo de Orden</label>
                        <select2 :disabled="record.is_payroll_contribution"
                            :options="types" v-model="record.type"
                            @input="getSourceDocuments(); resetInfoType();" />
                    </div>
                </div>
                <div class="col-md-3" id="helpFinanceDocumentType">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Tipo de Documento</label>
                        <select2 :options="documentTypes" v-model="record.documentType"
                            @input="getSourceDocuments(); disableIsPartial(); resetInfoDoc();"
                            :disabled="record.is_payroll_contribution" />
                    </div>
                </div>
                <div class="col-md-3" v-show="record.documentType == 'M'" id="helpFinanceCurrencyType">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Tipo de Moneda</label>
                        <select2 :options="currencies"
                            v-model="accounting.currency.id"
                            @input="sendEntryData(sendData)"
                            :disabled="record.is_payroll_contribution" />
                    </div>
                </div>
                <div class="col-md-3" v-show="record.documentType == 'T'" id="">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Mes:</label>
                        <select2 :options="months"
                            v-model="record.month"
                            @input="resetInfoDeduction(true)"
                            />
                    </div>
                </div>
                <div class="col-md-3" v-show="record.documentType == 'T'" id="">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Periodo:</label>
                        <select2 :options="periods"
                            v-model="record.period"
                            @input="resetInfoDeduction(); getSourceDocuments()"
                            :disabled="!record.month"
                            />
                    </div>
                </div>
                <div class="col-md-3" v-show="record.documentType != 'M'" id="helpFinanceDocumentOrigin">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Nro. Documento de Origen</label>
                        <select2 :options="documentSources"
                            v-model="record.document_sourceable_id"
                            :disabled="record.documentType == 'T' && (!record.month || !record.period) || record.is_payroll_contribution"
                            />
                    </div>
                </div>
            </div>
            <div v-if="record.documentType == 'T' && record.document_sourceable_id && deductionsToPay.length > 0" class="row">
                <div class="col-12 mt-4 mb-4">
                    <h6>Lista de Retenciones a ser pagadas</h6>
                </div>
            </div>
            <div v-if="record.documentType == 'T' && record.document_sourceable_id && deductionsToPay.length > 0" class="row">
                <div class="col-md-1">
                </div>
                <div class="col-md-10">
                    <table
                        class="table table-hover table-striped"
                    >
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Tipo</th>
                                <th>Monto en {{accounting.currency.symbol}}</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(deduction,
                                index) in deductionsToPay"
                                :key="index"
                            >
                                <td class="text-center">
                                    {{
                                        index + 1
                                    }}
                                </td>
                                <td class="text-center">
                                    {{
                                        deduction.name
                                    }}
                                </td>
                                <td class="text-center">
                                    {{
                                        deduction.amount
                                    }}
                                </td>
                                <td class="text-center">
                                    {{
                                        deduction.deducted_at
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-1">
                </div>
            </div>
            <div class="row">
                <div class="col-12 mt-4 mb-4">
                    <h6>Datos del Proveedor ó Beneficiario</h6>
                </div>
            </div>
            <div class="row">
                <div :class="record.documentType == 'T' ? 'col-md-6' : 'col-md-4'" id="helpFinanceBusinessName">
                    <div v-if="record.documentType != 'T'" class="form-group is-required">
                        <label for="" class="control-label">Nombre o Razón Social</label>
                        <span class="select2">
                            <select2 :options="receivers" v-model="record.name_sourceable_id"
                                @input="sendEntryData(sendData)" :disabled="record.is_payroll_contribution" />
                        </span>
                    </div>
                    <div v-if="record.documentType == 'T'" class="form-group is-required">
                        <label for="" class="control-label">Nombre o Razón Social</label>
                        <v-multiselect
                            :options="receivers"
                            track_by="text"
                            :hide_selected="false"
                            v-model="record.receiver"
                            :multiple="false"
                            :search_change="(query) => applyFunctionDebounce(query, searchReceivers)"
                            :internal_search="false"
                            :searchable="true"
                            :taggable="true"
                            :add_tag="addTag"
                            :group_values="'group'"
                            :group_label="'label'"
                            @input="selectAccountingAccount(); sendEntryData(sendData)"
                            style="margin-top: -8px;"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4" v-if="record.documentType == 'T'">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Cuenta contable</label>
                        <select2
                            :options="accounting_accounts"
                            v-model="record.accounting_account_id"
                            @input="sendEntryData(sendData)"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceDocumentNumber" v-if="record.documentType == 'M'">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Nro. de Documento (solo para órdenes manuales)</label>
                        <span class="d-inline" data-toggle="tooltip"
                            title="Indique un Nro. de documento para las órdenes manuales">
                            <input type="text" class="form-control input-sm" v-model="record.document_number">
                        </span>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceAmount">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Monto</label>
                        <span class="d-inline" data-toggle="tooltip" title="Indique un monto para la órden de pago">
                            <input type="number" step="0.01" class="form-control input-sm"
                                v-model="record.source_amount" :disabled="record.document_sourceable_id !== ''">
                        </span>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceConcept">
                    <div class="form-group is-required">
                        <label for="" class="control-label">Concepto</label>
                        <span class="d-inline" data-toggle="tooltip" title="Indique un concepto para la órden de pago">
                            <input :disabled="record.is_payroll_contribution"
                                type="text" class="form-control input-sm"
                                v-model="record.concept">
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12" id="helpFinanceObservation">
                    <div class="form-group">
                        <label for="" class="control-label">Observación</label>
                        <span class="d-inline" data-toggle="tooltip"
                            title="Indique una observación para la orden de pago">
                            <ckeditor :editor="ckeditor.editor" :config="ckeditor.editorConfig" class="form-control"
                                tag-name="textarea" rows="3" v-model="record.observations"></ckeditor>
                        </span>
                    </div>
                </div>
            </div>
            <div id="helpAccountingData">
                <div class="row">
                    <div class="col-12 mt-4 mb-4">
                        <h6>Datos Contables</h6>
                    </div>
                </div>
                <accounting-entry-generator ref="accountingEntryGenerator" :recordToConverter="[]" :showEdit="showEdit" />
            </div>
        </div>
        <div class="card-footer text-right">
            <div id="helpParamButtons">
                <buttonsDisplay :route_list="route_list" display="false"></buttonsDisplay>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    props: {
        edit_object: {
            type: String,
            required: false,
        },
        registered_accounts: {
            type: String,
            required: false,
        },
    },
    data() {
        return {
            record: {
                ordered_at: '',
                type: '',
                documentType: '',
                observations: '',
                source_amount: 0,
                amount: 0,
                institution_id: '',
                document_sourceable_id: '',
                name_sourceable_id: '',
                finance_payment_method_id: '',
                finance_bank_id: '',
                finance_bank_account_id: '',
                budget_compromise_id: '', //Solo es para identificar el compromiso de existir
                is_partial: false,
                document_number: '',
                concept: '',
                //datos para solo para deducciones
                receiver: '', //Para guardar los datos del Beneficiario en caso de un pago de retenciones
                deductions_ids: [], //Guarda los ids de las deducciones que se agruparon para ser pagadas
                month: '',
                period: '',
                is_payroll_contribution: false,
            },
            sendData: true,
            errors: [],
            types: [
                { 'id': '', 'text': 'Seleccione...' },
                { 'id': 'PR', 'text': 'Presupuestario' },
                { 'id': 'NP', 'text': 'No presupuestario' }
            ],
            allDocumentTypes: [
                { 'id': '', 'text': 'Seleccione...' },
                { 'id': 'P', 'text': 'Compromiso Presupuestario', 'type': 'PR' },
                { 'id': 'M', 'text': 'Manual', 'type': 'NP' },
                { 'id': 'C', 'text': 'Orden de Compra/Servicio', 'type': 'PR'},
                { 'id': 'T', 'text': 'Retenciones', 'type': 'NP' },
                { 'id': 'O', 'text': 'Otro', 'type': 'PR' }
            ],
            documentTypes: [],
            documentSources: [
                { 'id': '', 'text': 'Seleccione...' }
            ],
            receivers: [
                { 'id': '', 'text': 'Seleccione...' }
            ],
            documentNumbers: [
                { 'id': '', 'text': 'Seleccione...' }
            ],
            months: [
                { id: '', text: 'Seleccione...' },
                { id: 1, text: 'Enero' },
                { id: 2, text: 'Febrero' },
                { id: 3, text: 'Marzo' },
                { id: 4, text: 'Abril' },
                { id: 5, text: 'Mayo' },
                { id: 6, text: 'Junio' },
                { id: 7, text: 'Julio' },
                { id: 8, text: 'Agosto' },
                { id: 9, text: 'Septiembre' },
                { id: 10, text: 'Octubre' },
                { id: 11, text: 'Noviembre' },
                { id: 12, text: 'Diciembre' }
            ],
            periods: [
                { 'id': '', 'text': 'Seleccione...' },
                { 'id': 1, 'text': 'Primera Quincena' },
                { 'id': 2, 'text': 'Segunda Quincena' },
                { 'id': 3, 'text': 'Mensual' },
            ],
            currencies: [],
            autoAccounting: [],
            accounting_accounts: [],
            institutions: [],
            displayedMessage: false,
            enableInput: false,
            recordsAccounting: [],
            rowsToDelete: [],
            showEdit: true,
            eraseEdit: true,
            eraseDeductionData: true,
            deductionsToPay: [],
            accounting: {
                date: '',
                reference: '',
                concept: '',
                observations: '',
                category: '',
                totDebit: 0,
                totAssets: 0,
                institution_id: null,
                currency_id: null,
                currency: {
                    id: '',
                    symbol: '',
                    name: '',
                    decimal_places: 0,
                },
            },
            currency_id_tmp: '',
        }
    },
    watch: {
        'record.source_amount' : function (newValue, oldValue) {
            const vm = this;
            if (newValue) {vm.sendEntryData(vm.sendData)};
        },
        'record.document_sourceable_id' : function (newValue, oldValue) {
            const vm = this;
            newValue && vm.setCompromise(vm.sendData);
        },
        'record.type' : function (newValue, oldValue) {
            const vm = this;
            if (newValue == 'NP') {
                vm.documentTypes = vm.allDocumentTypes.filter(doc => (!doc.id || doc.type == 'NP'));
            } else if (newValue != oldValue) {
                vm.documentTypes = vm.allDocumentTypes.filter(doc => (!doc.id || doc.type == 'PR'));
                vm.getReceivers();
            }
        },

        'record.documentType' : function (newValue, oldValue) {
            const vm = this;
            (newValue == 'T' || newValue == 'M') && vm.getAccountingAccounts();
        }
    },
    computed: {
    },
    methods: {
        /**
         * Método que envía la información al componente de asientos contables
         * para generar el asiento de la orden de pago
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        sendEntryData(edit = true) {
            const vm = this;

            if (edit) {
                let data = [];
                let entryData = [];
                let currency_id = '';

                if (vm.record.document_sourceable_id) {
                    for (let doc of vm.documentSources) {
                        if (doc.id == vm.record.document_sourceable_id) {
                            if (doc.accounting_accounts) {
                                let accounts = Object.values(doc.accounting_accounts);
                                for (let acc of accounts) {
                                    data = {
                                        debit: true,
                                        assets: false,
                                        amount: acc.amount,
                                        account: acc.account,
                                        is_retention: false,
                                    };
                                    entryData.push(data);
                                }
                            }

                            if (doc.currency) {
                                vm.loading = true;
                                // Obtener el id del comprimiso seleccionado.
                                if (vm.record && vm.record.budget_compromise_id !== undefined) {
                                    axios.get(`${window.app_url}/budget/compromises/` + vm.record.budget_compromise_id)
                                        .then(response => {
                                            if (response.data) {
                                                // Obtener el registro de la orden de compra asociada al compromiso.
                                                return axios.get(`${window.app_url}/purchase/direct_hire/show-direct-hire-currency/`
                                                    + response.data.budget_compromise.document_number);
                                            } else {
                                                console.log("Error en la respuesta de la información");
                                                throw new Error("Error en la respuesta de la información");
                                                vm.loading = false;
                                            }
                                        })
                                        .then(response => {
                                            if (response.data) {
                                                vm.currency_id_tmp = response.data.record.currency_id;
                                                // Pasarle el id de la moneda al componente de asientos contables.
                                                vm.$refs.accountingEntryGenerator.changeCurrency(vm.currency_id_tmp);
                                            } else {
                                                console.log("Error en la respuesta de la información");
                                                vm.loading = false;
                                            }
                                        })
                                        .catch(error => {
                                            console.error("Error obteniendo la información:", error);
                                            vm.loading = false;
                                        });
                                } else {
                                    currency_id = doc.currency.id;
                                    vm.loading = false;
                                }
                            }
                        }
                    }

                    if (vm.record.documentType == 'T') {
                        data = {
                            debit: false,
                            assets: true,
                            amount: vm.record.amount,
                            account: vm.record.accounting_account_id,
                            is_retention: false,
                        };
                        entryData.push(data);
                    } else {
                        for (let receiver of vm.receivers) {
                        if (receiver.id != '') {
                            for (let child of receiver.children) {
                                if (child.id == vm.record.name_sourceable_id) {
                                    data = {
                                        debit: false,
                                        assets: true,
                                        amount: vm.record.amount,
                                        account: child.accounting_account_id,
                                        is_retention: false,
                                    };

                                    entryData.push(data);
                                }
                            }
                        }
                    }
                    }
                    vm.$refs.accountingEntryGenerator.chargeAccounts(entryData);
                    if (vm.currency_id_tmp != '') {
                        vm.$refs.accountingEntryGenerator.changeCurrency(vm.currency_id_tmp);
                    } else {
                        vm.$refs.accountingEntryGenerator.changeCurrency(currency_id);
                    }
                }

                if (vm.record.documentType == 'M') {
                    if (vm.currency_id_tmp != '') {
                        vm.$refs.accountingEntryGenerator.changeCurrency(vm.currency_id_tmp);
                    } else {
                        vm.$refs.accountingEntryGenerator.changeCurrency(vm.accounting.currency.id);
                    }
                }
            }
        },
        /**
         * Reinicia los campos del formularios
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            const vm = this;
            vm.record.ordered_at = '';
            vm.record.type = '';
            vm.record.documentType = '';
            vm.record.observations = '';
            vm.record.source_amount = 0;
            vm.record.amount = 0;
            vm.record.institution_id = '';
            vm.record.document_sourceable_id = '';
            vm.record.name_sourceable_id = '';
            vm.record.finance_payment_method_id = '';
            vm.record.finance_bank_id = '';
            vm.record.finance_bank_account_id = '';
            vm.record.budget_compromise_id = '';
            vm.record.is_partial = false;
            vm.record.document_number = '';
            vm.record.concept = '';
            vm.record.accounting_account_id = '';
            vm.record.receiver = '';
            vm.getCurrencies();
        },
        /**
         * Obtiene un listado de documentos a los cuales ordenar un pago
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getSourceDocuments(withoutLoader = false) {
            const vm = this;

            if (!vm.record.ordered_at ||
                !vm.record.type ||
                !vm.record.documentType ||
                vm.record.documentType == 'M'
            ) {
                return false;
            }
            if (vm.record.documentType == 'T' && (!vm.record.month || !vm.record.period)) {
                return false;
            }
            if (!withoutLoader) {
                vm.loading = true;
            }
            let sourceId = '';

            if (vm.edit_object) {
                let editData = JSON.parse(vm.edit_object);
                sourceId = editData.document_sourceable_id;
            }

            await axios.post(
                `${vm.app_url}/finance/pay-orders/documents/get-sources`,
                {
                    ordered_at: vm.record.ordered_at,
                    type: vm.record.type,
                    documentType: vm.record.documentType,
                    id: vm.record.id ? vm.record.id : '',
                    document_sourceable_id: vm.record.id ? sourceId : '',
                    edit : vm.edit_object ? true : false,
                    month: vm.record.month,
                    period: vm.record.period,
                }
            ).then(response => {

                vm.documentSources = response.data.records;

            }).catch(error => {
                console.error(error);
            });
            vm.displayedMessage = false;

            if (vm.edit_object) {
                let editData = JSON.parse(vm.edit_object);
                if(vm.record.documentType == 'T' && vm.inputEdit) {
                    vm.record.document_sourceable_id = editData.document_sourceable_id;
                    vm.inputEdit = false;
                } else if (vm.record.documentType != 'T') {
                    vm.record.document_sourceable_id = editData.document_sourceable_id;
                }
            }

            if (!withoutLoader) {
                vm.loading = false;
            }
        },

        /**
         * Establece los datos del compromiso si existen
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        setCompromise(edit = true) {
            const vm = this;

            if (edit) {
                if (vm.record.document_sourceable_id) {
                    const documentSource = vm.documentSources.filter(
                        (ds) => parseInt(ds.id) === parseInt(vm.record.document_sourceable_id)
                    )[0] || '';
                    let sourceable_id = '';

                    vm.receivers.forEach(receiver => {
                        if (receiver.children) {
                            receiver.children.forEach(rec => {
                                const foundReceiver = rec.associateables.find(
                                    r => documentSource && documentSource.receiver && r === documentSource.receiver.id
                                );

                                if (foundReceiver) {
                                    sourceable_id = rec.id;
                                }
                            });
                        }
                    });

                    if(vm.record.documentType == 'T' && documentSource && documentSource.receiver && documentSource.receiver.description) {
                        vm.searchReceivers(documentSource.receiver.description);

                        setTimeout(() => {
                            let receiver = {
                                text: documentSource.receiver.description,
                                id: documentSource.receiver.receiverable_id,
                                class: documentSource.receiver.receiverable_type,
                                group: documentSource.receiver.group,
                                accounting_account_id:
                                    documentSource.receiver.associateable_id,
                            };

                            vm.record.receiver = receiver;
                            vm.record.accounting_account_id = documentSource.receiver.associateable_id
                        }, 1000);
                    }

                    if(documentSource.deductions_ids && vm.record.documentType == 'T') {
                        vm.record.deductions_ids = documentSource.deductions_ids;
                        vm.getDeductionsToPay(vm.record.deductions_ids);
                    }
                    vm.accounting.currency = (documentSource) ? documentSource.currency : 0;
                    vm.record.budget_compromise_id = (documentSource) ? documentSource.budget_compromise_id : '';
                    vm.record.name_sourceable_id = sourceable_id;
                    vm.record.source_amount = (documentSource) ?
                        parseFloat(documentSource.budget_total_amount).toFixed(
                            this.accounting && this.accounting.currency ?
                            this.accounting.currency.decimal_places : 2
                        ) : 0;
                    vm.record.concept = (documentSource) ? documentSource.description : '';
                    vm.record.amount = (documentSource) ?
                        parseFloat(documentSource.budget_total_amount).toFixed(
                            this.accounting && this.accounting.currency ?
                            this.accounting.currency.decimal_places : 2
                        ) : 0;
                }
            }
        },

        /**
         * Agrega decimales al monto
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        addDecimals(value) {
            return parseFloat(value).toFixed(this.accounting.currency.decimal_places);
        },
        /**
         * Establece los datos de la orden de pago a generar
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async createRecord() {
            const vm = this;
            const url = vm.setUrl('finance/pay-orders');
            if (vm.record.id) {
                await vm.updateRecord(url);
            }
            else {
                vm.loading = true;
                var fields = {};
                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                /** Datos de los ítems contables */
                fields['accounting'] = vm.$refs.accountingEntryGenerator.data;
                fields['accountingItems'] = vm.$refs.accountingEntryGenerator.recordsAccounting;

                await axios.post(url, fields).then(response => {
                    bootbox.confirm('Desea generar el comprobante?', function (result) {
                        if (result) {
                            let link = document.createElement('a');
                            link.target = '_blank';
                            link.href = vm.setUrl(`finance/pay-orders/pdf/${response.data.record.id}`);
                            link.click();
                            setTimeout(() => {
                                location.href = vm.setUrl('finance/pay-orders');
                            }, 3000);
                        }
                        else {
                            location.href = vm.setUrl('finance/pay-orders');
                        }
                    });
                    resultStorage = true;
                }).catch(error => {
                    vm.errors = [];

                    if (typeof (error.response) != "undefined") {
                        for (let index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
                vm.loading = false;
            }
        },
        /**
         * Método que permite actualizar información
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta de la acci´on que modificará los datos
         */
        async updateRecord(url) {
            const vm = this;
            vm.loading = true;
            var fields = {};
            url = vm.setUrl(url);

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            /** Datos de los ítems contables */
            fields['accounting'] = vm.$refs.accountingEntryGenerator.data;
            fields['accountingItems'] = vm.$refs.accountingEntryGenerator.recordsAccounting;

            await axios.patch(`${url}${(url.endsWith('/')) ? '' : '/'}${vm.record.id}`, fields).then(response => {
                bootbox.confirm('Desea generar el comprobante?', function (result) {
                    if (result) {
                        let link = document.createElement('a');
                        link.target = '_blank';
                        link.href = vm.setUrl(`finance/pay-orders/pdf/${response.data.record.id}`);
                        link.click();
                        setTimeout(() => {
                            location.href = vm.setUrl('finance/pay-orders');
                        }, 3000);
                    }
                    else {
                        location.href = vm.setUrl('finance/pay-orders');
                    }
                });
                resultStorage = true;
            }).catch(error => {
                vm.errors = [];

                if (typeof (error.response) != "undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
            });
            vm.loading = false;
        },
        /**
         * Coloca el botón de pago parcial en false en caso que sea una orden manual
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        disableIsPartial() {
            const vm = this;
            if (vm.record.documentType == 'M') {
                vm.record.is_partial = '';
                vm.showEdit = false;
            } else {
                vm.showEdit = true;
            }
        },
        /**
         * Carga los datos en el formulario al editar un registro
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        async loadForm() {
            let vm = this;
            vm.sendData = false;
            let editData = JSON.parse(vm.edit_object);

            vm.eraseEdit = false;
            vm.eraseDeductionData = false;
            vm.inputEdit = true;
            vm.record.id = editData.id;
            vm.record.ordered_at = vm.format_date(editData.ordered_at, 'YYYY-MM-DD');
            vm.record.type = editData.type;
            setTimeout(() => {
                vm.record.documentType = editData.document_type;
            }, 1000);

            vm.record.observations = editData.observations;
            vm.record.source_amount = vm.currencyFormat(
                editData.source_amount,
                this.accounting.currency &&
                this.accounting.currency.decimal_places > 0 ?
                this.accounting.currency.decimal_places : 2
            );
            vm.record.amount = vm.currencyFormat(
                editData.amount,
                this.accounting.currency &&
                this.accounting.currency.decimal_places > 0 ?
                this.accounting.currency.decimal_places : 2
            );
            vm.record.institution_id = editData.institution_id;
            vm.record.name_sourceable_id = vm.record.documentType != 'T' ? editData.name_sourceable_id : null;
            vm.record.budget_compromise_id = editData.budget_compromise_id;
            vm.record.is_partial = editData.is_partial;
            vm.record.document_number = editData.document_number;
            vm.record.concept = editData.concept;
            vm.record.document_sourceable_id = vm.record.documentType != 'T' ? editData.document_sourceable_id : null;
            vm.record.month = editData.month;
            vm.record.period = editData.period;
            vm.record.is_payroll_contribution = editData.is_payroll_contribution;

            if (vm.record.documentType == 'M') {
                vm.accounting.currency.id = editData.currency_id;
            }
            if (vm.record.documentType != 'T') {
                await vm.getCurrencies().then(() => {
                    addAccounts();
                });
                await vm.getSourceDocuments(true); //Carga los documentos de origen y se le indica que no muestre el mensaje de espera
                const documentSource = vm.documentSources.filter(
                    (ds) => parseInt(ds.id) === parseInt(vm.record.document_sourceable_id)
                )[0] || '';

                vm.record.budget_compromise_id = (documentSource) ? documentSource.budget_compromise_id : '';
                vm.record.document_sourceable_id = documentSource.id;
                vm.sendData = true;

                function addAccounts() {
                    if (vm.registered_accounts) {
                        let registeredAccounts = JSON.parse(vm.registered_accounts);
                        vm.$refs.accountingEntryGenerator.recordsAccounting = [];

                        for (let account of registeredAccounts.accounting_entry.accounting_accounts) {
                            vm.$refs.accountingEntryGenerator.recordsAccounting.push({
                                id: account.accounting_account_id,
                                entryAccountId: account.accounting_entry_id,
                                debit: account.debit,
                                assets: account.assets,
                            });
                        }
                        vm.$refs.accountingEntryGenerator.data.totAssets = parseFloat(registeredAccounts.accounting_entry.tot_assets).toFixed(vm.accounting.currency && vm.accounting.currency.decimal_places ? vm.accounting.currency.decimal_places : 2);
                        vm.$refs.accountingEntryGenerator.data.totDebit = parseFloat(registeredAccounts.accounting_entry.tot_debit).toFixed(vm.accounting.currency && vm.accounting.currency.decimal_places ? vm.accounting.currency.decimal_places : 2);
                    }
                }
            }

            vm.record.finance_bank_account_id = editData.finance_bank_account_id;

            setTimeout(() => {
                vm.record.finance_bank_account_id = editData.finance_bank_account_id;
            }, 1500);
        },

        /**
         * Obtiene un listado de los receptores de órdenes de pago por cancelar
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        async getReceivers() {
            const vm = this;
            await axios.get('/finance/pay-orders/list/get-receivers').then(response => {
                vm.receivers = response.data.records;
            }).catch(error => {
                console.error(error);
            });
        },
        /**
         * Agrega una nuevo elemento al arreglo de beneficiarios.
         *
         * @param {type} newTag - La nueva etiqueta que se va a agregar.
         * @return {type} - Descripción del valor de retorno.
         */
        addTag(newTag) {
            let tag = [
                {
                    label: "Otros",
                    group: [
                        {
                            id: newTag,
                            text: newTag,
                            class: "Modules\\Finance\\Models\\FinancePaymentDeduction",
                            group: "Otros",
                        },
                    ],
                },
            ];

            this.receivers.push(tag);
            this.record.receiver = tag[0]["group"][0];
        },

        selectAccountingAccount() {
            const vm = this;

            if (vm.record.receiver.accounting_account_id) {
                vm.record.accounting_account_id =
                    vm.record.receiver.accounting_account_id;
                vm.record.name_sourceable_id =
                    vm.record.receiver.id;
            } else {
                vm.record.accounting_account_id = "";
                vm.record.name_sourceable_id = "";
            }
        },

        /**
         * limpia ciertos campos del formulario al cambiar de tipo de orden o tipo de documento
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        resetInfoType() {
            const vm = this;
            if (vm.eraseEdit) {
                vm.record.documentType = '';
                vm.record.observations = '';
                vm.record.source_amount = 0;
                vm.record.amount = 0;
                vm.record.document_sourceable_id = '';
                vm.record.name_sourceable_id = '';
                vm.record.finance_payment_method_id = '';
                vm.record.finance_bank_id = '';
                vm.record.finance_bank_account_id = '';
                vm.record.budget_compromise_id = '', //Solo es para identificar el compromiso de existir;
                vm.record.is_partial = false;
                vm.record.document_number = '';
                vm.record.concept = '';
                vm.record.accounting = '';
                vm.record.receiver = '';
                vm.record.deductions_ids = [];
                vm.recordsAccounting = [];
                vm.$refs.accountingEntryGenerator.data.totAssets = 0;
                vm.$refs.accountingEntryGenerator.data.totDebit = 0;
                vm.$refs.accountingEntryGenerator.recordsAccounting = [];
            }
        },

        /**
         * limpia ciertos campos del formulario al cambiar de tipo de orden o tipo de documento
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        resetInfoDoc() {
            const vm = this;
            if (vm.eraseEdit) {
                vm.record.source_amount = 0;
                vm.record.amount = 0;
                vm.record.document_sourceable_id = '';
                vm.record.name_sourceable_id = '';
                vm.record.budget_compromise_id = '', //Solo es para identificar el compromiso de existir;
                vm.record.is_partial = false;
                vm.record.document_number = '';
                vm.recordsAccounting = [];
                vm.$refs.accountingEntryGenerator.data.totAssets = 0;
                vm.$refs.accountingEntryGenerator.data.totDebit = 0;
                vm.$refs.accountingEntryGenerator.recordsAccounting = [];
                vm.record.deductions_ids = [];

                if (vm.record.documentType != 'T') {
                    vm.record.accounting = '';
                    vm.record.receiver = '';
                }
            } else {
                vm.eraseEdit = true;
            }
        },

        resetInfoDeduction(operation = false) {
            const vm = this;
            if (vm.eraseDeductionData) {
                vm.record.period = operation ? '' : vm.record.period;
                vm.documentSources = [
                    { 'id': '', 'text': 'Seleccione...' }
                ];
                vm.resetInfoDoc();
            } else {
                vm.eraseDeductionData = true;
            }
        },

        /**
         * Método que realiza una consulta para obtener todos los receptores que coincidan
         * con el query de la búsqueda
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        searchReceivers(query) {
            this.receivers = [];

            axios
                .get(`${window.app_url}/all-receivers`, {
                    params: { query: query },
                })
                .then((response) => {
                    this.receivers = response.data;
                });
        },
        /**
         * Obtiene un listado de cuentas patrimoniales
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAccountingAccounts() {
            const vm = this;
            if (vm.accounting_accounts.length > 0) {
                return;
            }
            vm.accounting_accounts = [];
            await axios
                .get(`${window.app_url}/accounting/accounts`)
                .then((response) => {
                    if (response.data.records.length > 0) {
                        vm.accounting_accounts.push({
                            id: "",
                            text: "Seleccione...",
                        });
                        $.each(response.data.records, function() {
                            vm.accounting_accounts.push({
                                id: this.id,
                                text: this.text,
                                disabled: this.original,
                            });
                        });
                    }
                })
                .catch((error) => {
                    vm.logs(
                        "PayrollConceptsComponent",
                        258,
                        error,
                        "getAccountingAccounts"
                    );
                });
        },
    },
    async mounted() {
        const vm = this;
        vm.loading = true;
        await vm.getInstitutions();
        await vm.getReceivers();
        await vm.getCurrencies();

        if (vm.edit_object) {
            await vm.loadForm();
        } else {
            vm.record.is_payroll_contribution = false;
        }
        vm.loading = false;
    }
}
</script>