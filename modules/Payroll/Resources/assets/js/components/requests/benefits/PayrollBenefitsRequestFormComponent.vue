<template>
    <section id="PayrollBenefitRequestForm">
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
                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                    </ul>
                </div>
            </div>
            <!-- ./mensajes de error -->
            <div class="row">
                <!-- código de la solicitud -->
                <div class="col-md-3" v-if="id > 0" id="helpPayrollBenefitRequestCode">
                    <div class="form-group is-required">
                        <label>Código de la solicitud:</label>
                        <input type="text" readonly data-toggle="tooltip" title="" class="form-control input-sm"
                            v-model="record.code">
                    </div>
                </div>
                <!-- ./código de la solicitud -->
                <!-- fecha de la solicitud -->
                <div class="col-md-2" id="helpPayrollBenefitRequestDate">
                    <div class="form-group is-required">
                        <label>Fecha de la solicitud:</label>
                        <input type="date" readonly data-toggle="tooltip" title="Fecha de generación de la solicitud"
                            class="form-control input-sm" v-model="record.created_at">
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <!-- ./fecha de la solicitud -->
                <!-- trabajador -->
                <div class="col-md-6" id="helpPayrollBenefitRequestStaff">
                    <div class="form-group is-required">
                        <label>Trabajador:</label>
                        <select2 :options="payroll_staffs" :disabled="(is_admin) ? null : 'disabled'"
                            @input="getPayrollStaffInfo();" v-model="record.payroll_staff_id">
                        </select2>
                    </div>
                </div>
                <!-- ./trabajador -->
            </div>
            <div class="row" v-show="payroll_benefits_policy">
                <div class="col-md-3">
                    <div class="form-group">
                        <strong> Monto acumulado: </strong>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="amount_accumulated"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <strong> Monto disponible: </strong>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="amount_available"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- monto solicitudo -->
                <div class="col-md-3" id="helpPayrollBenefitAmountRequested">
                    <div class="form-group is-required">
                        <label>Monto solicitado:</label>
                        <input type="text" data-toggle="tooltip" title="Indique la cantidad solicitada"
                            class="form-control input-sm" v-input-mask data-inputmask="
                                    'alias': 'numeric',
                                    'allowMinus': 'false'" v-model="record.amount_requested">
                    </div>
                </div>
                <!-- ./monto solicitado -->
                <!-- motivo -->
                <div class="col-12" id="helpPayrollBenefitMotive">
                    <div class="form-group is-required">
                        <label>Motivo de adelanto de prestaciones</label>
                        <ckeditor :editor="ckeditor.editor" id="motive" data-toggle="tooltip"
                            title="Indique el motivo de la solicitud" :config="ckeditor.editorConfig" class="form-control"
                            name="motive" tag-name="textarea" rows="2" v-model="record.motive">
                        </ckeditor>
                    </div>
                </div>
                <!-- ./motivo -->
            </div>
            <section class="row" v-show="payroll_staff['id'] > 0">
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
                </div>
            </section>
        </div>

        <div class="card-footer text-right" id="helpParamButtons">
            <button type="button" @click="reset()" class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                title="Borrar datos del formulario">
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" @click="redirect_back(route_list)" class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip" title="Cancelar y regresar">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" @click="createRecord('payroll/benefits-requests')"
                class="btn btn-success btn-icon btn-round" data-toggle="tooltip" title="Guardar registro">
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
                amount_requested: '',
                motive: '',
                payroll_staff_id: '',
                institution_id: ''
            },

            errors: [],
            records: [],
            institutions: [],
            payroll_staffs: [],
            payroll_benefits_requests: [],
            payroll_benefits_policy: {},
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
        },
        user_id: {
            type: Number
        }
    },
    async mounted() {
        const vm = this;
        vm.getInstitutions();
        await vm.getPayrollStaffs('auth');
        vm.getPayrollBenefitsPolicy();
        if (vm.id > 0) {
            vm.showRecord(vm.id);
        } else {
            vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
        }
        vm.record.payroll_staff_id = vm.payroll_staffs[1].id
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
                amount_requested: '',
                motive: '',
                payroll_staff_id: '',
                institution_id: ''
            };
            vm.payroll_benefits_requests = [];
            vm.record.created_at = vm.format_date(new Date(), 'YYYY-MM-DD');
        },
        /**
         * Método que obtiene la información del trabajador
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getPayrollStaffInfo() {
            const vm = this;
            if (vm.record.payroll_staff_id > 0) {
                axios.get(`${window.app_url}/payroll/staffs/${vm.record.payroll_staff_id}`).then(response => {
                    vm.payroll_staff = response.data.record;
                    axios.get(
                        `${window.app_url}/payroll/get-benefits-requests/${vm.record.payroll_staff_id}`
                    ).then(response => {
                        /**
                         * Calcular según politica de prestaciones:
                         * Monto acumulado
                         * Monto disponible
                         */
                        vm.payroll_vacation_requests = response.data.records;

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
                    });
                });
            }
        },
        getPayrollBenefitsPolicy() {
            const vm = this;
            axios.get(`${window.app_url}/payroll/get-benefits-policy`).then(response => {
                vm.payroll_benefits_policy = response.data.record;
            });
        },
        /**
         * Reescribe el método showRecord para cambiar su comportamiento por defecto
         * Método que muestra datos de un registro seleccionado
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param     {integer}    id    Identificador del registro a mostrar
         */
        showRecord(id) {
            const vm = this;
            axios.get(`${window.app_url}/payroll/benefits-requests/show/${id}`).then(response => {
                vm.record = response.data.record;
                vm.record.created_at = vm.format_date(response.data.record.created_at, 'YYYY-MM-DD');
            });
        }
    }
};
</script>
