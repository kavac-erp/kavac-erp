<template>
    <section id="PayrollReportEmploymentStatusForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <ul>
                    <li v-for="error in errors" :key="error">{{ error }}</li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <strong>Filtros</strong>
                </div>
                <!-- Trabajador -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Trabajador:</label>
                        <select2
                            :options="payroll_staffs"
                            v-model="record.payroll_staff_id"
                        >
                        </select2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button
                        type="button"
                        class='btn btn-sm btn-info float-right'
                        title="Buscar registro" data-toggle="tooltip"
                        @click="searchRecords('employment-status')"
                    >
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <hr>
            <!-- Tabla de registros -->
            <v-client-table
                :columns="columns"
                :data="records"
                :options="table_options"
            >
                <div slot="payroll_staff" slot-scope="props">
                    <span>
                        {{
                            props.row.payroll_staff
                                ? props.row.payroll_staff.first_name
                                    + ' ' + props.row.payroll_staff.last_name
                                : 'No definido'
                        }}
                    </span>
                </div>
                <div slot="active" slot-scope="props" class="text-center">
                    <span v-if="props.row.active">SI</span>
                    <span v-else>NO</span>
                </div>
                <div slot="id" slot-scope="props" class="text-center">
                    <button
                        v-if="route_show"
                        @click="show_info(props.row.id)"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        data-toggle="tooltip"
                        title="Ver registro"
                        data-placement="bottom"
                        type="button"
                    >
                        <i class="fa fa-eye"></i>
                    </button>
                    <button
                        @click="
                            createReport(
                                props.row.payroll_staff.id,
                                'employment-status',
                                $event
                            )
                        "
                        class="btn btn-primary btn-xs btn-icon btn-action"
                        data-toggle="tooltip"
                        title="Generar reporte"
                        data-placement="bottom"
                        type="button"
                    >
                        <i class="fa fa-file-pdf-o"></i>
                    </button>
                </div>
            </v-client-table>
            <!-- Final de Tabla de registros -->

            <!-- Modal -->
            <div
                class="modal fade"
                tabindex="-1"
                role="dialog"
                id="show_employment"
            >
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">×</span>
                            </button>
                            <h6>
                                <i class="icofont icofont-read-book ico-2x"></i>
                                Información Detallada de Datos Laborales y Personales
                            </h6>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Trabajador</b></label>
                                        <p id="payroll_staff"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <b>
                                                Fecha de ingreso a la institución
                                            </b>
                                        </label>
                                        <p id="start_date"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <b>
                                                Fecha de egreso de la institución
                                            </b>
                                        </label>
                                        <p id="end_date"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>¿Está Activo?</b></label>
                                        <div class="custom-control custom-switch">
                                            <span>{{ record.active ? 'Si' : 'No' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Correo Institucional</b></label>
                                        <p id="institution_email"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Tipo de la inactividad</b></label>
                                        <p id="payroll_inactivity_type"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Tipo de Cargo</b></label>
                                        <p id="payroll_position_type"></p>
                                        <label><b>Descripción de funciones</b></label>
                                        <p v-html="record.function_description"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Cargo</b></label>
                                        <p id="payroll_position"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Tipo de Personal</b></label>
                                        <p id="payroll_staff_type"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Tipo de Contrato</b></label>
                                        <p id="payroll_contract_type"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Institución</b></label>
                                        <p id="institution"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Departamento</b></label>
                                        <p id="department"></p>
                                        <label><b>Coordinación</b></label>
                                        <p id="coordination"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Ficha</b></label>
                                        <p id="worksheet_code"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Género</b></label>
                                        <p id="payroll_gender"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <b>¿Posee una Discapacidad?</b>
                                        </label>
                                        <div class="custom-control custom-switch">
                                            <span>
                                                {{ record.has_disability
                                                    ? 'Si' : 'No'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="col-md-4"
                                    v-if="record.has_disability"
                                >
                                    <div class="form-group">
                                        <label><b>Discapacidad</b></label>
                                        <p id="payroll_disability"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Tipo de Sangre</b></label>
                                        <p id="payroll_blood_type"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Seguro Social</b></label>
                                        <p id="social_security"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <b>¿Posee Licencia de Conducir?</b>
                                        </label>
                                        <div class="custom-control custom-switch">
                                            <span>
                                                {{
                                                    record.has_driver_license
                                                        ? 'Si' : 'No'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="col-md-4"
                                    v-show="record.has_driver_license"
                                >
                                    <div class="form-group">
                                        <label>
                                            <b>Grado de la Licencia de Conducir</b>
                                        </label>
                                        <p id="payroll_license_degree"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Nacionalidad</b></label>
                                        <p id="payroll_nationality"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Edad</b></label>
                                        <p id="age"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><b>Años de servicio</b></label>
                                        <p id="service_years"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final de Modal -->
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    current: '',
                    id_number: '',
                    institution_email: '',
                    active: '',
                    payroll_staff_id: '',
                },

                errors:         [],
                records:        [],
                payroll_staffs: [],
                columns: [
                    'payroll_staff',
                    'payroll_staff.id_number',
                    'institution_email',
                    'active',
                    'id'
                ],
            }
        },
        methods: {
            reset() {
                const vm = this;
                vm.record = {
                    id: '',
                    current: '',
                    id_number: '',
                    institution_email: '',
                    active: '',
                    payroll_staff_id: '',
                }
            },

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

            show_info(id) {
                const vm = this;
                axios.get(`${window.app_url}/payroll/employments/${id}`).then(response => {
                    vm.record = response.data.record;
                    const formatted_start_date = vm.convertDate(vm.record.start_date);
                    const formatted_end_date = vm.convertDate(vm.record.end_date);
                    $('#payroll_staff').text(`${vm.record.payroll_staff.first_name} ${vm.record.payroll_staff.last_name}`);
                    $('#start_date_apn').text(`${vm.record.start_date_apn}`);
                    $('#start_date').text(`${formatted_start_date}`);
                    vm.record.end_date ? $('#end_date').text(`${formatted_end_date}`) : $('#end_date').text('');
                    vm.record.payroll_inactivity_type ? $('#payroll_inactivity_type').text(`${vm.record.payroll_inactivity_type.name}`) : $('#payroll_inactivity_type').text('');
                    $('#institution_email').text(`${vm.record.institution_email}`);
                    $('#function_description').text(`${vm.record.function_description}`);
                    $('#payroll_position_type').text(`${vm.record.payroll_position_type.name}`);
                    $('#payroll_position').text(`${vm.record.payroll_positions[0].name}`);
                    $('#payroll_staff_type').text(`${vm.record.payroll_staff_type.name}`);
                    $('#institution').text(`${vm.record.department.institution.name}`);
                    $('#department').text(`${vm.record.department.name}`);
                    $('#coordination').text(
                        vm.record.payroll_coordination ?
                        `${vm.record.payroll_coordination.name}`
                        : '');
                    $('#worksheet_code').text(
                        vm.record.worksheet_code ?
                        `${vm.record.worksheet_code}`
                        : '');
                    $('#payroll_contract_type').text(`${vm.record.payroll_contract_type.name}`);
                    $('#payroll_gender').text(`${vm.record.payroll_staff.payroll_gender.name}`);
                    $('#payroll_disability').text(
                        (vm.record.payroll_staff.payroll_disability)
                        ? (`${vm.record.payroll_staff.payroll_disability.name}`)
                        : ' '
                    );
                    $('#payroll_blood_type').text(`${vm.record.payroll_staff.payroll_blood_type.name}`);
                    vm.record.payroll_staff.social_security ? $('#social_security').text(`${vm.record.payroll_staff.social_security}`) :
                    $('#social_security').text('');
                    $('#payroll_license_degree').text(
                        (vm.record.payroll_staff.payroll_license_degree)
                        ? (`${vm.record.payroll_staff.payroll_license_degree.name}`)
                        : ' '
                    );
                    $('#payroll_nationality').text(`${vm.record.payroll_staff.payroll_nationality.name}`);
                    $('#birthdate').text(`${vm.record.payroll_staff.birthdate}`);
                    $('#age').text(`${response.data.age}`);
                    let diff = vm.diff_datetimes(vm.record.startDateApn);
                    vm.record.service_years = diff.years < 0 ? diff.years * -1 : diff.years;
                    $('#service_years').text(`${vm.record.service_years}`);
                });
                $('#show_employment').modal('show');
            },
            createReport(id, current, event) {
                const vm = this;
                vm.loading = true;
                let fields = {
                    id:      id,
                    current: current
                };
                event.preventDefault();
                axios.post(`${window.app_url}/payroll/reports/${current}/create`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        window.open(response.data.redirect, '_blank');
                    }
                    else {
                        vm.reset();
                    }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom',
                                'Acceso Denegado',
                                'danger',
                                'screen-error',
                                error.response.data.message
                            );
                        }
                        console.log("error");
                    }
                    vm.loading = false;
                });
            },

            /**
             * Método que permite realizar las busquedas y filtrado de los
             * registros de la tabla.
             *
             * @method    searchRecords
             *
             * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
             */
            searchRecords(current) {
                const vm = this;
                vm.record.current = current;
                vm.loading = true;
                let fields = {};
                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                axios.post(`${window.app_url}/payroll/reports/vue-list`, fields).then(response => {
                    if (typeof(response.data.records) !== "undefined") {
                        vm.records = response.data.records;
                    }
                    vm.loading = false;
                }).catch(error => {
                    vm.errors = [];
                    if (typeof(error.response) !="undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.loading = false;
                });
            },
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'payroll_staff': 'Trabajador',
                'payroll_staff.id_number': 'Cedula',
                'institution_email': 'Correo Electrónico Institucional',
                'active': '¿Está activo?',
                'id': 'Acción'
            };
            this.table_options.columnsClasses = {
                'payroll_staff.id_number': 'text-center',
            };
            vm.table_options.sortable = [
                'payroll_staff.first_name',
                'payroll_staff.id_number',
                'institution_email',
                'active'
            ];
            vm.table_options.filterable = [
                'payroll_staff.first_name',
                'payroll_staff.id_number',
                'institution_email',
                'active'
            ];
        },
        mounted() {
            const vm = this;
            vm.getPayrollStaffs();
        }
    };
</script>
