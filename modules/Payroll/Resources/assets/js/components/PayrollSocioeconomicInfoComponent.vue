<template>
    <div id="PayrollSocioeconomicInfo" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="PayrollSocioeconomicInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document" style="max-width:60rem">
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
                        Información Detallada de los Datos Socioeconómicos
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active text-center" id="general" role="tabpanel">
                            <hr>
                            <h6 class="text-center">Datos básicos del Trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Nombres del Trabajador</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff ? record.payroll_staff.first_name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Apellidos del Trabajador</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff ? record.payroll_staff.last_name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Estado civil del Trabajador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{record.marital_status ? record.marital_status.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center">Información de Carga Familiar</h6><br>
                            <div v-if="record.payroll_childrens">
                                <div v-for="(child, index) in record.payroll_childrens" :key="index">
                                    <div class="row">
                                        <div class="col-md-4">
                                        <strong>Nombres:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.first_name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Apellidos:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.last_name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Cédula de identidad:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.id_number }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Fecha de nacimiento:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ format_date(child.birthdate) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Edad: </strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ setAge(child.birthdate) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Parentesco: </strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.payroll_relationship ? child.payroll_relationship.name : ''}}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Sexo: </strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.payroll_gender ? child.payroll_gender.name : ''}}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Dirección: </strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.address ? child.address : 'NO REGISTRADO'}}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4" v-if="child.payroll_relationship">
                                        <div  v-if="child.payroll_relationship.name == 'Hijo(a)'">
                                            <strong>¿Es estudiante?</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.is_student ? 'Si' : 'No' }}
                                                </span>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-md-4" v-if="child.has_scholarships">
                                            <strong>Tipo de Beca</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ child.payroll_scholarship_type ? child.payroll_scholarship_type.name : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="child.is_student">
                                        <div class="row" style="margin: 1px 0px;">
                                            <div class="col-md-4">
                                            <strong>Nivel de escolaridad:</strong>
                                                <div class="row" style="margin: 1px 0">
                                                    <span class="col-md-12">
                                                        {{ child.payroll_schooling_level_id && child.payroll_schooling_level ? child.payroll_schooling_level.name : '' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                            <strong>Centro de estudios:</strong>
                                                <div class="row" style="margin: 1px 0">
                                                    <span class="col-md-12">
                                                        {{ child.study_center ? child.study_center : ''}}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                            <strong>¿Posee discapacidad?</strong>
                                                <div class="row" style="margin: 1px 0">
                                                    <span class="col-md-12">
                                                        {{ child.has_disability ? 'Si' : 'No' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-4" v-if="child.has_disability">
                                            <strong>Discapacidad:</strong>
                                                <div class="row" style="margin: 1px 0">
                                                    <span class="col-md-12">
                                                        {{ child.payroll_disability ? child.payroll_disability.name : '' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr v-if="record.payroll_childrens.length > index + 1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            payroll_socioeconomic_id: Number,
        },
        data() {
            return {
                record: {
                    id: '',
                    full_name_twosome: '',
                    id_number_twosome: '',
                    birthdate_twosome: '',
                    payroll_staff_id: '',
                    marital_status_id: '',
                    payroll_childrens: [],
                },
                errors: [],
                //payroll_staffs: [],
                payroll_socioeconomic: [],
                marital_status: [],
                payroll_schooling_levels: [],
                payroll_disabilities: []
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             */
            reset() {
                this.record = {
                    id: '',
                    payroll_staff_id: '',
                    marital_status_id: '',
                    payroll_childrens: [],
                };
            },
        },

        created() {
        },

        mounted() {

        },
    };
</script>
