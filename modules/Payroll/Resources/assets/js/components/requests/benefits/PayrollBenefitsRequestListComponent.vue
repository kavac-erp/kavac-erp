<template>
    <section id="payrollBenefitsRequestListComponent">
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="date" slot-scope="props">
                <span> {{ format_date(props.row.created_at, 'DD-MM-YYYY') }} </span>
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
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex ">
                    <payroll-benefits-request-show :route_show="app_url + '/payroll/benefits-requests/show/' + props.row.id"
                        :id="props.row.id">
                    </payroll-benefits-request-show>
                    <button @click="editForm(props.row.id)"
                        :disabled="(props.row.status == 'approved' || props.row.status == 'rejected')"
                        class="btn btn-warning btn-xs btn-icon btn-action" data-toggle="tooltip" title="Modificar registro"
                        data-placement="bottom" type="button">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.index, '')"
                        :disabled="(props.row.status == 'approved' || props.row.status == 'rejected')"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" title="Eliminar registro"
                        data-toggle="tooltip" data-placement="bottom" type="button">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <payroll-review-benefits-request-pending-form
                        :route_show="app_url + '/payroll/benefits-requests/show/' + props.row.id" :id="props.row.id"
                        :request_status="props.row.status">
                    </payroll-review-benefits-request-pending-form>
                </div>
            </div>
        </v-client-table>
    </section>
</template>
<script>
export default {
    data() {
        return {
            record: {},
            records: [],
            columns: ['code', 'date', 'payroll_staff', 'status', 'id'],
        }
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'code': 'Código',
            'date': 'Fecha de la solicitud',
            'payroll_staff': 'Trabajador',
            'status': 'Estatus de la solicitud',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['code', 'date', 'payroll_staff', 'status'];
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
        deleteRecord(index, url) {
            const vm = this;
            var records = vm.records;
            var confirmated = false;
            var index = index - 1;
            url = (url) ? vm.setUrl(url) : vm.route_delete;

            bootbox.confirm({
                title: "¿Eliminar registro?",
                message: "¿Está seguro de eliminar este registro?",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar'
                    }
                },
                callback: function (result) {
                    if (result) {
                        confirmated = true;
                        axios.delete(url + '/' + records[index].id).then(response => {
                            if (typeof (response.data.error) !== "undefined") {
                                /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                return false;
                            }
                            records.splice(index, 1);
                            vm.showMessage('destroy');
                        }).catch(error => {
                            vm.logs('mixins.js', 498, error, 'deleteRecord');
                        });
                    }
                }
            });

            if (confirmated) {
                this.records = records;
                this.showMessage('destroy');
            }
        },
    }
};
</script>
