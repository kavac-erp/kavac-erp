<template>
    <section id="PayrollReportStaffsForm">
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
                <!-- trabajador -->
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset;">
                        <label>Trabajador:</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_staffs"
                            v-model="record.payroll_staffs"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <!-- ./trabajador -->
                <div class="col-md-2">
                    <label><strong>Datos personales</strong></label>
                    <div class="custom-control custom-switch">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="personal_data"
                            name="personal_data"
                            :value="false"
                            v-model="record.personal_data"
                        >
                        <label
                            class="custom-control-label"
                            for="personal_data"
                        ></label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><strong>Datos profesionales</strong></label>
                    <div class="custom-control custom-switch">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="professional_data"
                            name="professional_data"
                            :value="false"
                            v-model="record.professional_data"
                        >
                        <label
                            class="custom-control-label"
                            for="professional_data"
                        ></label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><strong>Datos socioeconómicos</strong></label>
                    <div class="custom-control custom-switch">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="socioeconomic_data"
                            name="socioeconomic_data"
                            :value="false"
                            v-model="record.socioeconomic_data"
                        >
                        <label
                            class="custom-control-label"
                            for="socioeconomic_data"
                        ></label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><strong>Datos laborales</strong></label>
                    <div class="custom-control custom-switch">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="employment"
                            name="employment"
                            :value="false"
                            v-model="record.employment_data"
                        >
                        <label
                            class="custom-control-label"
                            for="employment"
                        ></label>
                    </div>
                </div>
            </div>
            <!-- Datos personales -->
            <div v-show="record.personal_data">
                <div class="row">
                    <div class="col-md-12">
                        <strong>Datos personales</strong>
                    </div>
                    <!-- Genero -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Genero:</label>
                            <v-multiselect
                                track_by="text"
                                :options="genders"
                                v-model="record.payroll_genders"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Genero -->
                    <!-- Discapacidad -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Discapacidad:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_disabilities"
                                v-model="record.payroll_disabilities"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Discapacidad -->
                    <!-- Licencia -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Licencia:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_license_degrees"
                                v-model="record.payroll_license_degrees"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Licencia -->
                    <!-- Tipo de sangre -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Tipo de sangre:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_blood_types"
                                v-model="record.payroll_blood_types"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Tipo de sangre -->
                    <!-- Edad -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Edad:</label>
                            <div class="d-flex">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Minimo:</label>
                                        <input type="number" min="1" step="1"
                                            placeholder="Minimo"
                                            class="form-control input-sm"
                                            v-model="record.min_age">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Máximo:</label>
                                        <input type="number" min="1" step="1"
                                            placeholder="Máximo"
                                            class="form-control input-sm"
                                            v-model="record.max_age">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Edad -->
                </div>
            </div>
            <!-- ./Datos personales -->
            <!-- Datos profesionales -->
            <div v-show="record.professional_data">
                <div class="row">
                    <div class="col-md-12">
                        <strong>Datos profesionales</strong>
                    </div>
                    <!-- Grado de instrucción -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Grado de instrucción:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_instruction_degrees"
                                v-model="record.payroll_instruction_degrees"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Grado de instrucción -->
                    <!-- Profesión -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Profesión:</label>
                            <v-multiselect
                                track_by="text"
                                :options="professions"
                                v-model="record.payroll_professions"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Profesión -->
                    <!-- Estudia -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Estudia:</label>
                            <div class="col-12">
                                <div class="custom-control custom-switch">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="is_study"
                                        name="is_study" :value="false"
                                        v-model="record.is_study"
                                    >
                                    <label
                                        class="custom-control-label"
                                        for="is_study"
                                    ></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Estudia -->
                </div>
            </div>
            <!-- ./Datos profesionales -->
            <!-- Datos socioeconómicos -->
            <div v-show="record.socioeconomic_data">
                <div class="row">
                    <div class="col-md-12">
                        <strong>Datos socioeconómicos</strong>
                    </div>
                    <!-- Estado civil -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Estado civil:</label>
                            <v-multiselect
                                track_by="text"
                                :options="marital_status"
                                v-model="record.marital_status"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Estado civil -->
                    <!-- Hijos -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Hijos:</label>
                            <div class="col-12">
                                <div class="custom-control custom-switch">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="has_childs"
                                        name="has_childs"
                                        :value="false"
                                        v-model="record.has_childs"
                                    >
                                    <label
                                        class="custom-control-label"
                                        for="has_childs"
                                    ></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Hijos -->
                    <!-- Edad de los hijos -->
                    <div class="col-md-4" v-if="record.has_childs">
                        <div class="form-group">
                            <label>Rango de edad de los hijos:</label>
                            <div class="d-flex">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Minimo:</label>
                                        <input type="number" min="0" step="1"
                                            placeholder="Minimo"
                                            class="form-control input-sm"
                                            v-model="record.min_childs_age">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Máximo:</label>
                                        <input type="number" min="1" step="1"
                                            placeholder="Máximo"
                                            class="form-control input-sm"
                                            v-model="record.max_childs_age">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Edad de los hijos-->
                    <!-- Nivel de escolaridad-->
                    <div class="col-md-4" v-if="record.has_childs">
                        <div class="form-group" style="z-index: unset;">
                            <label>Nivel de escolaridad:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_schooling_levels"
                                v-model="record.payroll_schooling_levels"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Nivel de escolaridad -->
                </div>
            </div>
            <!-- ./Datos socioeconómicos -->
            <!-- Datos laborales -->
            <div v-show="record.employment_data">
                <div class="row">
                    <div class="col-md-12">
                        <strong>Datos laborales</strong>
                    </div>
                    <!-- Activo -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Activo:</label>
                            <div class="col-12">
                                <div class="custom-control custom-switch">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="is_active"
                                        name="is_active"
                                        :value="false"
                                        v-model="record.is_active"
                                    >
                                    <label class="custom-control-label" for="is_active"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Activo -->
                    <!-- Tipo de Inactividad -->
                    <div class="col-md-4" v-if="record.is_active == false">
                        <div class="form-group" style="z-index: unset;">
                            <label>Tipo de Inactividad:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_inactivity_types"
                                v-model="record.payroll_inactivity_types"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Tipo de Inactividad -->
                    <!-- Tipo de cargo -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Tipo de cargo:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_position_types"
                                v-model="record.payroll_position_types"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Tipo de cargo -->
                    <!-- Cargo -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Cargo:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_positions"
                                v-model="record.payroll_positions"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Cargo -->
                    <!-- Tipo de personal -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Tipo de personal:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_staff_types"
                                v-model="record.payroll_staff_types"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Tipo de personal -->
                    <!-- Tipo de contrato -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Tipo de contrato:</label>
                            <v-multiselect
                                track_by="text"
                                :options="payroll_contract_types"
                                v-model="record.payroll_contract_types"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Tipo de contrato -->
                    <!-- Departamento -->
                    <div class="col-md-4">
                        <div class="form-group" style="z-index: unset;">
                            <label>Departamento:</label>
                            <v-multiselect
                                track_by="text"
                                :options="departments"
                                v-model="record.departments"
                            >
                            </v-multiselect>
                        </div>
                    </div>
                    <!-- ./Departamento -->
                    <!-- Tiempo laborando en la institución/organización -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>
                                Tiempo laborando en la institución/organización:
                            </label>
                            <div class="d-flex">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Minimo:</label>
                                        <input type="number" min="0" step="1"
                                            placeholder="Minimo"
                                            class="form-control input-sm"
                                            v-model="record.min_time_worked">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Máximo:</label>
                                        <input type="number" min="0" step="1"
                                            placeholder="Máximo"
                                            class="form-control input-sm"
                                            v-model="record.max_time_worked">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Tiempo laborando en la institución/organización -->
                    <!-- Total años de servicio -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Total años de servicio:</label>
                            <div class="d-flex">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Minimo:</label>
                                        <input type="number" min="0" step="1"
                                            placeholder="Minimo"
                                            class="form-control input-sm"
                                            v-model="record.min_time_service">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Máximo:</label>
                                        <input type="number" min="0" step="1"
                                            placeholder="Máximo"
                                            class="form-control input-sm"
                                            v-model="record.max_time_service">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Total años de servicio -->
                </div>
            </div>
            <!-- ./Datos laborales -->
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-end">
                        <button
                            type="button"
                            class="btn btn-sm btn-info float-right"
                            title="Buscar registro"
                            data-toggle="tooltip"
                            @click="searchRecords('staffs')"
                        >
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr />
            <v-client-table
                :columns="columns"
                :data="(records.length > 1)? records : []"
                :options="table_options"
            >
            </v-client-table>
        </div>
        <div class="card-footer text-right">
            <button
                @click.prevent="createReport('staffs')"
                :disabled="records.length == 0"
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                title="Generar Reporte"
                type="button"
            >
                <span>Generar reporte</span>
                <i class="fa fa-file-pdf-o"></i>
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
                current: '',
                personal_data: '',
                professional_data: '',
                socioeconomic_data: '',
                employment_data: '',
                payroll_staffs: '',
                payroll_genders: '',
                payroll_disabilities: '',
                payroll_license_degrees: '',
                payroll_blood_types: '',
                payroll_instruction_degrees: '',
                payroll_professions: '',
                marital_status: '',
                payroll_inactivity_types: '',
                payroll_position_types: '',
                payroll_positions: '',
                payroll_staff_types: '',
                payroll_contract_types: '',
                payroll_schooling_levels: '',
                min_age : '',
                max_age : '',
                min_time_worked: '',
                max_time_worked: '',
                min_time_service: '',
                max_time_service: '',
                time_worked : '',
                service_years : '',
                departments: '',
                is_student: false,
                is_active: false,
                has_childs: false,
                min_childs_age : '',
                max_childs_age : '',
                schooling_levels: [],
            },

            errors: [],
            records: [],
            payroll_staffs: [],
            genders: [],
            payroll_disabilities: [],
            payroll_license_degrees: [],
            payroll_blood_types: [],
            payroll_instruction_degrees: [],
            professions: [],
            marital_status: [],
            payroll_inactivity_types: [],
            payroll_position_types: [],
            payroll_positions: [],
            payroll_staff_types: [],
            payroll_contract_types: [],
            departments: [],
            payroll_schooling_levels: [],
            columns: ['payroll_staff']
        };
    },
    props : {
        institution_id : '',
    },
    methods: {
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                current: '',
                payroll_staff_id: ''
            };
        },

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

        createReport(current) {
            const vm = this;
            vm.loading = true;
            //Ordenar los registro por orden alfabético
            vm.records.sort((a, b) => {
            if (a.payroll_staff > b.payroll_staff) {
                return 1;
            }
            if (a.payroll_staff < b.payroll_staff) {
                return -1;
            }
            return 0;
            });
            //end Ordenar los registro por orden alfabético
            let fields = {
                current,
                records: vm.records,
            };
            axios.post(`${window.app_url}/payroll/reports/${current}/create`, fields).then(response => {
                if (typeof response.data.redirect !== 'undefined') {
                    var url = response.data.redirect;
                        window.open(url, '_blank');
                } else {
                    vm.reset();
                }
                vm.loading = false;
            })
            .catch(error => {
                if (typeof error.response != 'undefined') {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 
                            'Acceso Denegado', 
                            'danger', 
                            'screen-error', 
                            error.response.data.message
                        ); 
                    }
                    console.log('error');
                }
                vm.loading = false;
            });
        },
        
        getYearAntiquity(start_date) {
            const vm = this;
            let payroll_staff_year = start_date.split('-')[0];
            let year_now = new Date().getFullYear();
            return year_now - parseInt(payroll_staff_year);
        },

        /**
         * Método que permite realizar las busquedas y filtrado de los registros de la tabla
         *
         * @method    searchRecords
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
         */
        searchRecords(current) {
            const vm = this;
            vm.record.current = current;

            if(!vm.record.payroll_staffs.length > 0){
                    bootbox.alert("Debe agregar al menos un trabajador a la solicitud");
					return false;
				};
            if(vm.record.personal_data && vm.record.max_age && vm.record.min_age  && vm.record.max_age < vm.record.min_age){
                bootbox.alert("El rango minimo de edad del trabajador no debe ser mayor al rango máximo");
                return false;
            };
            if (vm.record.socioeconomic_data && vm.record.has_childs && vm.record.max_childs_age && vm.record.min_childs_age) {
                if (Number(vm.record.max_childs_age) < Number(vm.record.min_childs_age)) {
                    bootbox.alert("El rango minimo de edad de los hijos no debe ser mayor al rango máximo");
                    return false;
                }
            };
            if (vm.record.employment_data) {
                if (
                    vm.record.min_time_worked && 
                    vm.record.max_time_worked && 
                    Number(vm.record.max_time_worked) < Number(vm.record.min_time_worked)
                ) {
                    bootbox.alert("El rango minimo de tiempo laborado no debe ser mayor al rango máximo");
                    return false;
                }
                if (
                    vm.record.min_time_service && 
                    vm.record.max_time_service && 
                    Number(vm.record.max_time_service) < Number(vm.record.min_time_service)
                ) {
                    bootbox.alert("El rango minimo de tiempo de servicio no debe ser mayor al rango máximo");
                    return false;
                }
            }
            vm.record.schooling_levels = vm.payroll_schooling_levels;
            vm.loading = true;
            axios.post(`${window.app_url}/payroll/reports/vue-list`, vm.record).then(response => {
                if (typeof response.data.records !== 'undefined') {
                    vm.columns = ['payroll_staff'];
                    vm.table_options.sortable = ['payroll_staff'];
                    vm.table_options.filterable = ['payroll_staff'];

                    if(vm.record.personal_data){
                        if (vm.record.payroll_genders.length > 0) {
                        vm.columns.push('payroll_gender')
                        vm.table_options.sortable.push('payroll_gender');
                        vm.table_options.filterable.push('payroll_gender');
                        }
                        if (vm.record.payroll_disabilities.length > 0) {
                            vm.columns.push('payroll_disability')
                            vm.table_options.sortable.push('payroll_disability');
                            vm.table_options.filterable.push('payroll_disability');
                        }
                        if (vm.record.payroll_license_degrees.length > 0) {
                            vm.columns.push('payroll_license')
                            vm.table_options.sortable.push('payroll_license');
                            vm.table_options.filterable.push('payroll_license');
                        }
                        if (vm.record.payroll_blood_types.length > 0) {
                            vm.columns.push('payroll_blood_type');
                            vm.table_options.sortable.push('payroll_blood_type');
                            vm.table_options.filterable.push('payroll_blood_type');
                        }
                        if (vm.record.min_age && vm.record.max_age && vm.record.min_age != '' && vm.record.max_age != '') {
                            vm.columns.push('payroll_age');
                            vm.table_options.sortable.push('payroll_age');
                            vm.table_options.filterable.push('payroll_age');
                        }
                    }
                    if(vm.record.professional_data){
                        if (vm.record.payroll_instruction_degrees.length > 0) {
                        vm.columns.push('payroll_instruction_degree');
                        vm.table_options.sortable.push('payroll_instruction_degree');
                        vm.table_options.filterable.push('payroll_instruction_degree');
                        }
                        if (vm.record.payroll_professions.length > 0) {
                            vm.columns.push('payroll_profession');
                            vm.table_options.sortable.push('payroll_profession');
                            vm.table_options.filterable.push('payroll_profession');
                        }
                            if (vm.record.is_study && vm.record.is_study != false) {
                            vm.columns.push('payroll_study');
                            vm.table_options.sortable.push('payroll_study');
                            vm.table_options.filterable.push('payroll_study');
                        }
                    }
                    if(vm.record.socioeconomic_data){
                        if (vm.record.marital_status.length > 0) {
                            vm.columns.push('payroll_marital_status');
                            vm.table_options.sortable.push('payroll_marital_status');
                            vm.table_options.filterable.push('payroll_marital_status');
                        }
                        if (vm.record.has_childs) {
                            vm.columns.push('payroll_childs');
                            vm.table_options.sortable.push('payroll_childs');
                            vm.table_options.filterable.push('payroll_childs');
                        }
                    }
                    if (vm.record.employment_data) {
                        if (!vm.record.is_active && vm.record.payroll_inactivity_types && vm.record.payroll_inactivity_types.length > 0) {
                            vm.columns.push('payroll_inactivity_type');
                            vm.table_options.sortable.push('payroll_inactivity_type');
                            vm.table_options.filterable.push('payroll_inactivity_type');
                        }
                        if (vm.record.payroll_position_types.length > 0) {
                            vm.columns.push('payroll_position_type');
                            vm.table_options.sortable.push('payroll_position_type');
                            vm.table_options.filterable.push('payroll_position_type');
                        }
                        if (vm.record.payroll_positions.length > 0) {
                            vm.columns.push('payroll_position');
                            vm.table_options.sortable.push('payroll_position');
                            vm.table_options.filterable.push('payroll_position');
                        }
                        if (vm.record.payroll_staff_types.length > 0) {
                            vm.columns.push('payroll_staff_type');
                            vm.table_options.sortable.push('payroll_staff_type');
                            vm.table_options.filterable.push('payroll_staff_type');
                        }
                        if (vm.record.payroll_contract_types.length > 0) {
                            vm.columns.push('payroll_contract_type');
                            vm.table_options.sortable.push('payroll_contract_type');
                            vm.table_options.filterable.push('payroll_contract_type');
                        }
                        if (vm.record.min_time_worked && vm.record.min_time_worked != '' && vm.record.max_time_worked && vm.record.max_time_worked != '') {
                            vm.columns.push('time_worked');
                            vm.table_options.sortable.push('time_worked');
                            vm.table_options.filterable.push('time_worked');
                        }
                        if (vm.record.min_time_service && vm.record.min_time_service != '' && vm.record.max_time_service && vm.record.max_time_service != '') {
                            vm.columns.push('time_service');
                            vm.table_options.sortable.push('time_service');
                            vm.table_options.filterable.push('time_service');
                        }
                        if (vm.record.departments.length > 0) {
                            vm.columns.push('department');
                            vm.table_options.sortable.push('department');
                            vm.table_options.filterable.push('department');
                        }
                        if (vm.record.employment_data) {
                            vm.columns.push('payroll_is_active');
                            vm.table_options.sortable.push('payroll_is_active');
                            vm.table_options.filterable.push('payroll_is_active');
                        }
                    }

                    vm.records = response.data.records;
                }
                vm.loading = false;
            }).catch(error => {
                vm.errors = [];

                if (typeof error.response != 'undefined') {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(
                                error.response.data.errors[index][0]
                            );
                        }
                    }
                }
                vm.loading = false;
            });
        },

        async getDepartments() {
            let vm = this;
            vm.departments = [];
            if (vm.institution_id) {
                await axios.get(`${window.app_url}/get-departments/${vm.institution_id}`)
                .then(response => {
                    vm.departments = response.data;
                }).catch(error => {
                    console.error(error);
                });
            }    
        },

        addAllToOptions() {
            const vm = this;
            vm.payroll_staffs = vm.payroll_staffs.filter(el => el.id != '');
            vm.payroll_staffs.push({'id':'todos', 'text':'Todos'});

            vm.genders = vm.genders.filter(el => el.id != '');
            vm.genders.push({'id':'todos', 'text':'Todos'});

            vm.payroll_disabilities = vm.payroll_disabilities.filter(el => el.id != '');
            vm.payroll_disabilities.push({'id':'todos', 'text':'Todos'});

            vm.payroll_license_degrees = vm.payroll_license_degrees.filter(el => el.id != '');
            vm.payroll_license_degrees.push({'id':'todos', 'text':'Todos'});

            vm.payroll_blood_types = vm.payroll_blood_types.filter(el => el.id != '');
            vm.payroll_blood_types.push({'id':'todos', 'text':'Todos'});

            vm.payroll_instruction_degrees = vm.payroll_instruction_degrees.filter(el => el.id != '');
            vm.payroll_instruction_degrees.push({'id':'todos', 'text':'Todos'});

            vm.professions = vm.professions.filter(el => el.id != '');
            vm.professions.push({'id':'todos', 'text':'Todos'});

            vm.marital_status = vm.marital_status.filter(el => el.id != '');
            vm.marital_status.push({'id':'todos', 'text':'Todos'});

            setTimeout(() => {
                vm.payroll_schooling_levels = vm.payroll_schooling_levels.filter(el => el.id != '');
                //vm.payroll_schooling_levels.push({'id':'todos', 'text':'Todos'});
                
                vm.payroll_inactivity_types = vm.payroll_inactivity_types.filter(el => el.id != '');
                vm.payroll_inactivity_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_position_types = vm.payroll_position_types.filter(el => el.id != '');
                vm.payroll_position_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_positions = vm.payroll_positions.filter(el => el.id != '');
                vm.payroll_positions.push({'id':'todos', 'text':'Todos'});

                vm.payroll_staff_types = vm.payroll_staff_types.filter(el => el.id != '');
                vm.payroll_staff_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_contract_types = vm.payroll_contract_types.filter(el => el.id != '');
                vm.payroll_contract_types.push({'id':'todos', 'text':'Todos'});

                vm.departments = vm.departments.filter(el => el.id != '');
                vm.departments.push({'id':'todos', 'text':'Todos'});
            }, 1000);
        },
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            payroll_staff: 'Trabajador',
            payroll_gender: 'Genero',
            payroll_disability: 'Discapacidad',
            payroll_license: 'Licencia',
            payroll_blood_type: 'Tipo de sangre',
            payroll_age: 'Edad',
            payroll_instruction_degree: 'Grado de instrucción',
            payroll_profession: 'Profesión',
            payroll_study: 'Estudia',
            payroll_marital_status: 'Estado civil',
            payroll_childs: 'Hijos',
            payroll_is_active: 'Activo',
            payroll_inactivity_type: 'Tipo de Inactividad',
            payroll_position_type: 'Tipo de cargo',
            payroll_position: 'Cargo',
            payroll_staff_type: 'Tipo de personal',
            payroll_contract_type: 'Tipo de contrato',
            department: 'Departamento',
            time_worked: 'Tiempo laborando en la institución/organización',
            time_service: 'Total años de servicio',
        };
        vm.table_options.sortable = [
            'payroll_staff'
        ];
        vm.table_options.filterable = [
            'payroll_staff'
        ];
    },
    async mounted() {
        const vm = this;
        await vm.getPayrollStaffs();       
        await vm.getGenders();
        await vm.getPayrollDisabilities();
        await vm.getPayrollLicenseDegrees();
        await vm.getPayrollBloodTypes();
        await vm.getPayrollInstructionDegrees();
        await vm.getProfessions();
        await vm.getMaritalStatus();
        await vm.getPayrollInactivityTypes();
        await vm.getPayrollPositionTypes();
        await vm.getPayrollPositions();
        await vm.getPayrollStaffTypes();
        await vm.getPayrollContractTypes();
        await vm.getDepartments();
        await vm.addAllToOptions();
        await vm.getPayrollSchoolingLevels()
        vm.initRecords(vm.route_list, '');
    }
};
</script>
