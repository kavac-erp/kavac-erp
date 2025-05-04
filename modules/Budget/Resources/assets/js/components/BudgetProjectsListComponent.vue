<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="id" slot-scope="props" class="text-center">
                <button
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                    type="button"
                    data-toggle="tooltip"
                    title="Ver registro"
                    @click="show_info(props.row.id)"
                >
                    <i class="fa fa-eye"></i>
                </button>

                <template v-if="(lastYear && format_date(props.row.from_date, 'YYYY') <= lastYear)">
                    <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        v-if="!props.row.disabled"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Eliminar registro"
                        @click="deleteRecord(props.row.id, '')"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span
                    v-if="props.row.active"
                    class="text-success font-weight-bold"
                >
                    SI
                </span>
                <span v-else class="text-danger font-weight-bold">NO</span>
            </div>
        </v-client-table>
        <!-- Modal -->
        <div
            id="show_employment"
            class="modal fade"
            tabindex="-1"
            role="dialog"
            aria-labelledby="BudgetCentralizedActionsInfoModalLabel"
            aria-hidden="true"
        >
            <!-- modal-dialog -->
            <div
                class="modal-dialog modal-lg text-left"
                role="document"
                style="max-width: 60rem; color: #636e7b; font-size: 13px"
            >
                <!-- modal-content -->
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6 style="font-size: 1em">
                            <i class="icofont icofont-read-book ico-2x"></i>
                                Información detallada del Proyecto
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Institución :</strong>
                                            <div class="row">
                                                <span    class="col-md-12">
                                                    <a id="institution"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Dependencia:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="department"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Responsable:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="responsable"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Cargo del Responsable:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="payroll_position"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Codigo:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="code"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4" v-if="'true' == onapre">
                                        <div class="form-group">
                                            <strong>Código ONAPRE:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="onapre_code"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Nombre:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a    id="name"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Activo:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="active"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de inicio:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="from_date"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <strong>Fecha de fin:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="to_date"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <strong>Descripción:</strong>
                                            <div class="row">
                                                <span class="col-md-12">
                                                    <a id="description"></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Final modal-body -->
                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                    <!-- Final modal-footer -->
                </div>
                <!-- Final modal-content -->
            </div>
            <!-- Final modal-dialog -->
        </div>
        <!-- Modal -->
    </div>
</template>

<script>
    export default {
        props: ['onapre'],
        data() {
            return {
                records: [],
                lastYear: "",
                columns: [
                    'code',
                    'name',
                    'active',
                    'id'
                ]
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'name': 'Proyecto',
                'active': 'Activo',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'name'];
            this.table_options.filterable = ['code', 'name'];
            this.table_options.columnsClasses = {
                'created_at': 'col-md-2',
                'code': 'col-md-2 text-center',
                'name': 'col-md-4 text-center',
                'active': 'col-md-2 text-center',
                'id': 'col-md-2 text-center'
            };
        },
        async mounted() {
            const vm = this;
            await vm.initRecords(vm.route_list, '');
            await vm.queryLastFiscalYear();
        },
        methods: {
            /**
             * Método que abre el modal, realiza la consulta y pasa los datos.
             */
            show_info(id) {
                axios.get(`${window.app_url}/budget/projects/get-detail-project/${id}`)
                .then(response => {
                    this.record = response.data;
                    $('#name').html(this.record.project.name);
                    $('#institution').html(this.record.cargo.payroll_employment.department.institution.name);
                    $('#department').html(this.record.cargo.payroll_employment.department.name);
                    $('#responsable').html(this.record.cargo.first_name + ' ' + this.record.cargo.last_name);
                    $('#payroll_position').html(this.record.cargo.payroll_employment.payrollPosition.name);
                    $('#code').html(this.record.project.code);
                    $('#onapre_code').html(this.record.project.onapre_code);
                    $('#active').html((this.record.project.active === true) ? 'Sí' : 'No');
                    $('#from_date').html(this.record.project.from_date ? this.format_date(this.record.project.from_date) : 'No definido');
                    $('#to_date').html(this.record.project.to_date ? this.format_date(this.record.project.to_date) : 'No definido');
                    $('#description').html(this.record.project.description ? this.record.project.description : 'No definido');
                });
                $('#show_employment').modal('show');
            }
        }
    };
</script>
