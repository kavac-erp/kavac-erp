<template>
    <div
        id="PayrollProfessionalInfo"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollProfessionalInfoModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document" style="max-width:60rem">
            <div class="modal-content">
                <!-- modal-header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Información Detallada de los Datos Profesionales
                    </h6>
                </div>
                <!-- Final modal-header -->
                <!-- modal-body -->
                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <h6 class="text-center">Datos básicos del trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Nombres del trabajador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff ? record.payroll_staff.first_name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Apellidos del Trabajador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff ? record.payroll_staff.last_name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Cédula de identidad:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff ? record.payroll_staff.id_number : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Grado de instrucción:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_instruction_degree ? record.payroll_instruction_degree.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Profesión:</strong>
                                        <div v-if="record.payroll_studies != 0">
                                            <div v-for="profession in professions" :key="profession.id">
                                                <div v-for="study in record.payroll_studies" :key="study.id">
                                                    <div class="row" style="margin-left: 1px;">
                                                        <span class="col-md-12">
                                                            {{ study.profession_id == profession.id ? profession.text : '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr v-if="record.payroll_studies.length >0">
                            <h6 class="text-center" v-show="record.payroll_studies.length >0">Estudios universitarios</h6><br>
                            <div class="row" v-for="(study, index) in record.payroll_studies" :key="index">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Nombre de la Universidad:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ study.university_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de graduación:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(study.graduation_year) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tipo de estudios:</strong>
                                        <div v-for="(study_type, index) in payroll_study_types" :key="index">
                                            <div class="row" style="margin-left: 1px;">
                                                <span class="col-md-12">
                                                    {{ study.payroll_study_type_id == study_type.id ? study_type.text : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Profesión:</strong>
                                        <div v-for="profession in professions" :key="profession.id">
                                            <div class="row" style="margin-left: 1px;">
                                                <span class="col-md-12">
                                                    {{ study.profession_id == profession.id ? profession.text : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center">Estudios en proceso</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Estudiante</strong>
                                        <div class="row" style="margin-left: 1px;">
                                            <span class="col-md-12">
                                                {{ record.is_student ? 'Si' : 'No' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" v-if="record.is_student">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <strong>Tipo de estudio que cursa</strong>
                                            <div class="row" style="margin-left: 1px;">
                                                <span class="col-md-12">
                                                    {{ record.payroll_study_type ? record.payroll_study_type.name : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" v-if="record.is_student">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <strong>Nombre del programa de estudio</strong>
                                                <div class="row" style="margin-left: 1px;">
                                                    <span class="col-md-12">
                                                        {{ record.study_program_name }}
                                                    </span>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr v-show="record.payroll_studies.length >0">
                            <h6 class="text-center" v-show="record.payroll_languages.length>0">Detalles del idioma</h6><br>
                            <div class="row" v-for="(payroll_language, index) in record.payroll_languages" :key="index">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Idioma:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ payroll_language.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Nivel del lenguaje:</strong>
                                        <div v-for="(level, index) in payroll_language_levels" :key="index">
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ payroll_language.pivot.payroll_language_level_id == level.id ? level.text : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr v-show="record.payroll_languages.length>0">
                            <h6
                                class="text-center"
                                v-if="
                                    (record.payroll_course
                                    && record.payroll_course.length > 0)
                                    || (record.payroll_acknowledgment
                                    && record.payroll_acknowledgment.length > 0)
                                "
                            >
                                Capacitación y Reconocimientos</h6><br>
                            <div v-if="record.payroll_acknowledgment">
                                <div
                                    class="row"
                                    v-for="
                                        (file, index)
                                        in record.payroll_acknowledgment.payroll_acknowledgment_files
                                    "
                                    :key="index"
                                >
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Tipo de reconocimiento</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ file.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Certificado del reconocimiento:</strong>
                                            <div v-for="(urls, index) in file.documents" :key="index">
                                                <div class="row" style="margin: 1px 0">
                                                    <!-- <span class="col-md-12">
                                                        {{ urls.url  }}
                                                    </span> -->
                                                    <a type="button" :href="`${app_url}/${urls.url}`" target="_blank">
                                                        <i class="fa fa-cloud-download fa-2x"></i>
                                                        <span>Documento</span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row" style="margin: 1px 0" v-if="file.image">
                                                <!-- <span class="col-md-12">
                                                    {{ file.image ? file.image.url : ''  }}
                                                </span> -->
                                                <a type="button" :href="`${app_url}/${file.image.url}`" target="_blank">
                                                    <i class="fa fa-cloud-download fa-2x"></i>
                                                    <span>Documento</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="record.payroll_course">
                                <div class="row" v-for="(course, index) in record.payroll_course.payroll_course_files" :key="index">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Nombre del curso realizado</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ course.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Certificado del curso realizado:</strong>
                                            <div v-for="(cert, index) in course.documents" :key="index">
                                                <div class="row" style="margin: 1px 0">
                                                    <!-- <span class="col-md-12">
                                                        {{ cert.url  }}
                                                    </span> -->
                                                    <a type="button" :href="`${app_url}/${cert.url}`" target="_blank">
                                                        <i class="fa fa-cloud-download fa-2x"></i>
                                                        <span>Documento</span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row" style="margin: 1px 0" v-if="course.image">
                                                <!-- <span class="col-md-12">
                                                    {{ course.image ? course.image.url : ''  }}
                                                </span> -->
                                                <a type="button" :href="`${app_url}/${course.image.url}`" target="_blank">
                                                    <i class="fa fa-cloud-download fa-2x"></i>
                                                    <span>Documento</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Final modal-body -->
                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal">
                        Cerrar
                    </button>
                </div>
                <!-- Final modal-footer -->
            </div>
        </div>
    </div>
</template>
<script>
    export default {
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
            }
        },
        created() {
            this.getPayrollStudyTypes();
            this.getProfessions();
            this.getPayrollLanguageLevels();
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Pablo Sulbarán <psulbaran@cenditel.gob.ve>
             */
            reset() {
            },
        },
    }
</script>