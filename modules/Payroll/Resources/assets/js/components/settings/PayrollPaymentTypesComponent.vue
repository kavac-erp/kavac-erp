<template>
    <section id="payrollPaymentTypesFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:;" title="Registros de tipos de nómina"
            data-toggle="tooltip" @click="addRecord('add_payroll_payment_type', 'payroll/payment-types', $event)">
            <i class="icofont icofont-law-document ico-3x"></i>
            <span>Tipos de<br>Nómina</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_payment_type" style="overflow-y: scroll;">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6 class="d-flex align-items-center">
                            <i class="icofont icofont-law-document ico-3x"></i>
                            Tipos de Nómina
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
                            <!-- código -->
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="code">Código:</label>
                                    <input type="text" id="code" placeholder="Código" data-toggle="tooltip"
                                        title="Indique el código del tipo de nómina (requerido)"
                                        class="form-control input-sm" v-model="record.code">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                            </div>
                            <!-- ./código -->
                            <!-- nombre -->
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input type="text" id="name" placeholder="Nombre" data-toggle="tooltip"
                                        title="Indique el nombre del tipo de nómina (requerido)" class="form-control input-sm"
                                        v-model="record.name">
                                </div>
                            </div>
                            <!-- ./nombre -->
                            <!-- orden de pago -->
                            <div class="col-md-2">
                                <div class=" form-group">
                                    <label>¿Recibos de pago?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                            title="¿Generar recibos de pago?">
                                            <input type="checkbox" class="custom-control-input" id="receiptOfpayment"
                                                v-model="record.receipt" :value="true" :disabled="record.skip_moments">
                                            <label class="custom-control-label" for="receiptOfpayment"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class=" form-group">
                                    <label>¿Orden de pago?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                            title="¿Generar orden de pago de forma automática?">
                                            <input type="checkbox" class="custom-control-input"
                                                @change="alertMessage(record.order)" id="paymentTypeOrder"
                                                v-model="record.order" :value="true" :disabled="record.skip_moments">
                                            <label class="custom-control-label" for="paymentTypeOrder"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ./orden de pago -->
                            <!-- omitir momentos presupuestarios -->
                            <div class="col-md-4">
                                <div class=" form-group">
                                    <label>¿Omitir momentos presupuestarios?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                            title="¿Omitir momentos presupuestarios?">
                                            <input type="checkbox" class="custom-control-input" id="skipMoments"
                                                v-model="record.skip_moments" :value="true" @change="updateCheckValues()">
                                            <label class="custom-control-label" for="skipMoments"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ./omitir momentos presupuestarios -->
                            <!-- periodicidad de pago -->
                            <div class="col-md-4">
                                <div class="form-group is-required" v-if="userPermission == false">
                                    <label>Periodicidad de pago</label>
                                    <select2
                                        :options="payment_periodicities"
                                        @input="initVars()"
                                        v-model="record.payment_periodicity"></select2>
                                </div>
                                <div class="form-group is-required" v-if="userPermission == true">
                                    <label>Periodicidad de pago</label>
                                    <select2 :options="payment_periodicities"
                                        @change="initVars()"
                                        v-model="record.payment_periodicity" disabled="true"></select2>
                                </div>

                            </div>
                            <!-- ./periodicidad de pago -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="periods">Períodos:</label>
                                    <input type="text" id="periods" placeholder="0" readonly data-toggle="tooltip"
                                        title="Número de períodos del tipo de nómina" class="form-control input-sm"
                                        v-model="record.periods_number">
                                </div>
                            </div>
                            <!-- fecha de inicio -->
                            <div class="col-md-4" v-if="record.payment_periodicity">
                                <div class="form-group is-required">
                                    <label>Fecha de inicio del primer período:</label>
                                    <input type="date" id="start_date" placeholder="Fecha de inicio" data-toggle="tooltip"
                                        title="Indique la fecha de inicio del primer período" class="form-control input-sm"
                                        @input="record.periods_number = ''; record.payroll_payment_periods = [];"
                                        :min="starOperationDate" v-model="record.start_date">
                                </div>
                            </div>
                            <!-- ./fecha de inicio -->
                            <!-- cuenta bancaria -->
                            <div v-if="finance" class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Cuenta bancaria:</label>
                                    <select2 :options="finance_bank_accounts" v-model="record.finance_bank_account_id">
                                    </select2>
                                </div>
                            </div>
                            <!-- ./cuenta bancaria -->
                            <div v-if="finance" class="col-md-4">
                                <div :class="['form-group', !moment_close_permission ? 'is-required' : '']">
                                    <label>Método de pago</label>
                                    <select2 :options="finance_payment_methods" v-model="record.finance_payment_method_id">
                                    </select2>
                                </div>
                            </div>
                            <div v-if="accounting" class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Categoría de cuenta contable</label>
                                    <select2 :options="accounting_entry_categories"
                                        v-model="record.accounting_entry_category_id">
                                    </select2>
                                </div>
                            </div>
                            <!-- conceptos -->
                            <div class="col-md-12">
                                <div class="form-group is-required">
                                    <label>Conceptos:</label>
                                    <v-multiselect :options="payroll_concepts" track_by="text" :hide_selected="false"
                                        v-model="record.payroll_concepts">
                                    </v-multiselect>
                                </div>
                            </div>
                            <!-- ./conceptos -->
                        </div>
                        <div class="row">
                            <div class="col-12 text-right" v-if="view_periods == true">
                                <button class="btn btn-primary btn-sm" type="button" @click="view_periods = false;"
                                    title="Ocultar períodos de nómina" data-toggle="tooltip">
                                    {{ record.id ? 'Ocultar nuevos períodos' : 'Ocultar períodos' }}
                                </button>
                            </div>
                            <div class="col-12 text-right" v-else-if="record.payment_periodicity
                                && record.start_date && paymentData">
                                <button class="btn btn-primary btn-sm" type="button" @click="view_periods = true;"
                                    title="Ver períodos de nómina" data-toggle="tooltip">
                                    {{ record.id ? 'Ver nuevos períodos' : 'Ver períodos' }}
                                </button>
                            </div>
                            <!-- Tabla con los periodos actuales -->
                            <div class="col-12 modal-body modal-table" v-if="record.id != ''">
                                <h6>Períodos Actuales:</h6>
                                <v-client-table :columns="periods_columns" :data="savedPeriods" :options="table_options">
                                    <div slot="start_date" slot-scope="props" class="text-center">
                                        <span>
                                            {{ props.row.start_date.includes("-") ? format_date(props.row.start_date) :
                                                props.row.start_date }}
                                        </span>
                                    </div>
                                    <div slot="end_date" slot-scope="props" class="text-center">
                                        <span>
                                            {{ props.row.end_date.includes("-") ? format_date(props.row.end_date) :
                                                props.row.end_date }}
                                        </span>
                                    </div>
                                    <div slot="payment_status" slot-scope="props" class="text-center">
                                        <span>
                                            {{
                                                (props.row.payment_status == 'pending')
                                                ? (props.row.in_payroll ? 'Pendiente - Nómina' : 'Pendiente')
                                                : 'Generado'
                                            }}
                                        </span>
                                    </div>
                                </v-client-table>
                            </div>
                            <!--/ Tabla con los periodos actuales -->

                            <div class="col-12 modal-body modal-table" v-if="view_periods">
                                <h6>{{ record.id ? 'nuevos períodos' : 'períodos' }}</h6>
                                <v-client-table :columns="periods_columns.filter(item => item !== 'payment_status')"
                                    :data="record.payroll_payment_periods" :options="table_options">
                                    <div slot="start_date" slot-scope="props" class="text-center">
                                        <span>
                                            {{ props.row.start_date.includes("-") ? format_date(props.row.start_date) :
                                                props.row.start_date }}
                                        </span>
                                    </div>
                                    <div slot="end_date" slot-scope="props" class="text-center">
                                        <span>
                                            {{ props.row.end_date.includes("-") ? format_date(props.row.end_date) :
                                                props.row.end_date }}
                                        </span>
                                    </div>
                                </v-client-table>
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
                            <button type="button" @click="createRecord('payroll/payment-types')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="payment_periodicity" slot-scope="props" class="text-center">
                                <span> {{ getPaymentPeriodicity(props.row.payment_periodicity) }} </span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
                                    data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/payment-types')"
                                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro"
                                    data-toggle="tooltip" type="button">
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
                order: false,
                receipt: false,
                individual: false,
                skip_moments: false,
                payment_periodicity: '',
                periods_number: '',
                start_date: '',
                finance_bank_account_id: '',
                finance_payment_method_id: '',
                accounting_account_id: '',
                accounting_entry_category_id: '',
                payroll_concepts: [],
                payroll_payment_periods: []

            },
            starOperationDate: this.start_operations_date,
            savedPeriods: [],
            savedpaymentData: { 'payment_periodicity': '', 'start_date': '' },
            userPermission: '',
            paymentPeriodsLength: '',
            payroll_payment_periods: [],
            view_periods: false,
            errors: [],
            records: [],
            columns: ['code', 'name', 'payment_periodicity', 'id'],
            periods_columns: ['number', 'start_date', 'start_day', 'end_date', 'end_day', 'payment_status'],
            payment_periodicities: [
                { "id": "", "text": "Seleccione..." },
                { "id": "daily", "text": "Diario" },
                { "id": "weekly", "text": "Semanal" },
                { "id": "biweekly", "text": "Quincenal" },
                { "id": "monthly", "text": "Mensual" },
                { "id": "bimonthly", "text": "Bimensual" },
                { "id": "three-monthly", "text": "Trimestral" },
                { "id": "four-monthly", "text": "Cuatrimestral" },
                { "id": "biannual", "text": "Semestral" },
                { "id": "annual", "text": "Anual" },
            ],

            days: [
                'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
            ],
            payroll_concepts: [],
            finance_bank_accounts: [],
            finance_payment_methods: [],
            accounting_accounts: [],
            accounting_entry_categories: [],
            payroll_concepts: [],
        }
    },
    props: {
        start_operations_date: {
            type: [Date, String],
            required: false,
            default: ''
        },
        accounting: {
            type: String,
            required: true
        },
        finance: {
            type: String,
            required: true
        },
        moment_close_permission: {
            type: Boolean,
            required: true,
        }
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'code': 'Código',
            'name': 'Nombre',
            'payment_periodicity': 'Periodicidad',
            'id': 'Acción',
            'number': 'N°',
            'start_date': 'Inicio de Período',
            'start_day': 'Día Inicial',
            'end_date': 'Fin de Período',
            'end_day': 'Día Final',
            'payment_status': 'Estatus de Pago'
        };
        vm.table_options.sortable = ['code', 'name', 'payment_periodicity', 'id'];
        vm.table_options.filterable = ['code', 'name', 'payment_periodicity', 'id'];
        vm.table_options.columnsClasses = {
            'code': 'col-xs-4',
            'name': 'col-xs-4',
            'payment_periodicity': 'col-xs-2',
            'id': 'col-xs-2'
        };
    },
    mounted() {
        const vm = this;
        $("#add_payroll_payment_type").on('show.bs.modal', function () {
            vm.reset();
            vm.getPayrollConcepts();
            vm.getUserPermission();
            if (vm.accounting) {
                //vm.getAccountingAccounts();
                vm.getAccountingEntryCategories();
            }
            if (vm.finance) {
                vm.getFinanceBankAccounts();
                vm.getFinancePaymentMethods();
            }
        });
    },
    computed: {
        paymentData: function () {
            const keysObj1 = Object.keys(this.savedpaymentData);
            for (const key of keysObj1) {
                if (this.record[key] !== this.savedpaymentData[key]) {
                    return true;
                }
            }
            return false;
        }
    },

    watch: {
        /**
         * Método que supervisa los cambios en el objeto record y actualiza el número de períodos
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         */
        record: {
            deep: true,
            handler: function () {
                if (this.record.id != '' && this.record.payroll_payment_periods.length > 0) {
                    this.payroll_payment_periods = this.record.payroll_payment_periods;
                    this.paymentPeriodsLength = this.record.payroll_payment_periods.length;
                }
                if (this.record.payment_periodicity != '' && this.record.periods_number == '') {
                    let number = 0;
                    if (this.record.payment_periodicity == 'daily') {
                        number = 365;
                    } else if (this.record.payment_periodicity == 'weekly') {
                        number = 52;
                    } else if (this.record.payment_periodicity == 'biweekly') {
                        number = 24;
                    } else if (this.record.payment_periodicity == 'monthly') {
                        number = 12;
                    } else if (this.record.payment_periodicity == 'bimonthly') {
                        number = 6;
                    } else if (this.record.payment_periodicity == 'three-monthly') {
                        number = 4;
                    } else if (this.record.payment_periodicity == 'four-monthly') {
                        number = 3;
                    } else if (this.record.payment_periodicity == 'biannual') {
                        number = 2;
                    } else if (this.record.payment_periodicity == 'annual') {
                        number = 1;
                    } else {
                        number = 0;
                    }
                    this.record.periods_number = number;
                };
                if ((this.record.periods_number > 0)
                    && (this.record.payroll_payment_periods)
                    && (this.record.payroll_payment_periods.length == 0)
                    && (this.record.start_date != '')) {
                    let array_start = '';
                    let array_end = '';
                    let number_day_start = '';
                    let number_day_end = '';
                    let current_date = this.record.start_date;
                    let date = current_date.split('-');
                    let currentDate = new Date(date[0], date[1], date[2]);
                    let start_date = '';
                    let end_date = '';
                    let start_day = '';
                    let end_day = '';
                    let actualDate = '';
                    var days = [];

                    if (this.leapYear(currentDate.getFullYear())) {
                        days = new Array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
                    } else {
                        days = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
                    }

                    for (var i = 0; i <= this.record.periods_number - 1; i++) {
                        if (this.record.payment_periodicity == 'daily') {
                            start_date = this.add_period(String(current_date), (1 * i), 'days');
                            end_date = this.add_period(String(current_date), (1 * i), 'days');
                        } else if (this.record.payment_periodicity == 'weekly') {
                            start_date = this.add_period(String(current_date), (6 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (6 * (i + 1) + i), 'days');
                        } else if (this.record.payment_periodicity == 'biweekly') {
                            if (i == 0) {
                                start_date = this.add_period(String(current_date), 0, 'days');
                                let date = start_date.split('/');
                                current_date = date[2] + '-' + date[1] + '-' + date[0];
                                actualDate = start_date.split('/');
                            }
                            else if (i != 0) {
                                start_date = this.add_period(String(current_date), 1, 'days');
                                let date = start_date.split('/');
                                current_date = date[2] + '-' + date[1] + '-' + date[0];
                                actualDate = start_date.split('/');
                            }

                            if (actualDate[0] < 15) {
                                end_date = this.add_period(String(current_date), (15 - actualDate[0]), 'days');
                                let date = end_date.split('/');
                                current_date = date[2] + '-' + date[1] + '-' + date[0];
                                actualDate = end_date.split('/');
                            }
                            else if (actualDate[0] >= 15) {
                                end_date = this.add_period(String(current_date), (days[actualDate[1] - 1] - actualDate[0]), 'days');
                                let date = end_date.split('/');
                                current_date = date[2] + '-' + date[1] + '-' + date[0];
                                actualDate = end_date.split('/');
                            }
                        } else if (this.record.payment_periodicity == 'monthly') {
                            if (i == 0) {
                                start_date = this.add_period(String(current_date), 0, 'days');
                                let date = start_date.split('/');
                                current_date = date[2] + '-' + date[1] + '-' + date[0];
                                actualDate = start_date.split('/');
                            }
                            else if (i != 0) {
                                start_date = this.add_period(String(current_date), 1, 'days');
                                let date = start_date.split('/');
                                current_date = date[2] + '-' + date[1] + '-' + date[0];
                                actualDate = start_date.split('/');
                            }

                            end_date = this.add_period(String(current_date), (days[actualDate[1] - 1] - 1), 'days');
                            let date = end_date.split('/');
                            current_date = date[2] + '-' + date[1] + '-' + date[0];
                            actualDate = end_date.split('/');
                            /*start_date = this.add_period(String(current_date), (30 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (30 * (i + 1) + i), 'days');
                            /*start_date = this.add_period(String(current_date), (1 * i), 'months');
                            end_date = this.add_period(String(current_date), (1 * i), 'months'); */
                        } else if (this.record.payment_periodicity == 'bimonthly') {
                            start_date = this.add_period(String(current_date), (60 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (60 * (i + 1) + i), 'days');
                            /*start_date = this.add_period(String(current_date), (2 * i), 'months');
                            end_date = this.add_period(String(current_date), (2 * (i + 1)), 'months'); */
                        } else if (this.record.payment_periodicity == 'three-monthly') {
                            start_date = this.add_period(String(current_date), (90 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (90 * (i + 1) + i), 'days');
                            /*start_date = this.add_period(String(current_date), (3 * i), 'months');
                            end_date = this.add_period(String(current_date), (3 * (i + 1)), 'months');*/
                        } else if (this.record.payment_periodicity == 'four-monthly') {
                            start_date = this.add_period(String(current_date), (120 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (120 * (i + 1) + i), 'days');
                            /*start_date = this.add_period(String(current_date), (4 * i), 'months');
                            end_date = this.add_period(String(current_date), (4 * (i + 1)), 'months');*/
                        } else if (this.record.payment_periodicity == 'biannual') {
                            start_date = this.add_period(String(current_date), (180 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (180 * (i + 1) + i), 'days');
                            /*start_date = this.add_period(String(current_date), (6 * i), 'months');
                            end_date = this.add_period(String(current_date), (4 * (i + 1)), 'months');*/
                        } else if (this.record.payment_periodicity == 'annual') {
                            start_date = this.add_period(String(current_date), (365 * i) + i, 'days');
                            end_date = this.add_period(String(current_date), (365 * (i + 1) + i), 'days');
                            /*start_date = this.add_period(String(current_date), (1 * i), 'years');
                            end_date = this.add_period(String(current_date), (1 * (i + 1)), 'years');*/
                        }
                        /** Revisar: moment(String(current_date)).weekday();  retorna NaN */
                        array_start = start_date.split('/');
                        array_end = end_date.split('/');
                        number_day_start = new Date(array_start[2] + '/' + array_start[1] + '/' + array_start[0]).getDay();
                        number_day_end = new Date(array_end[2] + '/' + array_end[1] + '/' + array_end[0]).getDay();
                        if (this.record.id !== '' && this.userPermission == true) {
                            this.record.payroll_payment_periods.push({
                                id: this.payroll_payment_periods[i + this.paymentPeriodsLength - this.record.periods_number]['id'],
                                number: i + 1,
                                start_date: start_date,
                                start_day: this.days[number_day_start],
                                end_date: end_date,
                                end_day: this.days[number_day_end],
                                payment_status: this.payroll_payment_periods[i]['payment_status']
                            });
                        }
                        else if (this.userPermission == false) {
                            this.record.payroll_payment_periods.push({
                                id: '',
                                number: i + 1,
                                start_date: start_date,
                                start_day: this.days[number_day_start],
                                end_date: end_date,
                                end_day: this.days[number_day_end],
                                payment_status: 'pending'
                            });
                        }
                    }
                }
            }
        }
    },
    methods: {
        initVars() {
            this.record.periods_number = '';
            this.record.payroll_payment_periods = [];
        },
        /**
         * Método que permite borrar todos los datos del formulario
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                code: '',
                name: '',
                order: false,
                receipt: false,
                individual: false,
                payment_periodicity: '',
                periods_number: '',
                finance_bank_account_id: '',
                finance_payment_method_id: '',
                accounting_account_id: '',
                accounting_entry_category_id: '',
                start_date: '',
                payroll_concepts: [],
                payroll_payment_periods: []
            };
            vm.payroll_payment_periods = [];
            vm.paymentPeriodsLength = '';
            vm.view_periods = false;
            vm.starOperationDate = vm.start_operations_date;
            vm.savedpaymentData = { 'payment_periodicity': '', 'start_date': '' };
            vm.errors = [];
        },
        /**
         * Reescribe el método "getOptions" para cambiar su comportamiento por defecto
         * Método que obtiene un arreglo con las opciones a listar
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getOptions(url) {
            const vm = this;
            vm.associated_records = [];
            url = vm.setUrl(url);
            axios.get(url).then(response => {
                if (response.data.length > 0) {
                    $.each(response.data, function (index, field) {
                        if (typeof (field['children']) != 'undefined') {
                            $.each(field['children'], function (index, field) {
                                vm.associated_records.push(field);
                            });
                        } else {
                            vm.associated_records.push(field);
                        }
                    });
                }
            });
        },

        getPaymentPeriodicity(payment_periodicity) {
            const vm = this;
            let value = '';
            $.each(vm.payment_periodicities, function (index, field) {
                if (field['id'] == payment_periodicity) {
                    value = field['text'];
                }
            });
            return value;
        },

        /**
         * Método que indica si el año es bisiesto
         *
         * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
         */
        leapYear(year) {
            return (year % 400 === 0) ? true :
                (year % 100 === 0) ? false :
                    year % 4 === 0;
        },

        /**
         * Método que indica si el usuario tiene el permiso de actualizar fecha de periodos o crear nuevos periodos
         *
         * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
         */
        getUserPermission() {
            const vm = this;
            axios.get(`${window.app_url}/payroll/get-user-permission`).then(response => {
                vm.userPermission = response.data.permission;
            });
        },

        /**
         * Obtiene los datos de las entidades bancarias registradas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getFinanceBankAccounts() {
            if (!this.finance) {
                return;
            }

            this.finance_bank_accounts = [];

            axios.get(`${window.app_url}/finance/get-bank-accounts`, { params: { group: true } }).then(response => {
                this.finance_bank_accounts = response.data;
            });
        },
        /**
         * Obtiene los datos de los métodos de pago
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getFinancePaymentMethods() {
            const vm = this;
            if (!vm.finance) {
                return;
            }

            await axios.get(`${vm.app_url}/finance/get-payment-methods`).then(response => {
                vm.finance_payment_methods = response.data || [];
            }).catch(error => {
                vm.logs('Finance/Resources/assets/js/_all.js', 127, error, 'getPaymentMethods');
            });
        },

        /**
         * Obtiene un listado de cuentas patrimoniales
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getAccountingAccounts() {
            const vm = this;

            if (!vm.accounting) {
                return;
            }

            vm.accounting_accounts = [];
            axios.get(`${window.app_url}/accounting/accounts`).then(response => {
                if (response.data.records.length > 0) {
                    vm.accounting_accounts.push({
                        id: '',
                        text: 'Seleccione...'
                    });
                    $.each(response.data.records, function () {
                        vm.accounting_accounts.push({
                            id: this.id,
                            text: `${this.code} - ${this.denomination}`
                        });
                    });
                }
            }).catch(error => {
                vm.logs('PayrollConceptsComponent', 258, error, 'getAccountingAccounts');
            });
        },

        /**
         * Obtiene un listado de cuentas patrimoniales
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getAccountingEntryCategories() {
            const vm = this;

            if (!vm.accounting) {
                return;
            }

            vm.accounting_entry_categories = [];
            axios.get(`${window.app_url}/accounting/get-categories`).then(response => {
                if (response.data.length > 0) {
                    vm.accounting_entry_categories.push({
                        id: '',
                        text: 'Seleccione...'
                    });
                    $.each(response.data, function () {
                        vm.accounting_entry_categories.push({
                            id: this.id,
                            text: `${this.acronym} - ${this.text}`
                        });
                    });
                }
            }).catch(error => {
                vm.logs('PayrollConceptsComponent', 258, error, 'getAccountingAccounts');
            });
        },

        eraseAccountingAccountValue() {
            const vm = this;

            if (vm.record.finance_bank_account_id) {
                vm.record.accounting_account_id = '';
            }
        },

        eraseFinanceBankAccountValue() {
            const vm = this;

            if (vm.record.accounting_account_id) {
                vm.record.finance_bank_account_id = '';
            }
        },
        alertMessage(value) {
            const vm = this;
            if (value) {
                bootbox.confirm({
                    title: "¿Generar orden de pago?",
                    message: "¿Está seguro que desea generar orden de pago?. Esto puede tardar un tiempo considerable según los datos a generar.",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },
                    callback: async function (result) {
                        if (!result) {
                            vm.record.order = false;
                        }
                    }
                });
            }
        },

        async initUpdate(id, event) {
            let vm = this;
            vm.errors = [];

            let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                return rec.id === id;
            })[0])) || vm.reset();

            vm.record = recordEdit;
            vm.savedPeriods = this.record.payroll_payment_periods;
            vm.savedpaymentData = {
                'payment_periodicity': vm.record.payment_periodicity,
                'start_date': vm.record.start_date
            }

            const findLastPeriodInPayroll = (obj) => {
                return Object.values(obj).reverse().find(element => element.in_payroll === true);
            };

            const result = findLastPeriodInPayroll(vm.record.payroll_payment_periods);
            if (result) {
                const editMinDate = new Date(result.end_date);
                editMinDate.setUTCDate(editMinDate.getUTCDate() + 1);
                vm.starOperationDate = editMinDate.toISOString().slice(0, 10)
            } else {
                vm.starOperationDate = vm.start_operations_date;
            }
            event.preventDefault();
        },

        async createRecord(url, list = true, reset = true) {
            const vm = this;
            url = vm.setUrl(url);

            if (vm.record.id) {
                if (vm.paymentData) {
                    bootbox.confirm({
                        title: "¿Modificar periodos?",
                        message: "Todos los periodos con estatus pendiente que no tengan nomina asociada serán modificados. ¿Está seguro de modificar los periodos?",
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
                                vm.record.star_operation_date = vm.starOperationDate
                                vm.updateRecord(url);
                            }
                        }
                    });
                } else {
                    vm.record.star_operation_date = vm.starOperationDate
                    vm.updateRecord(url);
                }
            } else {
                vm.loading = true;
                var fields = {};

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                fields['star_operation_date'] = vm.starOperationDate
                await axios.post(url, fields).then(response => {
                    if (typeof (response.data.redirect) !== "undefined") {
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

                    if (typeof (error.response) != "undefined") {
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
         * Método que actualiza los checks de recibos de pago y orden de pago en caso que
         * se omitan los momentos presupuestarios
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        updateCheckValues() {
            const vm = this;

            if (vm.record.skip_moments) {
                vm.record.receipt = false;
                vm.record.order = false;
            }
        }
    }
};
</script>
