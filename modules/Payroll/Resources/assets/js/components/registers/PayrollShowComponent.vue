<template>
    <section id="payrollShowComponent">
        <div class="row">
            <!-- nombre del registro de nómina -->
            <div class="col-md-4">
                <div class="form-group">
                    <strong>Nombre:</strong>
                    <div class="row" style="margin: 1px 0">
                        <span class="col-md-12" id="code">
                            {{ record.name }}
                        </span>
                    </div>
                    <input type="hidden" id="id">
                </div>
            </div>
            <!-- ./nombre del registro de nómina -->
            <!-- parámetros de nómina -->
            <div class="col-md-12">
                <div class="form-group">
                    <strong>Parámetros empleados en la nómina:</strong>
                    <div class="row" style="margin: 1px 0">
                        <div class="col-md-4" :key="payroll_parameter['id']"
                             v-for="payroll_parameter in record.payroll_parameters">
                            <div class="form-group">
                                <strong>{{ payroll_parameter['name'] }}:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="value">
                                        {{ payroll_parameter['value'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./parámetros de nómina -->
        </div>
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="payroll_staff" slot-scope="props">
                {{
                    props.row.payroll_staff
                        ? props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name
                        : 'No definido'
                }}
            </div>
            <div slot="assignments" slot-scope="props">
                <span>
                    <div v-for="att in props.row.assignments" :key="att.id">
                        <div v-if="zeroConcept == 'true'">
                            <p><strong> Concepto: </strong> {{ att.name }} </p>
                            <p><strong> Valor: </strong> {{ numberFormat(att.value) }} </p>
                        </div>
                        <div v-else-if="att.value != 0 && zeroConcept == 'false'">
                            <p><strong> Concepto: </strong> {{ att.name }} </p>
                            <p><strong> Valor: </strong> {{ numberFormat(att.value) }} </p>
                        </div>
                    </div>
                </span>
            </div>
            <div slot="deductions" slot-scope="props">
                <span>
                    <div v-for="att in props.row.deductions" :key="att.id">
                        <div v-if="zeroConcept == 'true'">
                            <p><strong> Concepto: </strong> {{ att.name }} </p>
                            <p><strong> Valor: </strong> {{ numberFormat(att.value) }} </p>
                        </div>
                        <div v-else-if="att.value != 0 && zeroConcept == 'false'">
                            <p><strong> Concepto: </strong> {{ att.name }} </p>
                            <p><strong> Valor: </strong> {{ numberFormat(att.value) }} </p>
                        </div>
                    </div>
                </span>
            </div>
            <div slot="total" slot-scope="props">
                <span> {{ calculateTotal(props.row) }} </span>
            </div>
        </v-client-table>
    </section>
</template>
<script>
    export default {
        data() {
            return {
                record: {
                    id:                        '',
                    name:                      '',
                    payroll_payment_period_id: '',
                    payroll_parameters:        []
                },
                records: [],
                parameters: [],
                numberDecimals: '',
                round: '',
                zeroConcept: '',
                columns: ['payroll_staff', 'assignments', 'deductions', 'total'],
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
            vm.table_options.headings = {
                'payroll_staff': 'Trabajador',
                'assignments':   'Asignaciones',
                'deductions':    'Deducciones',
                'total':         'Total'
            };
            vm.table_options.sortable   = ['payroll_staff', 'assignments', 'deductions'];
            vm.table_options.filterable = ['payroll_staff', 'assignments', 'deductions'];
        },

        mounted() {
            const vm = this;
            vm.showRecord(vm.payroll_id);
            vm.getParameters();
        },
        methods: {
            reset() {
                //
            },
            /**
             * Reescribe el método showRecord para cambiar su comportamiento por defecto
             * Método que muestra datos de un registro seleccionado
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param     {integer}    id    Identificador del registro a mostrar
             */
            showRecord(id) {
                const vm = this;
                axios.get(`${window.app_url}/payroll/registers/vue-info/${id}`).then(response => {
                    $.each(response.data.record.payroll_staff_payrolls, function(index, field) {
                        vm.records.push({
                            'assignments':   JSON.parse(field['assignments']),
                            'deductions':    JSON.parse(field['deductions']),
                            'payroll_staff': field['payroll_staff']
                        });
                    });
                    vm.record  = {
                        id:                        response.data.record.id,
                        name:                      response.data.record.name,
                        payroll_payment_period_id: response.data.record.payroll_payment_period_id,
                        payroll_payment_period:    response.data.record.payroll_payment_period,
                        payroll_parameters:        JSON.parse(response.data.record.payroll_parameters)
                    };
                });
            },
            calculateTotal(fields) {
                const vm = this;
                let total = 0;
                $.each(fields.assignments, function(index, field) {
                    total += field.value;
                });
                $.each(fields.deductions, function(index, field) {
                    total -= field.value;
                });
                return vm.numberFormat(total);
            },

            /**
            * Función que da formato al número con o sin redondear y con un número de decimales especificado en la configuración de los parametros de reporte
            *
            * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
            */
            numberFormat(number) {
                const vm = this;
                if(number) {
                    if(vm.round == 'true') {
                        return number.toFixed(vm.numberDecimals);
                    }
                    else return vm.myRound(number,vm.numberDecimals);
                }
                return '';
            },

            /**
            * Función que trunca el numero sin redondear y con un número de decimales especificado en la configuración de los parametros de reporte
            *
            * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
            */
            myRound(num, dec) {
                var exp = Math.pow(10, dec || 2); // 2 decimales por defecto
                return parseInt(num * exp, 10) / exp;
            },

            /**
            * Obtiene los datos de los parametros configurados para el reporte
            *
            * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
            */
            async getParameters() {
                const vm = this;
                await axios.get(`${vm.app_url}/payroll/get-report-parameters`).then(response => {
                    vm.parameters = response.data.records;

                    for(var i=0; i<(vm.parameters).length; i++){
                        if(vm.parameters[i].p_key == 'number_decimals') {
                            vm.numberDecimals = vm.parameters[i].p_value;
                        }
                        if(vm.parameters[i].p_key == 'round') {
                            vm.round = vm.parameters[i].p_value;
                        }
                        if(vm.parameters[i].p_key == 'zero_concept') {
                            vm.zeroConcept = vm.parameters[i].p_value;
                        }
                    }
                }).catch(error => {
                    console.log(error);
                });
            },
        }
    };
</script>
