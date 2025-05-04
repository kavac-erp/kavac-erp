<template>
    <section>
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="code" slot-scope="props" class="text-center">
                <span>
                    {{ props.row.code }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <citizenservice-add-indicators
                        :requestid="props.row.id"
                        :requeststate="props.row.state">
                    </citizenservice-add-indicators>
                    <citizenservice-request-info
                        :route_list="app_url + '/citizenservice/requests/vue-info/' + props.row.id"
                        >
                    </citizenservice-request-info>
                    <template v-if="(lastYear && format_date(props.row.date, 'YYYY') <= lastYear)">
                        <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-trash-o"></i>
                        </button>
                        <button class="btn btn-success btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-ban"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button @click="editForm(props.row.id)"
                                class="btn btn-warning btn-xs btn-icon btn-action"
                                title="Modificar registro" data-toggle="tooltip" type="button" v-has-tooltip
                                :disabled="props.row.state != 'Pendiente'">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button @click="deleteRecord(props.index, '')"
                                class="btn btn-danger btn-xs btn-icon btn-action"
                                title="Eliminar registro" data-toggle="tooltip" type="button" v-has-tooltip
                                :disabled="props.row.state != 'Pendiente'">
                            <i class="fa fa-trash-o"></i>
                        </button>
                        <citizenservice-request-pending
                            :requestid="props.row.id"
                            :route_update="app_url + '/citizenservice/requests/pending'"
                            request_type='accept'
                            :requeststate="props.row.state">
                        </citizenservice-request-pending>
                        <citizenservice-request-pending
                            :requestid="props.row.id"
                            :route_update="app_url + '/citizenservice/requests/pending'"
                            request_type='rejected'
                            :requeststate="props.row.state">
                        </citizenservice-request-pending>
                    </template>
                </div>
            </div>
            <div slot="requested_by" slot-scope="props" class="text-center">
                <span>{{ props.row.first_name + ' ' + props.row.last_name }}</span>
            </div>
            <div slot="observation" slot-scope="props" class="text-center">
                <span v-html="props.row.observation ? props.row.observation : 'No definido'"></span>
            </div>
        </v-client-table>
    </section>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            lastYear: "",
            columns: ['code', 'date', 'motive_request', 'state', 'observation', 'id']
        }
    },
    created() {
        this.table_options.headings = {
            'code': 'Código de la solicitud',
            'date': 'Fecha de la solicitud',
            'motive_request': 'Motivo',
            'state': 'Estado de la solicitud',
            'observation':'Observación de la solicitud',
            'id': 'Acción'
        };
        this.table_options.sortable = ['code', 'date', 'motive_request', 'state', 'observation'];
        this.table_options.filterable = ['code', 'date', 'motive_request', 'state', 'observation'];
    },
    async mounted () {
        this.initRecords(this.route_list, '');
        const vm = this;
        await vm.queryLastFiscalYear();
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
         */
        reset() {

        },
        deleteRecord(index, url) {
            var url = (url)?url:this.route_delete;
            var records = this.records;
            var confirmated = false;
            var index = index - 1;
            const vm = this;
            url = vm.setUrl(url);

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
                            if (typeof(response.data.error) !== "undefined") {
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
        acceptRequest(index) {
            const vm = this;
            var fields = this.records[index-1];
            var id = this.records[index-1].id;

            axios.put('/'+this.route_update+'/request-approved/'+id, fields).then(response => {
                if (typeof(response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                }
                else {
                    vm.readRecords(url);
                    vm.reset();
                    vm.showMessage('update');
                }
            }).catch(error => {
                vm.errors = [];

                if (typeof(error.response) !="undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
            });
        },
        rejectedRequest(index) {
            const vm = this;
            var fields = this.records[index-1];
            var id = this.records[index-1].id;

            axios.put('/'+this.route_update+'/request-rejected/'+id, fields).then(response => {
                if (typeof(response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                }
                else {
                    vm.readRecords(url);
                    vm.reset();
                    vm.showMessage('update');
                }
            }).catch(error => {
                vm.errors = [];

                if (typeof(error.response) !="undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
            });
        },
    }
};
</script>
