<template>
    <section id="payrollReviewBenefitsRequestPendingFormComponent">
        <a class="btn btn-default btn-xs btn-icon btn-action" href="#" title="Revisar solicitud" data-toggle="tooltip"
            :disabled="disabled(request_status)"
            @click="
            (!disabled(request_status)) ?
                addRecord('view_review_benefits_request_pending' + id, route_show, $event) :
                viewMessage()
            ">
            <i class="fa fa-filter"></i>
        </a>

        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_review_benefits_request_pending' + id">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            Revisión de solicitud de adelanto de prestaciones
                        </h6>
                    </div>
                    <div class="modal-body">
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
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="row">
                            <!-- código de la solicitud -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>Código de la solicitud:</strong>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12" id="code">
                                            {{ record.code }}
                                        </span>
                                    </div>
                                    <input type="hidden" id="id">
                                </div>
                            </div>
                            <!-- ./código de la solicitud -->
                            <!-- fecha de la solicitud -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>Fecha de la solicitud:</strong>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12" id="created_at">
                                            {{ record.created_at }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- ./fecha de la solicitud -->
                            <!-- trabajador -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>Trabajador:</strong>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12" id="payroll_staff">
                                            {{
                                                record.payroll_staff
                                                ? record.payroll_staff.first_name + ' ' + record.payroll_staff.last_name
                                                : 'No definido'
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- ./trabajador -->
                            <!-- Monto solicitudo -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>Monto solicitado:</strong>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12" id="amount_requested">
                                            {{ record.amount_requested }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- ./monto solicitado -->
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Estatus de la solicitud</label>
                                    <select2 :options="status" @input="updateStatusParameters" v-model="record.status">
                                    </select2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" v-if="record.status == 'rejected'">
                                <div class="form-group is-required">
                                    <label>Motivo del rechazo</label>
                                    <ckeditor :editor="ckeditor.editor" id="motive" data-toggle="tooltip"
                                        title="Indique el motivo de la solicitud" :config="ckeditor.editorConfig"
                                        class="form-control" name="motive" tag-name="textarea" rows="3"
                                        v-model="record.status_parameters.motive">
                                    </ckeditor>
                                </div>
                            </div>
                            <div class="col-md-4" v-if="record.status == 'approved'">
                                <div class="form-group is required">
                                    <label>Fecha de aprobación</label>
                                    <input type="date" data-toggle="tooltip" title="Fecha de aprobación de la solicitud"
                                        class="form-control input-sm" v-model="record.status_parameters.approval_date">
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" @click="updateRecord('/payroll/benefits-requests/review/')"
                            class="btn btn-primary btn-sm btn-round btn-modal-save">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
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
                institution_id: '',
                status_parameters: ''
            },

            records: [],
            errors: [],

            status: [
                { "id": "pending", "text": "Seleccione..." },
                { "id": "approved", "text": "Aprobado" },
                { "id": "rejected", "text": "Rechazado" }
            ],
        }
    },
    props: {
        id: {
            type: Number,
            required: true
        },
        request_status: {
            type: String,
            required: true
        },
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
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
                institution_id: '',
                status_parameters: ''
            };
        },
        /**
         * Reescribe el método initRecords para cambiar su comportamiento por defecto
         * Inicializa los registros base del formulario
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param     {string}    url         Ruta que obtiene los datos a ser mostrado en listados
         * @param     {string}    modal_id    Identificador del modal a mostrar con la información solicitada
         */
        initRecords(url, modal_id) {
            const vm = this;
            vm.reset();
            url = vm.setUrl(url);

            axios.get(url).then(response => {
                if (typeof (response.data.record) !== "undefined") {
                    let payrollVacationRequest = response.data.record;
                    vm.record = {
                        id: payrollVacationRequest['id'],
                        code: payrollVacationRequest['code'],
                        status: payrollVacationRequest['status'],
                        amount_requested: payrollVacationRequest['amount_requested'],
                        motive: payrollVacationRequest['motive'],
                        institution_id: payrollVacationRequest['institution_id'],
                        payroll_staff_id: payrollVacationRequest['payroll_staff_id'],
                        payroll_staff: payrollVacationRequest['payroll_staff'],
                        status_parameters: JSON.parse(payrollVacationRequest['status_parameters'])
                            ? JSON.parse(payrollVacationRequest['status_parameters'])
                            : {
                                approval_date: '',
                                motive: ''
                            },
                        created_at: vm.format_date(payrollVacationRequest['created_at'], 'DD-MM-YYYY')
                    }
                }
                if ($("#" + modal_id).length) {
                    $("#" + modal_id).modal('show');
                }
            }).catch(error => {
                if (typeof (error.response) !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }
                    else {
                        vm.logs(
                            'modules/payroll/resources/assets/js/components/requests/payroll-review-benefits-request-pending-form.js',
                            343, error, 'initRecords');
                    }
                }
            });
        },
        updateStatusParameters() {
            const vm = this;
            vm.record.status_parameters = {
                approval_date: '',
                motive: ''
            };
        },

        /**
         * Función que desabilita el botón de revisar solicitud.
         *
         * @param {String} status Estatus de la solicitud
         *
         * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
         */
            disabled(status = ''){
            if(status === 'approved'){
                return true;
            }
            else if(status === 'rejected'){
                return true;
            }
            else return false;
        },

        /**
         * Función que reescribe el método para mostrar un mensaje de alerta
         *
         * @author
         */
            viewMessage() {
            const vm = this;
            vm.showMessage(
                'custom', 'Alerta', 'danger', 'screen-error',
                'Esta solicitud ya fue aprobada o rechazada'
            );
            return false;
        },
    },
};
</script>
