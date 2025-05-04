<template>
    <div>
        <a class="btn btn-info btn-xs btn-icon btn-action" href="javascript:void(0)"
            title="Ver información del registro" data-toggle="tooltip" @click="initRecord(url)">
            <i class="fa fa-eye"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id='"view_task" + modal_id'>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close closeModal" id="close_modal" aria-label="Close"
                            @click="reset()">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información detallada de la Tarea
                        </h6>
                    </div>

                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div v-if="record.project_name" class="col-md-4">
                                        <div class="form-group">
                                            <strong>Proyecto Asociado:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record?.project.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else-if="record.subproject_name" class="col-md-4">
                                        <div class="form-group">
                                            <strong>Subproyecto Asociado:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record?.subproject.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else-if="record.product_name" class="col-md-4">
                                        <div class="form-group">
                                            <strong>Producto Asociado:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record?.product.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre de la Tarea:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Actividad Macro:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ 'Pertenece a: ' + record.activity_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Responsable de la Tarea:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.employers_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Estatus de la Actividad:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.activity_status_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Prioridad:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record?.priority.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Peso de la Actividad:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.weight }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Peso Porcentual:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ record.percentage }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de Asignación:</strong>
                                            <div class="row" style="margin: 1px 0">
                                                <span class="col-md-12">
                                                    {{ format_date(record.start_date) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de Entrega:</strong>
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
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                task_responsable_id: '',
                activity_status_id: '',
                priority: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                weight: ''
            },
            records: []
        };
    },
    methods: {
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                project_name: '',
                subproject_name: '',
                product_name: '',
                activity_plan_id: '',
                name: '',
                description: '',
                task_responsable_id: '',
                priority_id: '',
                start_date: '',
                end_date: '',
                weight: ''
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
                if ($("#view_task" + vm.modal_id).length) {
                    $("#view_task" + vm.modal_id).modal('show');
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
            $('#view_task' + vm.modal_id).modal('hide');
        });
    },
}
</script>