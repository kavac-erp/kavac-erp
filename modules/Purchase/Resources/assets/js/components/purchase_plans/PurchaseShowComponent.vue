<template>
    <div>
        <button
            @click="addRecord('show_purchase_plan_'+id, route_show, $event)"
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Visualizar registro"
            data-toggle="tooltip"
            v-has-tooltip
        >
            <i class="fa fa-eye"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'show_purchase_plan_'+id"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button
                            type="reset"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            INFORMACIÓN DEL PLAN DE COMPRA
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="row" v-if="records">
                            <div class="col-4">
                                <strong>Fecha de inicio: </strong>{{format_date(records.init_date) }}
                            </div>
                            <div class="col-4">
                                <strong>Fecha de culminación: </strong>{{format_date(records.end_date) }}
                            </div>
                            <div class="col-4">
                                <strong>Tipo de compra: </strong>{{purchase_type }}
                            </div>
                        </div>
                        <br>
                        <div class="row" v-if="records">
                            <div class="col-4"><strong>Proceso de compra: </strong>{{ purchase_process }}</div>
                            <div class="col-4"><strong>Responsable: </strong>{{ payroll_staff }}</div>
                        </div>
                        <br>
                        <h6 class="text-center text-info">DOCUMENTO DEL PLAN DE COMPRAS</h6>
                        <div class="row">
                            <div class="col-md-12" v-if="records.document">
                                <div id="documents">
                                    <ul class="feature-list list-group list-group-flush">
                                        <li class="list-group-item">
                                            <div class="feature-list-indicator bg-info"></div>
                                            <div class="feature-list-content p-0">
                                                <div class="feature-list-content-wrapper">
                                                    <a :href="'/purchase/purchase_plans/download/'+records.document.code">
                                                        {{ records.document.file }}
                                                    </a>
                                                    <div class="feature-list-content-left">
                                                        <div class="feature-list-subheading">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Final modal-body -->
                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                    <!-- Final modal-footer -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['id'],
    data() {
        return {
            records: [],
            files: {},
        }
    },
    mounted() {
        if (this.records.purchase_process) {
            this.record = this.records.purchase_process;
        }
    },
    methods: {
        uploadFile(input_id) {
            let vm = this;
            if (document.querySelector(`#${input_id}`)) {
                vm.loading = false;
                vm.files[input_id] = document.querySelector(`#${input_id}`).files[0];
                /** Se obtiene y da formato para enviar el archivo a la ruta */
                var formData = new FormData();
                var inputFile = document.querySelector('#' + input_id);
                formData.append("file", inputFile.files[0]);
                formData.append("purchase_plan_id", vm.id);
                axios.post('/purchase/purchase_plan_upload_file', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    vm.showMessage('update');
                    vm.loading = false;
                    $('#status_' + input_id).show("slow");
                }).catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 422 || error.response.status == 500) {
                            for (const i in error.response.data.errors) {
                                vm.showMessage(
                                    'custom', 'Error', 'danger', 'screen-error', error.response.data.errors[i][0]
                                );
                            }
                        }
                    }
                    vm.loading = false;
                });
            }
        },
    },
    computed: {
        purchase_type: function() {
            if (this.records.purchase_type) {
                return this.records.purchase_type.name;
            }
        },
        purchase_process: function() {
            if (this.records.purchase_process) {
                return this.records.purchase_process.name;
            }
        },
        payroll_staff: function() {
            if (this.records.payroll_staff) {
                return this.records.payroll_staff.first_name + ' ' + this.records.payroll_staff.last_name;
            }
        },
    }
};
</script>
