<template>
    <section id="payrollVacationRequestPendingListComponent">
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="date" slot-scope="props">
                <span> {{ format_date(props.row.created_at, 'YYYY-MM-DD') }} </span>
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
            
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <payroll-review-vacation-request-pending-form
                        :route_show="app_url + '/payroll/vacation-requests/show/' + props.row.id"
                        :id="props.row.id">
                    </payroll-review-vacation-request-pending-form>
                    <button @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            data-toggle="tooltip" title="Modificar registro"
                            v-has-tooltip type="button">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.row.id, '')"
                            class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                            title="Eliminar registro" data-toggle="tooltip"
                            v-has-tooltip type="button">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-client-table>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                columns: ['code', 'date', 'payroll_staff', 'id']
            }
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'code':          'Código',
                'date':          'Fecha de la solicitud',
                'payroll_staff': 'Trabajador',
                'id':            'Acción'
            };
            vm.table_options.sortable   = ['code', 'date', 'payroll_staff'];
            vm.table_options.sortable   = ['code', 'date', 'payroll_staff'];
        },
        mounted () {
            const vm = this;
            vm.initRecords(vm.route_list, '');
        },
        methods: {
            reset() {
                //
            }
        }
    };
</script>
