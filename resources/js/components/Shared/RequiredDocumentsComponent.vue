<template>
    <div class="text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="#" :title=" title ? title : 'Registros de documentos a solicitar'"
            data-toggle="tooltip" v-has-tooltip
            @click="addRecord('add_required_doc', '/required-documents/' + model + '/' + module, $event)">
            <i class="icofont icofont-copy-alt ico-3x"></i>
            <span>{{ short_name_component ? short_name_component : 'Documentos Requeridos' }}</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_required_doc">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-copy-alt inline-block"></i>
                            {{ name_component ? name_component : 'Documentos Requeridos' }}
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <ul>
                                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        type="text"
                                        placeholder="Nombre del documento requerido"
                                        data-toggle="tooltip"
                                        v-model="record.name"
                                        title="Indique el nombre del documento a solicitar (requerido)"
                                        class="form-control input-sm"
                                    >
                                </div>
                                <div class="form-group" v-if="typedoc">
                                    <label>Tipo:</label>
                                    <select2
                                        :options="type_docs"
                                        v-model="record.type"
                                    ></select2>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <ckeditor
                                        :editor="ckeditor.editor"
                                        data-toggle="tooltip"
                                        title="Indique la descripción para el documento a solicitar"
                                        :config="ckeditor.editorConfig"
                                        class="form-control"
                                        tag-name="textarea"
                                        rows="3"
                                        v-model="record.description"
                                        placeholder="Descripción del documento a solicitar"
                                    ></ckeditor>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                data-dismiss="modal"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                            >
                                Cerrar
                            </button>
                            <button
                                type="button"
                                @click="reset()"
                                class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                            >
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('required-documents/' + model + '/' + module)"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="description" slot-scope="props">
                                <p v-html="props.row.description"></p>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, '/required-documents/' + model + '/' + module)"
                                        class="btn btn-danger btn-xs btn-icon"
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
                    description: '',
                    type: '',
                },
                errors: [],
                records: [],
                type_docs: [],
                columns: ['name', 'description', 'type', 'id'],
            }
        },
        props: ['module', 'model', 'title', 'name_component', 'short_name_component', 'typedoc'],

        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    name: '',
                    description: '',
                    type: '',
                };
            },
        },
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'description': 'Descripción',
                'type': 'Tipo',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name', 'description', 'type'];
            this.table_options.filterable = ['name', 'description', 'type'];
            this.table_options.columnsClasses = {
                'name': 'col-md-3',
                'description': 'col-md-5',
                'type': 'col-md-3',
                'id': 'col-md-2'
            };
        },

        mounted() {
            const vm = this;
            vm.type_docs = [
                { "id": "", "text": "Seleccione..." },
                { "id": "Proveedor", "text": "Proveedor" },
                { "id": "Compra", "text": "Compra" },
            ];
        }
    };
</script>
