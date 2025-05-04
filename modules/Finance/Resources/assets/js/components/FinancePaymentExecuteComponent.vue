<template>
    <section>
        <!-- card-body -->
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
                    <h6>Datos del Pago</h6>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-3" id="helpFinanceDate">
                        <div class="form-group is-required">
                            <label class="control-label">Fecha</label>
                            <input
                                type="date"
                                class="form-control input-sm fiscal-year-restrict"
                                data-toggle="tooltip"
                                title="Fecha del pago"
                                v-model="record.paid_at"
                            >
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinanceIsPartialSwitch">
                        <div class="form-group is-required">
                            <label class="control-label">¿Pago Parcial?</label>
                            <select2
                                :options="isPartialOptions"
                                v-model="record.is_partial"
                                @input="changeTotalAmount();"
                                :disabled="record.reference_selected.length > 1 || record.is_deduction"
                            />
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinanceReceiver">
                        <div class="form-group is-required">
                            <label class="control-label">
                                Proveedor o Beneficiario
                            </label>
                            <select2
                                :options="receivers"
                                v-model="record.receiver_id"
                                @input="getPayOrders"
                            />
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinanceCurrency">
                        <div class="form-group is-required">
                            <label class="control-label">Tipo de moneda</label>
                            <span class="select2">
                                <select2
                                    :options="currencies"
                                    v-model="record.currency_id"
                                />
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinanceReferenceSelected">
                        <div class="form-group is-required">
                            <label>Nro. de Documento</label>
                            <v-multiselect
                                :options="references"
                                track_by="text"
                                :hide_selected="false"
                                data-toggle="tooltip"
                                title="Indique las ordenes de pago"
                                @input="
                                    setPayOrderData();
                                    isPartialDisabled();
                                    sendEntryData();
                                "
                                v-model="record.reference_selected"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinanceSourceMount">
                        <div class="form-group">
                            <label class="control-label">
                                Monto de la Orden
                            </label>
                            <input
                                type="text"
                                class="form-control text-right"
                                v-model="record.source_amount"
                                readonly
                            >
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinanceSubAmount">
                        <div class="form-group is-required">
                            <label class="control-label">
                                Monto del pago (Subtotal)
                            </label>
                            <input
                                type="text"
                                class="form-control text-right"
                                v-model="record.sub_amount"
                                v-is-numeric
                                :disabled="record.is_partial == '' || record.is_partial == 'false'"
                                @change="changeTotalAmount();"
                            >
                        </div>
                    </div>
                    <div v-if="!record.is_deduction" class="col-md-3" id="helpFinanceDeducationAmount">
                        <div class="form-group is-required">
                            <label>Retenciones</label>
                            <input
                                type="text"
                                class="form-control text-right"
                                v-model="record.deduction_amount"
                                readonly
                            >
                        </div>
                    </div>
                    <div class="col-md-3" id="helpFinancePaidAmount">
                        <div class="form-group is-required">
                            <label>Total a pagar</label>
                            <input
                                type="text"
                                class="form-control text-right"
                                v-model="record.paid_amount"
                                readonly
                            >
                        </div>
                    </div>
                    <div
                        class="col-md-3"
                        id="helpFinanceSelDeduction"
                        v-if="record.currency_id && !record.is_deduction"
                    >
                        <div class="form-group">
                            <label class="control-label">
                                Tipo de retención
                            </label>
                            <div class="row">
                                <div class="select2 col-10">
                                    <select2
                                        :options="deductions"
                                        v-model="selDeduction"
                                    />
                                </div>
                                <div class="col-2">
                                    <a
                                        href="javascript:void(0)"
                                        data-original-title="Agregar retención"
                                        class="btn btn-sm btn-info btn-action btn-tooltip"
                                        data-toggle="modal"
                                        data-target="#addDeduction"
                                    >
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3" id="Payment_number">
                        <div class="form-group" >
                            <label for="">Nro. Factura</label>
                            <input type="text" class="form-control text-right" v-model="record.payment_number"  >
                        </div>
                    </div>
                    <div class="col-md-3" id="general_bank_reference">
                        <div class="form-group is-required">
                            <label for="">Nro. de Referencia bancaria</label>
                            <input type="number" min="0" class="form-control text-right" v-model="record.general_bank_reference"  >
                        </div>
                    </div>
                </div>
                <div v-if="!record.is_deduction" class="row">
                    <div class="col-12 mb-4">
                        Retenciones
                    </div>
                    <div class="col-md-6 mb-4">
                        <table class="table table-sm table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="font-weight-bold">Tipo de retención</th>
                                    <th class="font-weight-bold">Base imponible</th>
                                    <th class="font-weight-bold">Monto</th>
                                    <th class="font-weight-bold">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(ret, index) in record.deductions" :key="index">
                                    <td>
                                        {{ ret.name }}
                                    </td>
                                    <td class="text-center">
                                        {{ ret.mor }}
                                    </td>
                                    <td class="text-center">
                                        {{ formatToCurrency(ret.amount) }}
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-danger btn-action"
                                            href="#"
                                            @click="deleteDeduction(index)"
                                            title="Eliminar este registro"
                                            data-toggle="tooltip"
                                        >
                                            <i class="fa fa-minus-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mt-4 mb-4">
                        <h6>Datos Bancarios</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4" id="helpFinancePaymentMethod">
                        <div class="form-group is-required">
                            <label for="" class="control-label">Método de Pago</label>
                            <span class="select2">
                                <select2
                                    :options="paymentMethods"
                                    v-model="record.finance_payment_method_id" />
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4" id="helpFinanceBank">
                        <div class="form-group is-required">
                            <label for="" class="control-label">Banco</label>
                            <span class="select2">
                                <select2 :options="banks"
                                    @input="getBankAccounts()"
                                    v-model="record.finance_bank_id" />
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4" id="helpFinanceAccountNumber">
                        <div class="form-group is-required">
                            <label for="" class="control-label">Nro. de Cuenta</label>
                            <span class="select2">
                                <select2 :options="accounts"
                                    v-model="record.finance_bank_account_id"
                                    @input="getAccountingAccountId();"
                                    >
                                </select2>
                            </span>
                        </div>
                    </div>
                    <div class="col-12" id="helpFinanceObservation">
                        <div class="form-group is-required">
                            <label  class="control-label">Observaciones</label>
                            <span
                                class="d-inline"
                                data-toggle="tooltip"
                                title="Indique una observación para la emisión de pago"
                            >
                                <ckeditor
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    class="form-control"
                                    tag-name="textarea"
                                    rows="3"
                                    v-model="record.observations"
                                ></ckeditor>
                            </span>
                        </div>
                    </div>
                </div>
                <hr>
                <div id="helpFinanceAccountingData">
                <div class="row">
                    <div class="col-12 mt-4 mb-4">
                        <h6>Datos Contables</h6>
                    </div>
                </div>
                <accounting-entry-generator
                    ref="accountingEntryGenerator"
                    :defaultBankReference="record.general_bank_reference"
                    :recordToConverter="[]"
                    :showEdit="true"
                />
            </div>
        </div>
        <!-- Final card-body -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <buttonsDisplay
                :route_list="route_list"
                display="false"
            ></buttonsDisplay>
        </div>
        <!-- Final card-footer -->
        <div
            class="modal fade text-left"
            id="addDeduction"
            tabindex="-1"
            aria-labelledby="addDeductionLabel"
            aria-hidden="true"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-mathematical-alt-2 inline-block"></i>
                            Retención
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>
                                        M.O.R / Base Imponible
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        data-toggle="tooltip"
                                        min="0"
                                        title="Monto objeto de retención o base imponible"
                                        v-model="mor"
                                        @input="setDeductionAmount"
                                        oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    >
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Monto de la retención</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        data-toggle="tooltip"
                                        v-model="deduction_amount"
                                        title="Monto calculado a partir de la base imponible"
                                        readonly
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="selDeduction=''"
                                data-dismiss="modal"
                            >
                                Cerrar
                            </button>
                            <button
                                type="button"
                                @click="addDeduction(); sendEntryData();"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
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
                    paid_at: '',
                    is_partial: '',
                    receiver_id: '',
                    reference_selected: {},
                    source_amount: 0,
                    sub_amount: 0,
                    deduction_amount: 0,
                    paid_amount: 0,
                    payment_number: 0,
                    general_bank_reference: '',
                    deductions: [],
                    observations: '',
                    currency_id: null,
                    autoAccounting: [],
                    accounting: [],
                    is_deduction: false,
                },
                selDeduction: '',
                accounting: [],
                errors: [],
                deductions: [],
                tmpDeductions: [],
                references: [],
                receivers: [],
                currencies: [],
                paymentMethods: [],
                banks: [],
                accounts: [],
                mor: 0,
                deduction_amount: 0,
                enableInput: true,
                recordsAccounting: [],
                rowsToDelete: [],
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
                isPartialOptions: [
                    {'id': '', 'text': 'Seleccione...'},
                    {'id': false, 'text': 'No'},
                    {'id': true, 'text': 'Si'},
                ],
                send: true,
            }
        },
        methods: {
            /**
             * Reinicia los campos del formularios
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async reset() {
                const vm = this;
                vm.record = {
                    paid_at: '',
                    is_partial: '',
                    receiver_id: '',
                    reference_selected: [],
                    source_amount: 0,
                    sub_amount: 0,
                    deduction_amount: 0,
                    paid_amount: 0,
                    payment_number: 0,
                    general_bank_reference: '',
                    deductions: [],
                    observations: '',
                    AutoAccounting: [],
                    accounting: [],
                    is_deduction: false,
                };
            },

            /**
             * Listado de órdenes de pago pendientes por ejecutar
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async getPayOrders() {
                const vm = this;
                if (!vm.record.receiver_id || !vm.record.currency_id) {
                    return false;
                }

                let associateables;

                vm.receivers.forEach(receiver => {
                    if (receiver.children) {
                        receiver.children.forEach(rec => {
                            const foundReceiver = rec.associateables.find(
                                r => r == vm.record.receiver_id
                            );

                            if (foundReceiver) {
                                associateables = rec.associateables;
                            }
                        });
                    }
                });

                let isUpdate = '';
                if (vm.edit_object) {
                    let editData = JSON.parse(vm.edit_object);
                    isUpdate = editData.id;
                }
                await axios.get(`/finance/pay-orders/pending/${associateables}/${vm.record.currency_id}/${isUpdate}`).then(response => {
                    vm.references = response.data.records;
                }).catch(error => {
                    console.error(error);
                });
            },

            /**
             * Establece el monto de la retención
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setDeductionAmount() {
                const vm = this;
                if (!vm.mor) {
                    return;
                }
                let decimal_places = 2;
                if (vm.record.currency_id && vm.currencies.length > 0) {
                    for (let currency of vm.currencies) {
                        if (currency.id === vm.record.currency_id) {
                            decimal_places = currency.decimal_places;
                        }
                    }
                }
                let deduction = vm.deductions.filter(d => d.id === parseInt(vm.selDeduction))[0] || '';
                let formula = deduction.formula.replace('monto', vm.mor);
                vm.deduction_amount = eval(formula).toFixed(decimal_places);
            },

            /**
             * Agrega la retención seleccionada
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            addDeduction() {
                const vm = this;
                vm.mor = parseFloat(vm.mor).toFixed(2)
                let deduction = vm.deductions.filter(d => d.id === parseInt(vm.selDeduction))[0] || '';
                vm.record.deductions.push({
                    id: deduction.id,
                    name: deduction.text,
                    mor: vm.mor,
                    amount: vm.deduction_amount,
                    formula: deduction.formula,
                    accounting_account: deduction.accounting_account
                });
                vm.setAmounts();
                //vm.deductions = vm.tmpDeductions.filter(d => d.id !== parseInt(vm.selDeduction))[0] || '';
                vm.selDeduction = '';
                vm.deduction_amount = 0;
                vm.mor = '';
                $('#addDeduction').find('.close').click();
            },

            /**
             * Elimina la retención seleccionada
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            deleteDeduction(index) {
                let vm = this;
                bootbox.confirm({
                    title: "¿Eliminar retención?",
                    message: `¿Esta seguro de eliminar esta retención?`,
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar',
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar',
                        },
                    },
                    callback: function (result) {
                        if (result) {
                            vm.record.deduction_amount = (vm.record.deduction_amount - vm.record.deductions[index].amount).toFixed(2);
                            vm.record.deductions.splice(index, 1);
                            vm.sendEntryData();
                            vm.setAmounts();
                        }
                    },
                });
            },

            /**
             * Calcula los montos del pago a ejecutar
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setAmounts() {
                const vm = this;
                let deduction_amount = 0;
                vm.record.deductions.forEach(d => {
                    deduction_amount += parseFloat(d.amount);
                });
                for (let reference of vm.record.reference_selected) {
                    vm.record.deduction_amount = parseFloat(deduction_amount).toFixed(reference.currency.decimal_places);
                    vm.record.paid_amount = parseFloat(
                        parseFloat(vm.record.sub_amount) - parseFloat(deduction_amount)
                    ).toFixed(reference.currency.decimal_places);
                }
            },

            /**
             * Obtiene un listado de los receptores de órdenes de pago por cancelar
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getReceivers() {
                const vm = this;
                await axios.get('/finance/payment-execute/list/get-receivers').then(response => {
                    vm.receivers = response.data.records;
                }).catch(error => {
                    console.error(error);
                });
            },

            /**
             * Establece los datos de la órden de pago seleccionada
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            setPayOrderData() {
                const vm = this;
                if (!vm.record.reference_selected) {
                    return false;
                }
                vm.record.source_amount = 0;
                vm.record.sub_amount = 0;
                vm.record.paid_amount = 0;
                for (let reference_selected of vm.record.reference_selected) {
                    let ref_amount = parseFloat(parseFloat(reference_selected.amount).toFixed(reference_selected.currency.decimal_places));
                    if (ref_amount < 0 && vm.edit_object) {
                        ref_amount = ref_amount * -1;
                    }
                    vm.record.source_amount += ref_amount;
                    vm.record.sub_amount += ref_amount;
                    vm.record.paid_amount += ref_amount;
                }

                vm.record.paid_amount > 0 ? vm.record.paid_amount = parseFloat(
                    vm.record.paid_amount - vm.record.deduction_amount
                ).toFixed(
                    this.accounting && this.accounting.currency &&
                    this.accounting.currency.decimal_places ?
                    this.accounting.currency.decimal_places : 2
                ) : vm.record.paid_amount = 0;
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
                const url = vm.setUrl('finance/payment-execute');

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

                    fields['accountingItems'] = vm.$refs.accountingEntryGenerator.recordsAccounting.map(item => {
                        return {...item, bank_reference: this.record.general_bank_reference }
                    });
                    fields['general_bank_reference'] = this.record.general_bank_reference;

                    await axios.post(url, fields).then(response => {
                        bootbox.confirm('¿Desea generar el comprobante?', function (result) {
                            if (result) {
                                let link = document.createElement('a');
                                link.target = '_blank';
                                link.href = vm.setUrl(`finance/payment-execute/pdf/${response.data.record.id}`);
                                link.click();
                                setTimeout(() => {
                                    location.href = vm.setUrl('finance/payment-execute');
                                }, 3000);
                            }
                            else {
                                location.href = vm.setUrl('finance/payment-execute');
                            }
                        });
                        resultStorage = true;
                    }).catch(error => {
                        vm.errors = [];
                        if (typeof(error.response) !="undefined") {
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

                await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
                    bootbox.confirm('¿Desea generar el comprobante?', function (result) {
                        if (result) {
                            let link = document.createElement('a');
                            link.target = '_blank';
                            link.href = vm.setUrl(`finance/payment-execute/pdf/${response.data.record.id}`);
                            link.click();
                            setTimeout(() => {
                                location.href = vm.setUrl('finance/payment-execute');
                            }, 3000);
                        }
                        else {
                            location.href = vm.setUrl('finance/payment-execute');
                        }
                    });
                    resultStorage = true;
                }).catch(error => {
                    vm.errors = [];
                    if (typeof(error.response) !="undefined") {
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
             * Coloca el botón de pago parcial en false en caso que sea una emisión
             * por mas de una orden de pago
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            isPartialDisabled() {
                const vm = this;
                if (vm.record.reference_selected.length > 1) {
                    vm.record.is_partial = '';
                }
            },

            /**
             * Cambia el monto del pago total, por el sub total cuando es un pago parcial
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            changeTotalAmount() {
                const vm = this;
                if (vm.record.is_partial == 'true') {
                    vm.record.paid_amount = vm.record.sub_amount - vm.record.deduction_amount;
                } else {
                    vm.record.sub_amount = vm.record.source_amount;
                    vm.record.paid_amount = vm.record.source_amount;
                }
                vm.sendEntryData();
            },

            /**
             * Carga los datos en el formulario al editar un registro
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            async loadForm() {
                let vm = this;
                let editData = JSON.parse(vm.edit_object);
                vm.send = false;
                vm.record.id = editData.id;
                vm.record.paid_at = vm.format_date(editData.paid_at, 'YYYY-MM-DD');
                vm.record.is_partial = editData.is_partial.toString();
                vm.record.receiver_id = editData.receiver_id;
                vm.record.currency_id = editData.currency_id;
                vm.record.observations = editData.observations;
                vm.record.reference_selected = editData.finance_pay_orders;
                vm.record.finance_payment_method_id = editData.finance_payment_method_id;
                vm.record.finance_bank_id = editData.finance_bank_account.finance_bank_id;
                await vm.getPayOrders();
                vm.record.source_amount = parseFloat(editData.source_amount).toFixed(editData.currency.decimal_places);
                vm.record.deduction_amount = parseFloat(editData.deduction_amount).toFixed(editData.currency.decimal_places);
                vm.record.paid_amount = parseFloat(editData.paid_amount).toFixed(editData.currency.decimal_places);
                vm.record.sub_amount = (parseFloat(vm.record.deduction_amount) + parseFloat(vm.record.paid_amount)).toFixed(editData.currency.decimal_places);
                vm.record.deductions = editData.finance_payment_deductions;
                if(vm.record.finance_bank_id) {
                    vm.record.payment_number = editData.payment_number;
                }
                vm.record.general_bank_reference = editData.general_bank_reference;
                const timeOpen = setTimeout(addAccounts, 1000);
                function addAccounts () {
                    if (vm.registered_accounts) {
                        let registeredAccounts = JSON.parse(vm.registered_accounts);
                        vm.$refs.accountingEntryGenerator.recordsAccounting = [];
                        for (let account of registeredAccounts.accounting_entry.accounting_accounts) {
                            vm.$refs.accountingEntryGenerator.recordsAccounting.push({
                                id: account.accounting_account_id,
                                bank_reference: vm.record.general_bank_reference,
                                debit: account.debit,
                                assets: account.assets,
                            });
                        }

                        vm.$refs.accountingEntryGenerator.calculateTot();

                        vm.$refs.accountingEntryGenerator.data.totAssets = parseFloat(
                            registeredAccounts.accounting_entry.tot_assets
                        ).toFixed(
                            this.accounting && this.accounting.currency ?
                            this.accounting.currency.decimal_places : 2
                        );
                        vm.$refs.accountingEntryGenerator.data.totDebit = parseFloat(
                            registeredAccounts.accounting_entry.tot_debit
                        ).toFixed(
                            this.accounting && this.accounting.currency ?
                            this.accounting.currency.decimal_places : 2
                        );
                    }
                    vm.send = true;
                }
                vm.setAmounts();
            },

            /**
             * Método que envía la información al componente de asientos contables
             * para generar el asiento de la orden de pago
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            sendEntryData(){
                const vm = this;

                if (vm.send != true) {
                    return;
                }

                let data = [];
                let entryData = [];
                let currency_id = '';
                if (vm.record.reference_selected.length > 0) {
                    for (let receiver of vm.receivers) {
                        if (receiver.id != '') {
                            for (let child of receiver.children) {
                                if (child.id == vm.record.receiver_id) {
                                    data = {
                                        debit: true,
                                        assets: false,
                                        amount: vm.record.sub_amount,
                                        account: child.accounting_account_id,
                                        is_retention: false,
                                    };
                                    entryData.push(data);
                                }
                            }
                        }
                    }

                    if (vm.record.bank_accounting_account_id) {
                        for (let ref of vm.record.reference_selected) {
                            let account = vm.record.bank_accounting_account_id;
                            let amount = vm.record.is_partial ? vm.record.sub_amount : ref.amount;
                            data = {
                                debit: false,
                                assets: true,
                                amount: amount,
                                account: account,
                                is_retention: false,
                            };
                            entryData.push(data);
                        }
                    }

                    for (let deduction of vm.record.deductions) {
                        let account = deduction.accounting_account ?
                            deduction.accounting_account.id :
                            deduction.deduction.accounting_account_id;
                        let amount = deduction.amount;
                        data = {
                            debit: false,
                            assets: true,
                            amount: amount,
                            account: account,
                            is_retention: true,
                        };
                        entryData.push(data);
                    }
                    if (vm.record.currency_id) {
                        currency_id = vm.record.currency_id;
                    }
                    vm.$refs.accountingEntryGenerator.chargeAccounts(entryData);
                    vm.$refs.accountingEntryGenerator.changeCurrency(currency_id);
                }
            },

            /**
             * Método que obtiene la cuenta contable asociada a la cuenta bancaria
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            getAccountingAccountId() {
                const vm = this;

                axios.get(`${window.app_url}/finance/payment-execute/bank/get-bank-accounting-account-id`, {
                    params : {
                        finance_bank_account_id : vm.record.finance_bank_account_id
                    }
                }).then(response => {
                    vm.record.bank_accounting_account_id = response.data;
                    vm.sendEntryData();
                })
            },

            /**
             * Método que obtiene la cuenta bancaria asociada a un registro al editarlo
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
             setBankAccount() {
                const vm = this;

                if (vm.edit_object) {
                    let editData = JSON.parse(vm.edit_object);
                    vm.record.finance_bank_account_id = editData.finance_bank_account_id;
                }
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

                    await vm.setBankAccount();
                }
            },
        },

        watch: {
            'record.reference_selected' : function (newValue) {
                const vm = this;
                if (newValue.length > 0) {
                    let referenceD = newValue.filter(
                        ref => ref.is_deduction == true
                    );

                    vm.record.is_deduction = referenceD.length > 0?
                    referenceD[0].is_deduction : false;
                    vm.record.is_partial = vm.record.is_deduction ? '' : vm.record.is_partial
                } else {
                    vm.record.is_deduction = false;
                    vm.record.is_partial = '';
                }
            }
        },
        async mounted() {
            const vm = this;
            vm.loading = true;
            await vm.reset();
            await vm.getReceivers();
            await vm.getDeductions();
            await vm.getCurrencies();
            await vm.getPaymentMethods();
            await vm.getBanks();
            // await vm.getBankAccounts();

            if (vm.edit_object) {
                await vm.loadForm();
            }
            vm.loading = false;
        }
    }
</script>
