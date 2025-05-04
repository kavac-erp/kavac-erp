<template>
    <section id="payrollParametersFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de parámetros" data-toggle="tooltip"
           @click="addRecord('add_payroll_parameter', 'payroll/parameters', $event)">
           <i class="icofont icofont-globe ico-3x"></i>
           <span>Parámetros<br>Globales</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_parameter">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-globe ico-3x"></i>
                            Parámetro Global de Nómina
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

                        <!-- nombre, tipo de parámetro y descripción -->
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <!-- nombre -->
                                <div class="form-group is-required">
                                    <label for="name">Nombre</label>
                                    <input type="text" id="name" placeholder="Nombre"
                                           v-is-text @input="normalizeParameterText($event.target.value, 'name')"
                                           class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
                                           title="Indique el nombre del parámetro (requerido)">
                                    <input id="id" type="hidden" name="id" v-model="record.id">
                                </div>
                                <!-- ./nombre -->

                                <!-- tipo de parámetro -->
                                <div class="form-group is-required">
                                    <label for="parameter_type">Tipo de parámetro</label>
                                    <select2 :options="parameter_types"
                                             @input="changeParameterType()"
                                             v-model="record.parameter_type"
                                    ></select2>
                                </div>
                                <!-- ./tipo de parámetro -->
                            </div>

                            <!-- descripción -->
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="description">Descripción</label>
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
                            </div>
                            <!-- ./descripción -->
                        </div>
                        <!-- ./nombre, tipo de parámetro y descripción -->


                        <!-- valor global -->
                        <div v-if="record.parameter_type == 'global_value'" class="row">
                            <!-- valor -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="value">Valor:</label>
                                    <input id="value" class="form-control input-sm" type="text"
                                           data-toggle="tooltip" placeholder="Valor"
                                           onfocus="this.select()"
                                           title="Indique el valor del parámetro (requerido)"
                                           v-model="record.value"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false'
                                           "
                                    />
                                </div>
                            </div>
                            <!-- ./valor -->

                            <!-- porcentaje -->
                            <div class="form-group col-md-6">
                                <label for="percentage">¿Porcentaje?</label>
                                <p-check class="d-block pretty p-switch p-fill p-bigger"
                                         color="success" off-color="text-gray" toggle
                                         data-toggle="tooltip"
                                         title="Indique si el valor indicado está expresado en porcentaje (requerido)"
                                         v-model="record.percentage">
                                    <label slot="off-label"></label>
                                </p-check>
                            </div>
                            <!-- ./porcentaje -->
                        </div>
                        <!-- ./valor global -->

                        <!-- variable procesada -->
                        <div v-show="record.parameter_type == 'processed_variable'" class="row">
                            <div class="col-md-6">
                                <!-- expediente, parámetro y registros -->
                                <div class="row">
                                    <div class="form-group col-6">
                                        <label for="worker_record">¿Expediente del Trabajador?</label>
                                        <p-radio class="d-block pretty p-switch p-fill p-bigger"
                                                 color="success" off-color="text-gray" toggle
                                                 data-toggle="tooltip"
                                                 title="Indique si desea utilizar una variable del expediente del Trabajador"
                                                 v-model="variable" value="worker_record">
                                            <label slot="off-label"></label>
                                        </p-radio>
                                    </div>

                                    <div class="form-group col-6">
                                        <label for="parameter">¿Parámetro?</label>
                                        <p-radio class="d-block pretty p-switch p-fill p-bigger"
                                                 color="success" off-color="text-gray" toggle
                                                 data-toggle="tooltip"
                                                 title="Indique si desea utilizar un parámetro previamente registrado"
                                                 v-model="variable" value="parameter">
                                            <label slot="off-label"></label>
                                        </p-radio>
                                    </div>
                                </div>
                                <!-- ./expediente, parámetro y registros -->

                                <!-- opciones -->
                                <div v-if="variable" class="form-group">
                                    <label for="register">Registro</label>
                                    <select2 :options="options"
                                             @input="getNameOption"
                                             v-model="variable_option"
                                    ></select2>
                                </div>
                                <!-- ./opciones -->
                            </div>


                            <!-- fórmula -->
                            <div class="col-md-6">
                                <formula-calc formulaInput="formula"
                                              :withAmountButton="variable_option_bool"
                                              :amountButtonText="variable_option_name"
                                              :amountButtonValue="variable_option_name"
                                              :requiredClass="true"
                                />
                            </div>
                            <!-- ./fórmula -->
                        </div>
                        <!-- ./variable procesada -->

                        <!-- variable reiniciable a cero -->
                        <div v-if="record.parameter_type == 'time_parameter'" class="row">
                            <!-- código -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="code">Código</label>
                                    <input type="text" id="name" placeholder="Código"
                                            class="form-control input-sm" v-model="record.code" data-toggle="tooltip"
                                            title="Indique el código del parámetro (requerido)"
                                            v-input-mask
                                            data-inputmask-regex="[0-9\s]*$">
                                </div>
                            </div>
                            <!-- ./código -->

                            <!-- activo -->
                            <div class="form-group col-md-3">
                                <label for="percentage">¿Activo?</label>
                                <p-check class="d-block pretty p-switch p-fill p-bigger"
                                         color="success" off-color="text-gray" toggle
                                         data-toggle="tooltip"
                                         title="Indique si el parámetro está activo (requerido)"
                                         v-model="record.active">
                                    <label slot="off-label"></label>
                                </p-check>
                            </div>
                            <!-- ./activo -->

                            <!-- listar en esquemas -->
                            <div class="form-group col-md-3">
                                <label for="percentage">¿Listar en esquemas?</label>
                                <p-check class="d-block pretty p-switch p-fill p-bigger"
                                         color="success" off-color="text-gray" toggle
                                         data-toggle="tooltip"
                                         title="Indique si el parámetro debe listarse en esquema de guardias (requerido)"
                                         v-model="record.list_in_schema">
                                    <label slot="off-label"></label>
                                </p-check>
                            </div>
                            <!-- ./listar en esquemas -->

                            <!-- acrónimo -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="acronym">Acrónimo</label>
                                    <input type="text" id="acronym" placeholder="Acrónimo"
                                            v-is-text @input="normalizeText($event.target.value, 'acronym')"
                                            class="form-control input-sm" v-model="record.acronym" data-toggle="tooltip"
                                            title="Indique el acrónimo del parámetro (requerido)">
                                </div>
                            </div>
                            <!-- ./acrónimo -->

                            <!-- valor máximo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="value_max">Valor máximo:</label>
                                    <input id="value_max" class="form-control input-sm" type="text"
                                           data-toggle="tooltip" placeholder="Valor máximo"
                                           onfocus="this.select()"
                                           title="Indique el valor máximo del parámetro"
                                           v-model="record.value_max"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': '0'
                                           "
                                    />
                                </div>
                            </div>
                            <!-- ./valor máximo -->

                            <!-- Categorías de hoja de tiempo -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="exception_type">Categorías de hoja de tiempo</label>
                                    <select2
                                        :options="exception_types"
                                        v-model="record.exception_type">
                                    </select2>
                                </div>
                            </div>
                            <!-- ./Categorías de hoja de tiempo -->
                        </div>
                        <!-- ./variable reiniciable a cero -->
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
							<button type="button" @click="createRecord('payroll/parameters')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table
                            :columns="columns"
                            :data="records.filter(el => el.name != 'Numero de lunes del mes')"
                            :options="table_options"
                        >
                            <div slot="description" slot-scope="props" v-html="props.row.description"></div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/parameters')"
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
    // Se importó el componente para que funcione bien
    import Formula from '../../../../../../../resources/js/components/Shared/FormulaCalculatorComponent.vue';

    export default {
        components: { 'formula-calc': Formula },
        data() {
            return {
                record: {
                    id:             '',
                    name:           '',
                    description:    '',
                    parameter_type: '',
                    percentage:     false,
                    value:          '',
                    formula:        '',
                },
                formula:              '',
                variable:             '',
                variable_option:      '',
                variable_option_name: '',
                variable_option_bool: false,

                type:                      '',
                value:                     '',
                operator:                  '',
                operators:                 [
                    {"id": "",   "text": "Ninguno"},
                    {"id": "==", "text": "Igualdad (==)"},
                    {"id": "!=", "text": "Desigualdad (!=)"},
                    {"id": ">",  "text": "Mayor estricto (>)"},
                    {"id": "<",  "text": "Menor estricto (<)"},
                    {"id": ">=", "text": "Mayor o igual (>=)"},
                    {"id": "<=", "text": "Menor o igual (<=)"}
                ],
                subOptions:      [],
                errors:          [],
                records:         [],
                columns:         ['parameter_type_value', 'code', 'acronym', 'name', 'description', 'id'],
                options:         [],
                parameter_types: [],
                exception_types: []
            }
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'parameter_type_value': 'Tipo de parámetro',
                'code':           'Código',
                'acronym':        'Acrónimo',
                'name':           'Nombre',
                'description':    'Descripción',
                'id':             'Acción'
            };
            vm.table_options.sortable       = ['parameter_type_value', 'code', 'acronym', 'name', 'description'];
            vm.table_options.filterable     = ['parameter_type_value', 'code', 'acronym', 'name', 'description'];
            vm.table_options.columnsClasses = {
                'parameter_type_value': 'col-xs-2',
                'code':           'col-xs-1',
                'acronym':        'col-xs-1',
                'name':           'col-xs-2',
                'description':    'col-xs-4',
                'id':             'col-xs-2'
            };
        },
        mounted() {
            const vm = this;
            $("#add_payroll_parameter").on('show.bs.modal', function() {
                vm.reset();
                vm.getPayrollParameterTypes();
                vm.getPayrollExceptionTypes();
                vm.getOptions('payroll/get-associated-records');
            });
        },
        watch: {
            /**
             * Método que supervisa los cambios en el campo variable y actualiza el listado de opciones
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             */
            variable: function(variable) {
                const vm = this;
                vm.operator = vm.value = '';
                if (vm.variable == 'parameter') {
                    vm.getOptions('payroll/get-parameters');
                } else if (vm.variable == 'worker_record') {
                    vm.getOptions('payroll/get-associated-records');
                }
            },

            /**
             * Método que supervisa los cambios en el campo type y actualiza el listado de opciones
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             */
            type: function(type) {
                const vm = this;
                if (vm.type == 'list') {
                    axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`).then(response => {
                        vm.subOptions = response.data;
                    });
                } else if (vm.type == 'boolean') {
                    vm.value = false;
                }
            }
        },
        computed: {
            /**
             * Método que actualiza el nombre de la variable a emplear en el cálculo
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             * @return    {string}
             */
            updateNameVariable: function() {
                const vm = this;
                var response = '';
                if (vm.variable_option != '') {
                    vm.options.forEach(function(value, index) {
                        if (value.id == vm.variable_option) {
                            response = value.text;
                        } else if (typeof value.children !== 'undefined') {
                            value.children.forEach(function(value, index) {
                                if (value.id == vm.variable_option) {
                                    response = value.text;
                                }
                            });
                        }
                    });
                }
                return response;
            },
        },
        methods: {
            initUpdate(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit =  JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;
                vm.formula = recordEdit.translate_formula ?? vm.record.formula;

                event.preventDefault();
            },
            /**
             * Método que borra todos los datos del formulario
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            reset() {
                const vm    = this;
                vm.errors   = [];
                vm.record   = {
                    id:             '',
                    name:           '',
                    description:    '',
                    parameter_type: '',
                    percentage:     false,
                    value:          '',
                    formula:        '',
                };
                vm.variable = '';
                vm.formula = '';
                vm.variable_option_name = '';
                vm.variable_option_bool = false;
            },
            /**
             * Método que borra los campos comunes del formulario
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            changeParameterType() {
                const vm = this;

                if (vm.record.parameter_type == 'processed_variable') {
                    vm.variable = '';
                    vm.record.list_in_schema = '';
                } else if (vm.record.parameter_type == 'global_value') {
                    vm.variable       = '';
                    vm.record.formula = '';
                    vm.record.list_in_schema = '';
                } else if (vm.record.parameter_type == 'resettable_variable') {
                    vm.variable          = '';
                    vm.record.formula    = '';
                    vm.record.percentage = false;
                    vm.record.list_in_schema = '';
                }

                if (vm.record.parameter_type == 'time_parameter' && !vm.record.id) {
                    vm.record.list_in_schema = true;
                }
            },
            /**
             * Método que obtiene un arreglo con los tipos de parámetros
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            getPayrollParameterTypes() {
                const vm = this;
                vm.parameter_types = [];
                axios.get(`${window.app_url}/payroll/get-parameter-types`).then(response => {
                    vm.parameter_types = response.data;
                });
            },
            /**
             * Método que obtiene un arreglo con las opciones a listar
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            getOptions(url) {
                const vm = this;
                vm.options = [];
                url = vm.setUrl(url);
                axios.get(url).then(response => {
                    vm.options = response.data;
                });
            },
            /**
             * Método que obtiene el acrónimo de la variable a emplear en el cálculo
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             * @return    {string}
             */
            getCodeVariable() {
                const vm = this;
                let response = '';
                let showFormula = '';
                if ((vm.variable != 'worker_record') ||
                    ((vm.operator != '') && (vm.value != '')) ||
                    ((vm.operator != '') && (vm.type == 'boolean')) ||
                    ((vm.operator == '') && (vm.type == 'number')))
                {
                    if (vm.variable_option != '') {
                        $.each(vm.options, function(index, field) {
                            if (field['id'] == vm.variable_option) {
                                if (typeof field['id'] !== 'undefined') {
                                    response = vm.variable + '(' + field['id'] + ')';
                                    showFormula = field['text'];
                                } else if (typeof field['id'] !== 'undefined') {
                                    response = 'if(' + field['id'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                    showFormula = 'Si(' + field['text'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                }
                            } else if (typeof field['children'] !== 'undefined') {
                                $.each(field['children'], function(index, field) {
                                    if (typeof field['id'] !== 'undefined') {
                                        if (field['id'] == vm.variable_option) {
                                            if (vm.operator == '') {
                                                response = field['id'];
                                                showFormula = field['text'];
                                            } else {
                                                response = 'if(' + field['id'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                                showFormula = 'Si(' + field['text'] + ' ' + vm.operator + ' ' + vm.value + '){}';
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    }
                    if (response != '') {
                        if (vm.record.formula != '') {
                            let keys = vm.record.formula.indexOf('}');
                            if (keys > 0) {
                                let firstFormula = vm.record.formula.substr(0, keys);
                                let lastFormula = vm.record.formula.substr(keys, vm.record.formula.length);
                                vm.record.formula = firstFormula + response + lastFormula;
                            } else {
                                vm.record.formula += response;
                            }
                        } else {
                            vm.record.formula += response;
                        }
                    }
                    if (showFormula != '') {
                        if (vm.formula != '') {
                            let keys = vm.formula.indexOf('}');
                            if (keys > 0) {
                                let firstFormula = vm.formula.substr(0, keys);
                                let lastFormula = vm.formula.substr(keys, vm.formula.length);
                                vm.formula = firstFormula + showFormula + lastFormula;
                            } else {
                                vm.formula += showFormula;
                            }
                        } else {
                            vm.formula += showFormula;
                        }
                    }
                }
            },
            getOptionType() {
                const vm = this;
                //vm.type = '';
                if (vm.variable_option != '') {
                    $.each(vm.options, function(index, field) {
                        if (field['id'] == vm.variable_option) {
                            if (vm.type == field['type']) {
                                axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`)
                                .then(response => {
                                    vm.subOptions = response.data;
                                });
                            }
                            if (typeof field['type'] !== 'undefined') {
                                vm.type = field['type'];
                                return;
                            }
                        } else if (typeof field['children'] !== 'undefined') {
                            $.each(field['children'], function(index, field) {
                                if (field['id'] == vm.variable_option) {
                                    if (vm.type == field['type']) {
                                        axios.get(`${window.app_url}/payroll/get-parameter-options/${vm.variable_option}`)
                                        .then(response => {
                                            vm.subOptions = response.data;
                                        });
                                    }
                                    if (typeof field['type'] !== 'undefined') {
                                        vm.type = field['type'];
                                        return;
                                    }
                                }
                            });
                        }
                    });
                }
            },
            /**
             * Método para asignar el nombre de la variable en uso a la calculadora
             *
             * @author    Angelo Osorio <adosorio@cenditel.gob.ve>
             */
            getNameOption(){
                const vm = this;
                if (vm.variable_option != '') {
                    if (vm.variable == "parameter"){
                        let filter = vm.options.find(({ id }) => id == vm.variable_option)
                        vm.variable_option_name = filter.text
                        vm.variable_option_bool = true
                    }
                    if (vm.variable == "worker_record"){
                        vm.options.forEach( element => {
                            if (element.children) {
                                let filter = element.children.find(({ id }) => id == vm.variable_option)
                                if (filter && filter.text && filter.text !== undefined){
                                    vm.variable_option_name = filter.text
                                    vm.variable_option_bool = true
                                }
                            }
                        });
                    }
                }
            },
            /**
             * Método que obtiene el estado de la propiedad is-invalid para elementos del formulario
             *
             * @method    isInvalid
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             *
             * @param     {string}    elName    Nombre del elemento a buscar
             * @param     {string}    model     Nombre del modelo donde buscar
             */
            isInvalid(elName, model = 'record') {
                const vm = this;

                if (typeof vm[model][elName] != 'undefined') {
                    let keys = vm[model][elName].indexOf('/0');
                    return (keys > 0) ? 'is-invalid': '';
                } else {
                    return '';
                }
            },
        }
    };
</script>
