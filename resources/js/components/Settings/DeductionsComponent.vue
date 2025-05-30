<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registro de deducciones" data-toggle="tooltip"
           @click="addRecord('add_deduction', 'deductions', $event)">
            <i class="icofont icofont-mathematical-alt-2 ico-3x"></i>
            <span>Deducciones</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_deduction">
            <div class="modal-dialog vue-crud" role="document" style="">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-mathematical-alt-2 inline-block"></i>
                            Deducción
                        </h6>
                    </div>
                    <div class="with-overflow-90vh">
                        <div class="modal-body">
                            <form-errors :listErrors="errors"></form-errors>
                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group is-required">
                                                <label>Nombre:</label>
                                                <input type="text" placeholder="Nombre" data-toggle="tooltip"
                                                    title="Indique el nombre de la deducción (requerido)"
                                                    class="form-control input-sm" v-model="record.name">
                                                <input type="hidden" v-model="record.id">
                                            </div>
                                        </div>
                                        <div class="col-10">
                                            <div class="form-group" v-if="accountingAccount">
                                                <label>Cuenta Contable:</label>
                                                <select2 :options="accounting_accounts"
                                                        v-model="record.accounting_account_id"></select2>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>Activa</label>
                                                <div class="custom-control custom-switch" data-toggle="tooltip"
                                                    title="Indique si la deducción se encuentra activa">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="deductionActive" v-model="record.active" :value="true">
                                                    <label class="custom-control-label" for="deductionActive"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Descripción:</label>
                                                <ckeditor :editor="ckeditor.editor" data-toggle="tooltip"
                                                            title="Indique la descripción de la deducción"
                                                            :config="ckeditor.editorConfig" class="form-control"
                                                            tag-name="textarea" rows="3" v-model="record.description"></ckeditor>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <formula-calculator formulaInput='formula' :withAmountButton="true"/>
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
                                        @click="reset()">
                                    Cancelar
                                </button>
                                <button type="button" @click="createRecord('deductions')"
                                        class="btn btn-primary btn-sm btn-round btn-modal-save">
                                    Guardar
                                </button>
                            </div>
                        </div>
                        <div class="modal-body modal-table">
                            <v-client-table :columns="columns" :data="records" :options="table_options">
                                <div slot="description" slot-scope="props" class="text-justify">
                                    <div class="mt-3" v-html="props.row.description"></div>
                                </div>
                                <div slot="active" slot-scope="props" class="text-center">
                                    <span v-if="props.row.active === true" class="text-bold text-success">SI</span>
                                    <span v-else class="text-bold text-danger">NO</span>
                                </div>
                                <div slot="accounting_account" slot-scope="props" class="text-center">
                                    <span v-if="props.row.accounting_account !== null">
                                        {{ props.row.accounting_account.group }}.
                                        {{ props.row.accounting_account.subgroup }}.
                                        {{ props.row.accounting_account.item }}.
                                        {{ props.row.accounting_account.generic }}.
                                        {{ props.row.accounting_account.specific }}.
                                        {{ props.row.accounting_account.subspecific }}
                                    </span>
                                    <span v-else>
                                        NO REGISTRADA
                                    </span>
                                </div>
                                <div slot="id" slot-scope="props" class="text-center">
                                    <button @click="initUpdate(props.row.id, $event)"
                                            class="btn btn-warning btn-xs btn-icon btn-action"
                                            title="Modificar registro" data-toggle="tooltip" type="button">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteRecord(props.row.id, 'deductions')"
                                            class="btn btn-danger btn-xs btn-icon btn-action"
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
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    accounting_account_id: '',
                    name: '',
                    description: '',
                    formula: '',
                    active: false
                },
                errors: [],
                records: [],
                accounting_accounts: [],
                columns: ['name', 'description', 'formula', 'accounting_account', 'active', 'id'],
            }
        },
        props: {
            accountingAccount: {
                type: Boolean,
                required: true,
                default: false
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
                    accounting_account_id: (this.accountingAccount) ? '' : null,
                    name: '',
                    description: '',
                    formula: '',
                    active: false
                };
            },
            /**
             * Obtiene un listado de cuentas patrimoniales
             *
             * @method     getAccountingAccounts
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getAccountingAccounts() {
                const vm = this;
                vm.accounting_accounts = [];
                axios.get('/accounting/accounts').then(response => {
                    if (response.data.records.length > 0) {
                        vm.accounting_accounts.push({
                            id: '',
                            text: 'Seleccione...'
                        });
                        $.each(response.data.records, function() {
                            vm.accounting_accounts.push({
                                id: this.id,
                                text: `${this.code} - ${this.denomination}`,
                                disabled:this.original
                            });
                        });
                    }
                }).catch(error => {
                    vm.logs('DeductionsComponent', 258, error, 'getAccountingAccounts');
                });
            }
        },
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'description': 'Descripción',
                'formula': 'Fórmula',
                'accounting_account': 'Cuenta Contable',
                'active': 'Activa',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name', 'description'];
            this.table_options.filterable = ['name', 'description'];
            this.table_options.columnsClasses = {
                'name': 'col-md-2',
                'description': 'col-md-3',
                'formula': 'col-md-2',
                'accounting_account': 'col-md-2',
                'active': 'col-md-1',
                'id': 'col-md-2'
            };
        },
        mounted() {
            const vm = this;

            $("#add_deduction").on('show.bs.modal', function() {
                vm.reset();
                vm.getAccountingAccounts();
            });
            $('.btn-formula').on('click', function() {
                if ($(this).data('value') === 0 && vm.record.formula.slice(-1) === '/') {
                    vm.showMessage(
                        'custom', 'Fórmula Inválida', 'warning', 'screen-warning', 'La división por cero no esta permitida'
                    );
                    return false;
                }
                vm.record.formula += $(this).data('value');
            });
        }
    };
</script>
