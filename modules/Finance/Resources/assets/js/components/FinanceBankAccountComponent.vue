<template>
    <div class="col-xs-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="#" title="Registros de cuentas bancarias"
            data-toggle="tooltip" @click="addRecord('add_bank_account', '/finance/bank-accounts', $event); getAccountTypes();">
            <i class="icofont icofont-law-document ico-3x"></i>
            <span>Cuentas Bancarias</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_bank_account">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-law-document inline-block"></i>
                            Cuenta Bancaria
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>¡Atención!</strong> Debe verificar los siguientes errores antes de continuar:
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
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label>Fecha de apertura</label>
                                    <input type="date" v-model="record.opened_at" class="form-control input-sm fiscal-year-restrict"
                                        data-toggle="tooltip"
                                        title="Indique la fecha en la que se aperturó la cuenta">
                                </div>
                            </div>
                            <!-- cuenta contable -->
                            <div v-if="accounting" class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Cuenta contable</label>
                                    <select2 :options="accounting_accounts"
                                             v-model="record.accounting_account_id"></select2>
                                </div>
                            </div>
                            <!-- ./cuenta contable -->
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Banco:</label>
                                    <select2 :options="banks" @input="getAgencies"
                                        v-model="record.finance_bank_id">
                                    </select2>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Agencia:</label>
                                    <select2 :options="agencies"
                                        v-model="record.finance_banking_agency_id">
                                    </select2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de Cuenta:</label>
                                    <select2 :options="account_types"
                                        v-model="record.finance_account_type_id">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Código Cuenta Cliente</label>
                                    <div class="row">
                                        <div class="col-3">
                                            <input type="text" class="form-control input-sm"
                                                id="bank_code" v-model="record.bank_code" readonly>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-sm"
                                                data-toggle="tooltip" v-model="record.ccc_number"
                                                title="Indique el número de cuenta sin guiones o espacios"
                                                maxlength="16">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group is-required">
                                    <label>Descripción</label>
                                    <ckeditor :editor="ckeditor.editor" id="description" data-toggle="tooltip"
                                        title="Indique la descripción u objetivo de la cuenta"
                                        :config="ckeditor.editorConfig" class="form-control" name="description"
                                        tag-name="textarea" rows="3" v-model="record.description"></ckeditor>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                    @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                    @click="reset">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('finance/bank-accounts')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="finance_banking_agency" slot-scope="props">
                                {{ props.row.finance_banking_agency.finance_bank.short_name }}
                            </div>
                            <div slot="ccc_number" slot-scope="props" class="text-center">
                                {{ format_bank_account(props.row.ccc_number) }}
                            </div>
                            <div slot="opened_at" slot-scope="props" class="text-center">
                                {{ convert_date(props.row.opened_at) }}
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="customUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, '/finance/bank-accounts')"
                                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            accounting: {
                type: String,
                required: true
            }
        },
        data() {
            return {
                record: {
                    id: '',
                    finance_bank_id: '',
                    finance_banking_agency_id: '',
                    finance_account_type_id: '',
                    ccc_number: '',
                    bank_code: '',
                    description: '',
                    opened_at: '',
                    accounting_account_id: '',
                },
                errors: [],
                records: [],
                banks: [],
                agencies: [],
                account_types: [],
                accounting_accounts: [],
                columns: ['finance_banking_agency', 'ccc_number', 'opened_at', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    finance_bank_id: '',
                    finance_banking_agency_id: '',
                    finance_account_type_id: '',
                    ccc_number: '',
                    bank_code: '',
                    description: '',
                    opened_at: '',
                    accounting_account_id: '',
                };
            },

            /**
             * Obtiene un listado de cuentas patrimoniales
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getAccountingAccounts() {
                const vm = this;
                vm.accounting_accounts = [];
                axios.get(`${window.app_url}/accounting/accounts`).then(response => {
                    if (response.data.records.length > 0) {
                        vm.accounting_accounts.push({
                            id:   '',
                            text: 'Seleccione...'
                        });
                        $.each(response.data.records, function() {
                            vm.accounting_accounts.push({
                                id:   this.id,
                                text: `${this.code} - ${this.denomination}`,
                                disabled:this.original
                            });
                        });
                    }
                }).catch(error => {
                    vm.logs('PayrollConceptsComponent', 258, error, 'getAccountingAccounts');
                });
            },
            /**
             * Método que cambia el formato de visualización de la fecha
             * en la tabla de registros.
             *
             * @method  convert_date
             * @author  Francisco Ruiz <javierrupe19@gmail.com>
             */
            convert_date(date){
                return new Date(date).toLocaleDateString('en-GB', {timeZone: 'UTC'});
            },

            customUpdate(id, event){
                let vm = this;
                vm.errors = [];

                let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;
                vm.record.finance_bank_id = vm.record.finance_banking_agency.finance_bank_id;
                vm.record.ccc_number = recordEdit.ccc_number.substr(4);
                vm.record.accounting_account_id = recordEdit.accounting_account_id;
                vm.record.opened_at = moment(vm.record.opened_at).add(1, 'days').format('YYYY-MM-DD');
                setTimeout(() => {
                    vm.record.finance_banking_agency_id = vm.record.finance_banking_agency.id;
                }, 1500);

                event.preventDefault();

            },
        },
        created() {
            this.table_options.headings = {
                'finance_banking_agency': 'Banco',
                'ccc_number': 'Código Cuenta Cliente',
                'opened_at': 'Fecha de apertura',
                'id': 'Acción'
            };
            this.table_options.sortable = ['finance_banking_agency', 'ccc_number'];
            this.table_options.filterable = ['finance_banking_agency', 'cc_number'];
            this.table_options.columnsClasses = {
                'finance_banking_agency': 'col-md-4',
                'ccc_number': 'col-md-4',
                'opened_at': 'col-md-2',
                'id': 'col-md-2'
            };
            this.getBanks();
            this.getAccountTypes();
            //console.log(this.accounting);
        },
        mounted() {
            const vm = this;
            vm.getAccountingAccounts();
        }
    };
</script>
