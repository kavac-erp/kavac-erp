<template>
    <section id="PayrollFormComponent">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Generar Nómina </h6>
                <div class="card-btns">
                    <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
                       title="Ir atrás" data-toggle="tooltip">
                        <i class="fa fa-reply"></i>
                    </a>
                    <a href="#" class="card-minimize btn btn-card-action btn-round" title="Minimizar"
                       data-toggle="tooltip">
                        <i class="now-ui-icons arrows-1_minimal-up"></i>
                    </a>
                </div>
            </div>

            <div class="card-body">
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
                        <!-- fecha de generación -->
                        <div class="form-group is-required">
                            <label>Fecha de generación:</label>
                            <input type="date"
                                   data-toggle="tooltip"
                                   title="Fecha de generación del registro de nómina"
                                   class="form-control input-sm" v-model="record.created_at">
                        </div>
                        <!-- ./fecha de generación -->
                    </div>
                    <div class="col-md-6">
                        <!-- nombre -->
                        <div class="form-group is-required">
                            <label>Nombre:</label>
                            <input type="text" placeholder="Nombre del registro de nómina"
                                   data-toggle="tooltip"
                                   title="Indique el nombre del registro de nómina (requerido)"
                                   class="form-control input-sm" v-model="record.name">
                            <input type="hidden" v-model="record.id">
                        </div>
                        <!-- ./nombre -->
                    </div>
                    <div class="col-md-6">
                        <!-- tipo de pago de nómina -->
                        <div class="form-group is-required">
                            <label>Tipo de pago de nómina:</label>
                            <select2 :options="payroll_payment_types"
                                     @input="getPayrollPaymentPeriod()"
                                     :disabled="record.id !== '' "
                                     v-model="record.payroll_payment_type_id"></select2>
                        </div>
                        <!-- ./tipo de pago de nómina -->
                    </div>
                    <div class="col-md-6">
                        <!-- período de pago -->
                        <div class="form-group is-required">
                            <label>Período de pago: </label>
                            <select2 id="paymentPeriod_select"
                                     :options="payroll_payment_periods" disabled
                                     v-model="record.payroll_payment_period_id"></select2>
                        </div>
                        <!-- ./período de pago -->
                    </div>
                    <div class="col-md-6">
                        <!-- conceptos -->
                        <div class="form-group is-required">
                            <label>Conceptos:</label>
                            <v-multiselect id="payroll_concepts"
                                           :options="payroll_concepts" track_by="text"
                                           :hide_selected="false"
                                           @input="getPayrollParameters()"
                                           v-model="record.payroll_concepts">
                            </v-multiselect>
                        </div>
                        <!-- ./conceptos -->
                    </div>
                    <div class="col-md-6" v-if="pending_concepts.length > 0">
                        <!-- conceptos -->
                        <div class="form-group">
                            <label>Conceptos de hoja de tiempo pendiente:</label>
                            <v-multiselect id="payroll_concepts"
                                           :options="payroll_concepts" track_by="text"
                                           :hide_selected="false" disabled
                                           v-model="pending_concepts">
                            </v-multiselect>
                        </div>
                        <!-- ./conceptos -->
                    </div>
                </div>
                <section v-show="record.payroll_payment_type_id > 0">
                    <hr>
                    <div class="row">
                        <div class="col-md-12" v-if="payroll_parameters.length > 0">
                            <h6 class="card-title"> Parámetros de nómina </h6>
                            <div class="row" style="margin: 1px 0">
                                <div class="col-md-12" :key="concept['id']"
                                    v-for="concept in payroll_parameters">
                                    <div class="form-group">
                                        <h6 class="card-title"> {{ concept['name'] }} </h6>
                                        <div class="row" style="margin: 1px 0">
                                            <div class="col-md-6" :key="staff['id']"
                                                v-for="staff in concept['staffs']">
                                                <div class="form-group">
                                                    <strong>{{ staff['name'] }}</strong>
                                                    <div class="row" style="margin: 1px 0">
                                                        <div class="col-md-12" :key="parameter['id']"
                                                            v-for="parameter in concept['parameters']">
                                                            <div class="form-group is-required">
                                                                <label>{{ parameter['name'] }}:</label>
                                                                <input :id="concept['id'] + '_parameter_' + parameter['id'] + '_' + staff['id']"
                                                                    class="form-control input-sm"
                                                                    type="text"
                                                                    data-toggle="tooltip"
                                                                    :disabled="parameter['name'] == 'Numero de lunes del mes'"
                                                                    :value="number_of_days_monday"
                                                                    v-if="parameter['name'] == 'Numero de lunes del mes'">

                                                                <input :id="concept['id'] + '_parameter_' + parameter['id'] + '_' + staff['id']"
                                                                    class="form-control input-sm"
                                                                    type="text"
                                                                    data-toggle="tooltip"
                                                                    :disabled="('object' !== typeof(parameter['value'])) ? parameter['value'] != '' : false"
                                                                    :value="('object' === typeof(parameter['value'])) ? parameter['value'][staff['id']] : parameter['value']"
                                                                    v-input-mask
                                                                    data-inputmask="'alias': 'numeric', 'allowMinus': 'false', 'rightAlign': 'false'"
                                                                    v-else-if="parameter['value']">

                                                                <input v-else :id="concept['id'] + '_parameter_' + parameter['id'] + '_' + staff['id']"
                                                                    type="text" data-toggle="tooltip"
                                                                    :title="'Indique el parámetro '+ parameter['name'] + ' de ' + staff['name'] + ' (requerido)'"
                                                                    class="form-control input-sm"
                                                                    v-input-mask
                                                                    data-inputmask="'alias': 'numeric', 'allowMinus': 'false'"
                                                                    >
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h6 class="card-title"> Información general </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Cantidad de días lunes de mes:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="number_of_days_monday"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Fecha de primer lunes de mes:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="first_monday"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Inicio de mes:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="start_month"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Día de semana de inicio de mes:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" id="start_month_day"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="card-footer text-right">
                <button type="button" @click="reset"
                        class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                        title="Borrar datos del formulario">
                    <i class="fa fa-eraser"></i>
                </button>
                <button type="button" @click="redirect_back(route_list)"
                        class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        title="Cancelar y regresar">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="createForm('payroll/registers')"
                        class="btn btn-success btn-icon btn-round">
                    <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id:                        '',
                    name:                      '',
                    payroll_payment_type_id:   '',
                    payroll_payment_period_id: '',
                    payroll_concepts:          [],
                    payroll_parameters:        []
                },
                number_of_days_monday:         '',
                userPermission:                '',
                pending_concepts:              [],
                payroll_assigned_periods:      [],
                payroll_payment_types:         [],
                payroll_payment_periods:       [],
                payroll_concepts:              [],
                payroll_parameters:            [],
                payroll_parameters_resettable: [],
                staff_concept_assign:          [],
                staff_concepts:                [],

                days: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'],
                errors:                  [],
                records:                 []
            }
        },
        props: {
            payroll_id: {
                type:     Number,
                required: false,
                default:  ''
            }
        },
        created() {
            const vm = this;
            vm.reset();
            vm.getPayrollConcepts();
            vm.getPayrollPaymentTypes();
            //vm.getPayrollParametersResettable();
        },
        mounted() {
            const vm = this;
            if (vm.payroll_id) {
                vm.showRecord(vm.payroll_id);
            }
        },
        watch: {
            /**
             * Método que supervisa los cambios en el objeto record y actualiza el período de pago seleccionado
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             */
            record: {
                deep: true,
                handler: function() {
                    const vm = this;
                    if (vm.record.payroll_payment_period_id == '') {
                        $.each(vm.payroll_payment_periods, function(index, field) {
                            if (
                                (field['payment_status'] == 'pending') &&
                                ('' === vm.record.payroll_payment_period_id) &&
                                (vm.payroll_id == field['payroll_id'])
                            ) {
                                vm.record.payroll_payment_period_id = field['id'];
                                $("#paymentPeriod_select").val(field['id']).select2();
                                /** Calcular las fechas de información general con moment */
                                vm.getGeneralInformation(field['text']);
                            }
                        });
                    }
                }
            }
        },
        methods: {
            /**
             * Método que permite borrar todos los datos del formulario
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            reset() {
                const vm  = this;
                vm.record = {
                    id:                        '',
                    name:                      '',
                    payroll_payment_type_id:   '',
                    payroll_payment_period_id: '',
                    payroll_concepts:          [],
                    payroll_parameters:        []
                };
                vm.pending_concepts = [];
                vm.payroll_assigned_periods = [];
                vm.number_of_days_monday = '';
                vm.record.created_at = '';
            },
            /**
             * Método que obtiene un arreglo con los periodos de pago asociados al tipo de pago
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            getPayrollPaymentPeriod() {
                const vm = this;
                vm.payroll_parameters               = [];
                vm.record.payroll_concepts          = [];
                vm.payroll_payment_periods          = [];
                vm.pending_concepts = [];
                vm.record.payroll_payment_period_id = '';

                if (vm.record.payroll_payment_type_id > 0) {
                    axios.get(
                        `${window.app_url}/payroll/get-payment-periods/${vm.record.payroll_payment_type_id}`
                    ).then(response => {
                        vm.payroll_payment_periods = response.data.records;
                        vm.record.payroll_concepts = response.data.concepts;
                        vm.pending_concepts = response.data.pending_concepts;
                        if (vm.payroll_id) {
                            $.each(vm.payroll_payment_periods, function(index, field) {
                                if (vm.record.payroll_payment_period) {
                                    if (vm.record.payroll_payment_period.id == field['id']) {
                                        vm.record.payroll_payment_period_id = field['id'];
                                        $("#paymentPeriod_select").val(field['id']).select2();
                                        vm.getGeneralInformation(field['text']);
                                    }
                                }
                            });
                        }
                    });
                }
            },
            /**
             * Método que obtiene un arreglo con los parámetros de nómina asociados a un concepto
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve>
             */
            async getPayrollParameters() {
                const vm = this;
                vm.payroll_parameters = [];
                if (vm.record.payroll_concepts.length > 0) {
                    vm.loading = true;
                    vm.payroll_parameters = [];
                    for (const concept of vm.record.payroll_concepts) {
                        await axios.get(`${window.app_url}/payroll/get-personal-concept-assign/${concept['id']}?payroll_id=${vm.record.id}`).then(response => {
                            if(typeof(response.data.record) !== 'undefined') {
                                if (!vm.payroll_parameters.some(elemento => elemento.id === response.data.record.id)) {
                                    vm.payroll_parameters.push(response.data.record);
                                }
                            }
                        });
                    };

                    if (vm.payroll_id) {
                        vm.getParametersInfo();
                    }
                };
                vm.loading = false;
            },
            async createForm(url) {
                const vm   = this;
                vm.errors  = [];
                vm.loading = true;
                let result = true;
                let payroll_parameters = [];
                vm.record.pending_concepts = vm.pending_concepts;

                for (const concept of vm.payroll_parameters) {
                    for (const parameter of concept['parameters']) {
                        for (const staff of concept['staffs']) {
                            let input = document.getElementById(concept['id'] + '_parameter_' + parameter['id'] + '_' + staff['id']);
                            payroll_parameters.push({
                                id:       parameter['id'],
                                name:     parameter['name'],
                                staff_id: staff['id'],
                                value:    input.value
                            });
                            if(input.value.trim() == '') {
                                bootbox.alert("Debe establecer todos los parámetros de nómina antes de continuar");
                                result = false;
                            };
                        };
                    };
                };
                /** @todo Validar si el periodo ya fue asignado en otro registro de nomina si no se esta editando un registro. */
                if(!vm.record.id && vm.record.payroll_payment_type_id !== '' && vm.record.payroll_payment_period_id !== '') {
                    await axios.get(`${window.app_url}/payroll/get-payroll-assigned-period/${vm.record.payroll_payment_period_id}/${vm.record.payroll_payment_type_id}`).then(response => {
                        if(response.data.assigned == true) {
                            bootbox.alert("El periodo de pago ya fue asignado a una registro de nomina, debe cerrar dicho periodo");
                            result = false;
                        }
                    });

                    if (result) {
                        vm.record.payroll_parameters = payroll_parameters;
                        setTimeout(function () {
                            vm.createRecord(url);
                        }, 2000);
                    }
                } else {
                    if (result) {
                        vm.record.payroll_parameters = payroll_parameters;
                        setTimeout(function () {
                            vm.createRecord(url);
                        }, 2000);
                    }
                }
            },
            getGeneralInformation(date) {
                const vm = this;
                let mondays = [];
                let number_of_days_monday = '';
                let monday = vm.start_day(date, "DD/MM/YYYY", 'month', 'Monday');
                let i = 0;
                let month_init = vm.start_day(date, "DD/MM/YYYY", 'month', i);
                while(month_init.date() != 1) {
                    i++;
                    month_init = vm.start_day(date, "DD/MM/YYYY", 'month', i);
                }
                if (monday.date() > 7) {
                    monday.add(7,'d');
                }
                let month = monday.month();
                while(month === monday.month()) {
                    mondays.push(monday.toString());
                    monday.add(7,'d');
                }
                vm.number_of_days_monday = mondays.length;
                document.getElementById('number_of_days_monday').innerText = mondays.length;
                document.getElementById('first_monday').innerText = mondays[0];
                document.getElementById('start_month').innerText = month_init.toString();
                document.getElementById('start_month_day').innerText = vm.days[i]
            },

            /**
             * Reescribe el método showRecord para cambiar su comportamiento por defecto
             * Método que muestra datos de un registro seleccionado
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param    {integer}    id    Identificador del registro a mostrar
             */
            showRecord(id) {
                const vm = this;
                axios.get(`${window.app_url}/payroll/registers/vue-info/${id}`).then(response => {
                    vm.record = response.data.record;
                    vm.record.created_at = vm.format_date(response.data.record.created_at, 'YYYY-MM-DD');
                    if (response.data.record.payroll_payment_period) {
                        vm.record.payroll_payment_type_id = response.data.record.payroll_payment_period.payroll_payment_type_id;
                    }
                })
            },

            /**
             * Método para cargar las variables reiniciables a cero al editar un registro
             *
             * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             *
             */
            getParametersInfo() {
                const vm = this;
                for (let field of vm.payroll_parameters) {
                    for (let parameter of vm.staff_concepts) {
                        if(field['id'] == parameter['parameter_id']) {
                            if(parameter['staffs'].length > 0) {
                                for (let [idx, staff] of parameter['staffs'].entries()) {
                                    for (let param of JSON.parse(vm.record.payroll_parameters)) {
                                        if (staff.id == param.staff_id && param.id == parameter['parameter_id']) {
                                            let input = document.getElementById(parameter['concept_id'] + '_parameter_' + field['id'] + '_' + idx);
                                            if (input && input.value == '') {
                                                input.value = param.value;
                                            }
                                        }
                                    }
                                }
                            } else {
                                let input = document.getElementById(parameter['concept_id'] + '_parameter_' + field['id']);
                                if (input && input.value == '') {
                                    input.value = JSON.parse(vm.record.payroll_parameters).value;
                                }
                            }
                        }
                    }
                }
            },

            /**
             * Método que obtiene un arreglo del personal asociado a las asignaciones de un concepto
             *
             * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
             */
            getPayrollPersonalConceptAssign(id) {
                const vm = this;
                vm.staff_concept_assign = [];
                axios.get(`${window.app_url}/payroll/get-personal-concept-assign/${id}`).then(response => {
                    vm.staff_concept_assign = response.data;
                });
            },

            /**
             * Método carga el listado de trabajadores asociados al parametro del concepto seleccionado
             *
             * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
             */
            getPayrollConceptParameter(parameter) {
                const vm = this;
                vm.staff_concept_assign = [];
                vm.staff_concepts = [];
                $.each(parameter, function(index, field) {
                    axios.get(`${window.app_url}/payroll/get-concept-parameter/${field['id']}`).then(response => {
                    })
                });
            },

            /**
             * Método que obtiene un arreglo con el nombre de los los parámetros reiniciables de nómina
             *
             * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
             */
            getPayrollParametersResettable() {
                const vm = this;
                vm.payroll_parameters_resettable = [];
                axios.get(`${window.app_url}/payroll/get-parameters-resettable`).then(response => {
                        vm.payroll_parameters_resettable = response.data;
                    });
            },

            /**
             * Método que obtiene un arreglo con el nombre de los los parámetros reiniciables de nómina
             *
             * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
             */
            getParametersResettable(payroll_parameters) {
                const vm = this;
                let parameters = [];
                $.each(payroll_parameters, function(index, field) {
                    //se busca el parametros en los reiniciables
                    let parameter = vm.payroll_parameters_resettable.find(({ name }) => name == field['name']);
                    if(typeof parameter !== "undefined") {
                        parameters.push({
                            'id': field['id'],
                            'name':  field['name'],
                            'value': field['value']
                        });
                    }
                });
                return parameters;
            },

            getPayrollAssignedPeriod(payment_period_id) {
                const vm = this;
                let assigned = false;
                if(payment_period_id && vm.payroll_assigned_periods) {
                    $.each(vm.payroll_assigned_periods, function(index, field) {
                        if(field['id'] == payment_period_id) {
                            assigned = true;
                        }
                    });
                }
                return assigned;
            }
        }
    };
</script>
