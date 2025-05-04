<template>
    <section id="payrollVacationRequestListComponent">
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="date" slot-scope="props">
                <span> {{ format_date(props.row.created_at, 'DD/MM/YYYY') }} </span>
            </div>
            <div slot="payroll_staff" slot-scope="props">
                <span>
                    {{ 
                        props.row.payroll_staff
                            ? props.row.payroll_staff.id
                                ? props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name
                                : 'No definido'
                            : 'No definido'

                    }}
                </span>
            </div>
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
            <div slot="reincorporation_date" slot-scope="props">
                <span>
                    {{ 
                         getDateOrMotive(props.row.status_parameters) 
                            ? getDateOrMotive(props.row.status_parameters)[2] == '/'
                                ? getDateOrMotive(props.row.status_parameters) 
                                : 'N/A' 
                            : 'N/A'

                    }}
                </span>
            </div>
            <div slot="motive" slot-scope="props">
                <span>
                    {{ 
                        getDateOrMotive(props.row.status_parameters) 
                            ? getDateOrMotive(props.row.status_parameters)[2] != '/'
                                ? getDateOrMotive(props.row.status_parameters).replace( /(<([^>]+)>)/ig, '')
                                : 'N/A' 
                            : 'N/A'

                    }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <section class="d-inline-flex">
                    <payroll-vacation-request-show
                        :route_show="app_url + '/payroll/vacation-requests/show/' + props.row.id"
                        :id="props.row.id">
                    </payroll-vacation-request-show>
                    <button @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            data-toggle="tooltip" title="Modificar registro"
                            v-has-tooltip type="button"
                            :disabled="props.row.status != 'pending'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.row.id, '')"
                            class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                            title="Eliminar registro" data-toggle="tooltip"
                            v-has-tooltip type="button"
                            :disabled="props.row.status != 'pending'">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <payroll-review-vacation-request-pending-form
                        :requestid="props.row.id"
                        :payroll_staff_id="props.row.payroll_staff_id"
                        :route_update="app_url + '/payroll/vacation-requests/'"
                        request_type="approved"
                        :requeststate="props.row.status"
                        :route_show="app_url + '/payroll/vacation-requests/show/' + props.row.id">
                    </payroll-review-vacation-request-pending-form>

                    <payroll-review-vacation-request-pending-form
                        :requestid="props.row.id"
                        :payroll_staff_id="props.row.payroll_staff_id"
                        :route_update="app_url + '/payroll/vacation-requests/'"
                        request_type="rejected"
                        :requeststate="props.row.status"
                        :route_show="app_url + '/payroll/vacation-requests/show/' + props.row.id">
                    </payroll-review-vacation-request-pending-form>
                </section>
            </div>
        </v-client-table>
    </section>
</template>
<script>
    export default {
        data() {
            return {
                record:  {},
                records: [],
                columns: ['code', 'date', 'payroll_staff', 'status', 'reincorporation_date', 'motive', 'id'],
            }
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'code':                 'Código',
                'date':                 'Fecha de la solicitud',
                'payroll_staff':        'Trabajador',
                'status':               'Estatus de la solicitud',
                'reincorporation_date': 'Fecha de reincorporación',
                'motive':               'Motivo del rechazo',
                'id':                   'Acción'
            };
            vm.table_options.sortable   = ['code', 'date', 'payroll_staff', 'status', 'reincorporation_date', 'motive'];
            vm.table_options.filterable = ['code', 'date', 'payroll_staff', 'status'];
        },

        mounted() {
            const vm = this;
            vm.initRecords(vm.route_list, '');
        },
        methods: {
            reset() {
                //
            },

            getDateOrMotive(parameter){
                const vm = this;
                let parameter_ = JSON.parse(parameter);
                return parameter_? parameter_[4] == '-' ? vm.format_date(parameter_, 'DD/MM/YYYY') : parameter_ : null;
            }
        }
    };
</script>
