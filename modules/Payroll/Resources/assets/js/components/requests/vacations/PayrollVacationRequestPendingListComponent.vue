<template>
    <section id="payrollVacationRequestPendingListComponent">
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="status" slot-scope="props">
                <span v-if="props.row.status == 'approved'">
                    Aprobado
                </span>
                <span v-else-if="props.row.status == 'pending'">
                    Pendiente
                </span>
                <span v-else>
                    Rechazado
                </span>
            </div>
            <div slot="date_request" slot-scope="props">
                <span> {{ format_date(props.row.date_request, 'DD/MM/YYYY') }} </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button @click.prevent="
                        showEditModal(
                            'EditSuspensionVacationRequest',
                            props.row,
                            'PayrollEditSuspensionVacation'
                        )" :disabled="props.row.status != 'pending'" class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip" title="Modificar registro" v-has-tooltip type="button">
                        <i class="fa fa-edit" aria-label="Modificar registro"></i>
                    </button>
                    <button @click.prevent="deleteRecord(props.row.id, 'payroll/suspension-vacation-requests')"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" title="Eliminar registro"
                        data-toggle="tooltip" v-has-tooltip type="button" aria-label="Eliminar registro"
                        :disabled="props.row.status != 'pending'">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <button @click.prevent="showApproveSuspensionVacationRequestModal(
                        'ApproveSuspensionVacationRequest',
                        props.row,
                        'PayrollApproveSuspensionVacationRequest'
                    )" :disabled="props.row.status != 'pending'" class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aceptar solicitud" aria-label="Aceptar solicitud" v-has-tooltip type="button">
                        <i class="fa fa-check"></i>
                    </button>
                    <button @click.prevent="showRejectSuspensionVacationRequestModal(
                        'RejectSuspensionVacationRequest',
                        props.row,
                        'PayrollRejectSuspensionVacationRequest'
                    )" :disabled="props.row.status != 'pending'" class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Rechazar solicitud" aria-label="Rechazar solicitud" v-has-tooltip type="button"
                        data-toggle="tooltip">
                        <i class="fa fa-ban"></i>
                    </button>
                </div>
            </div>
        </v-client-table>
        <payroll-edit-suspension-vacation ref="EditSuspensionVacationRequest">
        </payroll-edit-suspension-vacation>
        <payroll-approve-suspension-vacation-request ref="ApproveSuspensionVacationRequest">
        </payroll-approve-suspension-vacation-request>
        <payroll-reject-suspension-vacation-request ref="RejectSuspensionVacationRequest">
        </payroll-reject-suspension-vacation-request>
    </section>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            columns: [
                'payroll_vacation_request.code',
                'status',
                'date_request',
                'enjoyed_days',
                'pending_days',
                'suspension_reason',
                'id',
            ]
        }
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'payroll_vacation_request.code': 'Código',
            'status': 'Estado de la solicitud',
            'date_request': 'Fecha de la solicitud',
            'enjoyed_days': 'Días efectivamente disfrutados',
            'pending_days': 'Días pendientes',
            'suspension_reason': 'Motivo de suspensión',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['code', 'date',];
        vm.table_options.sortable = ['code', 'date', 'payroll_staff'];
    },
    mounted() {
        const vm = this;
        vm.initRecords(vm.route_list, '');
    },
    methods: {
        showApproveSuspensionVacationRequestModal(ref, record, modal) {
            const vm = this;
            modal === 'PayrollApproveSuspensionVacationRequest' ?
                vm.$refs[ref].action = 'approved' :
                vm.$refs[ref].action = 'rejected';
            vm.$refs[ref].record = record;
            $(`#${modal}`).modal('show');
        },
        showRejectSuspensionVacationRequestModal(ref, record, modal) {
            const vm = this;
            modal === 'PayrollApproveSuspensionVacationRequest' ?
                vm.$refs[ref].action = 'approved' :
                vm.$refs[ref].action = 'rejected';
            vm.$refs[ref].record = record;
            $(`#${modal}`).modal('show');
        },
        showEditModal(ref, record, modal, var_list = null) {
            const vm = this;
            vm.$refs[ref].record = record;
            vm.$refs[ref].getPendingDays();
            vm.$refs[ref].getHolidays();
            $(`#${modal}`).modal('show');
        },
    }
};
</script>
