<template>
    <div>
        <a class="btn btn-info btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Ver información del registro" aria-label="Ver información del registro" data-toggle="tooltip"
            @click="initRecord(url)">
            <i class="fa fa-eye"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id='"view_team" + modal_id'>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close closeModal" aria-label="Close" @click="reset()">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información detallada del trabajador
                        </h6>
                    </div>

                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.employerRecord.first_name ?
                                                        record.employerRecord.first_name : record.employerRecord.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Apellido:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.employerRecord.last_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cédula:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.employerRecord.id_number }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cargo:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.employerRecord.payroll_employment ?
                                                        record.employerRecord.payroll_employment.payroll_position.name :
                                                    record.employerRecord.position ? record.employerRecord.position.name
                                                    : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Rol:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.classificationRecord.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Descripción:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.classificationRecord.description }}
                                                </span>
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
        </div>
    </div>
</template>
<script>
export default {
    props: {
        modal_id: {
            type: Number
        },
        url: {
            type: String
        },
    },
    data() {
        return {
            record: {
                id: '',
                employerRecord: '',
                classificationRecord: ''
            },
        };
    },
    methods: {
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                employerRecord: '',
                classificationRecord: '',
            }
        },
        initRecord(url) {
            const vm = this;
            vm.reset();
            url = this.setUrl(url);

            axios.get(url).then(response => {
                if (typeof (response.data) !== "undefined") {
                    vm.record = response.data;
                }
                if ($("#view_team" + vm.modal_id).length) {
                    $("#view_team" + vm.modal_id).modal('show');
                }
            }).catch(error => {
                if (typeof (error.response) !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }
                    else {
                        vm.logs('resources/js/all.js', 343, error, 'initRecords');
                    }
                }
            });
        },
    },
    created() {
        const vm = this;
    },
    mounted() {
        const vm = this;
        $('.closeModal').click(function () {
            $('#view_team' + vm.modal_id).modal('hide');
        });
    },
}
</script>