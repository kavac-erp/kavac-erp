<template>
	<section class="text-center" id="payroll_arc_responsible">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
		   title="Registros de responsables de ARC" data-toggle="tooltip"
		   @click="addRecord('add_payroll_arc_responsible', 'payroll/arc-responsible', $event)">
           <i class="icofont icofont-users ico-3x"></i>
			<span>Responsables de ARC</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_arc_responsible">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-users ico-3x"></i>
							Responsables de ARC
						</h6>
					</div>
					<div class="modal-body">
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
                        <div class="row">
							<div class="col-md-4" id="helpEmploymentStaff">
								<div class="form-group is-required">
									<label>Trabajador:</label>
									<select2
										:options="payroll_staffs"
										v-model="record.payroll_staff_id">
									</select2>
									<input type="hidden" v-model="record.id">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group is-required">
									<label>Desde:</label>
									<input class="form-control input-sm"
										type="date"
										v-model="record.start_date"
										:max="fiscal_date"/>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>Hasta:</label>
									<input class="form-control input-sm"
										type="date"
										v-model="record.end_date"
										:disabled="record.start_date == ''"
										:min="record.start_date"
										:max="fiscal_date"/>
								</div>
							</div>
                        </div>
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
							<button type="button" @click="createRecord('payroll/arc-responsible')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="payroll_staff" slot-scope="props" class="text-center">
								{{ props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name }}
							</div>
							<div slot="start_date" slot-scope="props" class="text-center">
								{{ props.row.start_date ? format_date(props.row.start_date) : '' }}
							</div>
							<div slot="end_date" slot-scope="props" class="text-center">
								{{ props.row.end_date ? format_date(props.row.end_date) : '' }}
							</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'payroll/arc-responsible')"
										class="btn btn-danger btn-xs btn-icon btn-action"
										title="Eliminar registro" data-toggle="tooltip"
										type="button">
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
					id: '',
					payroll_staff_id: '',
                    start_date: '',
                    end_date: ''
				},
				fiscal_date: '',
				payroll_staffs: [],
				errors: [],
				records: [],
				columns: ['payroll_staff', 'start_date', 'end_date', 'id'],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			reset() {
				this.record = {
					id: '',
					payroll_staff_id: '',
                    start_date: '',
                    end_date: ''
				};
				this.errors = [];
			},
		},
		created() {
			this.table_options.headings = {
                'payroll_staff': 'Trabajador',
                'start_date': 'Desde',
                'end_date': 'Hasta',
				'id': 'Acción'
			};
			this.table_options.sortable = ['payroll_staff', 'start_date', 'end_date'];
			this.table_options.filterable = ['payroll_staff', 'start_date', 'end_date'];
			this.table_options.columnsClasses = {
				'payroll_staff': 'col-md-6',
                'start_date': 'col-md-2',
                'end_date': 'col-md-2',
				'id': 'col-md-2'
			};
		},
		mounted () {
			const vm = this;
			$("#add_payroll_arc_responsible").on('show.bs.modal', function() {
				vm.fiscal_date = window.execution_year + '-12-31';
				vm.getPayrollStaffs();
                vm.reset();
            });
		},
	};
</script>
