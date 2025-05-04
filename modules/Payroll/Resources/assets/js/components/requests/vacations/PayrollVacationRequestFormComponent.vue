<template>
    <section id="PayrollVacationRequestForm">
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
                <!-- código de la solicitud -->
                <div class="col-md-4" v-if="id > 0" id="helpPayrollVacationRequestCode">
                    <div class="form-group is-required">
                        <label>Código de la solicitud:</label>
                        <input type="text" readonly data-toggle="tooltip" title="" class="form-control input-sm"
                            v-model="record.code">
                    </div>
                </div>
                <!-- ./código de la solicitud -->
                <!-- fecha de la solicitud -->
                <div class="col-md-4" id="helpPayrollVacationRequestDate">
                    <div class="form-group is-required">
                        <label>Fecha de la solicitud:</label>
                        <input type="date" readonly data-toggle="tooltip" title="Fecha de generación de la solicitud"
                            class="form-control input-sm" v-model="record.created_at">
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <!-- ./fecha de la solicitud -->
                <!-- institution -->
                <div class="col-md-4" id="helpPayrollVacationRequestInstitution" v-if="is_admin">
                    <div class="form-group is-required">
                        <label>Organización:</label>
                        <select2 :options="institutions" v-model="record.institution_id">
                        </select2>
                    </div>
                </div>
                <!-- ./institution -->
                <!-- trabajador -->
                <div class="col-md-4" id="helpPayrollVacationRequestStaff">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2 :options="payroll_staffs" :disabled="(is_admin) ? null : 'disabled'"
                            @input="getPayrollStaffInfo(); getPayrollVacationPolicy()" v-model="record.payroll_staff_id">
                        </select2>
                    </div>
                </div>
                <!-- ./trabajador -->
                <!-- año del período vacacional -->
                <div class="col-md-4" id="helpPayrollVacationPeriodYear">
                    <div class="form-group is-required">
                        <label>Año del período vacacional:</label>
                        <v-multiselect :options="vacation_period_years" track_by="text" :hide_selected="false"
                            data-toggle="tooltip" title="Indique los periodos vacaionales"
                            @input="getPayrollVacationPeriods()" v-model="record.vacation_period_year">
                        </v-multiselect>
                    </div>
                </div>
                <!-- ./año del período vacacional -->
            </div>
            <div class="row">
                <!-- fecha de inicio de vacaciones -->
                <div class="col-md-4" id="helpPayrollVacationStartDate">
                    <div class="form-group is-required" style="z-index: unset;">
                        <label>Fecha de inicio de vacaciones:</label>
                        <input type="date" id="start_date" data-toggle="tooltip" title="Fecha de inicio de vacaciones"
                            @input="getcalculate()" :min="getMinDate()" class="form-control input-sm no-restrict"
                            v-model="record.start_date">
                    </div>
                </div>
                <!-- ./fecha de inicio de vacaciones -->
                <!-- fecha de culminación de vacaciones -->
                <div class="col-md-4" id="helpPayrollVacationEndDate">
                    <div class="form-group is-required">
                        <label>Fecha de culminación de vacaciones:</label>
                        <input type="date" id="end_date" data-toggle="tooltip" title="Fecha de culminación de vacaciones"
                            class="form-control input-sm no-restrict" v-model="record.end_date" :min="getMinDate()"
                            :max="getMaxDate()" @input="getcalculate()">
                    </div>
                </div>
                <!-- ./fecha de culminación de vacaciones -->
                <!-- días solicitudos -->
                <div class="col-md-4" v-if="record.vacation_period_year.length > 0" id="helpPayrollVacationDaysRequested">
                    <div class="form-group is-required">
                        <label>Días solicitados:</label>
                        <input type="text" data-toggle="tooltip" title="Indique la cantidad de días solicitados"
                            @input="updatePendingDays()" class="form-control input-sm" disabled
                            v-model="record.days_requested">
                    </div>
                </div>
                <!-- ./días solicitados -->
            </div>
            <div class="row" v-show="payroll_staff['id'] > 0">
                <div class="col-md-12">
                    <h6 class="card-title"> Información del trabajador </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong> Nombres: </strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="payroll_staff_first_name"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong>Apellidos:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="payroll_staff_last_name"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong>Fecha de ingreso:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="payroll_staff_start_date"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong>Cargo:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="payroll_staff_position"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong>Dependencia:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="payroll_staff_department"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-show="record.vacation_period_year.length > 0">
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong>Días de disfrute según antigüedad:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="vacation_days_to_antiquity"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <strong>Días pendientes:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12" id="pending_days"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 with-border with-radius" :key="vacation_request_for_period['period']"
                            v-for="vacation_request_for_period in vacation_request_for_periods">
                            <div class="form-group">
                                <strong> Período: </strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12"> {{ vacation_request_for_period['period'] }} </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <strong> Días Solicitados: </strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12"> {{ vacation_request_for_period['days_requested'] }} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button type="button" @click="reset()" class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                title="Borrar datos del formulario" v-has-tooltip>
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" @click="redirect_back(route_list)" class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip" title="Cancelar y regresar" v-has-tooltip>
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="createRecord('payroll/vacation-requests')"
                class="btn btn-success btn-icon btn-round" data-toggle="tooltip" title="Guardar registro" v-has-tooltip>
                <i class="fa fa-save"></i>
            </button>
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
                status: '',
                days_requested: '',
                vacation_period_year: '',
                start_date: '',
                end_date: '',
                institution_id: '',
                payroll_staff_id: ''
            },

            errors: [],
            records: [],
            vacation_period_years: [],
            payroll_staffs: [],
            payroll_vacation_requests: [],
            vacation_request_for_periods: [],
            vacation_days_por_period: [],
            institutions: [],
            holidays: [],
            holidaysCount: 0,
            payroll_vacation_policy: {},
            payroll_staff: {
                id: '',
                code: '',
                first_name: '',
                last_name: '',
                payroll_nationality_id: '',
                id_number: '',
                passport: '',
                email: '',
                birthdate: '',
                payroll_gender_id: '',
                emergency_contact: '',
                emergency_phone: '',
                parish_id: '',
                address: '',
                has_disability: '',
                disability: '',
                social_security: '',
                has_driver_license: '',
                payroll_license_degree_id: '',
                payroll_blood_type_id: ''
            }
        }
    },
    props: {
        id: {
            type: Number,
            required: false,
            default: ''
        },
        is_admin: {
            type: [Boolean, String],
            required: true,
            default: false
        }
    },
    async mounted() {
        const vm = this;
        if (vm.is_admin) {
            vm.getInstitutions();
        }
        await vm.getPayrollStaffs('auth');
        vm.getHolidays();
        if (vm.id > 0) {
            vm.showRecord(vm.id);
        } else {
            vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
        }

        vm.record.payroll_staff_id = vm.payroll_staffs[1]?.id ?? vm.payroll_staffs[0].id;
        // if (vm.record.payroll_staff_id) {
        //     vm.getPayrollVacationPolicy();
        // }
    },
    created() {
        const vm = this;
        vm.reset();
    },
    methods: {
        /**
         * Método que cambia el formato de visualización de la fecha a
         * dd/mm/yyyy.
         *
         * @method convertDate
         *
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
         *
         * @param {dateString} dateString fecha ha ser fornateada
         */
        convertDate(dateString) {
            if (!dateString) {
                // Devuelve una cadena vacía si dateString es nulo o vacío.
                return "";
            }
            const dateParts = dateString.split("-");
            const year = dateParts[0];
            const month = dateParts[1];
            const day = dateParts[2];
            return `${day}/${month}/${year}`;
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
                status: '',
                days_requested: '',
                vacation_period_year: '',
                start_date: '',
                end_date: '',
                institution_id: '',
                payroll_staff_id: ''
            };
            vm.payroll_vacation_requests = [];
            vm.vacation_request_for_periods = [];
            vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
        },
        /**
         * Método que obtiene la información del trabajador
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
         */
        getPayrollStaffInfo() {
            const vm = this;
            if (vm.record.payroll_staff_id > 0) {
                vm.record.vacation_period_year = (vm.record.id) ? vm.record.vacation_period_year : [];

                axios.get(`${window.app_url}/payroll/staffs/${vm.record.payroll_staff_id}`).then(response => {
                    vm.payroll_staff = response.data.record;
                    axios.get(
                        `${window.app_url}/payroll/get-vacation-requests/${vm.record.payroll_staff_id}`
                    ).then(response => {
                        /**
                         * Extraer dias solicitados
                         * Número de periodos por año
                         * 
                         * Calcular según politica vacacional:
                         * periodos validos
                         * dias pendiente por periodo
                         * dias acumulados
                         */
                        vm.payroll_vacation_requests = response.data.records;

                        let payroll_staff_date = vm.format_date(
                            vm.payroll_staff['payroll_employment']['start_date'],
                            'YYYY-MM-DD'
                        );
                        let payroll_staff_year = payroll_staff_date.split('-')[0];

                        let year_now = new Date().getFullYear();

                        vm.vacation_period_years = [];
                        vm.vacation_period_years.push({
                            "id": "",
                            "text": "Seleccione..."
                        });

                        let requested_period = [];
                        for (let vacation_request of vm.payroll_vacation_requests) {
                            requested_period.push(JSON.parse(vacation_request.vacation_period_year));
                        }

                        let period = 0;
                        let periods;
                        let period_years = [];
                        let requested_period_years = [];

                        for (var i = parseInt(payroll_staff_year); i <= year_now; i++) {
                            if (i != parseInt(payroll_staff_year)) {
                                period++
                                let year = null;
                                let find = false;
                                if (period <= vm.payroll_vacation_policy.vacation_period_per_year) {
                                    if (requested_period.length > 0) {
                                        for (periods of requested_period) {
                                            for (let p of periods) {
                                                if (p.text == i) {
                                                    requested_period_years.push({
                                                        "id": i,
                                                        "text": i,
                                                        "yearId": i - parseInt(payroll_staff_year)
                                                    });
                                                    year = requested_period_years[requested_period_years.length - 1].id;
                                                    find = true;
                                                }
                                            }
                                        }
                                    }
                                    if (find == false && i != year) {
                                        period_years.push({
                                            "id": i,
                                            "text": i,
                                            "yearId": i - parseInt(payroll_staff_year)
                                        });
                                    }
                                }
                            }
                        };

                        if (vm.record.id) {
                            vm.vacation_period_years = requested_period_years.concat(period_years);
                        } else {
                            vm.vacation_period_years = period_years;
                        }

                        vm.vacation_days_por_period = this.getVacationDays(vm.vacation_period_years, {
                            vacationDays: vm.payroll_vacation_policy.vacation_days,
                            fromYear: vm.payroll_vacation_policy.from_year,
                            yearsForAdditionalDays: vm.payroll_vacation_policy.years_for_additional_days,
                            additionalDays: vm.payroll_vacation_policy.additional_days_per_year,
                            maxAdditionalDays: vm.payroll_vacation_policy.maximum_additional_days_per_year
                        }
                        );
                        document.getElementById('payroll_staff_first_name').innerText =
                            vm.payroll_staff['first_name']
                                ? vm.payroll_staff['first_name']
                                : 'No definido';
                        document.getElementById('payroll_staff_last_name').innerText =
                            vm.payroll_staff['last_name']
                                ? vm.payroll_staff['last_name']
                                : 'No definido';
                        document.getElementById('payroll_staff_start_date').innerText =
                            vm.payroll_staff['payroll_employment']
                                ? vm.payroll_staff['payroll_employment']['start_date']
                                    ? vm.convertDate(vm.payroll_staff['payroll_employment']['start_date'])
                                    : 'No definido'
                                : 'No definido';
                        document.getElementById('payroll_staff_department').innerText =
                            vm.payroll_staff['payroll_employment']
                                ? vm.payroll_staff['payroll_employment']['department']
                                    ? vm.payroll_staff['payroll_employment']['department']['name']
                                        ? vm.payroll_staff['payroll_employment']['department']['name']
                                        : 'No definido'
                                    : 'No definido'
                                : 'No definido';
                        if (vm.payroll_staff['payroll_responsibility']) {
                            document.getElementById('payroll_staff_position').innerText = vm.payroll_staff['payroll_responsibility']['payroll_position']['name'];
                        } else if (vm.payroll_staff['payroll_employment']) {
                            document.getElementById('payroll_staff_position').innerText = vm.payroll_staff['payroll_employment']['payroll_positions'][0]['name'];
                        } else {
                            document.getElementById('payroll_staff_position').innerText = 'No definido';
                        }

                        if (vm.record.id) {
                            vm.getPayrollVacationPeriods();
                        }
                    });
                });
            }
        },
        /**
         * Método que obtiene los dias de vacaciones correspondietes
         *por año de servicio
         * @author  Manuel Zambrano <mazambrano@cenditel.gob.ve>
         * 
         * @param {array} vacationPeriodYears - periodos de vacaiones disponibles
         * para el empleado seleccionado
         * @param {object} config - configuración de la politica de vacaciones
         *
         * @return {array} vacationDaysCalculated
         */
        getVacationDays(vacationPeriodYears, config) {
            const vm = this;
            let vacationDaysCalculated = [...vacationPeriodYears];
            let _vacationDaysPerYear = [];
            let days = config.vacationDays;

            const ids = vacationPeriodYears.map(obj => obj.yearId);
            const maxYearId = Math.max(...ids);
            const yearsApn = (vm.payroll_vacation_policy.old_jobs)
                ? vm.payroll_staff.payroll_employment.years_apn
                ? vm.payroll_staff.payroll_employment.years_apn
                : null
                : null

            const yearApn = (yearsApn === "0" || yearsApn === null)
                ? 0
                : parseInt(yearsApn.split(' ')[1]);

            const totalPeriodsVacational = Array.from(
                { length: yearApn + maxYearId },
                (_, i) => i + 1
            );

            for (let i = 0; i < totalPeriodsVacational.length; i++) {
                let year = totalPeriodsVacational[i];
                if (year >= config.fromYear && (year - config.fromYear) % config.yearsForAdditionalDays === 0) {
                    days += config.additionalDays;
                    config.vacationDays = days;
                }
                _vacationDaysPerYear[i] = (days >= config.maxAdditionalDays)
                    ? config.maxAdditionalDays
                    : days
            }

            vacationDaysCalculated = vacationDaysCalculated.map(obj => ({
                ...obj,
                vacationDays: _vacationDaysPerYear[obj.yearId + yearApn - 1]
            }));
            return vacationDaysCalculated;
        },

        updatePendingDays() {
            const vm = this;
            let vacation_days_to_antiquity = document.getElementById('vacation_days_to_antiquity');
            if (vacation_days_to_antiquity) {
                let pending_days = document.getElementById('pending_days');
                if (pending_days) {
                    if (parseInt(vm.record['days_requested']) > parseInt(pending_days.innerText)) {
                        vm.record['days_requested'] = '';
                        vm.record['end_date'] = '';
                    }
                }
            }
        },
        getPayrollVacationPolicy() {
            const vm = this;
            if (vm.record.payroll_staff_id) {
                axios.get(`${window.app_url}/payroll/get-vacation-policy/${vm.record.payroll_staff_id}`)
                    .then(response => {
                        vm.payroll_vacation_policy = response.data.record;
                    });
            }

        },
        /**
        * Actualiza los dias de vacaciones a mostrar según
        * los periodos de vacaciones seleccionados
        *@author  Manuel Zambrano <mazambrano@cenditel.gob.ve>
        *
        * @return {void}
        */
        getPayrollVacationPeriods() {
            const vm = this;
            const vacationDaysPerPeriod = vm.vacation_days_por_period
            let totalVacationDays = 0;
            if (vm.record.vacation_period_year.length > 0) {
                this.record.vacation_period_year.forEach(period => {
                    totalVacationDays += vacationDaysPerPeriod.find(
                        element => element.yearId == period.yearId).vacationDays
                });
                document.getElementById('vacation_days_to_antiquity').innerText =
                    totalVacationDays != 0 ? totalVacationDays : 'No definido';

                document.getElementById('pending_days').innerText =
                    totalVacationDays != 0 ? totalVacationDays : 'No definido';

                vm.updatePendingDays();
            }
        },
        /**
         * Reescribe el método showRecord para cambiar su comportamiento por defecto
         * Método que muestra datos de un registro seleccionado
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param    {integer}    id    Identificador del registro a mostrar
         */
        async showRecord(id) {
            const vm = this;
            await axios.get(`${window.app_url}/payroll/vacation-requests/show/${id}`).then(response => {
                vm.record = response.data.record;
                vm.record.vacation_period_year = JSON.parse(vm.record.vacation_period_year);
                vm.record.created_at = vm.format_date(response.data.record.created_at, 'YYYY-MM-DD');
            });
        },

        /**
         * Método que carga los días feriados
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         */
        getHolidays() {
            const vm = this;
            let url = vm.setUrl('payroll/get-holidays');

            axios.get(url).then(response => {
                if (typeof (response.data) !== "undefined") {
                    vm.holidays = response.data;
                }
            });
        },

        /**
         * Método que agrega la fecha de finalización de las vacaciones
         *
         * @author  Henry Paredes <hparedes@cenditel.gob.ve>
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        getcalculate() {
            const vm = this;

            if (vm.payroll_vacation_policy.business_days) {
                if (vm.record.start_date) {
                    let start_date = new Date(document.getElementById('start_date').value.replaceAll('-', '/'));
                    let ed_value = document.getElementById('end_date').value;
                    let e_date = ed_value == '' ? vm.getMaxDate() : ed_value;
                    let end_date = new Date(e_date.replaceAll('-', '/'));

                    let diff = end_date.getTime() - start_date.getTime()
                    let dias = diff / (1000 * 60 * 60 * 24)
                    vm.holidaysCount = 0;

                    const sumarLaborables = (f, n) => {
                        for (var i = 0; i < n; i++) {
                            f.setTime(f.getTime() + (1000 * 60 * 60 * 24));

                            if (i == 0 && f.getDay() == 0) {
                                dias--;
                            } else if (i == 0 && f.getDay() == 1) {
                                dias--;
                            }

                            /** Se identifica si existen sabados o domingos en el periodo establecido */
                            if ((f.getDay() == 6) || (f.getDay() == 0)) {
                                /** Si existe un dia no laborable se hace el bucle una unidad mas larga */
                                dias--;
                            } else if (vm.holidays.length > 0) {
                                for (let holiday of vm.holidays) {
                                    if (holiday.text != 'Seleccione...') {
                                        let holidayDate = new Date(holiday.text);
                                        holidayDate.setTime(holidayDate.getTime() + (1000 * 60 * 60 * 24));
                                        if (holidayDate.getTime() >= f.getTime() && holidayDate < (f.getTime() + (1000 * 60 * 60 * 24))) {
                                            dias--;
                                            vm.holidaysCount++;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    sumarLaborables(start_date, dias);

                    if (document.getElementById('end_date').value == '') {
                        return;
                    } else {
                        vm.record.days_requested = dias + 1;
                    }
                }
            } else {
                if (document.getElementById('end_date').value == '') {
                    return;
                } else {
                    let start_date = new Date(document.getElementById('start_date').value.replaceAll('-', '/'));
                    let ed_value = document.getElementById('end_date').value;
                    let e_date = ed_value == '' ? vm.getMaxDate() : ed_value;

                    let end_date = new Date(e_date.replaceAll('-', '/'));
                    let diff = end_date.getTime() - start_date.getTime();
                    let dias = diff / (1000 * 60 * 60 * 24);

                    vm.record.days_requested = dias + 1;
                }
            }
        },

        /**
         * Método que permite crear o actualizar un registro
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
         * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
         *                         El valor por defecto es verdadero.
         * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
         *                         El valor por defecto es verdadero.
         */
        createRecord(url, list = true, reset = true) {
            const vm = this;
            url = vm.setUrl(url);

            if (vm.record.id) {
                if (vm.record.days_requested != document.getElementById('vacation_days_to_antiquity').innerText) {
                    vm.errors = [];
                    vm.errors.push('El número de días solicitados debe ser igual al número de días pendientes');
                    vm.loading = false;
                    return;
                }
                vm.updateRecord(url);
            }
            else {
                vm.loading = true;
                var fields = {};

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                if (vm.record.days_requested != document.getElementById('vacation_days_to_antiquity').innerText) {
                    vm.errors = [];
                    vm.errors.push('El número de días solicitados debe ser igual al número de días pendientes');
                    vm.loading = false;
                    return;
                }
                axios.post(url, fields).then(response => {
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
                        vm.loading = false;
                        vm.showMessage('store');
                    }

                }).catch(error => {
                    vm.errors = [];

                    if (typeof (error.response) != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }

                    vm.loading = false;
                });
            }
        },

        getMinDate() {
            const vm = this;
            if (vm.payroll_vacation_policy.min_days_advance) {
                let date = new Date(new Date().getTime() - (new Date().getTimezoneOffset() * 60000)).toISOString().split('T')[0];
                let newDate = vm.add_period(date, vm.payroll_vacation_policy.min_days_advance, 'days', 'YYYY-MM-DD');

                return newDate;
            } else {
                let date = new Date(new Date().getTime() - (new Date().getTimezoneOffset() * 60000)).toISOString().split('T')[0];

                return date;
            }
        },

        addBusinessDaysToDate(days = 0, date = '') {
            const vm = this;
            let remaining = 0;

            const sumarNoLaborables = (f, n) => {
                for (var i = 0; i < n; i++) {
                    /** Se identifica si existen sabados o domingos en el periodo establecido */
                    if ((f.getDay() == 6) || (f.getDay() == 0)) {
                        /** Si existe un dia no laborable se agrega a la regla del calendario */
                        remaining++;
                        n++;
                    }
                    f.setTime(f.getTime() + (1000 * 60 * 60 * 24));
                }

            }
            sumarNoLaborables(new Date(date.replaceAll('-', '/')), days);

            let newDate = vm.add_period(date, (remaining + days - 1), 'days');
            let arrayDate = newDate.split("/");
            return arrayDate[2] + '-' + arrayDate[1] + '-' + arrayDate[0];

        },

        getMaxDate() {
            const vm = this;
            if (vm.record.start_date) {
                if (vm.payroll_vacation_policy.business_days) {
                    let days = parseInt(document.getElementById('vacation_days_to_antiquity').innerText);
                    let holidaysCount = vm.holidaysCount + days;
                    return vm.addBusinessDaysToDate(holidaysCount, vm.record.start_date);
                } else {
                    let date = new Date(vm.record.start_date);
                    let days = parseInt(document.getElementById('vacation_days_to_antiquity').innerText);
                    let newDate = vm.add_period(date, days, 'days', 'YYYY-MM-DD');
                    return newDate;
                }
            }
        }
    }
};
</script>
