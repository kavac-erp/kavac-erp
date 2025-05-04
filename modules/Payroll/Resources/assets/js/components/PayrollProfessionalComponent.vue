<template>
    <section id="PayroProfessionalForm">
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
                <div class="col-md-4" id="helpProfessionalStaff">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2
                            :options="payroll_professional"
                            v-if="payroll_professional.length > 0"
                            v-model="record.payroll_staff_id"
                            :disabled="isEditMode"
                        >
                        </select2>
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <div class="col-md-4" id="helpProfessionalInstructionDegree">
                    <div class="form-group is-required">
                        <label>Grado de Instrucción:</label>
                        <select2
                            :options="payroll_instruction_degrees"
                            v-model="record.payroll_instruction_degree_id"
                        >
                        </select2>
                    </div>
                </div>
            </div>

            <hr>
            <h6 class="card-title" id="helpProfessionalUniversityStudy">
                Estudios Universitarios
                <i
                    class="fa fa-plus-circle cursor-pointer"
                    @click="addPayrollStudies"
                ></i>
            </h6>
            <div
                class="row"
                v-for="(payroll_study, index) in record.payroll_studies"
                :key="index"
            >
                <div class="col-3" id="helpProfessionalUniversity">
                    <div class="form-group is-required">
                        <label>Nombre de la Universidad:</label>
                        <input type="text" class="form-control input-sm"
                            v-model="payroll_study.university_name"/>
                    </div>
                </div>
                <div class="col-2" id="helpProfessionalGraduationYear">
                    <div class="form-group is-required">
                        <label>Año de Graduación:</label>
                        <input
                            type="date"
                            :max="getCurrentDate()"
                            class="form-control input-sm"
                            v-model="payroll_study.graduation_year"
                        />
                    </div>
                </div>
                <div
                    class="col-3"
                    id="helpProfessionalStudyType"
                    v-if="payroll_study_types.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Tipo de Estudio:</label>
                        <select2
                            :options="payroll_study_types"
                            v-model="payroll_study.payroll_study_type_id"
                        >
                        </select2>
                    </div>
                </div>
                <div
                    class="col-3"
                    id="helpProfessionalProfession"
                    v-if="professions.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Profesión:</label>
                        <select2
                            :options="professions"
                            v-model="payroll_study.profession_id"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <br>
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(index, record.payroll_studies)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>

            <hr>
            <h6 class="card-title" id="helpProfessionalUniversityStudy">
                Estudios en proceso
            </h6>
            <div class="row">
                <div class="col-md-4" id="helpProfessionalIsStudent">
                    <div class="form-group">
                        <label>¿Es Estudiante?</label>
                        <div class="col-md-12">
                            <div
                                class="custom-control custom-switch"
                                data-toggle="tooltip"
                                title="Indique si el trabajador es estudiante o no">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="is_student"
                                    v-model="record.is_student"
                                    :value="true"
                                    name="is_student"
                                >
                                <label
                                    class="custom-control-label"
                                    for="is_student"
                                ></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none" id="block_student">
                <div
                    class="col-md-4"
                    id="helpProfessionalTypeStudy"
                    v-if="payroll_study_types.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Tipo de Estudio:</label>
                        <select2
                            :options="payroll_study_types"
                            v-model="record.payroll_study_type_id"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpProfessionalProgramName">
                    <div class="form-group is-required">
                        <label>Nombre del Programa de Estudio:</label>
                        <input type="text" class="form-control input-sm"
                            v-model="record.study_program_name"/>
                    </div>
                </div>
                <div class="col-md-4" id="helpProfessionalClassSchedule">
                    <div class="form-group">
                        <label for="class_schedules">
                            Horario de Clase:
                        </label>
                        <div v-for="(document, index) in payroll_class_schedule.documents" :key="index">
                            <a
                                :href="`/${document.url}`"
                                target="_blank"
                            >
                                Documento
                            </a>
                            <button
                                class="btn btn-sm btn-danger btn-action"
                                type="button"
                                @click="
                                    deleteDocument(
                                        index,
                                        payroll_class_schedule.documents
                                    )
                                "
                                title="Eliminar este dato"
                                data-toggle="tooltip"
                            >
                                <i class="fa fa-minus-circle"></i>
                            </button>
                        </div>
                        <input
                            id="class_schedules"
                            name="class_schedules"
                            type="file"
                            accept=".odt, .pdf"
                            @change="processFiles($event)"
                            multiple
                        >
                    </div>
                </div>
            </div>

            <hr>

            <h6 class="card-title" id="helpAddProfessionalLanguage">
                Detalles de Idiomas
                <i
                    class="fa fa-plus-circle cursor-pointer"
                    @click="addPayrollLanguages"
                ></i>
            </h6>
            <div
                class="row"
                v-for="(payroll_language, index) in record.payroll_languages"
                :key="index"
            >
                <div
                    class="col-3"
                    id="helpProfessionalLanguage"
                    v-if="payroll_languages.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Idioma:</label>
                        <select2 :options="payroll_languages"
                            v-model="payroll_language.payroll_lang_id">
                        </select2>
                    </div>
                </div>
                <div
                    class="col-3"
                    id="helpProfessionalLanguageLevel"
                    v-if="payroll_language_levels.length > 0"
                >
                    <div class="form-group is-required">
                        <label>Nivel de Idioma:</label>
                        <select2 :options="payroll_language_levels"
                            v-model="payroll_language.payroll_language_level_id">
                        </select2>
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <br>
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(index, record.payroll_languages)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>

            <hr>
            <h6 class="card-title" id="helpProfessionalAcks">
                Capacitación y Reconocimientos
                <i
                    class="fa fa-plus-circle cursor-pointer"
                    @click="addPayrollCouAckFiles"
                ></i>
            </h6>
            <!--Formulario de registros nuevos-->
            <div
                class="row"
                v-for="(payroll_cou_ack_file, index) in record.payroll_cou_ack_files"
                :key="index"
            >
                <div class="col-md-5">
                    <div class="col-md-12" id="helpProfessionalCourseName">
                        <div class="form-group is-required">
                            <label>Nombre del Curso:</label>
                            <input type="text" class="form-control input-sm"
                                v-model="payroll_cou_ack_file.course_name"/>
                        </div>
                    </div>
                    <div class="col-md-12" id="helpProfessionalCourse">
                        <div class="form-group">
                            <label>Curso:</label>
                            <div v-show="payroll_cou_ack_file.course_file_url">
                                <a
                                    type="button"
                                    :href="`${app_url}/${payroll_cou_ack_file.course_file_url}`"
                                    target="_blank"
                                >
                                    <i class="fa fa-cloud-download fa-2x"></i>
                                    <span>Documento</span>
                                </a>
                            </div>
                            <br>
                            <input
                                :id="'course_'+index"
                                type="file"
                                accept=".png, .jpg, .pdf, .odt"
                                @change="processFile($event, index)"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="col-md-12" id="helpProfessionalAckName">
                        <div class="form-group">
                            <label>Nombre del Reconocimiento:</label>
                            <input type="text" class="form-control input-sm"
                                v-model="payroll_cou_ack_file.ack_name"/>
                        </div>
                    </div>
                    <div class="col-md-12" id="helpProfessionalAck">
                        <div class="form-group">
                            <label>Reconocimiento:</label>
                            <div v-show="payroll_cou_ack_file.ack_file_url">
                                <a
                                    type="button"
                                    :href="`${app_url}/${payroll_cou_ack_file.ack_file_url}`"
                                    target="_blank"
                                >
                                    <i class="fa fa-cloud-download fa-2x"></i>
                                    <span>Documento</span>
                                </a>
                            </div>
                            <br>
                            <input
                                :id="'acknowledgment_'+index"
                                type="file"
                                accept=".png, .jpg, .pdf, .odt"
                                @change="processFile($event, index)"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="form-group">
                        <br>
                        <button
                            class="btn btn-sm btn-danger btn-action"
                            type="button"
                            @click="removeRow(index, record.payroll_cou_ack_files)"
                            title="Eliminar este dato"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
                <hr class="col-md-9">
            </div>
        </div>

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
                @click="createRecord('payroll/professionals')"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
    var formData = new FormData();
    export default {
        props: {
            payroll_professional_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    payroll_staff_id: '',
                    payroll_instruction_degree_id: '',
                    instruction_degree_name: '',
                    is_student: '',
                    payroll_study_type_id: '',
                    study_program_name: '',
                    class_schedule_ids: [],
                    professions: [],
                    payroll_languages: [],
                    payroll_cou_ack_files: [],
                    payroll_studies:[],
                },
                errors: [],
                payroll_professional: [],
                payroll_instruction_degrees: [],
                professions: [],
                json_professions: [],
                payroll_study_types: [],
                payroll_languages: [],
                payroll_language_levels: [],
                payroll_class_schedule: '',
                payroll_cou_ack_files: [],
                isEditMode: false,
            }
        },
        methods: {
            /**
             * Método que carga los datos profesionales del empleado.
             */
            getProfessional() {
                const vm = this;
                axios.get(`${window.app_url}/payroll/professionals/${vm.payroll_professional_id}`).then(response => {
                    vm.record.id = response.data.record.id;
                    vm.record.payroll_staff_id = response.data.record.payroll_staff_id;
                    vm.record.payroll_instruction_degree_id = response.data.record.payroll_instruction_degree_id;
                    vm.record.instruction_degree_name = response.data.record.instruction_degree_name;
                    vm.record.is_student = response.data.record.is_student;
                    vm.record.payroll_study_type_id = response.data.record.payroll_study_type_id;
                    vm.record.study_program_name = response.data.record.study_program_name;
                    vm.record.class_schedule = response.data.record.class_schedule;
                    vm.record.professions = response.data.record.professions;
                    vm.record.payroll_staff = response.data.record.payroll_staff;
                    vm.record.payroll_study_type = response.data.record.payroll_study_type;
                    vm.record.payroll_instruction_degree = response.data.record.payroll_instruction_degree;
                    for (const a in response.data.record.payroll_languages) {
                        vm.record.payroll_languages.push({
                            payroll_lang_id: response.data.record.payroll_languages[a].id,
                            payroll_language_level_id: response.data.record.payroll_languages[a].pivot.payroll_language_level_id,
                        });
                    }
                    vm.payroll_class_schedule = (response.data.record.payroll_class_schedule) ? response.data.record.payroll_class_schedule : {};

                    // Bloquear el select del trabajador cuando esté en modo edit.
                    this.isEditMode = true;

                    for (const a in response.data.record.payroll_course.payroll_course_files) {
                        var payroll_course_file = response.data.record.payroll_course.payroll_course_files[a];
                        var payroll_acknowledgment_file = response.data.record.payroll_acknowledgment.payroll_acknowledgment_files[a];
                        this.record.payroll_cou_ack_files.push({
                            course_name: payroll_course_file.name,
                            course: {
                                id: (payroll_course_file.image) ? payroll_course_file.image.id : payroll_course_file.documents[0].id,
                                file_type: (payroll_course_file.image) ? 'img' : 'doc',
                            },
                            course_file_url: (payroll_course_file.image) ? payroll_course_file.image.url : payroll_course_file.documents[0].url,
                            ack_name: (typeof(payroll_acknowledgment_file) != 'undefined') ? payroll_acknowledgment_file.name : '',
                            ack: {
                                id: (typeof(payroll_acknowledgment_file) != 'undefined') ? (payroll_acknowledgment_file.image) ? payroll_acknowledgment_file.image.id :  payroll_acknowledgment_file.documents[0].id ?? '' : '',
                                file_type: (typeof(payroll_acknowledgment_file) != 'undefined') ? (payroll_acknowledgment_file.image)?'img' : 'doc' : '',
                            },
                            ack_file_url: (typeof(payroll_acknowledgment_file) != 'undefined') ? (payroll_acknowledgment_file.image) ? payroll_acknowledgment_file.image.url : payroll_acknowledgment_file.documents[0].url?? '' : '',
                        });
                    }
                    vm.record.payroll_studies = response.data.record.payroll_studies;
                });
            },

            /**
             * Limpia los datos del formulario.
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            reset() {
                this.record = {
                    id: '',
                    payroll_staff_id: '',
                    payroll_instruction_degree_id: '',
                    instruction_degree_name: '',
                    is_student: false,
                    payroll_study_type_id: '',
                    study_program_name: '',
                    class_schedule_ids: [],
                    professions: [],
                    payroll_languages: [],
                    payroll_cou_ack_files: [],
                    payroll_studies: [],
                };
            },

            /**
             * Agrega una nueva Fila para el registro de idiomas
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            addPayrollLanguages() {
                this.record.payroll_languages.push({
                    payroll_lang_id: '',
                    payroll_language_level_id: '',
                });
            },

            /**
             * Agrega una nueva fila para el registro de cursos y reconocimientos
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            addPayrollCouAckFiles() {
                this.record.payroll_cou_ack_files.push({
                    course_name: '',
                    course: {
                        id: '',
                        file_type: '',
                    },
                    course_file_url: '',
                    ack_name: '',
                    ack: {
                        id: '',
                        file_type: '',
                    },
                    ack_file_url: '',
                });
            },

            /**
             * Agrega una nueva Fila para el registro de estudios
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            addPayrollStudies() {
                this.record.payroll_studies.push({
                    university_name: '',
                    graduation_year: '',
                    payroll_study_type_id: '',
                    profession_id: '',
                });
            },

            /**
             * Guarda multiples archivos y guarda los id en la variable correspondiente
             * del objeto record
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            processFiles(event) {
                const vm = this;
                var inputFiles = document.querySelector(`#${event.currentTarget.id}`);
                for (var x = 0; x < inputFiles.files.length; x++) {
                    formData.append(`documents[${x}]`, inputFiles.files[x]);
                }
                axios.post(`${window.app_url}/upload-document`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    vm.record.class_schedule_ids = response.data.document_ids;
                    vm.showMessage(
                        'custom', 'Éxito', 'success', 'screen-ok',
                        'Documento cargado de manera exitosa.'
                    );
                }).catch(error => {
                    vm.errors = [];
                    if (typeof(error.response) != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
            },

            /**
             * Guarda un archivo y guarda el id en la variable correspondiente del
             * objeto record
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            processFile(event, index) {
                const vm = this;
                var inputFile = document.querySelector(`#${event.currentTarget.id}`);
                const size_file = (inputFile.files[0].size / 1024 / 1024).toFixed(2);
                if(size_file > 2){
                    alert("El tamaño del archivo no debe ser mayor a 2 MB");
                    return false;
                }else{
                    if( inputFile.files[0].type.match('image/png') || inputFile.files[0].type.match('image/jpeg') || inputFile.files[0].type.match('image/jpg') ) {
                        formData.append("image", inputFile.files[0]);
                        axios.post(`${window.app_url}/upload-image`, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(response => {
                            if( inputFile.id.match(`course_${index}`) ) {
                                vm.record.payroll_cou_ack_files[index].course.id = response.data.image_id;
                                vm.record.payroll_cou_ack_files[index].course.file_type = 'img';
                            }
                            else {
                                vm.record.payroll_cou_ack_files[index].ack.id = response.data.image_id;
                                vm.record.payroll_cou_ack_files[index].ack.file_type = 'img';
                            }
                            vm.showMessage(
                                'custom', 'Éxito', 'success', 'screen-ok',
                                'Imagen cargada de manera exitosa.'
                            );
                        }).catch(error => {
                            vm.errors = [];
                            if (typeof(error.response) != "undefined") {
                                for (var index in error.response.data.errors) {
                                    if (error.response.data.errors[index]) {
                                        vm.errors.push(error.response.data.errors[index][0]);
                                    }
                                }
                            }
                        });
                    }
                    else {
                        formData.append(`documents[${0}]`, inputFile.files[0]);
                        axios.post(`${window.app_url}/upload-document`, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(response => {
                            if( inputFile.id.match(`course_${index}`) ) {
                                vm.record.payroll_cou_ack_files[index].course.id = response.data.document_ids[0].id;
                                vm.record.payroll_cou_ack_files[index].course.file_type = 'doc';
                            }
                            else {
                                vm.record.payroll_cou_ack_files[index].ack.id = response.data.document_ids[0].id;
                                vm.record.payroll_cou_ack_files[index].ack.file_type = 'doc';
                            }
                            vm.showMessage(
                                'custom', 'Éxito', 'success', 'screen-ok',
                                'Documento cargado de manera exitosa.'
                            );
                        }).catch(error => {
                            vm.errors = [];
                            if (typeof(error.response) != "undefined") {
                                for (var index in error.response.data.errors) {
                                    if (error.response.data.errors[index]) {
                                        vm.errors.push(error.response.data.errors[index][0]);
                                    }
                                }
                            }
                        });
                    }
                }
            },

            /**
             * Elimina un documento
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            deleteDocument(index, documents) {
                axios.delete(`${window.app_url}/upload-document/${documents[index].id}`).then(response => {
                    documents.splice(index, 1);
                });
            },

            /**
             * Elimina una fila de capacitación y reconocimientos
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             */
            deletePayrollCouAckFile() {

            },

            getCurrentDate() {
                let date = new Date().toISOString().split("T")[0];
                return date;
            }
        },
        async created() {
            this.loading = true;
            this.record.is_student = false;
            this.record.payroll_languages = [];
            this.record.professions = [];
            this.record.payroll_cou_ack_files = [];
            this.record.payroll_studies = [];
            //this.getPayrollStaffs();
            await this.getPayrollInstructionDegrees();
            await this.getProfessions();
            await this.getPayrollStudyTypes();
            await this.getPayrollLanguages();
            await this.getPayrollLanguageLevels();
            if (this.payroll_professional_id) {
                await this.getPayrollProfession(this.payroll_professional_id);
            } else {
                await this.getPayrollProfession('filter');
            }
            this.loading = false;
        },
        async mounted() {
            this.loading = true;
            if (this.payroll_professional_id) {
                await this.getProfessional();
                await this.getJsonProfessions();
            }
            this.loading = false;
        },
        watch: {
            record: {
                deep: true,
                handler: function() {
                    const vm = this;
                    if (vm.record.is_student) {
                        $('#block_student').removeClass('d-none');
                    }
                    else {
                        $('#block_student').addClass('d-none');
                    }
                }
            }
        }
    };
</script>
