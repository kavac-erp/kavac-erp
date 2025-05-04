<template>
    <div
        id="PayrollEmploymentInfo"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollEmploymentInfoModalLabel"
        aria-hidden="true"
    >
        <div
            class="modal-dialog modal-lg"
            role="document"
            style="max-width:60rem"
        >
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
                        Información Detallada de Datos Laborales
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <h6 class="text-center">Datos básicos del trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Trabajador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff.first_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de ingreso a la institución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    convertDate(record.start_date)
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-show="record.active == false" class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de egreso de la institución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    convertDate(record.end_date)
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>¿Está activo?:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" 
                                                :class="{'text-success': record.active, 'text-danger': !record.active}">
                                                {{ record.active == true ? 'Si' : 'No' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-show="record.active == false" class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tipo de inactividad:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_inactivity_type? record.payroll_inactivity_type.name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Correo institucional:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.institution_email }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Descripción de funciones:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                <span v-html="record.function_description"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tipo de cargo :</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_position_type.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Cargo:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_positions.length > 0
                                                    ? record.payroll_positions[0].name
                                                    : 'Sin cargo asignado'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Coordinación:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_coordination
                                                    ? record.payroll_coordination.name
                                                    : ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tipo de personal:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff_type.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tipo de contrato:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_contract_type.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Organización:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.department
                                                    ? record.department.institution.name
                                                    : ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Ficha:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.worksheet_code
                                                    ? record.worksheet_code
                                                    : ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Departamento:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.department.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <h6 class="text-center">Trabajos anteriores</h6><br>
                                <div
                                    v-for="(job, index) in record.payroll_previous_job"
                                    class="row"
                                    :key="index"
                                >
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre de la organización:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ job.organization_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Teléfono de la organización:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ job.organization_phone }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de sector:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ job.payroll_sector_type.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cargo:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ job.previous_position }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de personal:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ job.payroll_staff_type ? job.payroll_staff_type.name : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de inicio:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{
                                                        convertDate(job.start_date)
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de cese:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{
                                                        convertDate(job.end_date)
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center">Antigüedad del trabajador</h6><br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Años en otras instituciones públicas:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.years_apn }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-show="record.active == true" class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tiempo laborando en la institución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.institution_years }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-show="record.active == false" class="col-md-4">
                                    <div class="form-group">
                                        <strong>Tiempo laborado en la institución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.time_worked }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Total años de servicio:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.service_years }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-default btn-sm btn-round btn-modal-close"
                        data-dismiss="modal"
                    >
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
                    payroll_staff: {},
                    payroll_inactivity_type: {},
                    institution_id: '',
                    years_apn: '',
                    start_date: '',
                    end_date: '',
                    active: '',
                    institution_email: '',
                    function_description: '',
                    payroll_position_type: {},
                    payroll_positions: {},
                    payroll_coordination: {},
                    payroll_staff_type: {},
                    institution: {},
                    department: {},
                    payroll_contract_type: {},
                    previous_jobs: [],
                    institution_years: '',
                    service_years: '',
                    time_worked: '',
                    worksheet_code: '',
                },
                errors: [],
            }
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
        }
    }
</script>
