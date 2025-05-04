<template>
    <section id="PayrollEmploymentForm">
        <!-- card-body -->
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                        Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li
                            v-for="(error, index) in errors"
                            :key="index"
                        >
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpEmploymentStaff">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2
                            :options="payroll_staffs"
                            v-model="record.payroll_staff_id"
                            :disabled="isEditMode"
                        >
                        </select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <div
                    :class="[record.active ? 'col-md-4' : 'col-md-2']"
                    id="helpEmploymentIsActive"
                >
                    <div class="form-group">
                        <label>¿Está Activo?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="Indique si el trabajador está activo o no"
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="active"
                                    name="active"
                                    v-model="record.active"
                                    :value="true"
                                >
                                <label
                                    class="custom-control-label"
                                    for="active"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-show="record.active == false"
                    class="col-md-2"
                    id="helpEmploymentIsReleaseCharge"
                >
                    <div class="form-group">
                        <label>¿Libera cargo?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador libera el cargo o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="release_charge"
                                    name="release_charge"
                                    v-model="record.release_charge"
                                >
                                <label
                                    class="custom-control-label"
                                    for="release_charge"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentStartDate">
                    <div class="form-group is-required">
                        <label>Fecha de ingreso a la institución:</label>
                        <input
                            @input="diff_datetimes(record.start_date)"
                            type="date"
                            class="form-control input-sm"
                            v-model="record.start_date"
                            :max="fiscal_date"
                        />
                    </div>
                </div>
                <div
                    v-show="record.active == false"
                    class="col-md-4" id="helpEmploymentEndDate"
                >
                    <div class="form-group">
                        <label>Fecha de egreso de la institución:</label>
                        <input
                            @input="time_worked(); diff_datetimes(record.start_date);"
                            type="date"
                            class="form-control input-sm"
                            v-model="record.end_date"
                            :max="fiscal_date"
                        />
                    </div>
                </div>
                <div class="col-md-4" v-if="!record.active">
                    <div class="form-group is-required">
                        <label>Tipo de Inactividad:</label>
                        <select2 :options="payroll_inactivity_types"
                            v-model="record.payroll_inactivity_type_id"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentEmail">
                    <div class="form-group">
                        <label>Correo Institucional:</label>
                        <input type="email" class="form-control input-sm"
                            v-model="record.institution_email"
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentPossitionType">
                    <div class="form-group is-required">
                        <label>Tipo de Cargo:</label>
                        <select2
                            :options="payroll_position_types"
                            v-model="record.payroll_position_type_id"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentPossition">
                    <div class="form-group is-required">
                        <label>Cargo:</label>
                        <select2
                            :options="payroll_positions"
                            v-model="record.payroll_position_id"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentCoordination">
                    <div class="form-group">
                        <label>Coordinación:</label>
                        <select2
                            :options="payroll_coordinations"
                            v-model="record.payroll_coordination_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentStaffType">
                    <div class="form-group is-required">
                        <label>Tipo de Personal:</label>
                        <select2 :options="payroll_staff_types"
                            v-model="record.payroll_staff_type_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentContractType">
                    <div class="form-group is-required">
                        <label>Tipo de Contrato:</label>
                        <select2 :options="payroll_contract_types"
                            v-model="record.payroll_contract_type_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentInstitution">
                    <div class="form-group is-required">
                        <label>Organización:</label>
                        <select2
                            :options="institutions"
                            @input="getDepartments()"
                            v-model="record.institution_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentDepartment">
                    <div class="form-group is-required">
                        <label>Departamento:</label>
                        <select2
                            :options="departments"
                            v-model="record.department_id"
                            id="department"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentWorkSheetCode">
                    <div class="form-group">
                        <label>Ficha:</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.worksheet_code"
                            maxlength="5"
                            oninput="
                                if (
                                    !/^[0-9]*$/.test(this.value)
                                ) this.value = this.value.replace(/[^0-9]/g, '');
                            "
                        />
                    </div>
                </div>
                <div class="col-12" id="helpEmploymentFunction">
                    <div class="form-group">
                        <label>Descripción de Funciones:</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="function_description"
                            data-toggle="tooltip"
                            title="Indique una descripción para las funciones"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="function_description"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.function_description"
                        ></ckeditor>
                    </div>
                </div>
                <!-- TRABAJOS ANTERIOS -->
                <div class="col-md-12" id="helpEmploymentPreviousJobs">
                    <br>
                    <h6 class="card-title">
                        Trabajos anteriores
                        <i
                            class="fa fa-plus-circle cursor-pointer"
                            @click="addPreviousJob()"
                        ></i>
                    </h6>
                    <div
                        class="row"
                        v-for="(job, index) in record.previous_jobs"
                        :key="index"
                    >
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="organization_name">
                                    Nombre de la organización:
                                </label>
                                <input
                                    type="text"
                                    id="organization_name"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Nombre de la organización"
                                    v-model="job.organization_name"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Teléfono de la organización</label>
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    placeholder="+00-000-0000000"
                                    v-model="job.organization_phone"
                                    v-input-mask
                                    data-inputmask="'mask': '+99-999-9999999'"
                                />
                            </div>
                        </div>
                        <div
                            class="col-md-4"
                            v-if="payroll_sector_types.length > 0"
                        >
                            <div class="form-group is-required">
                                <label>Tipo de sector:</label>
                                <select2
                                    :options="payroll_sector_types"
                                    v-model="job.payroll_sector_type_id"
                                    @input="
                                        antiquity(); diff_datetimes(record.start_date);
                                    "
                                >
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Cargo:</label>
                                <input
                                    type="text"
                                    id="previous_position"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="Cargo"
                                    v-model="job.previous_position"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Tipo de Personal:</label>
                                <select2 :options="payroll_staff_types"
                                    v-model="job.payroll_staff_type_id">
                                </select2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Fecha de inicio:</label>
                                <input
                                    @change="
                                        antiquity();
                                        diff_datetimes(record.start_date);
                                    "
                                    type="date"
                                    class="form-control input-sm"
                                    v-model="job.start_date"
                                    :max="fiscal_date"
                                />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Fecha de cese:</label>
                                <input
                                    @change="
                                        antiquity();
                                        diff_datetimes(record.start_date);
                                    "
                                    type="date"
                                    class="form-control input-sm"
                                    v-model="job.end_date"
                                    :max="fiscal_date"
                                />
                            </div>
                        </div>
                        <div class="col-1">
                            <div class="form-group">
                                <button
                                    class="mt-4 btn btn-sm btn-danger btn-action"
                                    type="button"
                                    @click="
                                    removeRow(index, record.previous_jobs);
                                    "
                                    title="Eliminar este dato"
                                    data-toggle="tooltip"
                                >
                                    <i class="fa fa-minus-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TRABAJOS ANTERIORES -->
            </div>
            <hr>
            <div class="row">
                <h6 class="card-title col-md-12">Antigüedad del trabajador</h6>
                <div class="col-md-4" id="helpEmploymentYears">
                    <div class="form-group">
                        <label>Años en otras instituciones públicas:</label>
                        <input type="text" class="form-control input-sm"
                            v-model="record.years_apn" disabled="true"/>
                    </div>
                </div>
                <div
                    v-if="record.active"
                    class="col-md-4"
                    id="helpEmploymentYears"
                >
                    <div class="form-group">
                        <label>
                            Tiempo laborando en la institución/organización:
                        </label>
                        <input type="text" class="form-control input-sm"
                            v-model="record.institution_years" disabled="true"/>
                    </div>
                </div>
                <div
                    v-else
                    class="col-md-4"
                    id="helpEmploymentYears"
                >
                    <div class="form-group">
                        <label>
                            Tiempo laborado en la institución/organización:
                        </label>
                        <input type="text" class="form-control input-sm"
                            v-model="record.time_worked" disabled="true"/>
                    </div>
                </div>
                <div class="col-md-4" id="helpEmploymentYears">
                    <div class="form-group">
                        <label>Total años de servicio:</label>
                        <input type="text" class="form-control input-sm"
                            v-model="record.service_years" disabled="true"/>
                    </div>
                </div>
                
            </div>
        </div>
        <!-- Final card-body -->

        <!-- card-footer -->
        <div class="card-footer text-right" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                type="button"
                title="Borrar datos del formulario"
                @click="reset"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="generateRecord()"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </section>
</template>
<script>
    export default {
        props: {
            payroll_employment_id: Number,
        },
        watch: {
            'record.active' () {
                if (this.record.active) {
                    this.record.end_date = '';
                }
            }
        },
        data() {
            return {
                record: {
                    id: '',
                    payroll_staff_id: '',
                    institution_id: '',
                    years_apn: '',
                    start_date: '',
                    end_date: '',
                    active: '',
                    release_charge: '',
                    payroll_inactivity_type_id: '',
                    institution_email: '',
                    function_description: '',
                    payroll_position_type_id: '',
                    payroll_position_id: '',
                    payroll_coordination_id: '',
                    payroll_staff_type_id: '',
                    institution_id: '',
                    department_id: '',
                    payroll_contract_type_id: '',
                    previous_jobs: [],
                    institution_years: '',
                    service_years: '',
                    time_worked: '',
                    worksheet_code: '',
                },
                errors: [],
                payroll_staffs: [],
                payroll_inactivity_types: [],
                payroll_position_types: [],
                payroll_positions: [],
                employments_positions: [],
                payroll_coordinations: [],
                payroll_staff_types: [],
                departments: [],
                payroll_contract_types: [],
                institutions: [],
                fiscal_year: '',
                fiscal_date: '',
                years_apn: '',
                worksheet_code: '',
                isEditMode: false,
            }
        },
        methods: {
            /**
             * Obtiene los datos de los cargos registrados en la institucion
             * que no sean cargos de responsabilidad.
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
             */
            getPayrollPositions() {
                const vm = this;
                vm.payroll_positions = [];

                axios.get(`${window.app_url}/payroll/positions`).then(response => {
                    // Filtrar los registros donde el campo 'responsible' sea false.
                    const filteredPositions = response.data.records.filter(
                        item => item.responsible === false
                    );

                    // Mapear los datos para obtener el formato deseado.
                    vm.payroll_positions = filteredPositions.map(item => ({
                        id: item.id,
                        text: item.name
                    }));

                    // Agregar el elemento "Seleccione..." al principio del resultado.
                    vm.payroll_positions.unshift({ id: '', text: 'Seleccione...' });
                });
            },

            /**
             * Obtiene los datos registrados de la tabla intermedia entre
             * PayrollEmployment y PayrollPosition.
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
             */
            async getPayrollEmploymentsPositions() {
                const vm = this;
                vm.employments_positions = [];
                await axios.get(`${window.app_url}/payroll/get-employments-positions`)
                .then(response => {
                    vm.employments_positions = response.data;
                });
            },

            async getDepartments() {
                let vm = this;
                vm.departments = [];
                if (vm.record.institution_id) {
                    await axios.get(`${window.app_url}/get-departments/${vm.record.institution_id}`).then(response => {
                        vm.departments = response.data;
                    });
                }
                if(vm.record.id) {
                    axios.get(`${window.app_url}/payroll/employments/${vm.payroll_employment_id}`).then(response => {
                        let data = response.data.record;
                        vm.record.department_id = data.department_id;
                    });
                }
            },

            /**
             * Obtiene los datos de los años fiscales registrados
             * 
             * @author Natanael Rojo <rojonatanael99@gmail.com>
             */
            async getFiscalYear() {
                const vm = this;
                axios.get(`${window.app_url}/fiscal-years/opened/list`).then(response => {
                    vm.fiscal_year = response.data.records[0].id;
                });
            },

            /**
             * Método que carga los datos guardados del empleado
             */
            async getEmployment() {
                let vm = this;
                try {
                    const response = await axios.get(`${window.app_url}/payroll/employments/${this.payroll_employment_id}`);
                    const data = response.data.record;
                    vm.record = {
                        id: data.id,
                        payroll_staff_id: data.payroll_staff_id,
                        institution_id: data.department.institution_id,
                        years_apn: data.years_apn,
                        worksheet_code: data.worksheet_code,
                        start_date: data.start_date,
                        end_date: data.end_date ? data.end_date : '',
                        active: data.active,
                        payroll_inactivity_type_id: data.payroll_inactivity_type_id
                            ? data.payroll_inactivity_type_id : '',
                        institution_email: data.institution_email
                            ? data.institution_email : '',
                        function_description: data.function_description
                            ? data.function_description : '',
                        payroll_position_type_id: data.payroll_position_type_id,
                        payroll_position_id: data.payroll_positions[0].id,
                        payroll_coordination_id: data.payroll_coordination_id,
                        payroll_staff_type_id: data.payroll_staff_type_id,
                        department_id: data.department.id,
                        payroll_contract_type_id: data.payroll_contract_type_id,
                        previous_jobs: data.payroll_previous_job
                            ? data.payroll_previous_job : '',
                    }

                    // Bloquear el select del trabajador cuando esté en modo edit.
                    this.isEditMode = true;

                    /* Se consulta la tabla intermedia, se busca el registro
                    que corresponda con el empleado según id y se obtiene el
                    valor de active */
                    let searchQueryActive = vm.employments_positions.record.find(
                        record => record.payroll_employment_id === vm.record.id
                    );

                    if (searchQueryActive.active) {
                        vm.record.release_charge = false;
                    } else {
                        vm.record.release_charge = true;
                    }

                    vm.antiquity();
                    vm.diff_datetimes(vm.record.start_date);
                } catch (error) {
                    console.error('Error al cargar los datos del empleado', error);
                }
            },

            /**
             * Agrega los campos para registrar el trabajo anterior de un trabajador
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            addPreviousJob() {
                const vm = this;
                vm.record.previous_jobs.push({
                    organization_name: '',
                    organization_phone: '',
                    payroll_sector_type_id: '',
                    previous_position: '',
                    payroll_staff_type_id: '',
                    start_date: '',
                    end_date: '',
                    payroll_employment_id: '',
                });
            },

            /**
             * Método que limpia todos los datos del formulario.
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    institution_id: '',
                    payroll_staff_id: '',
                    years_apn: '',
                    start_date: '',
                    end_date: '',
                    active: false,
                    release_charge: false,
                    payroll_inactivity_type_id: '',
                    institution_email: '',
                    function_description: '',
                    payroll_position_type_id: '',
                    payroll_position_id: '',
                    payroll_coordination_id: '',
                    payroll_staff_type_id: '',
                    department_id: '',
                    payroll_contract_type_id: '',
                    worksheet_code: '',
                };
            },

            generateRecord() {
                const vm = this;
                vm.errors = [];
                if (vm.record.previous_jobs){
                    for (let job of vm.record.previous_jobs){
                        if (job.organization_name == ''){
                            vm.errors.push('El campo nombre de la organización es obligatorio')
                        }
                        if (job.organization_phone == ''){
                            vm.errors.push('El campo teléfono de la organización es obligatorio')
                        }
                        if (job.payroll_sector_type_id == ''){
                            vm.errors.push('El campo tipo de sector es obligatorio')
                        }
                        if (job.previous_position == ''){
                            vm.errors.push('El campo cargo es obligatorio')
                        }
                        if (job.payroll_staff_type_id == ''){
                            vm.errors.push('El campo tipo de personal es obligatorio')
                        }
                        if (job.start_date == ''){
                            vm.errors.push('El campo fecha de inicio es obligatorio')
                        }
                        if (job.end_date == ''){
                            vm.errors.push('El campo fecha de cese es obligatorio')
                        }
                    }
                }
                if (vm.errors < 1) {
                    vm.createRecord('payroll/employments');
                }
            },

            /**
             * Método que calcula los años en otras instituciones públicas
             *
             * @method     antiquity
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             */
            antiquity() {
                const vm = this;
                vm.record.years_apn = 0;
                let data_years = 0;
                let data_months = 0;
                let data_days = 0;
                let years = 0;
                let months = 0;
                let days = 0;
                for (let job of vm.record.previous_jobs){
                    for (let sector_type of vm.payroll_sector_types) {
                        if(job.payroll_sector_type_id == sector_type.id && sector_type.text == 'Público'){
                            let now = job.start_date;
                            let ms = moment(job.end_date,"YYYY-MM-DD HH").diff(moment(now,"YYYY-MM-DD"));
                            let d = moment.duration(ms);

                            if (d._data.years < 0){
                                data_years = d._data.years * -1;
                            } else {
                                data_years = d._data.years;
                            }
                            if (d._data.months < 0){
                                data_months = d._data.months * -1;
                            } else {
                                data_months = d._data.months
                            }
                            if (d._data.days < 0){
                                data_days = d._data.days * -1;
                            } else {
                                data_days = d._data.days
                            }

                            years += data_years;
                            months += data_months;
                            days += data_days;

                            if (months > 12) {
                                months = months%12;
                                years = years + 1;
                            }

                            if (days > 30) {
                                days = days%30;
                                months = months + 1;
                            }

                            vm.record.years_apn = 'Años: ' + years + ' Meses: '
                                + months + ' Días: ' + days;
                            vm.years_apn = years;
                        }
                    }
                }
            },

            /**
             * Método que calcula los años en otras instituciones públicas
             *
             * @method     time_worked
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             */
            time_worked() {
                const vm = this;
                var now = vm.record.start_date;
                var ms = 0;
                if (vm.fiscal_date && !vm.record.end_date) {
                    ms = moment(now,"YYYY-MM-DD").diff(moment(vm.fiscal_date,"YYYY-MM-DD"));
                } else if (vm.fiscal_date && vm.record.end_date) {
                    ms = moment(now,"YYYY-MM-DD").diff(moment(vm.record.end_date,"YYYY-MM-DD"));
                }  else {    
                    ms = moment(vm.record.end_date,"YYYY-MM-DD").diff(moment(now,"YYYY-MM-DD"));
                }

                var d = moment.duration(ms);
                let data_years = 0;
                let data_months = 0;
                let data_days = 0;
                if (d._data.years < 0){
                    data_years = d._data.years * -1;
                } else {
                    data_years = d._data.years;
                }
                if (d._data.months < 0){
                    data_months = d._data.months * -1;
                } else {
                    data_months = d._data.months
                }
                if (d._data.days < 0){
                    data_days = d._data.days * -1;
                } else {
                    data_days = d._data.days
                }

                let time = {
                    years: `Años: ${data_years}`,
                    months: `Meses: ${data_months}`,
                    days: `Días: ${data_days}`,
                };
                if (data_days > 0) {
                    vm.record.time_worked = time.years + ' ' + time.months + ' ' + time.days;
                } else {
                    vm.record.time_worked = 0;
                };
            },

            /**
             * Método que calcula la diferencia entre dos fechas con marca de tiempo
             *
             * @method     diff_datetimes
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             * @param      {string}  dateThen    Fecha a comparar para obtener la diferencia con respecto a la fecha actual
             *
             * @return     {[type]}  Objeto con información de la diferencia obtenida entre las dos fechas
             */
            diff_datetimes(dateThen) {
                const vm = this;
                let now = moment().format("YYYY-MM-DD");
                let ms = 0;
                if (vm.fiscal_date && !vm.record.end_date) {
                    ms = moment(dateThen,"YYYY-MM-DD").diff(moment(vm.fiscal_date,"YYYY-MM-DD"));
                } else if (vm.fiscal_date && vm.record.end_date) {
                    ms = moment(dateThen,"YYYY-MM-DD").diff(moment(vm.record.end_date,"YYYY-MM-DD"));
                }  else {    
                    ms = moment(dateThen,"YYYY-MM-DD").diff(moment(now,"YYYY-MM-DD"));
                }
                let d = moment.duration(ms);
                let data_years = 0;
                let data_months = 0;
                let data_days = 0;

                if (d._data.years < 0){
                    data_years = d._data.years * -1;
                }
                if (d._data.months < 0){
                    data_months = d._data.months * -1;
                }
                if (d._data.days < 0){
                    data_days = d._data.days * -1;
                }

                let time = {
                    years: `Años: ${data_years}`,
                    months: `Meses: ${data_months}`,
                    days: `Días: ${data_days}`,
                };

                if (data_days > 0) {
                    vm.record.institution_years = time.years + ' ' + time.months + ' ' + time.days;
                } else {
                    vm.record.institution_years = 0;
                };

                if(data_years) {
                    vm.record.service_years = data_years + vm.years_apn;
                } else {
                    vm.record.service_years = vm.years_apn;
                }
            },
            /**
             * Elimina la fila del elemento indicado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             *
             * @param  {integer}      index Indice del elemento a eliminar
             * @param  {object|array} el    Elemento del cual se va a eliminar un elemento
             */
            removeRow: async function(index, el) {
                const vm = this;
                // Calcular años de duración del trabajo anterior
                let old_job_start_date = vm.record.previous_jobs[index].start_date; 
                let old_job_end_date = vm.record.previous_jobs[index].end_date;

                let ms = moment(old_job_start_date,"YYYY-MM-DD").diff(moment(old_job_end_date,"YYYY-MM-DD"));
                let d = moment.duration(ms);

                let data_years = 0;
                if (d._data.years < 0){
                    data_years = d._data.years * -1;
                } else {
                    data_years = d._data.years;
                }

                //Restar años del trabajo anterior con los años del servicio total
                vm.years_apn -=  data_years;

                el.splice(index, 1);
                await Promise.all ([
                    vm.antiquity(),
                    vm.diff_datetimes(vm.record.start_date)
                ]);
            },
        },

        async created() {
            this.loadingState(true); // Inicio de spinner de carga.
            this.record.active = true;
            await Promise.all([
                this.getFiscalYear(),
                this.getPayrollEmploymentsPositions(),
                this.getPayrollInactivityTypes(),
                this.getPayrollPositionTypes(),
                this.getPayrollPositions(),
                this.getPayrollCoordinations(),
                this.getPayrollStaffTypes(),
                this.getPayrollContractTypes(),
                this.getInstitutions(),
                this.getPayrollSectorTypes(),
            ]);

            if (this.fiscal_year) {
                this.fiscal_date = this.fiscal_year + "-12-31";
            }

            if (this.payroll_employment_id) {
                await this.getPayrollStaffs(this.payroll_employment_id);
                await this.getEmployment();
            } else {
                await this.getPayrollStaffs('filter');
                this.record.previous_jobs = [];
            }
            this.loadingState(); // Finaliza spinner de carga.
        },
    };
</script>
