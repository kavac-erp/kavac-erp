<template>
    <div>
        <a class="btn btn-info btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Ver información del registro" aria-label="Ver información del registro" data-toggle="tooltip"
            @click="initRecord(url)">
            <i class="fa fa-eye"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id='"view_product" + modal_id'>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close closeModal" id="close_modal" aria-label="Close"
                            @click="reset()">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información detallada del Producto
                        </h6>
                    </div>

                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Proyecto Asociado:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.project_id ? record.project.name : 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Subproyecto Asociado:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.subproject_id ? record.subproject.name : 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Descripción:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.description }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
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
                                            <strong>Dependencia:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.dependency.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Responsable del producto:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.responsable.first_name ? record.responsable.first_name :
                                                        record.responsable.name }}
                                                    {{ record.responsable.last_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Tipo de producto: </strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record?.product_types ? record.product_types[0].name : 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de inicio:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ format_date(record.start_date) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de culminación:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ format_date(record.end_date) }}
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
        }
    },
    data() {
        return {
            record: {
                id: "",
                name: "",
                description: "",
                project: "",
                project_id: "",
                subproject: '',
                subproject_id: '',
                code: "",
                dependency: "",
                type_product: "",
                responsable: "",
                start_date: "",
                end_date: ""
            },
            records: []
        };
    },
    methods: {
        reset() {
            const vm = this;
            vm.record = {
                id: "",
                name: "",
                description: "",
                project: "",
                project_id: "",
                subproject: '',
                subproject_id: '',
                code: "",
                dependency: "",
                type_product: "",
                responsable: "",
                start_date: "",
                end_date: ""
            }
        },
        initRecord(url) {
            const vm = this;
            vm.reset();
            url = this.setUrl(url);

            axios.get(url).then(response => {
                if (typeof (response.data.records) !== "undefined") {
                    vm.record = response.data.records;
                }
                if ($("#view_product" + vm.modal_id).length) {
                    $("#view_product" + vm.modal_id).modal('show');
                }
            }).catch(error => {
                if (typeof (error.response) !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }
                    else {
                        vm.logs('resources/js/alSul.js', 343, error, 'initRecords');
                    }
                }
            });
        },
    },
    mounted() {
        const vm = this;
        $('.closeModal').click(function () {
            $('#view_product' + vm.modal_id).modal('hide');
        });
    },
}
</script>