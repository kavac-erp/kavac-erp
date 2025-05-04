<template>
    <section id="PayrollStaffForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                    Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button" class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffName">
                    <div class="form-group is-required">
                        <label>Nombres</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.first_name"
                        />
                        <input type="hidden" v-model="record.id" v-is-text>
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffLastName">
                    <div class="form-group is-required">
                        <label>Apellidos</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.last_name"
                            v-is-text
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffNationality">
                    <div class="form-group is-required">
                        <label>Nacionalidad</label>
                        <select2
                            :options="payroll_nationalities"
                            v-model="record.payroll_nationality_id"
                        ></select2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffIdNumber">
                    <div class="form-group is-required">
                        <label>Cédula de Identidad</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.id_number"
                            v-is-digits
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffPassport">
                    <div class="form-group">
                        <label>Pasaporte</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.passport"
                            v-is-digits
                            maxlength="20"
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffRif">
                    <div class="form-group is-required">
                        <label>Rif</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.rif"
                            maxlength="10"
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffEmail">
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input
                            type="email"
                            class="form-control input-sm"
                            v-model="record.email"
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffBirthDate">
                    <div class="form-group is-required">
                        <label>Fecha de Nacimiento</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="record.birthdate"
                        />
                    </div>
                </div>
                <div
                    class="col-md-4"
                    id="helpStaffGender"
                    v-if="genders.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Género</label>
                        <select2
                            :options="genders"
                            v-model="record.payroll_gender_id"
                        ></select2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffEmergencyContact">
                    <div class="form-group">
                        <label>
                            Nombres y Apellidos de la Persona de Contacto
                        </label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.emergency_contact"
                            v-is-text
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffEmergencyContactPhone">
                    <div class="form-group">
                        <label>Teléfono de la Persona de Contacto</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            placeholder="+00-000-0000000"
                            v-model="record.emergency_phone"
                            v-input-mask
                            data-inputmask="'mask': '+99-999-9999999'"
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group" id="helpStaffDisability">
                        <label>¿Posee una Discapacidad?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador posee una discapacidad o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="
                                        custom-control-input sel_has_disability
                                    "
                                    id="has_disability"
                                    v-model="record.has_disability"
                                    :value="true"
                                    name="has_disability"
                                >
                                <label
                                    class="custom-control-label"
                                    for="has_disability"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="col-md-4"
                    id="helpStaffDisabilityName"
                    v-if="
                        record.has_disability && payroll_disabilities.length > 0
                    "
                >
                    <div class="form-group is-required">
                        <label>Discapacidad</label>
                        <select2
                            :options="payroll_disabilities"
                            v-model="record.payroll_disability_id"
                        >
                        </select2>
                    </div>
                </div>
                <div
                    class="col-md-4"
                    id="helpStaffBloodType"
                    v-if="payroll_blood_types.length > 0"
                >
                    <div class="form-group">
                        <label>Tipo de Sangre</label>
                        <select2
                            :options="payroll_blood_types"
                            v-model="record.payroll_blood_type_id"
                        >
                        </select2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffSocialSecurity">
                    <div class="form-group">
                        <label>Seguro Social</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.social_security"
                            title="Indique el número de seguro social"
                        />
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffDiverLicense">
                    <div class="form-group">
                        <label>¿Posee Licencia de Conducir?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="
                                    Indique si el trabajador posee licencia de conducir o no
                                "
                            >
                                <input
                                    type="checkbox"
                                    class="custom-control-input sel_has_driver_license"
                                    id="has_driver_license"
                                    v-model="record.has_driver_license"
                                    :value="true"
                                    name="has_driver_license"
                                >
                                <label
                                    class="custom-control-label"
                                    for="has_driver_license"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="col-md-4"
                    id="helpStaffLicenseDegree"
                    v-if="
                        record.has_driver_license
                        && payroll_license_degrees.length > 0
                    "
                >
                    <div class="form-group is-required">
                        <label>Grado de Licencia de Conducir</label>
                        <select2
                            :options="payroll_license_degrees"
                            v-model="record.payroll_license_degree_id"
                        >
                        </select2>
                    </div>
                </div>
            </div>
            <br>
            <div>
                <label>Dirección de Habitación</label>
            </div>
            <div class="row">
                <div
                    class="col-md-4"
                    id="helpStaffCountry"
                    v-if="countries.length > 0"
                >
                    <div class="form-group is-required">
                        <label>País</label>
                        <select2
                            :options="countries"
                            @input="getEstates()"
                            v-model="record.country_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffState">
                    <div class="form-group is-required">
                        <label>Estado</label>
                        <select2
                            :options="estates"
                            @input="getMunicipalities()"
                            v-model="record.estate_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffMunicipality">
                    <div class="form-group is-required">
                        <label>Municipio</label>
                        <select2
                            :options="municipalities"
                            @input="getParishes()"
                            v-model="record.municipality_id"
                        ></select2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4" id="helpStaffParish">
                    <div class="form-group is-required">
                        <label>Parroquia</label>
                        <select2
                            :options="parishes"
                            v-model="record.parish_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpStaffAddress">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            v-model="record.address"
                        />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12" id="helpStaffMedicalHistory">
                    <div class="form-group">
                        <label>Historial Médico</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="medical_history"
                            data-toggle="tooltip"
                            title="Indique el historial médico"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="2"
                            v-model="record.medical_history"
                        >
                        </ckeditor>
                    </div>
                </div>
            </div>

            <hr>
            <h6
                class="card-title"
                id="helpStaffUniformSize"
            >
                Talla de uniforme
                <i
                    class="fa fa-plus-circle cursor-pointer"
                    @click="addUniformSize()"
                ></i>
            </h6>
            <div class="row" v-for="(uniform, u) in record.uniform_sizes" :key="u">
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="uniform_name">Nombre:</label>
                        <input
                            type="text"
                            id="uniform_name"
                            class="form-control input-sm"
                            data-toggle="tooltip"
                            title="Requerimiento del solicitante"
                            v-model="uniform.name"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label for="uniform_name">Talla:</label>
                        <input
                            type="text"
                            id="uniform_name"
                            class="form-control input-sm"
                            data-toggle="tooltip"
                            title="Requerimiento del solicitante"
                            v-model="uniform.size"
                        >
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <button
                            class="mt-4 btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(u, record.uniform_sizes)"
                            title="Eliminar este dato" data-toggle="tooltip"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            <h6 class="card-title" id="helpStaffPhone">
                Números Telefónicos
                <i class="fa fa-plus-circle cursor-pointer" @click="addPhone"></i>
            </h6>
            <div class="row phone-row" v-for="(phone, i) in record.phones" :key="i">
                <div class="col-3">
                    <div class="form-group is-required">
                        <select
                            data-toggle="tooltip"
                            v-model="phone.type"
                            class="select2"
                            title="
                                Seleccione el tipo de número telefónico
                            "
                            :data-phone-index="i"
                        >
                            <option value="">Seleccione...</option>
                            <option value="M">Móvil</option>
                            <option value="T">Teléfono</option>
                            <option value="F">Fax</option>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group is-required">
                        <input
                            type="text"
                            placeholder="Cod. Area"
                            data-toggle="tooltip"
                            title="Indique el código de área"
                            v-model="phone.area_code"
                            class="form-control input-sm" v-is-digits
                        >
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <input
                            type="text"
                            placeholder="Número"
                            data-toggle="tooltip"
                            title="Indique el número telefónico"
                            v-model="phone.number"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <input
                            type="text"
                            placeholder="Extensión"
                            data-toggle="tooltip"
                            title="Indique la extención telefónica (opcional)"
                            v-model="phone.extension"
                            class="form-control input-sm"
                            v-is-digits
                        >
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(i, record.phones)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip" type="button"
                title="Borrar datos del formulario"
                @click="reset()"
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
                @click="createRecord('payroll/staffs')"
                class="btn btn-success btn-icon btn-round"
                data-toggle="tooltip"
                title="Guardar registro"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>

<script>
    export default {
        props: {
            payroll_staff_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    first_name: '',
                    last_name: '',
                    payroll_nationality_id: '',
                    id_number: '',
                    passport: '',
                    rif: '',
                    email: '',
                    birthdate: '',
                    payroll_gender_id: '',
                    has_disability: '',
                    payroll_disability_id: '',
                    payroll_blood_type_id: '',
                    social_security: '',
                    has_driver_license: '',
                    payroll_license_degree_id: '',
                    emergency_contact: '',
                    emergency_phone: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    parish_id: '',
                    address: '',
                    medical_history: '',
                    uniform_sizes: [],
                    phones: [],
                },
                errors: [],
                payroll_nationalities: [],
                genders: [],
                countries: [],
                estates: [],
                municipalities: [],
                parishes: [],
                payroll_license_degrees: [],
                payroll_blood_types: [],
                payroll_disabilities: [],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                const vm = this;
                vm.record = {
                    id: '',
                    first_name: '',
                    last_name: '',
                    payroll_nationality_id: '',
                    id_number: '',
                    passport: '',
                    rif: '',
                    email: '',
                    birthdate: '',
                    payroll_gender_id: '',
                    has_disability: '',
                    payroll_disability_id: '',
                    payroll_blood_type_id: '',
                    social_security: '',
                    has_driver_license: '',
                    payroll_license_degree_id: '',
                    emergency_contact: '',
                    emergency_phone: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    parish_id: '',
                    address: '',
                    medical_history: '',
                    uniform_sizes: [],
                    phones: [],
                };
            },

            async getStaff() {
                let vm = this;
                await axios.get(`${window.app_url}/payroll/staffs/${vm.payroll_staff_id}`).then(response => {
                    let data = response.data.record;
                    vm.record = {
                        id: data.id,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        payroll_nationality_id: data.payroll_nationality_id,
                        id_number: data.id_number,
                        passport: data.passport ? data.passport : '',
                        rif: data.rif ? data.rif : '',
                        email: data.email ? data.email : '',
                        birthdate: data.birthdate,
                        payroll_gender_id: data.payroll_gender_id,
                        has_disability: data.has_disability ? data.has_disability : false,
                        payroll_disability_id: data.payroll_disability_id,
                        payroll_blood_type_id: data.payroll_blood_type_id,
                        social_security: data.social_security,
                        has_driver_license: data.has_driver_license
                            ? data.has_driver_license : false,
                        payroll_license_degree_id: data.payroll_license_degree_id,
                        emergency_contact: data.emergency_contact
                            ? data.emergency_contact : '',
                        emergency_phone: data.emergency_phone
                            ? data.emergency_phone : '',
                        country_id: data.country_id,
                        estate_id: data.estate_id,
                        municipality_id: data.municipality_id,
                        parish_id: data.parish_id,
                        address: data.address,
                        medical_history: data.medical_history
                            ? data.medical_history : '',
                        uniform_sizes: data.payroll_staff_uniform_size
                            ? data.payroll_staff_uniform_size : [],
                        phones: data.phones ? data.phones : [],
                    }
                    vm.record.parish = data.parish;
                    vm.record.country_id = vm.record.parish.municipality.estate.country_id;
                });
            },

            /**
             * Obtiene los Estados del Pais seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  William Páez <wpaez@cenditel.gob.ve> | paez.william8@gmail.com
             */
            async getEstates() {
                const vm = this;
                vm.estates = [];
                if (vm.record.country_id) {
                    await axios.get(`${window.app_url}/get-estates/${vm.record.country_id}`).then(response => {
                        vm.estates = response.data;
                    });
                    if (vm.record.id) {
                        vm.record.estate_id = vm.record.parish.municipality.estate_id;
                    }
                }
            },

            /**
             * Obtiene los Municipios del Estado seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  William Páez <wpaez@cenditel.gob.ve> | paez.william8@gmail.com
             */
            async getMunicipalities() {
                const vm = this;
                vm.municipalities = [];
                if (vm.record.estate_id) {
                    await axios.get(`${window.app_url}/get-municipalities/${vm.record.estate_id}`).then(response => {
                        vm.municipalities = response.data;
                    });
                    if (vm.record.id) {
                        vm.record.municipality_id = vm.record.parish.municipality_id;
                    }
                }
            },

            /**
             * Obtiene las Parroquias del Municipio seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  William Páez <wpaez@cenditel.gob.ve> | paez.william8@gmail.com
             */
            async getParishes() {
                const vm = this;
                vm.parishes = [];
                if (vm.record.municipality_id) {
                    await axios.get(`${window.app_url}/get-parishes/${vm.record.municipality_id}`).then(response => {
                        vm.parishes = response.data;
                    });
                    if (vm.record.id) {
                        vm.record.parish_id = vm.record.parish.id;
                    }
                }
            },

            /**
             * Agrega una nueva columna para las tallas de uniformes
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            addUniformSize() {
                const vm = this;
                if (vm.record.uniform_sizes.length == 0) {
                    vm.record.uniform_sizes.push({
                        name: 'Camisa',
                        size: '',
                        payroll_staff_id: '',
                    });
                    vm.record.uniform_sizes.push({
                        name: 'Pantalón',
                        size: '',
                        payroll_staff_id: '',
                    });
                    vm.record.uniform_sizes.push({
                        name: 'Calzado',
                        size: '',
                        payroll_staff_id: '',
                    });
                } else {
                    vm.record.uniform_sizes.push({
                        name: '',
                        size: '',
                        payroll_staff_id: '',
                    });
                }
            },
        },
        async created() {
            this.loading = true;
            await this.getPayrollNationalities();
            await this.getGenders();
            await this.getCountries();
            await this.getEstates();
            await this.getMunicipalities();
            await this.getPayrollLicenseDegrees();
            await this.getPayrollBloodTypes();
            await this.getPayrollDisabilities();
            this.loading = false;
        },
        async mounted() {
            const vm = this;
            vm.loading = true;
            if (vm.payroll_staff_id) {
                await vm.getStaff();
            } else {
                this.record.has_disability = false;
                this.record.has_driver_license = false;
                this.record.phones = [];
                this.record.uniform_sizes = [];
            }
            vm.loading = false;
        }
    };
</script>
