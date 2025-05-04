<template>
	<div class="col-xs-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary"
		   href="#" title="Registros de tipos de cuenta bancaria"
		   data-toggle="tooltip"
		   @click="addRecord( 'add_account_type','/finance/account-types', $event)">
			<i class="icofont icofont-mathematical ico-3x"></i>
			<span>Tipos<br>Cuenta</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_account_type">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-mathematical inline-block"></i>
							Tipo de Cuenta
						</h6>
					</div>
					<div class="modal-body">
						<div class="alert alert-danger" v-if="errors.length > 0">
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
								<li v-for="(error, index) in errors" :key="index">{{ error }}</li>
							</ul>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group is-required">
									<label>Nombre:</label>
									<input type="text" placeholder="Nombre del tipo de cuenta"
										   data-toggle="tooltip" v-model="record.name"
										   title="Indique el nombre del Tipo de cuenta (requerido)"
										   class="form-control input-sm">
			                    </div>
							</div>
							<div class="col-md-6">
								<div class="form-group is-required">
									<label>Código:</label>
									<input type="text" placeholder="Código asociado al tipo de cuenta"
										   data-toggle="tooltip" v-model="record.code"
										   title="Indique el código asociado al tipo de cuenta (requerido)"
										   class="form-control input-sm">
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
							<button type="button" @click="createRecord('finance/account-types')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options" class="text-center">
	                		<div slot="id" slot-scope="props">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, '/finance/account-types')"
										class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
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
	</div>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					name: '',
					code: ''
				},
				errors: [],
				records: [],
				columns: ['name', 'code', 'id'],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
			 */
			reset() {
				this.record = {
					id: '',
					name: '',
					code: ''
				};
			},
		},
		created() {
			this.table_options.headings = {
				'name': 'Nombre',
				'code': 'Código',
				'id': 'Acción',
			};
			this.table_options.sortable = ['name'];
			this.table_options.filterable = ['name'];
			this.table_options.columnsClasses = {
				'name': 'col-md-5',
				'code': 'col-md-5',
				'id': 'col-md-2',
			};
		},
	};
</script>
