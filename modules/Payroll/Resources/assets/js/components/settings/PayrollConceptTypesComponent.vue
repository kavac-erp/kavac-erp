<template>
    <section id="payrollConceptTypesFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de tipos de concepto" data-toggle="tooltip"
           @click="addRecord('add_payroll_concept_type', 'payroll/concept-types', $event)">
           <i class="icofont icofont-mathematical-alt-1 ico-3x"></i>
           <span>Tipos de<br>Concepto</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_concept_type">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-mathematical-alt-1 ico-3x"></i>
                            Tipo de Concepto
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- mensajes de error -->
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
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <!-- ./mensajes de error -->
                        <div class="row">
                            <div class="col-md-6">
                                <!-- nombre -->
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input type="text" id="name" placeholder="Nombre"
                                           class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
                                           v-is-text
                                           title="Indique el nombre del tipo de concepto (requerido)">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                                <!-- ./nombre -->
                                <!-- signo -->
                                <div class="form-group is-required">
                                    <label for="sign">Signo:</label>
                                    <select2 :options="signs"
                                             v-model="record.sign"></select2>
                                </div>
                                <!-- ./signo -->
                            </div>
                            <div class="col-md-6">
                                <!-- descripción -->
                                <div class="form-group">
                                    <label for="description">Descripción:</label>
                                    <ckeditor :editor="ckeditor.editor" id="description" data-toggle="tooltip"
                                              title="Indique la descripción del tipo de concepto"
                                              :config="ckeditor.editorConfig" class="form-control"
                                              name="description" tag-name="textarea"
                                              v-model="record.description"></ckeditor>
                                </div>
                                <!-- ./descripción -->
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
							<button type="button" @click="createRecord('payroll/concept-types')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="description" slot-scope="props">
                                <span v-html="props.row.description"></span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/concept-types')"
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
    </section>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id:          '',
                    name:        '',
                    description: '',
                    sign:        ''
                },
                errors:  [],
                records: [],
                columns: ['name', 'description', 'sign', 'id'],
                signs:   [
                    {"id": "",  "text":  "Seleccione..."},
                    {"id": "+",  "text": "+"},
                    {"id": "-",  "text": "-"},
                    {"id": "NA", "text": "No aplica"}
                ]
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             */
            reset() {
                const vm  = this;
                vm.record = {
                    id:          '',
                    name:        '',
                    description: '',
                    sign:        ''
                };
            },
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'name':        'Nombre',
                'description': 'Descripción',
                'sign':        'Signo',
                'id':          'Acción'
            };
            vm.table_options.sortable       = ['name', 'description', 'sign'];
            vm.table_options.filterable     = ['name', 'description', 'sign'];
            vm.table_options.columnsClasses = {
                'name':        'col-xs-4',
                'description': 'col-xs-4',
                'sign':        'col-xs-2',
                'id':          'col-xs-2'
            };
        },
    };
</script>
