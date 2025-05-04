<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registros de tipos de organizaciones"
           data-toggle="tooltip" @click="addRecord('add_institution_type', 'institution-types', $event)">
            <i class="icofont icofont-building-alt ico-3x"></i>
            <span>Tipo de<br>Organizaciones</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_institution_type">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-building-alt inline-block"></i>
                            Tipo de Organización
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Acrónimo:</label>
                                    <input type="text" placeholder="Acrónimo"
                                        data-toggle="tooltip"
                                        title="Indique el acrónimo del tipo de organización"
                                        class="form-control input-sm"
                                        v-model="record.acronym"
                                        maxlength="4" v-is-text>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input type="text" placeholder="Tipo" class="form-control input-sm" data-toggle="tooltip"
                                           v-model="record.name" title="Indique el nombre del tipo de organización (requerido)"
                                           v-is-text>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                    @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                    @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('institution-types')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'institution-types')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    acronym: '',
                    name: ''
                },
                errors: [],
                records: [],
                columns: ['acronym', 'name', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    acronym: '',
                    name: ''
                };
            },
            /**
             * Método para la eliminación de registros
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} id    ID del Elemento seleccionado para su eliminación
             * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
             */
            deleteRecord(id, baseUrl) {
                const vm = this;
                /** @type {string} URL que atiende la petición de eliminación del registro */
                var url = vm.setUrl(baseUrl);

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
                                vm.readRecords(baseUrl);
                                vm.showMessage('destroy');
                            }).catch(error => {
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                            vm.loading = false;
                        }
                    }
                });
            },
            /**
             * Reescribe el metodo para cambiar su comportamiento por defecto
             * Método que obtiene los registros a mostrar
             *
             * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
             *
             * @param  {string} url Ruta que obtiene todos los registros solicitados
             */
            readRecords(url) {
                const vm = this;
                vm.loading = true;
                url = vm.setUrl(url);

                axios.get(url).then(response => {
                    if (typeof(response.data.records) !== "undefined") {
                        vm.records = response.data.records;
                        // Actualiza "select" en html
                        vm.updateSelect($("#institution_type_id"), response.data.records);
                    }
                    vm.loading = false;
                }).catch(error => {
                    vm.logs('mixins.js', 285, error, 'readRecords');
                });
            },
        },
        created() {
            this.table_options.headings = {
                'acronym': 'Acrónimo',
                'name': 'Nombre',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name', 'acronym'];
            this.table_options.filterable = ['name', 'acronym'];
            this.table_options.columnsClasses = {
                'acronym': 'col-md-2',
                'name': 'col-md-8',
                'id': 'col-md-2'
            };
        },
    };
</script>
