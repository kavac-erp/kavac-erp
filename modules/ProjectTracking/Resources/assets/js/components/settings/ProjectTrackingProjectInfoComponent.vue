<template>
    <div>
        <a class="btn btn-info btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Ver información del registro" aria-label="Ver información del registro" data-toggle="tooltip"
            @click="initRecord(url)">
            <i class="fa fa-eye"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id='"view_project" + modal_id'>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close closeModal" aria-label="Close" @click="reset()">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información detallada del Proyecto
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
                                            <strong>Tipo de proyecto:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.project_type.name }}
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
                                            <strong>Tipos de producto: </strong>
                                            <div class="row" style="margin: 1px 0"
                                                v-if="record?.product_types.length > 0"
                                                v-for="(productType, index) in record.product_types" :key="index">
                                                <span class="col-md-12">
                                                    <!-- {{ record.type_product_id ? record.type_product.name : 'N/A' }} -->
                                                    {{ productType.name ? productType.name : 'N/A' }}{{ index <
                                                        record.product_types.length - 1 ? ', ' : '' }} </span>
                                            </div>
                                            <div class="row" style="margin: 1px 0" v-else>
                                                <span class="col-md-12">
                                                    N/A
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Responsable del proyecto:</strong>
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
                                            <strong>Monto de financiamiento:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ (record.financing_amount && record.currency_id) ?
                                                        record.currency.symbol + '. ' + record.financing_amount : 'N/A' }}
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
                project_type: "",
                code: "",
                dependency: "",
                type_product: "",
                type_product_id: "",
                responsable: "",
                financing_amount: "",
                currency: "",
                currency_id: "",
                start_date: "",
                end_date: "",
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
                project_type: "",
                code: "",
                dependency: "",
                type_product: "",
                type_product_id: "",
                responsable: "",
                financing_amount: "",
                currency: "",
                currency_id: "",
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
                if ($("#view_project" + vm.modal_id).length) {
                    $("#view_project" + vm.modal_id).modal('show');
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
    },
    mounted() {
        const vm = this;
        $('.closeModal').click(function () {
            $('#view_project' + vm.modal_id).modal('hide');
        });
    },
}
</script>