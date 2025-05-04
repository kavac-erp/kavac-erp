<template>
    <v-client-table :columns="columns" :data="records" :options="table_options">
        <div slot="code" slot-scope="props" class="text-center">
            <span>
                {{ props.row.code }}
            </span>
        </div>
        <div slot="requested_by" slot-scope="props">
            <span>
                {{ (props.row.payroll_staff) ? props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name :
                    ((props.row.department) ? props.row.department.name : '') }}
            </span>
        </div>
        <div slot="motive" slot-scope="props" class="text-center">
            <span> {{ prepareText(props.row.motive) }} </span>
        </div>
        <div slot="request_date" slot-scope="props">
            <span>
                {{ (props.row.request_date) ? format_date(props.row.request_date):format_date(props.row.created_at) }}
            </span>
        </div>
        <div slot="id" slot-scope="props" class="text-center">
            <div class="d-inline-flex">
                <warehouse-req-info :route_list="app_url + '/warehouse/requests/info/' + props.row.id"
                    :infoid="props.row.id">
                </warehouse-req-info>
                <warehouse-request-pending :requestid="props.row.id"
                    v-if="((props.row.delivered == false) && (props.row.state == 'Aprobado'))">
                </warehouse-request-pending>
                <template v-if="(lastYear && format_date(props.row.created_at, 'YYYY') <= lastYear)">
                    <button class="btn btn-success btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-check"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-ban"></i>
                    </button>
                </template>
                <template v-else>
                    <button @click="approvedRequest(props.index)" class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aceptar solicitud" data-toggle="tooltip" type="button"
                        :disabled="props.row.state != 'Pendiente'">
                        <i class="fa fa-check"></i>
                    </button>
                    <button @click="rejectedRequest(props.index)" class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Rechazar solicitud" data-toggle="tooltip" type="button"
                        :disabled="props.row.state != 'Pendiente'">
                        <i class="fa fa-ban"></i>
                    </button>
                </template>
            </div>
        </div>
    </v-client-table>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            lastYear: "",
            columns: ['code', 'requested_by', 'motive', 'state', 'request_date', 'id']
        }
    },
    created() {
        this.table_options.headings = {
            'code': 'Código',
            'requested_by': 'Solicitado por',
            'motive': 'Motivo',
            'state': 'Estado de la solicitud',
            'request_date': 'Fecha de la solicitud',
            'id': 'Acción'
        };
        this.table_options.sortable = ['code', 'requested_by', 'motive', 'state', 'created_at'];
        this.table_options.filterable = ['code', 'requested_by', 'motive', 'state', 'created_at'];
    },
    async mounted() {
        this.initRecords(this.route_list, '');
        const vm = this;
        await vm.queryLastFiscalYear();
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {

        },
        rejectedRequest(index) {
            const vm = this;
            var dialog = bootbox.confirm({
                title: '¿Rechazar operación?',
                message: "<p>¿Seguro que desea rechazar esta operación?. Una vez rechazada la operación no se podrán realizar cambios en la misma.<p>",
                size: 'medium',
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
                        var fields = vm.records[index - 1];
                        var id = vm.records[index - 1].id;

                        axios.put('/warehouse/requests/request-rejected/' + id, fields).then(response => {
                            if (typeof (response.data.redirect) !== "undefined")
                                location.href = response.data.redirect;
                        }).catch(error => {
                            vm.errors = [];
                            if (typeof (error.response) != "undefined") {
                                for (var index in error.response.data.errors) {
                                    if (error.response.data.errors[index]) {
                                        vm.errors.push(error.response.data.errors[index][0]);
                                    }
                                }
                            }
                        });
                    }
                }
            });
        },
        prepareText(text) {
            return text.replace('<p>', '').replace('</p>', '');

        },
        approvedRequest(index) {
            const vm = this;
            var dialog = bootbox.confirm({
                title: '¿Aprobar operación?',
                message: "<p>¿Seguro que desea aprobar esta operación?. Una vez aprobada la operación no se podrán realizar cambios en la misma.<p>",
                size: 'medium',
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
                        var fields = vm.records[index - 1];
                        var id = vm.records[index - 1].id;

                        axios.put('/warehouse/requests/request-approved/' + id, fields).then(response => {
                            if (typeof (response.data.redirect) !== "undefined")
                                location.href = response.data.redirect;
                        }).catch(error => {
                            vm.errors = [];
                            if (typeof (error.response) != "undefined") {
                                for (var index in error.response.data.errors) {
                                    if (error.response.data.errors[index]) {
                                        vm.errors.push(error.response.data.errors[index][0]);
                                    }
                                }
                            }
                        });
                    }
                }
            });
        },
    }
};
</script>
