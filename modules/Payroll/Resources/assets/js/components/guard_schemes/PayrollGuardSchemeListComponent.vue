<template>
    <div>
        <v-client-table ref="tableResults" :columns="columns" :data="records" :options="table_options">
            <div slot="institution" slot-scope="props" class="text-center">
                <span>{{ props.row.institution }}</span>
            </div>
            <div slot="date" slot-scope="props" class="text-center">
                <span>{{ format_date(props.row.from_date) + ' - ' + format_date(props.row.to_date)}}</span>
            </div>
            <div slot="code" slot-scope="props" class="text-center">
                <span>{{ props.row.payroll_supervised_group.code }}</span>
            </div>
            <div slot="supervisor" slot-scope="props">
                <span>{{ props.row.payroll_supervised_group.supervisor.name }}</span>
            </div>
            <div slot="approver" slot-scope="props">
                <span>{{ props.row.payroll_supervised_group.approver.name }}</span>
            </div>
            <div slot="status" slot-scope="props" class="text-center">
                <span v-if="props.row.document_status.action == 'EL'" class="text-warning">
                    {{ props.row.document_status.name }}
                </span>
                <span v-else-if="props.row.document_status.action == 'PR'" class="text-info">
                    {{ props.row.document_status.name }}
                </span>
                <span v-else-if="props.row.document_status.action == 'CE'" :style="{color: props.row.document_status.color}">
                    {{ props.row.document_status.name }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('GuardSchemeInfo', props.row.id ,'PayrollGuardSchemeInfo')"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button v-if="index_permission"
                        @click.prevent="setDetails('GuardSchemePeriodsInfo', props.row.id ,'PayrollGuardSchemePeriodsInfo')"
                        class="btn btn-default btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registros de periodos del esquema de guardias"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button">
                    <i class="fa fa-th-list"></i>
                </button>
                <button v-if="approve_permission"
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar registro"
                        data-toggle="tooltip"
                        @click="'CE' == props.row.document_status.action || null == props.row.document_status.pending_period
                            ? 'javascript:void(0)' : approve(props.row)"
                        type="button"
                        :disabled="'CE' == props.row.document_status.action || null == props.row.document_status.pending_period">
                    <i class="fa fa-check"></i>
                </button>
                <button v-if="approve_permission"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Rechazar registro"
                        data-toggle="tooltip"
                        @click="'CE' == props.row.document_status.action || null == props.row.document_status.pending_period
                            ? 'javascript:void(0)' : reject(props.row)"
                        type="button"
                        :disabled="'CE' == props.row.document_status.action || null == props.row.document_status.pending_period">
                    <i class="fa fa-ban"></i>
                </button>
                <button @click="'CE' == props.row.document_status.action
                            ? 'javascript:void(0)' : editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro" data-toggle="tooltip" type="button"
                        :disabled="'CE' == props.row.document_status.action">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="'EL' != props.row.document_status.action
                            ? 'javascript:void(0)' : deleteRecord(props.row.id, 'payroll/guard-schemes')"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro" data-toggle="tooltip"
                        type="button"
                        :disabled="'EL' != props.row.document_status.action">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-client-table>
        <payroll-guard-scheme-info ref="GuardSchemeInfo"></payroll-guard-scheme-info>
        <payroll-guard-scheme-periods-info ref="GuardSchemePeriodsInfo"
                                           :confirm_permission="confirm_permission"
                                           :request_review_permission="request_review_permission">
        </payroll-guard-scheme-periods-info>
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            columns: ['institution', 'date', 'code', 'supervisor', 'approver', 'status', 'id'],
        };
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'institution': 'Organización',
            'date':        'Período',
            'code':        'Código',
            'supervisor':  'Supervisor',
            'approver':    'Aprobador',
            'status':      'Estatus',
            'id':          'Acción'
        };
        vm.table_options.sortable       = ['institution', 'date', 'code', 'supervisor', 'approver', 'status'];
        vm.table_options.filterable     = ['institution', 'date', 'code', 'supervisor', 'approver', 'status'];

    },
    mounted() {
        const vm = this;
        vm.readRecords(vm.route_list);
    },
    props: {
        approve_permission: String,
        index_permission: String,
        confirm_permission: String,
        request_review_permission: String,
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {
            //
        },

        /**
         * Método que establece los datos del registro seleccionado para el
         * cual se desea mostrar detalles.
         *
         * @method    setDetails
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param     string   ref       Identificador del componente
         * @param     integer  id        Identificador del registro seleccionado
         * @param     object  var_list  Objeto con las variables y valores a
         * asignar en las variables del componente
         */
        setDetails(ref, id, modal ,var_list = null) {
            const vm = this;
            if (var_list) {
                for(var i in var_list){
                    vm.$refs[ref][i] = var_list[i];
                }
            } else {
                vm.$refs[ref].record = JSON.parse(JSON.stringify(vm.$refs.tableResults.data.filter(r => {
                    return r.id === id;
                })[0]));
            }
            vm.$refs[ref].id = id;

            $(`#${modal}`).modal('show');
        },

        /**
         * Método que permite aprobar un periodo del esquema de guardias
         *
         * @author    Henry Paredes <hparedes@cenditel.gob.ve>
         */
        approve(row) {
            const vm = this;
            const url = vm.setUrl(`payroll/guard-schemes/approve/${row.document_status.pending_period?.id}`);

            bootbox.confirm({
                title: "¿Aprobar período de esquema de guardias?",
                size: 'medium',
                message: "<div class='row'>"+
                            "<div class='col-md-6'>"+
                                "<div class='form-group'>"+
                                    "<strong>Desde:</strong>"+
                                    "<div class='row' style='margin: 1px 0'>"+
                                        "<span class='col-md-12'>"+
                                            vm.format_date(row.document_status.pending_period.from_date, 'DD/MM/YYYY')+
                                        "</span>"+
                                    "</div>"+
                                "</div>"+
                            "</div>"+
                            "<div class='col-md-6'>"+
                                "<div class='form-group'>"+
                                    "<strong>Hasta:</strong>"+
                                    "<div class='row' style='margin: 1px 0'>"+
                                        "<span class='col-md-12'>"+
                                            vm.format_date(row.document_status.pending_period.to_date, 'DD/MM/YYYY')+
                                        "</span>"+
                                    "</div>"+
                                "</div>"+
                            "</div>"+
                        "</div>",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    }
                },
                callback: function(result) {
                    if (result) {
                        const fromDate = row.document_status.pending_period.from_date;
                        const toDate = row.document_status.pending_period.to_date;

                        if ('' == fromDate && '' == toDate) return false;
                        if (fromDate && toDate && fromDate > toDate) {
                            bootbox.alert("La fecha de inicio debe ser menor que la fecha de fin");
                            return false;
                        }
                        vm.loading = true;

                        axios.put(url, {
                            from_date: fromDate,
                            to_date: toDate,
                            payroll_guard_scheme_id: row.id,
                        }).then(response => {
                            if (response.status == 200){
                                location.reload();
                            }
                        }).catch(error => {
                            if (typeof(error.response) !="undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                            }
                        });
                        vm.loading = false;
                    }
                }
            });
        },
        reject(row) {
            const vm = this;
            const url = vm.setUrl(`payroll/guard-schemes/reject/${row.document_status.pending_period?.id}`);

            let dialog = bootbox.confirm({
				    title: '¿Rechazar período de esquema de guardias?',
                    size: 'medium',
				    message:"<div class='row'>"+
                                "<div class='col-md-6'>"+
                                    "<div class='form-group'>"+
                                        "<strong>Desde:</strong>"+
                                        "<div class='row' style='margin: 1px 0'>"+
                                            "<span class='col-md-12'>"+
                                                vm.format_date(row.document_status.pending_period.from_date, 'DD/MM/YYYY')+
                                            "</span>"+
                                        "</div>"+
                                    "</div>"+
                                "</div>"+
                                "<div class='col-md-6'>"+
                                    "<div class='form-group'>"+
                                        "<strong>Hasta:</strong>"+
                                        "<div class='row' style='margin: 1px 0'>"+
                                            "<span class='col-md-12'>"+
                                                vm.format_date(row.document_status.pending_period.to_date, 'DD/MM/YYYY')+
                                            "</span>"+
                                        "</div>"+
                                    "</div>"+
                                "</div>"+
								"<div class='col-md-12'>"+
									"<div class='form-group'>"+
										"<label>Observaciones</label>"+
										"<textarea data-toggle='tooltip' class='form-control input-sm'"+
											" title='Indique las observaciones presentadas en el esquema de guardias'"+
											" id='observation'>"+
										"</textarea>"+
									"</div>"+
								"</div>"+
							"</div>",
				    buttons: {
	    				cancel: {
	    					label: '<i class="fa fa-times"></i> Cancelar'
	    				},
	    				confirm: {
	    					label: '<i class="fa fa-check"></i> Confirmar'
	    				}
	    			},
	    			callback: function(result) {
                        if (result) {
                            const observation = document.getElementById('observation').value;
                            vm.loading = true;

                            axios.put(url, {
                                observation: observation
                            }).then(response => {
                                if (response.status == 200){
                                    location.reload();
                                }
                            }).catch(error => {
                                if (typeof(error.response) !="undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                }
                            });
                            vm.loading = false;
                        }
                    }
	    		});
        }
    },
};
</script>
