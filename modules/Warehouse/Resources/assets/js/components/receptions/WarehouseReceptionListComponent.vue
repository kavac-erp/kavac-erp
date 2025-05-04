<template>
    <v-client-table :columns="columns" :data="records" :options="table_options">
        <div slot="code" slot-scope="props" class="text-center">
            <span>
                {{ props.row.code }}
            </span>
        </div>
        <div slot="warehouse" slot-scope="props">
            <span>
                {{ (props.row.warehouse_institution_warehouse_end)?props.row.warehouse_institution_warehouse_end.warehouse.name:'N/A' }}
            </span>
        </div>
        <div slot="state" slot-scope="props">
            <span>
                {{ (props.row.state)?props.row.state:'N/A' }}
            </span>
        </div>
        <div slot="reception_date" slot-scope="props">
            <span>
                {{ (props.row.reception_date) ? format_date(props.row.reception_date) : format_date(props.row.created_at) }}
            </span>
        </div>
        <div slot="id" slot-scope="props" class="text-center">
            <div class="d-inline-flex">
                <warehouse-rec-info
                    :route_list="app_url + '/warehouse/receptions/info/'+ props.row.id">
                </warehouse-rec-info>
                <template v-if="(lastYear && format_date(props.row.created_at, 'YYYY') <= lastYear)">
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
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Modificar registro"
                        :disabled="props.row.state != 'Pendiente'"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Eliminar registro"
                        :disabled="props.row.state != 'Pendiente'"
                        @click="deleteRecord(props.index, '')"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <button
                        class="btn btn-success btn-xs btn-icon btn-action"
                        type="button"
                        title="Aceptar solicitud"
                        data-toggle="tooltip"
                        :disabled="props.row.state != 'Pendiente'"
                        @click="approvedRequest(props.index)"
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Rechazar solicitud"
                        :disabled="props.row.state != 'Pendiente'"
                        @click="rejectedRequest(props.index)"
                    >
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
            columns: ['code', 'description', 'warehouse', 'reception_date', 'state', 'id']
        }
    },
    created() {
        this.table_options.headings = {
            'code': 'Código',
            'description': 'Descripción',
            'warehouse': 'Almacén',
            'reception_date': 'Fecha de ingreso',
            'state': 'Estado de la solicitud',
            'id': 'Acción'
        };
        this.table_options.sortable = ['code', 'description', 'warehouse', 'reception_date', 'state'];
        this.table_options.filterable = ['code', 'description', 'warehouse', 'reception_date', 'state'];
    },
    async mounted () {
        const vm = this;
        this.initRecords(this.route_list, '');
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
                        var fields = vm.records[index-1];
                        var id = vm.records[index-1].id;
                        axios.put(vm.route_update + '/reception-approved/' + id, fields).then(response => {
                            if (typeof(response.data.redirect) !== "undefined")
                                location.href = response.data.redirect;
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
                    }
                }
            });

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
                        var fields = vm.records[index-1];
                        var id = vm.records[index-1].id;

                        axios.put(vm.route_update + '/reception-rejected/' + id, fields).then(response => {
                            if (typeof(response.data.redirect) !== "undefined")
                                location.href = response.data.redirect;
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
                    }
                }
            });

        },
    }
};
</script>
