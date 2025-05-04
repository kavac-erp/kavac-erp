<template>
    <div class="card-body col-md-12">
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableMax">
            <div slot="observation" slot-scope="props" class="text-center">
                <span>
                    {{ (props.row.observation)? props.row.observation:'No definido'}}
                </span>
            </div>
            <div slot="created_at" slot-scope="props" class="text-center">
                <span>
                    {{ (props.row.created_at)? format_date(props.row.created_at):'N/A'}}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="d-flex justify-content-center">
                <template v-if="(lastYear && format_date(props.row.created_at, 'YYYY') <= lastYear)">
                    <button class="btn btn-success btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-check"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-ban"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-success btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Aceptar Solicitud"
                        :disabled="(props.row.state == 'Aprobado' || props.row.state == 'Rechazado') ? true : false"
                        @click="acceptRequest(props.index)"
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Rechazar Solicitud"
                        :disabled="(props.row.state == 'Aprobado' || props.row.state == 'Rechazado')? true:false"
                        @click="rejectedRequest(props.index)"
                    >
                        <i class="fa fa-ban"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Eliminar registro"
                        :disabled="(props.row.state == 'Aprobado' || props.row.state == 'Rechazado')? true:false"
                        @click="deleteRecord(props.row.id, 'asset/requests/deliveries')"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
            </div>

        </v-client-table>
    </div>
</template>

<script>
export default {
    data() {
        return {
            record: {
                id: '',
                observation: '',
                state: '',
                asset_request_id: '',
            },
            lastYear: "",
            records: [],
            errors: [],
            columns: ['asset_request.code', 'state', 'user.name', 'created_at', 'observation','id'],
        }
    },
    created() {
        this.readRecords(this.route_list);
        this.table_options.headings = {
            'asset_request.code': 'Código de solicitud',
            'state': 'Estado de entrega',
            'user.name': 'Solicitante',
            'created_at': 'Fecha de emisión',
            'observation': 'Observaciones',
            'id': 'Acción'
        };
        this.table_options.sortable = ['asset_request.code', 'state', 'user.name', 'created_at'];
        this.table_options.filterable = ['asset_request.code', 'state', 'user.name', 'created_at'];


    },
    async mounted () {
        const vm = this;
        await vm.queryLastFiscalYear();
    },
    methods: {
        reset() {
            this.record = {
                id: '',
                observation: '',
                state: '',
                asset_request_id: '',
            }
        },

        acceptRequest(index)
        {
            const vm = this;
            var fields = vm.records[index-1];
            vm.record.id = fields.id;
            vm.record.state = 'Aprobado';
            vm.record.asset_request_id = fields.asset_request.id;
            var dialog = bootbox.confirm({
                title: '¿Aprobar entrega de equipos?',
                message:"<div class='row'>"+
                            "<div class='col-md-12'>"+
                                "<div class='form-group'>"+
                                    "<label>Observaciones generales</label>"+
                                    "<textarea data-toggle='tooltip' class='form-control input-sm'"+
                                        " title='Indique las observaciones presentadas en la solicitud'"+
                                        " id='request_observation'>"+
                                    "</textarea>"+
                                "</div>"+
                            "</div>"+
                        "</div>",
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
                        vm.record.observation = document.getElementById('request_observation').value;
                        vm.createRecord('asset/requests/deliveries');
                    }
                }
            });

        },
        rejectedRequest(index)
        {
            const vm = this;
            var fields = vm.records[index-1];
            vm.record.id = fields.id;
            vm.record.state = 'Rechazado';
            vm.record.asset_request_id = fields.asset_request.id;
            var dialog = bootbox.confirm({
                title: '¿Rechazar entrega de equipos?',
                message: "<div class='row'>"+
                            "<div class='col-md-12'>"+
                                "<div class='form-group'>"+
                                    "<label>Observaciones generales</label>"+
                                    "<textarea data-toggle='tooltip' class='form-control input-sm'"+
                                        " title='Indique las observaciones presentadas en la solicitud'"+
                                        " id='request_observation'>"+
                                    "</textarea>"+
                                "</div>"+
                            "</div>"+
                        "</div>",
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
                        vm.record.observation = document.getElementById('request_observation').value;
                        vm.createRecord('asset/requests/deliveries');
                    }
                }
            });

        },

        /**
         * Método que permite crear o actualizar un registro
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
         * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
         *                         El valor por defecto es verdadero.
         * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
         *                         El valor por defecto es verdadero.
         */
        async createRecord(url, list = true, reset = true) {
            const vm = this;
            url = vm.setUrl(url);

            if (vm.record.id) {
                vm.updateRecord(url);
            }
            else {
                vm.loading = true;
                var fields = {};

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                await axios.post(url, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.errors = [];
                        if (reset) {
                            vm.reset();
                        }
                        if (list) {
                            vm.readRecords(url);
                        }

                        vm.showMessage('store');
                    }
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }

                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }

                });

                vm.loading = false;
            }

        },

        /**
         * Método que permite actualizar información
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta de la acci´on que modificará los datos
         */
        async updateRecord(url) {
            const vm = this;
            vm.loading = true;
            var fields = {};
            url = vm.setUrl(url);

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
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
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }

                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
            });
            vm.loading = false;
        },

        /**
     * Método para la eliminación de registros
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  {integer} id    ID del Elemento seleccionado para su eliminación
     * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
     */
    deleteRecord(id, url) {
        const vm = this;
        /** @type {string} URL que atiende la petición de eliminación del registro */
        var url = vm.setUrl((url)?url:vm.route_delete);

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
            callback: async function (result) {
                if (result) {
                    vm.loading = true;
                    /** @type {object} Objeto con los datos del registro a eliminar */
                    let recordDelete = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                        return rec.id === id;
                    })[0]));

                    await axios.delete(`${url}${url.endsWith('/')?'':'/'}${recordDelete.id}`).then(response => {
                        if (typeof(response.data.error) !== "undefined") {
                            /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                            vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                            return false;
                        }
                        /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                        vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                            return rec.id !== id;
                        })));
                        if (typeof(vm.$refs.tableResults) !== "undefined") {
                            vm.$refs.tableResults.refresh();
                        }
                        vm.showMessage('destroy');
                    }).catch(error => {
                        if (typeof(error.response) !="undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
                        }
                        vm.logs('mixins.js', 498, error, 'deleteRecord');
                    });
                    vm.loading = false;
                }
            }
        });
    },

    }
};
</script>
