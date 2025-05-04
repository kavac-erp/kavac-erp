<template>
    <section id="PayrollEmploymentForm">
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
                        <li v-for="error in errors" :key="error">{{ error }}</li>
                    </ul>
                </div>
            </div>
            <!-- Sección para registrar movimiento bancario -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h6>Datos del movimiento bancario</h6>
                </div>
                <div class="col-md-4" id="helpFinanceInstitution">
                    <div class="form-group is-required">
                        <label>Institución:</label>
                        <select2 :options="institutions"
                            v-model="record.institution_id"></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancePaymentDate">
                    <div class="form-group is-required">
                        <label>Fecha de pago:</label>
                        <input type="date"
                                class="form-control input-sm fiscal-year-restrict"
                                data-toggle="tooltip"
                                title="Fecha del movimiento bancario"
                            v-model="record.payment_date"/>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceTransactionType">
                    <div class="form-group is-required">
                        <label>Tipo de transacción:</label>
                        <select2 :options="transaction_types"
                            v-model="record.transaction_type">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceBankAccount">
                    <div class="form-group is-required">
                        <label>Nro. de cuenta:</label>
                        <select2 :options="bank_accounts" @input="getBankAccountData(); sendEntryData();"
                            v-model="record.finance_bank_account_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceBankName" v-if="record.finance_bank_account_id">
                    <div class="form-group is-required">
                        <label>Banco:</label>
                        <input type="text" class="form-control input-sm" v-model="record.bank"
                            disabled/>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceTypeAccount" v-if="record.finance_bank_account_id">
                    <div class="form-group is-required">
                        <label>Tipo de cuenta:</label>
                        <input type="text" class="form-control input-sm" v-model="record.account_type"
                            disabled/>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceCurrency">
                    <div class="form-group is-required">
                        <label>Tipo de moneda:</label>
                        <select2 :options="currencies" @input="changeCurrency(record.currency_id)"
                            v-model="record.currency_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceAmount" v-if="showFieldAmount" >
                    <div class="form-group">
                        <label>Monto:</label>
                        <input
                            type="text"
                            v-input-mask
                            data-inputmask="
                                'alias': 'numeric',
                                'allowMinus': 'false'"
                            onfocus="$(this).select()"
                            class="form-control input-sm"
                            data-toggle="tooltip"
                            title="Monto"
                            v-model="record.amount"
                            tabindex="-1"
                            disabled/>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceReference">
                    <div class="form-group is-required">
                        <label>Nro. Documento de Origen:</label>
                        <input type="text" class="form-control input-sm"
                            v-model="record.reference"/>
                    </div>
                </div>
                <div class="col-md-8" id="helpFinanceConcept">
                    <div class="form-group is-required">
                        <label>Concepto:</label>
                        <textarea
                                rows="2"
                                class="form-control input-sm"
                                tabindex="14"
                                v-model="record.concept"
                        >
                        </textarea>
                    </div>
                </div>
            </div>
            <br>
            <!-- Sección de compromisos -->
            <div v-show="budget == 1 && record.transaction_type === 'Nota de débito'">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h6>Datos del compromiso</h6>
                    </div>
                </div>
                <h6 class="text-center card-title">Cuentas presupuestarias de gastos</h6>
                <div class="row">
                    <div class="col-md-12 pad-top-20 table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th class="col-1">ID</th>
                                    <th class="col-2 text-uppercase">CÓDIGO</th>
                                    <th class="col-1 text-uppercase">CUENTA</th>
                                    <th class="col-2 text-uppercase">CÓDIGO ACCIÓN ESPECÍFICA</th>
                                    <th class="col-2 text-uppercase">DESCRIPCIÓN</th>
                                    <th class="col-2 text-uppercase">CONCEPTO</th>
                                    <th class="col-1 text-uppercase">MONTO</th>
                                    <th class="col-1">
                                        <a class="btn btn-sm btn-info btn-action btn-tooltip" href="#"
                                           data-original-title="Agregar compromiso" data-toggle="modal"
                                           data-target="#add_account">
                                            <i class="fa fa-plus-circle"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(account, index) in record.accounts" :key="index">
                                    <td
                                        class="text-center"
                                    >
                                        {{
                                            account.budget_tax_key
                                                ? account.budget_tax_key
                                                : index + 1
                                        }}
                                    </td>
                                    <td class="text-center">{{ account.pro_code }}</td>
                                    <td class="text-center">{{ account.code }}</td>
                                    <td class="text-center">{{ account.sp_acc_code }}</td>
                                    <td class="text-center">{{ account.denomination }}</td>
                                    <td class="text-center">{{ account.description }}</td>
                                    <td class="text-center">{{ formatToCurrency(account.amount, '') }}</td>
                                    <td class="text-center">
                                        <input type="hidden" name="account_id[]" readonly
                                               :value="account.specific_action_id + '|' + account.account_id">
                                        <input type="hidden" name="budget_account_amount[]" readonly
                                               :value="account.amount">
                                        <button class="btn btn-sm btn-danger btn-action" @click="deleteAccountCompromise(index)"
                                           title="Eliminar este registro" data-toggle="tooltip">
                                            <i class="fa fa-minus-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="pad-top-40">
                    <h6 class="text-center card-title">Cuentas presupuestarias de impuestos</h6>
                    <div class="row">
                        <div class="col-md-12 pad-top-20">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th class="col-1">ID</th>
                                        <th class="col-2 text-uppercase">CÓDIGO</th>
                                        <th class="col-1 text-uppercase">CUENTA</th>
                                        <th class="col-2 text-uppercase">CÓDIGO ACCIÓN ESPECÍFICA</th>
                                        <th class="col-2 text-uppercase">DESCRIPCIÓN</th>
                                        <th class="col-2 text-uppercase">CONCEPTO</th>
                                        <th class="col-1 text-uppercase">MONTO</th>
                                        <th class="col-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(tax_account, index) in record.tax_accounts" :key="index">
                                        <td
                                            v-if="tax_account.tax_id"
                                            class="text-center"
                                        >
                                            {{
                                                tax_account.budget_tax_key
                                                    ? tax_account.budget_tax_key
                                                    : index + 1
                                            }}
                                        </td>
                                        <td class="text-center">{{ tax_account.pro_code }}</td>
                                        <td class="text-center">{{ tax_account.code }}</td>
                                        <td class="text-center">{{ tax_account.sp_acc_code }}</td>
                                        <td class="text-center">{{ tax_account.denomination }}</td>
                                        <td class="text-center">{{ tax_account.description }}</td>
                                        <td class="text-center">{{ formatToCurrency(tax_account.amount, '') }}</td>
                                        <td class="text-center">
                                            <input type="hidden" name="account_id[]" readonly
                                                   :value="tax_account.specific_action_id + '|' + tax_account.account_id">
                                            <input type="hidden" name="budget_tax_account_amount[]" readonly
                                                   :value="tax_account.amount">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Modal para agregar cuentas presupuestarias -->
                    <div class="modal fade" tabindex="-1" role="dialog" id="add_account">
                        <div class="modal-dialog vue-crud" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <h6>
                                        <i class="ion-arrow-graph-up-right"></i>
                                        Agregar cuentas
                                    </h6>
                                </div>
                                <div class="modal-body">
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
                                                <li v-for="error in errors" :key="error">{{ error }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group is-required">
                                                <label>Acción Específica:</label>
                                                <select2 :options="specific_actions"
                                                         @input="getAccounts(); getTaxAccounts()"
                                                         v-model="specific_action_id"/>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group is-required">
                                                <label>Cuenta:</label>
                                                <select2 :options="accounts" v-model="account_id"/>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group is-required">
                                                <label>Concepto:</label>
                                                <input type="text" class="form-control input-sm" data-toggle="tooltip"
                                                       v-model="account_concept"
                                                       title="Indique el concepto de la cuenta presupuestaria a agregar">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mt-4">
                                            <div class="form-group is-required">
                                            <label>Monto:</label>
                                                <input
                                                    type="text"
                                                    v-input-mask
                                                    data-inputmask="
                                                        'alias': 'numeric',
                                                        'allowMinus': 'false'"
                                                    onfocus="$(this).select()"
                                                    class="form-control input-sm"
                                                    data-toggle="tooltip"
                                                    title="Indique el monto a asignar para la cuenta seleccionada"
                                                    v-model="account_amount"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <div class="form-group">
                                                <label>Impuesto:</label>
                                                <select2
                                                    :options="taxes"
                                                    v-model="account_tax_id"
                                                    @input="getTaxAccounts()"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                            data-dismiss="modal">
                                        Cerrar
                                    </button>
                                    <button type="button" @click="addAccount"
                                            class="btn btn-primary btn-sm btn-round btn-modal-save">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <!-- Sección de asiento contable -->
            <div v-show="accounting == 1 && record.currency_id">
                <div id="helpAccountingData">
                    <div class="row">
                        <div class="col-12 mt-4 mb-4">
                            <h6>Datos Contables</h6>
                        </div>
                    </div>
                    <accounting-entry-generator ref="accountingEntryGenerator" :recordToConverter="[]" :showEdit="!record.transaction_type || record.transaction_type === 'Nota de débito'" @inputDebit="getInputDebitValue" />
                </div>
            </div>
            <div class="card-footer pull-right" id="helpParamButtons">
                <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                    title="Borrar datos del formulario" @click="reset"><i class="fa fa-eraser"></i>
                </button>
                <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        title="Cancelar y regresar" @click="redirect_back(route_list)">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="createRecord('finance/movements')" data-toggle="tooltip"
                        title="Guardar registro" class="btn btn-success btn-icon btn-round">
                    <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
    </section>
</template>
<script>

    export default {
        props: {
            accounting_list: {
                type: Array,
                default: []
            },
            entry_categories: {
                type: Array,
                default: []
            },
            accounting: {
                type: Number,
                default: 0
            },
            budget: {
                type: Number,
                default: 0
            },
            movementid: {
                type: Number
            },
            accounting_account_id: {
                type: Number,
                default: null
            }
        },
        data() {
            return {
                record: {
                    payment_date: '',
                    transaction_type: '',
                    finance_bank_account_id: '',
                    reference: '',
                    concept: '',
                    amount: 0.00,
                    currency_id: null,
                    recordsAccounting: [],
                    accounting_account_id: '',
                    entry_concept: '',
                    entry_category: '',
                    institution_id: '',
                    accounts: [],
                    tax_accounts: [],
                    totDebit: 0,
                    totAssets: 0,
                },
                records: [],
                data: {
                    date: '',
                    reference: '',
                    concept: '',
                    observations: '',
                    category: '',
                    totDebit: 0,
                    totAssets: 0,
                    institution: {
                        id: '',
                        rif: '',
                        acronym: '',
                        name: 0,
                    },
                    currency: {
                        id: '',
                        symbol: '',
                        name: '',
                        decimal_places: 2,
                    },
                },
                bank_accounts: [],
                currencies: [],
                accounting_accounts: [],
                categories: [],
                rowsToDelete: [],
                transaction_types:  [
                    {'id' : '', 'text' : "Seleccione..."},
                    {'id' : 'Nota de crédito', 'text' : 'Nota de crédito'},
                    {'id' : 'Nota de débito', 'text' : 'Nota de débito'}
                ],
                taxes: [{'id': '', 'text': 'Seleccione...'}],
                taxesData: [],
                specific_actions: [],
                specific_action_id: '',
                account_id: '',
                account_concept: '',
                account_amount: 0,
                account_tax_id: '',
                accounts: [],
                tax_accounts: [],
                institutions: [],
                columns: ['code', 'account', 'esp_code', 'description', 'amount', 'id'],
                errors: [],
                arrayKeys: [],
            }
        },

        created() {
            $("#select2").val("");
            const vm = this;
            this.table_options.headings = {
                'code': 'Código',
                'account': 'Cuenta',
                'esp_code': 'Código Acción Específica',
                'description': 'Descripción',
                'amount': 'Monto',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'account', 'esp_code', 'description', 'amount'];
            this.table_options.filterable = ['code', 'esp_code', 'description'];
            this.table_options.columnsClasses = {
                'code': 'col-md-2',
                'account': 'col-md-2',
                'esp_code': 'col-md-2',
                'description': 'col-md-2',
                'amount': 'col-md-2',
                'id': 'col-md-2'
            };
            this.accounting_accounts = this.accounting_list;
            this.categories = this.entry_categories;
            vm.getFinanceBankAccounts();
            vm.getCurrencies();
            vm.getInstitutions();
            vm.getTaxes();
            if (vm.movementid) {
                vm.loadForm(vm.movementid);
            }
            this.record.recordsAccounting = [];
            this.record.accounts = [];
            this.record.tax_accounts = [];
        },

        mounted() {
            const vm = this;
            $("#add_account").on('shown.bs.modal', function() {
                if (vm.specific_actions.length === 0) {
                    /** Carga las acciones específicas para la respectiva formulación */
                    vm.getSpecificActions();
                }
            }).on('hide.bs.modal', function() {
                /** @type {Array} Inicializa el arreglo de acciones específicas a seleccionar */
                vm.specific_actions = [];
                /** @type array Inicializa el arreglo de las cuentas presupuestarias seleccionadas */
                vm.accounts = [];
            });
        },

        methods: {
            /**
             * Método que carga la información del formulario al editar
             */
            async loadForm(id){
                const vm = this;
                vm.arrayKeys = [];

                await axios.get('/finance/movements/vue-info/'+id).then(async (response) => {
                    if(typeof(response.data.record != "undefined")){
                        let data = response.data.record[0];

                        vm.record.id = data.id;
                        vm.record.accounting_entry_id = data.accounting_entry_pivot ? data.accounting_entry_pivot.accounting_entry_id : '';
                        vm.record.payment_date = vm.format_date(data.payment_date, 'YYYY-MM-DD');
                        vm.record.transaction_type = data.transaction_type;
                        vm.record.finance_bank_account_id = data.finance_bank_account_id;
                        vm.record.reference = data.reference;
                        vm.record.concept = data.concept;
                        vm.record.amount = data.amount;
                        vm.record.currency_id = data.currency_id;

                        vm.record.entry_concept = data.accounting_entry_pivot && data.accounting_entry_pivot.accounting_entry ?
                                                              data.accounting_entry_pivot.accounting_entry.concept :
                                                              '';
                        vm.record.entry_category = data.accounting_entry_pivot && data.accounting_entry_pivot.accounting_entry ?
                                                           data.accounting_entry_pivot.accounting_entry.accounting_entry_category_id :
                                                           '';
                        vm.record.institution_id = data.institution_id;
                        vm.record.totDebit = data.accounting_entry_pivot && vm.addDecimals(data.accounting_entry_pivot.accounting_entry) ?
                                                       data.accounting_entry_pivot.accounting_entry.tot_debit :
                                                       '';
                        vm.record.totAssets = data.accounting_entry_pivot && vm.addDecimals(data.accounting_entry_pivot.accounting_entry) ?
                                                       data.accounting_entry_pivot.accounting_entry.tot_assets :
                                                       '';

                        if (data.accounting_entry_pivot && data.accounting_entry_pivot.accounting_entry) {
                            for (let entry of data.accounting_entry_pivot.accounting_entry.accounting_accounts) {
                                vm.record.recordsAccounting.push({
                                    assets: entry.assets,
                                    debit: entry.debit,
                                    entryAccountId: entry.id,
                                    id: entry.account.id
                                })
                            }
                        }

                        if (data.budget_compromise) {
                            for (let compromise_details of data.budget_compromise.budget_compromise_details) {
                                let accounting_account_id = '';
                                await vm.getAccountingAccountId(compromise_details.budget_account_id).then(data => accounting_account_id = data.accounting_account_id);
                                if (!compromise_details.tax_id) {
                                    if (compromise_details.budget_tax_key) vm.arrayKeys.push(compromise_details.budget_tax_key);
                                    vm.record.accounts.push({
                                        spac_description: `${compromise_details.budget_sub_specific_formulation.specific_action.specificable.code}-
                                                           ${compromise_details.budget_sub_specific_formulation.specific_action.code} |
                                                           ${compromise_details.budget_sub_specific_formulation.specific_action.name}`,
                                        code: compromise_details.budget_account.code,
                                        description: compromise_details.description,
                                        amount: compromise_details.amount,
                                        specific_action_id: compromise_details.budget_sub_specific_formulation.budget_specific_action_id,
                                        account_id: compromise_details.budget_account_id,
                                        tax_id: compromise_details.tax_id ? compromise_details.tax_id : '',
                                        pro_code: compromise_details.budget_sub_specific_formulation.specific_action.specificable.code,
                                        sp_acc_code: compromise_details.budget_sub_specific_formulation.specific_action.code,
                                        denomination: compromise_details.budget_account.denomination,
                                        budget_tax_key: compromise_details.budget_tax_key,
                                        accounting_account_id: accounting_account_id
                                    });
                                }

                                if (compromise_details.tax_id) {
                                    let tax;
                                    let tax_percentage;
                                    let tax_description;
                                    for (tax of vm.taxesData){
                                        if (compromise_details.tax_id && compromise_details.tax_id == tax.id) {
                                            tax_description = tax.description;
                                            tax_percentage = tax.histories[0].percentage;
                                        }
                                    }

                                    vm.record.tax_accounts.push({
                                        spac_description: `${compromise_details.budget_sub_specific_formulation.specific_action.specificable.code}-
                                                           ${compromise_details.budget_sub_specific_formulation.specific_action.code} |
                                                           ${compromise_details.budget_sub_specific_formulation.specific_action.name}`,
                                        code: compromise_details.budget_account.code,
                                        description: compromise_details.description,
                                        amount: compromise_details.amount,
                                        specific_action_id: compromise_details.budget_sub_specific_formulation.budget_specific_action_id,
                                        account_id: compromise_details.budget_account_id,
                                        tax_id: compromise_details.tax_id ? compromise_details.tax_id : '',
                                        pro_code: compromise_details.budget_sub_specific_formulation.specific_action.specificable.code,
                                        sp_acc_code: compromise_details.budget_sub_specific_formulation.specific_action.code,
                                        denomination: compromise_details.budget_account.denomination,
                                        budget_tax_key : compromise_details.budget_tax_key,
                                        accounting_account_id: accounting_account_id
                                    });
                                }
                            }
                        }
                        await vm.sendEntryData();
                    }
                });

            },

            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                const vm = this;
                vm.record = {
                    payment_date: '',
                    transaction_type: '',
                    finance_bank_account_id: '',
                    reference: '',
                    concept: '',
                    amount: '',
                    currency_id: null,
                    recordsAccounting: [],
                    accounting_account_id: '',
                    entry_concept: '',
                    entry_category: '',
                    institution_id: '',
                    accounts: [],
                    totDebit: 0,
                    totAssets: 0,
                };
                vm.records = [];
                vm.data = {
                    date: '',
                    reference: '',
                    concept: '',
                    observations: '',
                    category: '',
                    totDebit: 0,
                    totAssets: 0,
                    institution: {
                        id: '',
                        rif: '',
                        acronym: '',
                        name: 0,
                    },
                    currency: {
                        id: '',
                        symbol: '',
                        name: '',
                        decimal_places: 2,
                    },
                };
                this.getCurrencies();
                errors: [];
                this.arrayKeys = [];
            },

            addDecimals(value) {
                return parseFloat(value).toFixed(this.data.currency.decimal_places);
            },

            /**
             * [validateTotals valida que los totales sean positivos]
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             * @return {boolean}
             */
            validateTotals: function() {
                return !(this.data.totDebit.toFixed(this.data.currency.decimal_places) >= 0 &&
                    this.data.totAssets.toFixed(this.data.currency.decimal_places) >= 0);
            },

            /**
             * Vacia la información del debe y haber de la columna sin cuenta seleccionada
             *
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             */
            changeSelectinTable: function(record) {
                // si asigna un select en vacio, vacia los valores del debe y haber de esa fila
                if (record.id == '') {
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
            CalculateTot: function() {

                this.data.totDebit = 0;
                this.data.totAssets = 0;

                /** Se recorren todo el arreglo que tiene todas las filas de las cuentas y saldos para el asiento y se calcula el total */
                for (var i = this.record.recordsAccounting.length - 1; i >= 0; i--) {
                    if (this.record.recordsAccounting[i].id != '') {
                        var debit = (this.record.recordsAccounting[i].debit != '') ? this.record.recordsAccounting[i].debit : 0;
                        var assets = (this.record.recordsAccounting[i].assets != '') ? this.record.recordsAccounting[i].assets : 0;

                        this.record.recordsAccounting[i].debit = parseFloat(debit).toFixed(this.data.currency.decimal_places);
                        this.record.recordsAccounting[i].assets = parseFloat(assets).toFixed(this.data.currency.decimal_places)

                        if (this.record.recordsAccounting[i].debit < 0 || this.record.recordsAccounting[i].assets < 0) {
                            this.$refs.AccountingEntryGenerator.showAlertMessages('Los valores en la columna del DEBE y el HABER deben ser positivos.');
                        }

                        this.data.totDebit += (this.record.recordsAccounting[i].debit != '') ? parseFloat(this.record.recordsAccounting[i].debit) : 0;
                        this.data.totAssets += (this.record.recordsAccounting[i].assets != '') ? parseFloat(this.record.recordsAccounting[i].assets) : 0;
                    }
                }

                this.record.totDebit = this.data.totDebit.toFixed(this.data.currency.decimal_places);
                this.record.totAssets = this.data.totAssets.toFixed(this.data.currency.decimal_places);
                this.record.amount = this.record.totDebit;
            },

            /**
             * Establece la información base para cada fila de cuentas
             *
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             */
            addAccountingAccount: function() {
                if ($('#select2').val() != '') {
                    for (var i = this.accounting_accounts.length - 1; i >= 0; i--) {
                        if (this.accounting_accounts[i].id == $('#select2').val()) {
                            this.record.recordsAccounting.push({
                                id: $('#select2').val(),
                                entryAccountId: null,
                                debit: 0,
                                assets: 0,
                            });
                            $('#select2').val('');
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
                    axios.get(`${window.app_url}/currencies/info/${currency_id}`).then(response => {
                        this.data.currency = response.data.currency;
                        this.data.currency_id = response.data.currency.id;
                    });
                } else {
                    this.data.currency = {
                        id: '',
                        symbol: '',
                        name: '',
                        decimal_places: 2,
                    };
                    this.data.currency_id = '';
                }
                this.CalculateTot();
            },

            /**
             * Elimina la fila de la cuenta y vuelve a calcular el total del asiento
             *
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             */
            deleteAccount: function(index, id) {
                this.rowsToDelete.push(id);
                this.record.recordsAccounting.splice(index, 1);
                this.CalculateTot();
            },

            /**
             * Elimina una cuenta del listado de cuentas agregadas
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @param  {integer} index Índice del elemento a eliminar
             */
            deleteAccountCompromise(index) {
                let vm = this;
                bootbox.confirm({
                    title: "Eliminar cuenta?",
                    message: `¿Está seguro de eliminar esta cuenta del compromiso actual?`,
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar',
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar',
                        },
                    },
                    callback: function(result) {
                        if (result) {
                            let budget_tax_key = vm.record.accounts[index].budget_tax_key;
                            vm.record.accounts.splice(index, 1);
                            // vm.record.tax_accounts.splice(index, 1);
                            vm.record.tax_accounts = vm.record.tax_accounts.filter(
                                (item) => item.budget_tax_key != budget_tax_key
                            );
                            vm.sendEntryData();
                        }
                    },
                });
            },

            /**
             * vacia los valores del debe y del haber de la fila de la cuenta y vuelve a calcular el total del asiento
             *
             * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             */
            clearValues: function(index) {
                this.record.recordsAccounting[index].assets = 0.00;
                this.record.recordsAccounting[index].debit = 0.00;
                this.CalculateTot();
            },

            /**
             * Obtiene los datos de las entidades bancarias registradas
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getFinanceBankAccounts() {
                this.bank_accounts = [];
                axios.get(`${window.app_url}/finance/get-bank-accounts`).then(response => {
                    this.bank_accounts = response.data;
                });
            },
            /**
             * Completa los campos de banco y de tipo de cuenta según el número de cuenta seleccionado
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            getBankAccountData() {
                const vm = this;
                vm.record.bank = '';
                vm.record.account_type = '';
                if (vm.record.finance_bank_account_id) {
                    for (let account of vm.bank_accounts) {
                        if (vm.record.finance_bank_account_id == account.id) {
                            vm.record.bank = account.bank_name ? account.bank_name : '';
                            vm.record.account_type = account.bank_account_type ? account.bank_account_type : '';
                            vm.record.accounting_account_id = account.accounting_account_id;
                        }
                    }
                }
            },

            /**
             * Obtiene las cuentas presupuestarias formuladas de la acción específica seleccionada
             *
             * @method    getAccounts
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getAccounts() {
                const vm = this;
                vm.loading = true;
                vm.accounts = [];

                if (vm.specific_action_id) {
                    let specificActionId = vm.specific_action_id;
                    let compromisedAt = vm.record.payment_date;
                    await axios.get(
                        `${window.app_url}/budget/get-opened-accounts/${specificActionId}/${compromisedAt}`
                    ).then(response => {
                        if (response.data.result) {
                            vm.accounts = response.data.records;
                        }
                        if (response.data.records.length === 1 && response.data.records[0].id === "") {
                            vm.showMessage(
                                'custom', 'Alerta!', 'danger', 'screen-error',
                                `No existen cuentas aperturadas para esta acción específica o con saldo para la fecha
                                seleccionada`
                            );
                        }
                    }).catch(error => {
                        console.error(error);
                    });
                }

                vm.loading = false;
            },

            /**
             * Listado de impuestos
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getTaxes() {
                const vm = this;
                axios.get(`${window.app_url}/get-taxes`).then(response => {
                    if (response.data.records.length > 0) {
                        vm.taxesData = response.data.records;
                        for (let tax of vm.taxesData) {
                            vm.taxes.push({
                                'id' : tax.id,
                                'text' : tax.name + ' ' + tax.histories[0].percentage + '%',
                            });
                        }
                    }
                }).catch(error => {
                    console.error(error);
                });
            },

            /**
             * Agrega una cuenta presupuestaria al compromiso
             *
             * @method     addAccount
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async addAccount() {
                const vm = this;

                if (
                    !vm.specific_action_id ||
                    !vm.account_id ||
                    !vm.account_concept ||
                    vm.account_amount === ""
                ) {
                    bootbox.alert("Debe indicar todos los datos requeridos (*)");
                    return;
                }
                vm.loading = true;
                if (
                    Number(vm.account_amount) == 0
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "danger",
                        "screen-error",
                        "El monto a comprometer no puede ser cero"
                    );
                    setTimeout(() => {
                        vm.loading = false;
                    }, 500);
                    return;
                }

                let specificAction = {};
                let account = {};
                let accounting_account_id = '';
                let tax_accounting_account_id = '';

                await vm.getSpecificActionDetail(vm.specific_action_id).then(detail => specificAction = detail.record);

                await vm.getAccountDetail(vm.account_id).then(detail => account = detail.record);

                await vm.getAccountingAccountId(vm.account_id).then(data => accounting_account_id = data.accounting_account_id);

                if (vm.account_concept.length > 400) {
                    vm.errors.push('El campo concepto debe ser menor a 400 caracteres')
                } else {
                    let budget_tax_key = vm.generateUnitKey();
                    vm.record.accounts.push({
                        'spac_description': `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                        'code': account.code,
                        'description': vm.account_concept,
                        'amount': vm.account_amount,
                        'specific_action_id': vm.specific_action_id,
                        'account_id': vm.account_id,
                        'tax_id': "",
                        'pro_code': specificAction.specificable.code,
                        'sp_acc_code': specificAction.code,
                        'denomination' : account.denomination,
                        'budget_tax_key': budget_tax_key,
                        'accounting_account_id': accounting_account_id,
                    });

                    if (vm.account_tax_id) {
                        let tax;
                        let tax_percentage;
                        let tax_description;
                        let tax_code;
                        for (tax of vm.taxesData){
                            if (vm.account_tax_id == tax.id) {
                                tax_description = tax.description;
                                tax_percentage = tax.histories[0].percentage;
                            }
                        }

                        if (vm.tax_accounts.length > 0) {
                            for (let tax_account of vm.tax_accounts) {
                                await vm.getAccountingAccountId(tax_account.id).then(data => tax_accounting_account_id = data.accounting_account_id);
                                vm.record.tax_accounts.push({
                                    'spac_description': `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                                    'code': tax_account.code,
                                    'description': vm.account_concept,
                                    'amount': (vm.account_amount * tax_percentage) /
                                                100 /
                                                vm.tax_accounts.length,
                                    'specific_action_id': vm.specific_action_id,
                                    'account_id': tax_account.id,
                                    'tax_id': vm.account_tax_id,
                                    'pro_code': specificAction.specificable.code,
                                    'sp_acc_code': specificAction.code,
                                    'denomination': tax_description,
                                    'budget_tax_key': budget_tax_key,
                                    'accounting_account_id': tax_accounting_account_id ?? accounting_account_id,
                                });
                            }
                        } else {
                            vm.record.tax_accounts.push({
                                'spac_description': `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                                'code': account.code,
                                'description': vm.account_concept,
                                'amount': vm.account_amount * tax_percentage / 100,
                                'specific_action_id': vm.specific_action_id,
                                'account_id': vm.account_id,
                                'tax_id': vm.account_tax_id,
                                'pro_code': specificAction.specificable.code,
                                'sp_acc_code': specificAction.code,
                                'denomination': tax_description,
                                'budget_tax_key': budget_tax_key,
                                'accounting_account_id': accounting_account_id,
                            });
                        }
                    }
                    console.log(vm.record.accounts, vm.record.tax_accounts);
                    bootbox.confirm({
                        title: "Agregar cuenta",
                        message: `Desea agregar otra cuenta?`,
                        buttons: {
                            cancel: {
                                label: '<i class="fa fa-times"></i> Cancelar'
                            },
                            confirm: {
                                label: '<i class="fa fa-check"></i> Confirmar'
                            }
                        },
                        callback: function (result) {
                            if (!result) {
                                $("#add_account").find('.close').click();
                            }

                            vm.specific_action_id = '';
                            vm.account_id = '';
                            vm.account_concept = '';
                            vm.account_amount = 0;
                            vm.account_tax_id = '';
                            vm.sendEntryData();
                        }
                    });
                }
                setTimeout(() => {
                    vm.loading = false;
                }, 500);
            },

            /**
             * Método que envía la información al componente de asientos contables
             * para generar el asiento.
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async sendEntryData(edit = true) {
                const vm = this;
                let amount = 0.00;

                if (edit) {
                    let data = [];
                    let entryData = [];
                    let currency_id = '';

                    if (vm.record.accounting_account_id || vm.record.accounts.length) {
                        for (let doc of vm.record.accounts) {
                            if (doc.accounting_account_id) {
                                data = {
                                    debit: true,
                                    assets: false,
                                    amount: doc.amount,
                                    account: doc.accounting_account_id,
                                    is_retention: false,
                                };
                                amount += parseFloat(doc.amount);
                                entryData.push(data);
                            }
                        }
                        for (let tax of vm.record.tax_accounts) {
                            if (tax.accounting_account_id) {
                                data = {
                                    debit: true,
                                    assets: false,
                                    amount: tax.amount,
                                    account: tax.accounting_account_id,
                                    is_retention: false,
                                };
                                amount += parseFloat(tax.amount);
                                entryData.push(data);
                            }
                        }

                        if (vm.record.accounting_account_id && vm.record.transaction_type === 'Nota de débito') {
                            data = {
                                debit: false,
                                assets: true,
                                amount: amount > 0 ? amount : vm.record.amount,
                                account: vm.record.accounting_account_id,
                                is_retention: false,
                            };
                            entryData.push(data);
                        }


                        if (vm.record.recordsAccounting.length === 0 && vm.record.transaction_type === 'Nota de crédito') {
                            data = {
                                debit: true,
                                assets: false,
                                amount: amount > 0 ? amount : vm.record.amount,
                                account: vm.record.accounting_account_id,
                                is_retention: false,
                            };
                            entryData.push(data);
                        }
                        if (vm.record.recordsAccounting.length > 0 && vm.record.transaction_type === 'Nota de crédito') {
                            vm.record.recordsAccounting.forEach(element => {
                                data = {
                                    debit: element.debit > 0 ? true : false,
                                    assets: element.assets > 0 ? true : false,
                                    amount: element.debit > 0 ? element.debit : element.assets,
                                    account: element.id,
                                    is_retention: false,
                                }
                                entryData.push(data);
                            });
                        }
                          entryData.length > 0 && vm.$refs.accountingEntryGenerator.chargeAccounts(entryData);
                          entryData.length > 0 && vm.$refs.accountingEntryGenerator.changeCurrency(vm.currency_id);
                          vm.record.amount = vm.addDecimals(amount > 0 ? amount : vm.record.amount);
                    }
                }
            },

            /**
             * Obtiene las Acciones Específicas
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @param {string} type Tipo de registro
             */
            async getSpecificActions() {
                const vm = this;
                vm.loading = true;
                vm.specific_actions = [];
                vm.accounts = [];

                if (vm.record.payment_date && vm.record.institution_id) {
                    let year = vm.record.payment_date.split("-")[0];
                    let url = `${window.app_url}/budget/get-group-specific-actions/${year}/1/${vm.record.institution_id}`;
                    await axios.get(url).then(response => {
                        vm.specific_actions = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
                } else {
                    $("#add_account").find('.close').click();
                    bootbox.alert('Debe indicar la institución y la fecha del pago antes de agregar cuentas a un compromiso');
                }

                vm.loading = false;
            },

            async getSpecificActionDetail(id) {
                const response = await axios.get(
                    `${window.app_url}/budget/detail-specific-actions/${id}`
                );
                return response.data;
            },
            async getAccountDetail(id) {
                const response = await axios.get(
                    `${window.app_url}/budget/detail-accounts/${id}`
                );
                return response.data;
            },

            async getAccountingAccountId(id) {
                const response = await axios.get(
                    `${window.app_url}/finance/movements/budget-accounting-accounts/${id}`
                );
                return response.data;
            },

            /**
             * Genera una clave única para la lista de cuentas presupuestarias de gastos
             * y de impuestos.
             *
             * @return {string} La clave única generada.
             */
            generateUnitKey() {
                const vm = this;
                let key = vm.arrayKeys.length > 0 ? vm.arrayKeys[vm.arrayKeys.length - 1] : 1;

                while (vm.arrayKeys.indexOf(key) !== -1) {
                    key ++;
                }
                vm.arrayKeys.push(key);
                return key;
            },

            /**
             * Obtiene las cuentas presupuestarias formuladas de la acción específica seleccionada
             * que son para impuestos
             *
             * @method    getTaxAccounts
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            async getTaxAccounts() {
                const vm = this;
                vm.loading = true;
                vm.tax_accounts = [];

                if (vm.specific_action_id) {
                    let specificActionId = vm.specific_action_id;
                    let compromisedAt = vm.record.payment_date;
                    await axios
                        .get(
                            `${window.app_url}/budget/get-opened-tax-accounts/${specificActionId}/${compromisedAt}`
                        )
                        .then((response) => {
                            if (response.data.result) {
                                vm.tax_accounts = response.data.records;
                            }
                            if (
                                response.data.records.length === 1 &&
                                response.data.records[0].id === ""
                            ) {
                                vm.showMessage(
                                    "custom",
                                    "Alerta!",
                                    "danger",
                                    "screen-error",
                                    `No existen cuentas aperturadas para esta acción específica o con saldo para la fecha
                                                                    seleccionada`
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                }

                vm.loading = false;
            },

            /**
             * Crear un nuevo registro
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async createRecord(url) {
                const vm = this;
                url = vm.setUrl(url);

                if (vm.record.id) {
                    vm.updateRecord(url);
                }
                else {
                    vm.loading = true;
                    var fields = {};

                    for (var index in vm.record) {
                        fields[index] = vm.record[index];
                    }
                    if (vm.record.transaction_type != 'Nota de débito') {
                        fields['account'] = [];
                        fields['tax_accounts'] = [];
                    }
                    /** Datos de los ítems contables */
                    fields['amount'] = vm.$refs.accountingEntryGenerator.data.totDebit;
                    fields['accounting'] = vm.$refs.accountingEntryGenerator.data;
                    fields['recordsAccounting'] = vm.$refs.accountingEntryGenerator.recordsAccounting;

                    await axios.post(url, fields).then(response => {
                        if (typeof(response.data.redirect) !== "undefined") {
                            location.href = response.data.redirect;
                        }
                        else {
                            vm.errors = [];
                            vm.showMessage('store');
                        }
                    }).catch(error => {
                        vm.errors = [];

                        if (typeof(error.response) !="undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
                            for (var index in error.response.data.errors) {
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
                if (vm.record.transaction_type != 'Nota de débito') {
                    fields['account'] = [];
                    fields['tax_accounts'] = [];
                }
                /** Datos de los ítems contables */
                fields['amount'] = vm.$refs.accountingEntryGenerator.data.totDebit;
                fields['accounting'] = vm.$refs.accountingEntryGenerator.data;
                fields['recordsAccounting'] = vm.$refs.accountingEntryGenerator.recordsAccounting;

                await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.errors = [];
                        vm.showMessage('update');
                    }

                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
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
             * Método que permite actualizar la información del monto por el banco asignado
             *
             * @author  Luis Ramírez <lgramirez@cenditel.gob.ve>
             *
             * @param  {string} value valor del monto asignado por el banco seleccionado
             */
            getInputDebitValue(value) {
                const vm = this;
                if (vm.record.id && vm.record.transaction_type === 'Nota de crédito') {
                    vm.record.amount = value;
                }
                return
            },
        },

        watch: {
            'record.accounting_account_id': function(oldValue, newValue) {
                const vm = this;
			    if (vm.record.id && vm.record.transaction_type === 'Nota de crédito' ) {
                    if (vm.accounting_account_id !== vm.record.accounting_account_id) {
                            vm.record.recordsAccounting = [];
                            let entryData = [];
                            vm.$refs.accountingEntryGenerator.chargeAccounts(entryData);
                        }
                    }
                return
               }
		},
        computed: {
            showFieldAmount: function() {
                const vm = this;
                return (vm.record.transaction_type == 'Nota de débito' || vm.record.id) ? true : false;
            }
        }
    };
</script>
