<template>
    <div class="text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="#" title="Registros de modalidades de compras" data-toggle="tooltip" v-has-tooltip
            @click="addRecord('add_purchase_types', '/purchase/purchase_types', $event),
                    readRecords(`/required-documents/supplier/purchase`)">
            <i class="now-ui-icons shopping_tag-content ico-3x"></i>
            <span>Modalidades de<br>Compras</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_purchase_types">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="aqui">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-box inline-block"></i>
                            Modalidades de compras
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- Componente para mostrar errores en el formulario -->
                        <purchase-show-errors ref="purchaseShowError" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input type="text" placeholder="Nombre del tipo de compra" data-toggle="tooltip" v-has-tooltip v-model="record.name" title="Indique el nombre del tipo de compra (requerido)" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group" data-toggle="tooltip" v-has-tooltip title="Indique la descripción para el tipo de compra">
                                    <label>Descripción:</label>
                                    <ckeditor :editor="ckeditor.editor" :config="ckeditor.editorConfig" class="form-control" tag-name="textarea" rows="3" v-model="record.description" placeholder="Descripción del tipo de compra"></ckeditor>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class=" form-group">
                                    <label>Seleccione los documentos para esta modalidad de compra:</label>
                                    <br>
                                    <table align="center" >
                                        <tr>
                                            <div class="row col-xs-12" v-for="i in Math.ceil(required_documents.length / 3)" :key="i">
                                                <div
                                                    v-for="(required_document, index) in required_documents.slice((i - 1) * 3, i * 3)"
                                                    :key="index"
                                                >
                                                    <td style="width:250px">
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <input type="checkbox" :id="required_document.id"
                                                                    :value="required_document.id" v-model="checkedNames"
                                                                    title="Seleccione el documento">
                                                            <label :for="required_document.name"><strong>{{required_document.name}}</strong></label>
                                                        </div>
                                                    </td>
                                                </div>
                                            </div>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <modal-form-buttons saveRoute="/purchase/purchase_types"></modal-form-buttons>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="description" slot-scope="props">
                                <p v-html="props.row.description"></p>
                            </div>
                            <div slot="documents" slot-scope="props">
                                <div v-for="(document, index) in props.row.documents" :key="index">
                                    {{`${document.name}.`}}
                                </div>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button @click="loadData(props.row)"
                                            class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro" data-toggle="tooltip" v-has-tooltip>
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteRecord(props.row.id,'/purchase/purchase_types')" class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro" data-toggle="tooltip" v-has-tooltip>
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
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
            records: [],
            checkedNames: [],
            required_documents: [],
            errors: [],
            columns: ['name', 'description', 'documents', 'id'],
            record: {
                name: '',
                description: '',
                purchase_processes_id: '',
                documents_id: [],
            },
            edit: false,
        }
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            this.edit = false;
            this.checkedNames = [],
            this.record = {
                id: '',
                name: '',
                description: '',
                purchase_processes_id: '',
                documents_id: [],
            };
        },
        createRecord(url) {
            const vm = this;
            vm.record.documents_id = vm.checkedNames;
            if (!vm.edit) {
                vm.loading = true;
                axios.post(url, vm.record).then(response => {
                    vm.records = response.data.records;
                    vm.showMessage("store");
                    vm.reset();
                    vm.loading = false;
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.$refs.purchaseShowError.refresh();
                    vm.loading = false;
                });
            } else if (vm.edit && vm.record.id) {
                vm.loading = true;
                axios.put(url + '/' + vm.record.id, vm.record).then(response => {
                    vm.records = response.data.records;
                    vm.showMessage("update");
                    vm.reset();
                    vm.loading = false;
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.$refs.purchaseShowError.refresh();
                    vm.loading = false;
                });
            }
        },
        loadData(record) {
            this.edit = true;
            this.record = record;
            this.checkedNames = (this.record.documents_id)?JSON.parse(this.record.documents_id):[];
        },
        /**
         * Método que reescribe la funcionalidad original de 'readRecords' para obtiener los registros a mostrar
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta que obtiene todos los registros solicitados
         */
        readRecords(url) {
            const vm = this;
            url = this.setUrl(url);

            axios.get(url).then(response => {
                if (typeof(response.data.records) !== "undefined") {
                    vm.required_documents = response.data.records;
                }
            }).catch(error => {
                vm.logs('mixins.js', 285, error, 'readRecords');
            });
        },
    },
    created() {
        this.table_options.headings = {
            'name': 'Nombre',
            'description': 'Descripción',
            'documents': 'Documentos necesarios',
            'id': 'Acción'
        };
        this.table_options.sortable = ['name', 'description', 'documents'];
        this.table_options.filterable = ['name', 'description', 'documents'];
        this.table_options.columnsClasses = {
            'name': 'col-xs-2',
            'description': 'col-xs-4',
            'documents': 'col-xs-4',
            'id': 'col-xs-1'
        };
    },
    mounted() {
        //
    }
};
</script>
