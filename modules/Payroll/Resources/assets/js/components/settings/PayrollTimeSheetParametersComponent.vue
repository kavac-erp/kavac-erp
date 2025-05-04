<template>
    <section id="payrollTimeSheetParameterFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de parámetros de la hoja de tiempo" data-toggle="tooltip"
           @click="addRecord('add_payroll_time_sheet_parameters', 'payroll/time-sheet-parameters', $event)">
           <i class="icofont icofont-abacus-alt ico-3x"></i>
           <span>Parámetros de<br>Hoja de Tiempo</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_time_sheet_parameters">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-abacus-alt ico-3x"></i>
                            Parámetros de la hoja de tiempo
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
                                <!-- Código -->
                                <div class="form-group is-required">
                                    <label for="parameter_code">Código:</label>
                                    <input type="text" id="parameter_code" placeholder="Código"
                                           class="form-control input-sm" v-model="record.code" data-toggle="tooltip"
                                           title="Indique el Código del parámetro (requerido)">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                                <!-- ./Código -->
                            </div>
                            <div class="col-md-6">
                                <!-- Nombre -->
                                <div class="form-group is-required">
                                    <label for="parameter_name">Nombre:</label>
                                    <input type="text" id="parameter_name" placeholder="nombre"
                                           v-is-text class="form-control input-sm" v-model="record.name"
                                           data-toggle="tooltip" title="Indique el nombre del parámetro (requerido)">
                                </div>
                                <!-- ./Nombre -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Descripción -->
                                <div class="form-group">
                                    <label for="parameter_description">Descripción:</label>
                                    <ckeditor
                                        id="description"
                                        class="form-control"
                                        data-toggle="tooltip"
                                        name="description"
                                        tag-name="textarea"
                                        title="Indique la descripción del parámetro"
                                        :config="ckeditor.editorConfig"
                                        :editor="ckeditor.editor"
                                        v-model="record.description"
                                    ></ckeditor>
                                </div>
                                <!-- ./Descripción -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Parámetros</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique los parámetros a utilizar en la hoja de tiempo"
                                        track_by="text"
                                        :hide_selected="false"
                                        :options="time_parameters"
                                        :group_values="'group'"
                                        :group_label="'label'"
                                        :group_select="true"
                                        v-model="record.time_parameters"
                                    >
                                    </v-multiselect>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de nómina</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique los tipos de pago en los que aplican estos parámetros"
                                        track_by="text"
                                        :hide_selected="false"
                                        :options="payroll_payment_types"
                                        v-model="record.payment_types"
                                    >
                                    </v-multiselect>
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
							<button type="button" @click="createRecord('payroll/time-sheet-parameters')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="time_parameters" slot-scope="props">
                                <div v-for="param in props.row.payroll_parameter_time_sheet_parameters" :key="param.parameter_id">
                                    <span>
                                        {{
                                            JSON.parse(param.parameter.p_value).acronym + ' - ' +
                                            JSON.parse(param.parameter.p_value).name
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div slot="payment_types" slot-scope="props">
                                <div v-for="p_type in props.row.payroll_payment_type_time_sheet_parameters" :key="p_type.payroll_payment_type_id">
                                    <span>
                                        {{
                                            p_type.payroll_payment_type.code + ' - ' +
                                            p_type.payroll_payment_type.name
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/time-sheet-parameters')"
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
                    id: '',
                    code: '',
                    name: '',
                    description: '',
                    time_parameters: [],
                    payment_types: []
                },
                errors:  [],
                records: [],
                time_parameters: [],
                payroll_payment_types: [],
                columns: ['code', 'name', 'time_parameters', 'payment_types', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                const vm  = this;
                vm.record = {
                    id: '',
                    code: '',
                    name: '',
                    description: '',
                    time_parameters: [],
                    payment_types: []
                };
            },

            /**
             * Obtiene los datos de los trabajadores registrados agrupados por departamento
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getPayrollTimeParameters() {
                this.time_parameters = [];
                await axios.get(`${window.app_url}/payroll/get-time-parameters?setting=true`).then(response => {
                    this.time_parameters = Object.values(response.data);
                });
            },

            /**
             * Método que carga el formulario con los datos a modificar
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} index Identificador del registro a ser modificado
             * @param {object} event   Objeto que gestiona los eventos
             */
            async initUpdate(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;
                event.preventDefault();
                vm.record.time_parameters = [];
                vm.record.payment_types = [];

                for (const value of recordEdit.payroll_parameter_time_sheet_parameters) {
                    let pValue = JSON.parse(value.parameter.p_value);
                    vm.record.time_parameters.push({
                        id: value.parameter_id,
                        text: pValue.acronym + ' - ' + pValue.name
                    })
                }

                for (const value of recordEdit.payroll_payment_type_time_sheet_parameters) {
                    let pType = value.payroll_payment_type;
                    vm.record.payment_types.push({
                        id: value.payroll_payment_type_id,
                        text: pType.code + ' - ' + pType.name
                    })
                }
            },
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'code': 'Código',
                'name': 'Nombre',
                'time_parameters': 'Parámetros',
                'payment_types': 'Tipos de pago',
                'id': 'Acción'
            };
            vm.table_options.sortable       = ['code', 'name', 'time_parameters', 'payment_types'];
            vm.table_options.filterable     = ['code', 'name', 'time_parameters', 'payment_types'];
            vm.table_options.columnsClasses = {
                'code': 'col-xs-2',
                'name': 'col-xs-2',
                'time_parameters': 'col-xs-3',
                'payment_types': 'col-xs-3',
                'id': 'col-xs-2'
            };
        },
        mounted() {
            const vm = this;
            $("#add_payroll_time_sheet_parameters").on('show.bs.modal', function() {
                vm.getPayrollPaymentTypes();
                vm.getPayrollTimeParameters();
            });
        },
    };
</script>
