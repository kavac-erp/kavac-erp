<template>
    <div id="PayrollStaffInfo" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="PayrollStaffInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                         Información Detallada de los Datos Personales
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <h6 class="text-center">Datos básicos del Trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Código:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.code }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Nombres del Trabajador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.first_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Apellidos del Trabajador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.last_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Cédula:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.id_number }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Correo electrónico:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.email }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de nacimiento:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(record.birthdate) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Pasaporte:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.passport ? record.passport : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Rif:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.rif ? record.rif : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Género:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_gender ? record.payroll_gender.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center">Datos de la persona de contacto</h6><br>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Nombres y Apellidos de la Persona de Contacto:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.emergency_contact ? record.emergency_contact : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Teléfono de la Persona de Contacto:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.emergency_phone ? record.emergency_phone : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center">Datos de salud del trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>¿Posee discapacidad?</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.has_disability == true ? 'Si' : 'No' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="record.has_disability == true" class="col-md-4">
                                    <div class="form-group">
                                        <strong>Discapacidad</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_disability ? record.payroll_disability.name : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tipo de sangre:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_blood_type ? record.payroll_blood_type.name : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Seguro social:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.social_security ? record.social_security : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>¿Posee licencia de conducir?</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.has_driver_license == true ? 'Si' : 'No'}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Historial médico:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                <span v-html="record.medical_history ? record.medical_history : 'NO REGISTRADO'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center">Datos donde reside el trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>País:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.parish && record.parish.municipality && record.parish.municipality.estate && record.parish.municipality.estate.country ? record.parish.municipality.estate.country.name : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Estado:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.parish && record.parish.municipality && record.parish.municipality.estate ? record.parish.municipality.estate.name : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Municipio:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.parish && record.parish.municipality ? record.parish.municipality.name : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Parroquia:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.parish ? record.parish.name : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Dirección:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.address ? record.address : 'NO REGISTRADO' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <h6 class="text-center">Talla de uniforme</h6><br>
                                <div v-for="(uniform_size, index) in record.payroll_staff_uniform_size" class="row" :key="index">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Tipo de ropa:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ uniform_size.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Talla:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ uniform_size.size }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div>
                            <h6 class="text-center">Teléfonos del trabajador</h6><br>
                                <div v-for="(phone, index) in record.phones" class="row" :key="index">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                        <strong>Tipo de teléfono:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ phone.type }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                        <strong>Número de teléfono:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ phone.area_code + ' ' + phone.number }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                        <strong>Extensión telefónica:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ phone.extension ? phone.extension : 'NO APLICA' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal">
                        Cerrar
                    </button>
                </div>
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
                    code: '',
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
                    parish: {},
                },
                errors: [],
                payroll_nationalities: [],
                payroll_genders: [],
                countries: [],
                estates: [],
                municipalities: [],
                parishes: [],
                payroll_license_degrees: [],
                payroll_blood_types: [],
                payroll_disabilities: [],
            }
        },
        created() {
            //
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Pablo Sulbarán <dcontreras@cenditel.gob.ve>
             */
            reset() {
            },
        },
    }
</script>
