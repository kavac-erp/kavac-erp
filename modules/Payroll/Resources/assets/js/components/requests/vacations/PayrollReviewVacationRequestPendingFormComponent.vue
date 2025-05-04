<template>
    <section id="payrollReviewVacationRequestPendingFormComponent">
        <a class="btn btn-success btn-xs btn-icon btn-action" href="#" title="Aceptar solicitud"
            aria-label="Aceptar solicitud" data-toggle="tooltip"
            @click="initPending('view_pending_a' + requestid, $event)" v-if="request_type == 'approved'"
            :disabled="requeststate == 'rejected' || requeststate == 'approved' || requeststate == 'suspended'">
            <i class="fa fa-check"></i>
        </a>
        <a class="btn btn-danger btn-xs btn-icon btn-action" href="#" title="Rechazar solicitud"
            aria-label="Rechazar solicitud" data-toggle="tooltip"
            @click="initPending('view_pending_r' + requestid, $event)" v-else
            :disabled="requeststate == 'rejected' || requeststate == 'suspended' || requeststate == 'approved'">
            <i class="fa fa-ban"></i>
        </a>

        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_pending_a' + requestid">
            <div class="modal-dialog modal-xs" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            ¿Seguro que desea aprobar esta solicitud?
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
                                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    Una vez aprobada la operación no se podrán realizar cambios en la misma.
                                </p>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group is required">
                                    <label>Fecha de reincorporación</label>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12">
                                            {{ getReincorporationDate() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal" title="Presione para cerrar la ventana" data-toggle="tooltip"
                            v-has-tooltip>
                            Cerrar
                        </button>
                        <button type="button" @click="updateRecord('/payroll/vacation-requests/approved/' + requestid)"
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                            title="Presione para guardar el registro" data-toggle="tooltip" v-has-tooltip>
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_pending_r' + requestid">
            <div class="modal-dialog modal-xs" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            ¿Seguro que desea rechazar esta solicitud?
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
                                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    Una vez rechazada la operación no se podrán realizar cambios en la misma.
                                </p>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Motivo del rechazo:</label>
                                    <ckeditor :editor="ckeditor.editor" data-toggle="tooltip"
                                        title="Indique alguna observación referente a la operación que esta realizando "
                                        :config="ckeditor.editorConfig" class="form-control" tag-name="textarea"
                                        rows="3" v-model="record.motive" placeholder="Motivo del rechazo"></ckeditor>
                                    <input type="hidden" v-model="record.id" id="id">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal" title="Presione para cerrar la ventana" data-toggle="tooltip"
                            v-has-tooltip>
                            Cerrar
                        </button>
                        <button type="button" @click="updateRecord('/payroll/vacation-requests/rejected/' + requestid)"
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                            title="Presione para guardar el registro" data-toggle="tooltip" v-has-tooltip>
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
                days_requested: '',
                vacation_period_year: '',
                start_date: '',
                end_date: '',
                institution_id: '',
                payroll_staff_id: '',
                reincorporation_date: '',
                motive: ''
            },

            records: [],
            errors: [],
            payroll_vacation_policy: null
        }
    },
    props: {
        requestid: Number,
        requeststate: String,
        payroll_staff_id: Number,
        request_type: {
            type: String,
            required: true,
            default: 'approved'

        }
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
                days_requested: '',
                vacation_period_year: '',
                start_date: '',
                end_date: '',
                institution_id: '',
                payroll_staff_id: '',
                status_parameters: ''
            };
        },
        async initPending(modal_id, event) {
            const vm = this;
            let url = `/payroll/vacation-requests/${vm.request_type}/${vm.requestid}/${true}`;
            if (typeof (url) != 'undefined') {
                url = vm.setUrl(url);
                axios.put(url, vm.record).then(response => {
                    if (typeof (response.data.redirect) !== "undefined") {
                        if (this.requeststate == 'pending' || this.requeststate == 'rescheduled') {
                            $(".modal-body #id").val(this.requestid);
                            if ($("#" + modal_id).length) {
                                $('#' + modal_id).modal('show');
                            }
                            event.preventDefault();

                            this.initRecords(this.route_show, modal_id);
                            axios.get(`${window.app_url}/payroll/get-vacation-policy/${vm.payroll_staff_id}`).then(response => {
                                vm.payroll_vacation_policy = response.data.record;
                            });
                        }
                    }
                }).catch(error => {
                    vm.errors = [];
                    if (typeof (error.response) != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                    }

                });
            }
        },
        updateRecord(url) {
            console.log(url)
            const vm = this;
            var id = $(".modal-body #id").val();
            if (typeof (url) != 'undefined') {
                url = vm.setUrl(url);
                axios.put(url, vm.record).then(response => {
                    if (typeof (response.data.redirect) !== "undefined")
                        location.href = response.data.redirect;
                }).catch(error => {
                    vm.errors = [];
                    if (typeof (error.response) != "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }

                });
            }
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
                    vm.record = response.data.record;
                    vm.record.vacation_period_year = JSON.parse(vm.record.vacation_period_year);
                    vm.record.created_at = vm.format_date(response.data.record.created_at, 'YYYY-MM-DD');
                    vm.record.status_parameters = JSON.parse(vm.record.status_parameters);
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
                        vm.logs('modules/payroll/resources/assets/js/components/requests/vacations/.js', 343, error, 'initRecords');
                    }
                }
            });
        },
        /**
         * Método que carga los días feriados
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         */
        getHolidays() {
            const vm = this;
            let url = vm.setUrl('payroll/get-holidays');

            axios.get(url).then(response => {
                if (typeof (response.data) !== "undefined") {
                    vm.holidays = response.data;
                }
            });
        },
        getReincorporationDate() {
            const vm = this;
            if (vm.payroll_vacation_policy) {
                if (vm.payroll_vacation_policy.business_days) {
                    if (vm.record.end_date) {
                        let start_date = new Date(vm.record.end_date.replaceAll('-', '/'));
                        if (start_date.getDay() == 5) {
                            vm.record.reincorporation_date = vm.add_period(vm.record.end_date, 3, 'days', "YYYY-MM-DD");
                        } else if (start_date.getDay() == 6) {
                            vm.record.reincorporation_date = vm.add_period(vm.record.end_date, 2, 'days', "YYYY-MM-DD");
                        } else {
                            vm.record.reincorporation_date = vm.add_period(vm.record.end_date, 1, 'days', "YYYY-MM-DD");
                        }
                    }
                } else {
                    vm.record.reincorporation_date = vm.add_period(vm.record.end_date, 1, 'days', "YYYY-MM-DD");
                }
            }
            return (vm.record.reincorporation_date != '') ? vm.record.reincorporation_date : vm.record.end_date;
        }
    }
};
</script>
