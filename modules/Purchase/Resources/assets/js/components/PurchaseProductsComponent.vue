<template>
    <div class="text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="#" title="Registro de Catálogo de Productos y Servicios"
            data-toggle="tooltip"
            @click="addRecord( 'add_products', '/purchase/products', $event)">
            <i class="ion icofont icofont-ui-cart ico-2x"></i>
            <span>Catálogo de Productos y Servicios</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_products">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-toggle="tooltip" v-has-tooltip
                                class="btn btn-primary btn-xs btn-icon btn-action"
                                style="margin-right: 3.5rem; margin-top: -.1rem;"
                                title="Presione para subir los registros mediante hoja de cálculo."
                                @click="setFile('importFile')">
                            <i class="fa fa-upload"></i>
                        </button>
                        <input  id="importFile" name="importFile"
                                type="file" style="display:none;"
                                @change="importData()">

                        <button type="button" data-toggle="tooltip" v-has-tooltip
                                class="btn btn-primary btn-xs btn-icon btn-action"
                                style="margin-right: 1.5rem; margin-top: -.1rem;"
                                title="Presione para descargar el documento con la información de los registros."
                                @click="exportData()">
                            <i class="fa fa-download"></i>
                        </button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="ion icofont icofont-ui-cart inline-block"></i>
                            Catálogo de Productos y Servicios
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                        @click.prevent="errors = []">
                                    <span aria-hidden="true">
                                        <i class="now-ui-icons ui-1_simple-remove"></i>
                                    </span>
                                </button>
                                <ul>
                                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre del producto o servicio:</label>
                                    <input type="text" placeholder="Nombre del producto o servicio"
                                           data-toggle="tooltip" v-model="record.name"
                                           title="Indique el nombre del producto o servicio (requerido)"
                                           class="form-control input-sm">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Código:</label>
                                    <input type="text" placeholder="Código del producto o servicio"
                                           data-toggle="tooltip" v-model="record.code"
                                           title="Indique el código del producto o servicio (requerido)"
                                           class="form-control input-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button"
                                    class="btn btn-default btn-sm btn-round btn-modal-close"
                                    @click="clearFilters" data-dismiss="modal">
                                    Cerrar
                            </button>
                            <button type="button"
                                    class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                    @click="reset()">
                                    Cancelar
                            </button>
                            <button type="button"
                                    @click="createRecord('purchase/products')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                    Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-tooltip"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, '/purchase/products')"
                                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
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
                    name: '',
                    code: '',
                },
                errors: [],
                records: [],
                columns: ['name','code', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                this.record = {
                    id: '',
                    name: '',
                    code: '',
                };
            },

            /**
             * Método que se utiliza para exportar los productos e insumos
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            exportData() {
                /**
                 * instrucciones para exportar registros
                 */
                location.href = `${window.app_url}/purchase/products/export`;
            },

            /**
             * Método que se utiliza para importar los productos e insumos
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            importData() {
                /**
                 * instrucciones para importar registros
                 */
                const vm = this;
                var url = '/purchase/products/import' ;
                var formData = new FormData();
                var importFile = document.querySelector('#importFile');
                formData.append("file", importFile.files[0]);
                vm.loading = true;
                axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    console.log('exit');
                    vm.loading = false;
                    vm.showMessage('store');
                }).catch(error => {
                    console.log('failure');
                    vm.loading = false;

                });
            },
        },
        mounted() {
            const vm = this;
        },
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'id': 'Acción',
                'code' : 'Código',
            };
            this.table_options.sortable = ['name','code'];
            this.table_options.filterable = ['name','code'];
            this.table_options.columnsClasses = {
                'name': 'col-4',
                'code': 'col-6',
                'id': 'col-2'
            };
        },
    };
</script>
