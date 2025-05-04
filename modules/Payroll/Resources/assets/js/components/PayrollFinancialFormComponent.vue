<template>
    <section id="PayrollFinancialFormComponent">
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
                <div
                    class="col-md-4" id="helpFinancialStaff"
                    v-if="payroll_staffs.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2
                            :options="payroll_staffs"
                            id="payroll_staff_id"
                            v-model="record.payroll_staff_id"
                            :disabled="isEditMode"
                        >
                        </select2>
                    </div>
                </div>
                <div
                    class="col-md-4"
                    id="helpFinancialBank"
                    v-if="banks.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Banco:</label>
                        <select2
                            :options="banks"
                            id="finance_bank_id"
                            v-model="record.finance_bank_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancialTypeAccount">
                    <div
                        class="form-group is-required"
                        v-if="account_types.length > 0"
                    >
                        <label>Tipo de Cuenta:</label>
                        <select2
                            :options="account_types"
                            id="finance_account_type_id"
                            v-model="record.finance_account_type_id">
                        </select2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4" id="helpFinancialAccountNumber">
                    <div class="form-group is-required">
                        <label>Número de cuenta:</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            id="bank_code"
                            v-model="record.payroll_account_number"
                            v-input-mask data-inputmask-regex="[0-9]*"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button
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
                @click="createRecord('payroll/financials')"
                class="btn btn-success btn-icon btn-round"
                data-toggle="tooltip"
                title="Guardar registro"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
    export default {
        props: {
            payrollfinancial_edit: {
                type: Object,
                default: function() {
                    return null
                }
            },
        },
        data() {
            return {
                record: {
                    id: '',
                    payroll_staff_id: '',
                    finance_bank_id: '',
                    finance_account_type_id: '',
                    payroll_account_number: '',
                },
                errors: [],
                payroll_staffs: [],
                banks: [],
                account_types: [],
                isDisable: false,
                isEditMode: false,
            }
        },
        methods: {
            reset() {
                this.record = {
                    id: '',
                    payroll_staff_id: '',
                    finance_bank_id: '',
                    finance_account_type_id: '',
                    payroll_account_number: '',
                };
                this.isDisable = false;
            },
        },
        async created() {
            const vm = this;
            await vm.getPayrollStaffs('financial');
            await vm.getBanks();
            await vm.getAccountTypes();
        },
        async mounted() {
            const vm = this;
            if(vm.payrollfinancial_edit) {
                await vm.getPayrollStaffs();
                vm.record.id = vm.payrollfinancial_edit.id;
                vm.record.payroll_staff_id
                    = vm.payrollfinancial_edit.payroll_staff_id;
                vm.record.finance_bank_id
                    = vm.payrollfinancial_edit.finance_bank_id;
                vm.record.finance_account_type_id
                    = vm.payrollfinancial_edit.finance_account_type_id;
                vm.record.payroll_account_number
                    = vm.payrollfinancial_edit.payroll_account_number;
                vm.isDisable = true;
                // Bloquear el select del trabajador cuando esté en modo edit.
                vm.isEditMode = true;
            }
        },
    };
</script>
