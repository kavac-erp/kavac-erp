<template>
    <div
        id="PayrollTimeSheetPendingInfo"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollTimeSheetPendingInfoModalLabel"
        aria-hidden="true"
    >
        <div
            class="modal-dialog modal-lg"
            role="document"
            style="max-width:80%"
        >
            <div class="modal-content">
                <div class="modal-header">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Información Detallada de la Hoja de Tiempo Pendiente
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Desde:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(record.from_date, 'DD/MM/YYYY') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Hasta:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(record.to_date, 'DD/MM/YYYY') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Código grupo de supervisados:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_supervised_group ?
                                                    record.payroll_supervised_group.code :
                                                    ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Supervisor:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_supervised_group ?
                                                    record.payroll_supervised_group.supervisor.id_number + ' - ' +
                                                    record.payroll_supervised_group.supervisor.first_name + ' ' +
                                                    record.payroll_supervised_group.supervisor.last_name :
                                                    ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Aprobador:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.payroll_supervised_group ?
                                                    record.payroll_supervised_group.approver.id_number + ' - ' +
                                                    record.payroll_supervised_group.approver.first_name + ' ' +
                                                    record.payroll_supervised_group.approver.last_name :
                                                    ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Estatus:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    record.document_status ?
                                                    record.document_status.name :
                                                    ''
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <strong>Observaciones:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12" v-for="(observation, index) in record.observations" :key="index">
                                                {{
                                                    observation
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div style="border-top: 1px solid #eeeeee !important;">
                                <h6 class="text-center"
                                    style="text-transform: uppercase;
                                    color: #0073b7;
                                    padding-top: 24px;">
                                    Registros
                                </h6>
                                <div>
                                    <div class="col-md-12">
                                        <v-client-table ref="tableResults" :columns="draggableColumns" :data="draggableData" :options="table_options">
                                            <div slot="child_row" slot-scope="props">
                                                <strong>Observaciones:</strong>
                                                <span v-html="props.row.Observación"></span>
                                                <hr>
                                                <strong>Conceptos:</strong>
                                                <div v-for="(concept, i) in props.row.Conceptos" :key="i">
                                                    <div v-for="(con, cI) in concept.payroll_concepts" :key="cI">
                                                        <span>{{ con.text }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </v-client-table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-default btn-sm btn-round btn-modal-close"
                        data-dismiss="modal"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    from_date: '',
                    to_date: '',
                    payroll_supervised_group_id: '',
                    payroll_time_sheet_parameter_id: '',
                    supervisor: '',
                    approver: '',
                    time_sheet_data: {},
                    observations:'',
                },
                errors: [],
                payroll_supervised_groups: [],
                payroll_time_sheet_parameters: [],
                draggableColumns: [],
                draggableData: [],
                table_options: {
                    orderBy: {
                        column: 'N°'
                    },

                    filterable: ['Nombre']
                }
            }
        },
        methods: {
            /**
             * Método que obitiene los parámetros de hoja de tiempo
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
             async getPayrollTimeSheetParameters() {
                const vm = this;
                vm.payroll_time_sheet_parameters = [];
                await axios.get(`${window.app_url}/payroll/get-time-sheet-parameters`).then(response => {
                    vm.payroll_time_sheet_parameters = response.data;
                });
            },

            /**
             * Método que carga las columnas de la hoja de tiempo
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
             setTimeSheetColumns() {
                const vm = this;
                vm.draggableColumns = [];

                if (vm.record.payroll_time_sheet_parameter_id) {
                    let draggableColumns = vm.record.time_sheet_columns.sort((a, b) => a.position - b.position);
                    const filteredColumns = draggableColumns.filter(column => {
                        const columnName = column.name.toLowerCase();
                        return !columnName.includes('conceptos') && !columnName.includes('observación');
                    });

                    const filteredColumnNames = filteredColumns.map(column => column.name);

                    vm.draggableColumns = filteredColumnNames;
                } else {
                    vm.draggableColumns = [];
                }
            },

            /**
             * Método que carga los datos de la hoja de tiempo
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
             setTimeSheetData() {
                const vm = this;
                let draggableData = []
                vm.draggableData = [];

                if (vm.record.payroll_supervised_group_id) {
                    let group = vm.payroll_supervised_groups.find(function ($group) {
                        return vm.record.payroll_supervised_group_id == $group['id'];
                    })
                    let index = 1;

                    group.payroll_staffs.forEach(staff => {
                        draggableData.push({
                            'id': index,
                            'N°': index++,
                            'Ficha': staff.worksheet_code,
                            'Nombre': staff.name,
                            'staff_id': staff.id,
                        })
                    });

                    draggableData.map((item) => {
                        Object.entries(vm.record.time_sheet_data).forEach(value => {
                            let formValue = value[0];
                            let lastIndex = formValue.lastIndexOf('-');

                            if (lastIndex !== -1) {
                                const beforeHyphen = formValue.slice(0, lastIndex);
                                const afterHyphen = formValue.slice(lastIndex + 1);

                                if (afterHyphen.trim() == item.staff_id) {
                                    item[beforeHyphen.trim()] = value[1];
                                }
                            }
                        })
                    })
                }

                vm.draggableData = draggableData;
            },
        },
        created() {
            //
        },
        mounted() {
            const vm = this;

            $("#PayrollTimeSheetPendingInfo").on('show.bs.modal', function() {
                vm.getPayrollSupervisedGroups(vm.record.id, 'pending');
                vm.getPayrollTimeSheetParameters().then(() => {
                    vm.setTimeSheetColumns();
                    vm.setTimeSheetData();  
                });
            });
        }
    }
</script>
