<template>
    <div>
        <a class="btn btn-info btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Ver información del registro" aria-label="Ver información del registro" data-toggle="tooltip"
            @click="initRecord(url)">
            <i class="fa fa-eye"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id='"view_activity" + modal_id'>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close closeModal" aria-label="Close" @click="reset()">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información detallada de la actividad macro
                        </h6>
                    </div>

                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre de la actividad:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.activityRecord.name_activity }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Orden:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.activityRecord.orden }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de proyecto:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.activityRecord.project_tracking_project_types_id ?
                                                        record.activityRecord.project_tracking_project_types.name : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de producto:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.activityRecord.project_tracking_type_products_id ?
                                                        record.activityRecord.project_tracking_type_products.name : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Descripción:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.activityRecord.description }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Responsable de la actividad:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ activity_info.responsable_activity }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de inicio:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ activity_info.start_date_activity }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha fin:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ activity_info.end_date_activity }}
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
        activity_info: {
            type: Object
        },
    },
    data() {
        return {
            record: {
                id: '',
                activityRecord: '',
                start_date: '',
                end_date: '',
                /* team_members: ''*/
            },
        };
    },
    methods: {
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                activityRecord: '',
                start_date: '',
                end_date: '',
                /*team_members: '',*/
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
                if ($("#view_activity" + vm.modal_id).length) {
                    $("#view_activity" + vm.modal_id).modal('show');
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
            $('#view_activity' + vm.modal_id).modal('hide');
        });
    },
}
</script>