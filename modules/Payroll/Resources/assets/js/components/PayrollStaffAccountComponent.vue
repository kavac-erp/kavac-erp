<template>
    <section id="PayrollStaffAccountForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                        Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li
                            v-for="(error, index) in errors"
                            :key="index"
                        >
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffAccountStaff">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2
                            :options="payroll_staffs"
                            v-model="record.payroll_staff_id"

                        >
                        </select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffAccountStaff">
                    <div class="form-group is-required">
                        <label>Cuenta contable:</label>
                        <select2
                            :options="accounting_accounts"
                            v-model="record.accounting_account_id"
                        >
                        </select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <a
                        class="btn btn-sm btn-info btn-action btn-tooltip"
                        href="javascript:void(0)"
                        data-original-title="Agregar cuenta contable"
                        title="Agregar cuenta contable"
                        @click="addStaffAccount();"
                        style="margin-top: auto; margin-bottom: 0.4cm;"
                >
                        <i class="fa fa-plus-circle"></i>
                </a>
                <div class ="col-md-12">
                    <div class="modal-body modal-table text-center">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button @click="editStaffAccount(props.index, $event)"
                                            class="btn btn-warning btn-xs btn-icon btn-action"
                                            title="Modificar registro" data-toggle="tooltip" type="button">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button v-if="isEditMode"
                                            class="btn btn-danger btn-xs btn-icon btn-action"
                                            title="Eliminar registro" data-toggle="tooltip"
                                            disabled
                                            type="button">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                    <button v-else
                                            @click="deleteStaffAccount(props.index, $event)"
                                            class="btn btn-danger btn-xs btn-icon btn-action"
                                            title="Eliminar registro" data-toggle="tooltip"
                                            type="button">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        </v-client-table>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button v-if="!isEditMode"
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                type="button"
                title="Borrar datos del formulario"
                @click="reset"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="generateRecord();"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
    export default {
        props: {
            payroll_staff_account_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    payroll_staff_id: '',
                    accounting_account_id: '',
                    accounting_registers: []
                },
                errors: [],
                payroll_staffs: [],
                accounting_accounts: [],
                records: [],
                old_payroll_staff_id: '',
                old_accounting_account_id: '',
                edit_payroll_staff_id: '',
                edit_accounting_account_id: '',
                columns: ['payroll_staff', 'accounting_account', 'id'],
                editIndex: null,
                isEditMode: false,
                currentUrl: window.location.pathname,
            }
        },
        methods: {
            /**
             * Método que limpia todos los datos del formulario.
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    payroll_staff_id: '',
                    accounting_account_id: '',
                    accounting_registers: [],
                };
                this.records = [];
                this.editIndex = null;
            },

            /**
             * Obtiene un listado de cuentas patrimoniales
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
            */
            getAccountingAccounts() {
                const vm = this;
                vm.accounting_accounts = [];
                axios.get(`${window.app_url}/accounting/get_original_accounts`).then(response => {
                    if (response.data.length > 0) {
                        $.each(response.data, function() {
                            vm.accounting_accounts.push({
                                id:   this.id,
                                text: this.text,
                                disabled: this.disabled
                            });
                        });
                    }
                }).catch(error => {
                    vm.logs('PayrollStaffAccountComponent', 258, error, 'getAccountingAccounts');
                });
            },

            addStaffAccount() {
                const vm = this;
                vm.errors = [];

                if (vm.editIndex === null) {
                    if (vm.record.payroll_staff_id == null || vm.record.payroll_staff_id == '') {
                        vm.errors.push('El campo trabajador es obligatorio.');
                    }
                    if (vm.record.accounting_account_id == null || vm.record.accounting_account_id == '') {
                        vm.errors.push('El campo datos contables es obligatorio.');
                    }

                    if (vm.records.length > 0) {
                        for (let register of vm.records) {
                            if (vm.record.accounting_account_id == register.accounting_account_id && vm.record.payroll_staff_id == register.payroll_staff_id) {
                            vm.errors.push('Ya existe un registro similar asignado en la tabla de registros.');
                            }
                        }
                    }

                    if(vm.errors.length > 0){
                        $('html,body').animate({
                            scrollTop: $("#PayrollStaffAccountForm").offset()
                        }, 1000);

                        return;
                    } else {
                        vm.appendTable(vm.payroll_staffs, vm.accounting_accounts);
                    }
                } else if (vm.editIndex >= 0) {

                    vm.appendTable(vm.payroll_staffs, vm.accounting_accounts, false, true);
                }
                vm.resetStaffAccount();
            },

            resetStaffAccount() {
                const vm = this;
                vm.record.payroll_staff_id = '';
                vm.record.accounting_account_id = '';
                vm.editIndex = null;
            },

            editStaffAccount(index, event) {
                const vm = this;
                vm.record.payroll_staff_id = vm.records[index - 1].payroll_staff_id;
                vm.record.accounting_account_id = vm.records[index - 1].accounting_account_id;
                vm.editIndex = index-1;
                event.preventDefault();
            },

            appendTable(payroll_staff, accounts, edit = false, edit_index = false) {
                const vm = this;
                let edited_payroll_staff_id = '';
                let edited_accounting_account_id = '';

                if (edit) {
                    vm.record.accounting_registers = [];
                    vm.records = [];
                }

                let employer_name;
                let employer_id;

                for (let personal of payroll_staff) {
                    if (vm.record.payroll_staff_id == personal.id) {
                        employer_name = personal.text;
                        employer_id = personal.id;
                    }
                }

                let account_name;
                let account_id;

                for (let acc of accounts) {
                    if (vm.record.accounting_account_id == acc.id) {
                        account_name = acc.text;
                        account_id = acc.id;
                    }
                }
                if (edit_index) {
                    edited_payroll_staff_id = vm.records[vm.editIndex].payroll_staff_id;
                    edited_accounting_account_id = vm.records[vm.editIndex].accounting_account_id;
                    vm.records.splice(vm.editIndex, 1);
                    vm.record.accounting_registers.splice(vm.editIndex, 1);
                }

                if(edited_payroll_staff_id == vm.edit_payroll_staff_id && edited_accounting_account_id == vm.edit_accounting_account_id) {
                    vm.record.accounting_registers.unshift({
                        payroll_staff: employer_name.split(' - ')[1],
                        payroll_staff_id: employer_id,
                        accounting_account: account_name,
                        accounting_account_id: account_id,
                    });

                    vm.records.unshift({
                        id: '',
                        payroll_staff: employer_name,
                        payroll_staff_id: employer_id,
                        accounting_account: account_name,
                        accounting_account_id: account_id,
                    });

                    vm.edit_payroll_staff_id = employer_id;
                    vm.edit_accounting_account_id = account_id;

                } else {
                    vm.record.accounting_registers.push({
                        payroll_staff: employer_name.split(' - ')[1],
                        payroll_staff_id: employer_id,
                        accounting_account: account_name,
                        accounting_account_id: account_id,
                    });

                    vm.records.push({
                        id: '',
                        payroll_staff: employer_name,
                        payroll_staff_id: employer_id,
                        accounting_account: account_name,
                        accounting_account_id: account_id,
                    });
                }
            },

            deleteStaffAccount(index, event) {
                const vm = this;
                vm.records.splice(index - 1, 1);
                vm.record.accounting_registers.splice(index - 1, 1);
            },

            generateRecord() {
                let vm = this;

                for (let account of vm.record.accounting_registers) {
                    if (vm.old_accounting_account_id == account.accounting_account_id && vm.old_payroll_staff_id == account.payroll_staff_id) {
                        vm.errors.push('El registro a modificar ya esta en el sistema');
                    }
                }

                if (vm.record.accounting_registers.length < 1) {
                    vm.errors.push('Debes asignar al menos una cuenta contable en la tabla de registros')
                }

                if(vm.errors.length > 0){
                    $('html,body').animate({
                        scrollTop: $("#PayrollStaffAccountForm").offset()
                    }, 1000);
                    return;
                }

                if (vm.errors < 1) {
                    vm.createRecord('payroll/staff-accounts');
                }
            },

            /**
             * Método que carga los datos guardados del empleado
             */
            async loadForm(id) {
                const vm = this;
                vm.payroll_staffs = [];

                let recordEdit ={
                    id: '',
                    payroll_staff_id: '',
                    accounting_account_id: '',
                };

                await axios.get(`${window.app_url}/payroll/staff-accounts/${id}`).then(response => {
                    let data = response.data.record;

                    recordEdit = {
                        id: data.id,
                        payroll_staff_id: data.payroll_staff_id,
                        accounting_account_id: data.accounting_account_id
                    }
                    // Modo edicion activado
                    vm.isEditMode = true;
                });

                await axios.get(`${window.app_url}/payroll/get-staffs/staff-accounts?payroll_staff_id=${recordEdit.payroll_staff_id}`).then(response => {
                    vm.payroll_staffs = response.data;
                });

                vm.record = recordEdit;

                vm.appendTable(vm.payroll_staffs, vm.accounting_accounts, true);
                vm.old_payroll_staff_id = vm.record.payroll_staff_id;
                vm.old_accounting_account_id = vm.record.accounting_account_id;
                vm.edit_payroll_staff_id = vm.record.payroll_staff_id;
                vm.edit_accounting_account_id = vm.record.accounting_account_id;
                vm.record.payroll_staff_id = '';
                vm.record.accounting_account_id = '';

            },
        },
        async mounted() {
            const currentUrl = window.location.pathname;
            const vm = this;
            vm.getPayrollStaffs('all');
            vm.getAccountingAccounts();

            vm.record.accounting_registers = [];

            if (vm.payroll_staff_account_id) {
                vm.loadForm(vm.payroll_staff_account_id);
            }
        },

        created() {

            this.table_options.headings = {
                payroll_staff: 'Trabajador',
                accounting_account: 'Cuenta patrimonial',
                id:'Acción'
            };
            this.table_options.sortable = ['payroll_staff', 'accounting_account', 'id'];
            this.table_options.filterable = ['payroll_staff', 'accounting_account', 'id'];
            this.table_options.columnsClasses = {
                payroll_staff: 'col-md-4',
                accounting_account: 'col-md-4',
                id:'col-md-2 text-center'
            };
        },
    };
</script>
