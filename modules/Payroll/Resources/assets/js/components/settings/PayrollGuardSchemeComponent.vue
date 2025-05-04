<template>
	<section id="payrollGuardSchemeFormComponent">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de esquema de guardias" data-toggle="tooltip"
           @click="addRecord('add_payroll_guard_scheme', 'payroll/guard-schemes', $event)">
           <i class="icofont icofont-calendar ico-3x"></i>
           <span>Esquema de<br>Guardias</span>
        </a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_guard_scheme">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-calendar ico-3x"></i>
							Esquema de Guardias
						</h6>
					</div>
					<div class="modal-body">
                        <!-- mensajes de error -->
						<div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                        @click.prevent="errors = []">
                                    <span aria-hidden="true">
                                        <i class="now-ui-icons ui-1_simple-remove"></i>
                                    </span>
                                </button>
                                <ul>
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <!-- ./mensajes de error -->
                        <div class="row">
                            <!-- Organización -->
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Organización:</label>
                                    <select2 :options="institutions" v-model="record.institution_id"></select2>
                                </div>
                            </div>
                            <!-- ./Organización -->
                            <!-- período a planificar -->
							<div class="col-md-4">
								<div class="form-group is-required">
									<label>Desde:</label>
									<input type="date" id="from_date" placeholder="Desde"
											data-toggle="tooltip" title="Indique la fecha inicial del período a planificar"
											:min="start_operations_date"
											:max="(record.to_date == '') ? '' : record.to_date"
											class="form-control input-sm" v-model="record.from_date">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group is-required">
									<label>Hasta:</label>
									<input type="date" id="to_date" placeholder="Hasta"
											data-toggle="tooltip" title="Indique la fecha final período a planificar"
                                            @input="generateData"
											:min="record.from_date" :disabled="(record.from_date == '')"
											class="form-control input-sm" v-model="record.to_date">
								</div>
							</div>
							<!-- ./período a planificar -->
                        </div>
						<div class="row">
							<!-- Código de grupo de supervisados -->
							<div class="col-md-4">
								<div class="form-group is-required">
									<label>Código:</label>
									<select2 :options="payroll_supervised_groups"
                                        @input="getDatasupervisedGroup()"
                                        v-model="record.payroll_supervised_group_id"></select2>
								</div>
							</div>
							<!-- ./Código de grupo de supervisados -->
                            <div class="col-md-4" v-if="record.payroll_supervised_group">
                                <div class="form-group">
                                    <strong>Supervisor:</strong>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12" id="supervisor">
                                            {{ record.payroll_supervised_group.supervisor.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" v-if="record.payroll_supervised_group">
                                <div class="form-group">
                                    <strong>Aprobador:</strong>
                                    <div class="row" style="margin: 1px 0">
                                        <span class="col-md-12" id="Approver">
                                            {{ record.payroll_supervised_group.approver.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
						</div>
                        <!-- Tabla para la planificación de esquemas de guardias -->
                        <div class="table-responsive" style="overflow-y: auto; height: 100%;"
                            v-if="'' !== record.from_date && '' !== record.to_date">
                            <table class="table table-hover table-striped table-bordered">
                                <thead style="top: -2px">
                                    <tr>
                                        <th class="text-capitalize" rowspan="2">N°</th>
                                        <th class="text-capitalize" style="min-width: 100px" rowspan="2">Ficha</th>
                                        <th class="text-capitalize" style="min-width: 100px" rowspan="2">Trabajador</th>
                                        <th class="text-capitalize" style="min-width: 100px"
                                            v-for="(month, index) in months" :key="index"
                                            :colspan="daysPerMonth[month].length">
                                            <span>{{ month }}</span>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th v-for="(field, index) in totalDays" :key="index"
                                            class="text-capitalize cursor-pointer"
                                            :style="(field.view)
                                                ? 'min-width: 100px; background-color: white;'
                                                : 'min-width: 100px; background-color: darkgray;'"
                                            @click="setEditColumns(index)">
                                            <span>{{ field['day'] }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="staff in visibleRows" :key="staff.id">
                                        <td>{{ staff.index }}</td>
                                        <td>{{ staff.worksheet_code }}</td>
                                        <td>{{ staff.name }}</td>
                                        <td v-for="(field, fIndex) in totalDays" :key="fIndex"
                                            class="td-with-border"
                                            :style="field.view ? 'cursor: auto;' : 'cursor: not-allowed;'">
                                            <div class="custom-multiselect" style="display: grid;"
                                                v-if="!field.view &&
                                                    record.data_source[staff.id + '-' + field['month'] + '-' + field['day']] &&
                                                    record.data_source[staff.id + '-' + field['month'] + '-' + field['day']].length > 0">
                                                <div class="btn-group" style="background-color: white; color: darkgray; white-space: nowrap;">
                                                    <button id="custom-multiselect_button" type="button" class="btn btn-secondary dropdown-toggle text-left" data-toggle="dropdown" data-display="static" aria-expanded="false"
                                                            style="background-color: white; color: darkgray;">
                                                        <div class="multiselect__tags" style="display: flex; flex-wrap: wrap;">
                                                            <div class="multiselect__tags-wrap" style="inline-grid"
                                                                v-for="(selection, index) in record.data_source[staff.id + '-' + field['month'] + '-' + field['day']]" :key="index">
                                                                <span class="multiselect__tag" :style="'white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'">
                                                                    {{ selection.acronym }}
                                                                    <span class="badge badge-light" :style="'left: 1rem;'"
                                                                        v-if="selection.count > 1">
                                                                        {{ selection.count }}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                            <v-custom-multiselect
                                                v-else-if="field.view"
                                                track_by="acronym"
                                                :options="payroll_time_parameters"
                                                v-model="record.data_source[staff.id + '-' + field['month'] + '-' + field['day']]">
                                                <template v-slot:customOptionLabel="{ option }">
                                                    <span>{{ option.text }}</span>
                                                </template>
                                            </v-custom-multiselect>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="VuePagination-2 row col-md-12 ">
                                <nav class="text-center">
                                    <ul class="pagination VuePagination__pagination" style="">
                                        <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-chunk" v-if="page != 1">
                                            <a class="page-link" @click="changePage(1)">PRIMERO</a>
                                        </li>
                                        <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-chunk disabled">
                                            <a class="page-link">&lt;&lt;</a>
                                        </li>
                                        <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-page" v-if="page > 1">
                                            <a class="page-link" @click="changePage(page - 1)">&lt;</a>
                                        </li>
                                        <li :class="(page == number)?'VuePagination__pagination-item page-item active':'VuePagination__pagination-item page-item'" v-for="(number, index) in pageValues" :key="index" v-if="number <= lastPage">
                                            <a class="page-link active" role="button" @click="changePage(number)">{{number}}</a>
                                        </li>
                                        <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-next-page" v-if="page < lastPage">
                                            <a class="page-link" @click="changePage(page + 1)">&gt;</a>
                                        </li>
                                        <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-next-chunk disabled">
                                            <a class="page-link">&gt;&gt;</a>
                                        </li>
                                        <li class="VuePagination__pagination-item page-item  VuePagination__pagination-item-prev-chunk" v-if="lastPage != page">
                                            <a class="page-link" @click="changePage(lastPage)">ÚLTIMO</a>
                                        </li>
                                    </ul>
                                    <p class="VuePagination__count text-center col-md-12" style=""> </p>
                                </nav>
                            </div>
                        </div>
                        <!-- Final de la tabla para la planificación de esquemas de guardias -->
	                </div>
					<div class="modal-footer">
	                	<div class="form-group">
	                		<button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
									@click="clearFilters" data-dismiss="modal">
								Cerrar
							</button>
							<button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
									@click="reset()">
								Cancelar
							</button>
							<button type="button" @click="createRecord('payroll/guard-schemes')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
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
	                		<div slot="id" slot-scope="props" class="text-center">
                                <button v-if="props.row.document_status == 'PR'"
                                        class="btn btn-success btn-xs btn-icon btn-action"
                                        title="Aprobar registro"
                                        data-toggle="tooltip"
                                        type="button"
                                        :disabled="'CE' == props.row.document_status">
                                    <i class="fa fa-check"></i>
                                </button>
                                <button v-if="props.row.document_status == 'PR'"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Rechazar registro"
                                        data-toggle="tooltip"
                                        type="button"
                                        :disabled="'CE' == props.row.document_status">
                                    <i class="fa fa-block"></i>
                                </button>
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'payroll/guard-schemes')"
										class="btn btn-danger btn-xs btn-icon btn-action"
										title="Eliminar registro" data-toggle="tooltip"
										type="button"
                                        :disabled="'PR' == props.row.document_status">
									<i class="fa fa-trash-o"></i>
								</button>
	                		</div>
	                	</v-client-table>
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
					id:                          '',
                    institution_id:              '',
                    from_date:                   '',
                    to_date:                     '',
                    payroll_supervised_group_id: '',
                    payroll_supervised_group:    null,
                    data_source:                 {},
				},
                payroll_supervised_groups: [],
                institutions: [],
				errors:  [],
				records: [],
				columns: ['institution', 'date', 'code', 'supervisor', 'approver', 'id'],
                payroll_time_parameters: [],
                supervised_groups: {},
                months: [],
                translateMonths: {
                    'January': 'Enero',
                    'February': 'Febrero',
                    'March': 'Marzo',
                    'April': 'Abril',
                    'May': 'Mayo',
                    'June': 'Junio',
                    'July': 'Julio',
                    'August': 'Agosto',
                    'September': 'Septiembre',
                    'October': 'Octubre',
                    'November': 'Noviembre',
                    'December': 'Diciembre',
                },
                editColumns: {},
                daysPerMonth: [],
                totalDays: [],
                pageValues: [1,2,3,4,5,6,7,8,9,10],
                lastPage: '',
                page: 1,
                perPage: 5,
			}
		},
        props: {
            start_operations_date: {
                type:     [Date, String],
                required: false,
                default:  ''
            }
        },
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			reset() {
				const vm = this;
				vm.errors = [];
				vm.record = {
					id:                          '',
                    institution_id:              '',
                    from_date:                   '',
                    to_date:                     '',
                    payroll_supervised_group_id: '',
                    payroll_supervised_group:    null,
                    data_source:                 {},
				};
			},
            async getDatasupervisedGroup() {
                const vm = this;
                if ('' !== vm.record.payroll_supervised_group_id) {
                    vm.record.payroll_supervised_group = vm.payroll_supervised_groups.find(function ($group) {
                        return vm.record.payroll_supervised_group_id == $group['id'];
                    });
                } else {
                    vm.record.payroll_supervised_group = null;
                }
                await vm.generateData();
            },
            async generateData() {
                const vm = this;
                vm.months = [];
                vm.daysPerMonth = [];
                vm.totalDays = [];

                if (
                    '' === vm.record.from_date &&
                    '' === vm.record.to_date &&
                    vm.record.payroll_supervised_group
                ) {
                    return false;
                };

                vm.loading = true;
                let start_date = moment(vm.record.from_date);
                let end_date = moment(vm.record.to_date);

                while (start_date.isBefore(end_date) || start_date.isSame(end_date)) {
                    if (!vm.months.includes(vm.translateMonths[start_date.format("MMMM")])) {
                        vm.months.push(vm.translateMonths[start_date.format("MMMM")]);
                        vm.daysPerMonth[vm.translateMonths[start_date.format("MMMM")]] = [];
                    }
                    vm.daysPerMonth[vm.translateMonths[start_date.format("MMMM")]].push(start_date.format("D"));
                    vm.totalDays.push({
                        'month': vm.translateMonths[start_date.format("MMMM")],
                        'day': start_date.format("D"),
                        'view': false
                    });
                    start_date.add(1, 'day');
                }
                if (vm.record.payroll_supervised_group) {
                    vm.lastPage = Math.ceil(vm.record.payroll_supervised_group.payroll_staffs.length / vm.perPage);
                }
                vm.loading = false;
            },
            /**
             * Obtiene los datos de los trabajadores registrados agrupados por departamento
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             */
            async getPayrollTimeParameters() {
                const vm = this;
                vm.payroll_time_parameters = [];
                await axios.get(`${window.app_url}/payroll/get-time-parameters?group=false`).then(response => {
                    vm.payroll_time_parameters = Object.values(response.data);
                });
            },
            changePage(page) {
                const vm = this;
                vm.page = page;
                var pag = 0;
                while(1) {
                    if (pag + 10 >= vm.page) {
                        pag += 1;
                        break;
                    } else {
                        pag += 10;
                    }
                }
                vm.pageValues = [];
                for (var i = 0; i < 10; i++) {
                    vm.pageValues.push(pag + i);
                }
            },
            setEditColumns(index) {
                const vm = this;
                vm.totalDays[index].view = !vm.totalDays[index].view;
            },
		},
		created() {
			const vm = this;
            vm.table_options.headings = {
                'institution': 'Organización',
                'date':        'Período',
                'code':        'Código',
                'supervisor':  'Supervisor',
                'approver':    'Aprobador',
                'id':          'Acción'
            };
            vm.table_options.sortable       = ['institution', 'date', 'code', 'supervisor', 'approver'];
            vm.table_options.filterable     = ['institution', 'date', 'code', 'supervisor', 'approver'];
            vm.table_options.columnsClasses = {
                'institution': 'col-xs-2',
                'date':        'col-xs-2',
                'code':        'col-xs-2',
                'supervisor':  'col-xs-2',
                'approver':    'col-xs-2',
                'id':          'col-xs-2',
            };
		},
		mounted () {
			const vm = this;
			$("#add_payroll_guard_scheme").on('show.bs.modal', function () {
                vm.reset();
                vm.getPayrollSupervisedGroups();
                vm.getPayrollTimeParameters();
                vm.getInstitutions();
            });
		},
        watch: {
            perPage(res) {
                if (this.page == 1){
                    this.changePage(res);
                } else {
                    this.changePage(1);
                }
            },
            page(res) {
                this.changePage(res);
            }
        },
        computed: {
            visibleRows() {
                const vm = this;
                let records = (vm.record.payroll_supervised_group)
                    ? vm.record.payroll_supervised_group.payroll_staffs
                        ? vm.record.payroll_supervised_group.payroll_staffs
                        : []
                    : [];
                const startIndex = (vm.page - 1) * vm.perPage;
                const endIndex = startIndex + vm.perPage;

                return records.slice(startIndex, endIndex).map((staff, index) => ({
                    ...staff,
                    index: startIndex + index + 1
                }));
            },
        },
	};
</script>
