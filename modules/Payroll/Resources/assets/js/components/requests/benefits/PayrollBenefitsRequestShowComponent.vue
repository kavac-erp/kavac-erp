<template>
    <section id="payrollBenefitsRequestShow">
        <a class="btn btn-info btn-xs btn-icon btn-action" href="#" title="Ver información del registro"
            data-toggle="tooltip" @click="addRecord('view_benefits_request' + id, route_show, $event)">
            <i class="fa fa-eye"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_benefits_request' + id">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información de la solicitud de adelanto de prestaciones registrada
                        </h6>
                    </div>

                    <div class="modal-body">
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
                            <!-- días solicitudos -->
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
                            <!-- ./días solicitados -->
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

            records: [],
            errors: []
        }
    },
    props: {
        id: {
            type: Number,
            required: true
        }
    },
    mounted() {
    },
    created() {
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
                institution_id: ''
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
                    vm.record = response.data.record;
                    vm.record.created_at = vm.format_date(response.data.record.created_at, 'DD-MM-YYYY');
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
                        vm.logs('modules/payroll/resources/assets/js/components/requests/payroll-benefits-request-show.js', 343, error, 'initRecords');
                    }
                }
            });
        }
    },
};
</script>
