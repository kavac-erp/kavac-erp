<template>
	<div class="col-xs-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
		   title="Registros de departamentos" data-toggle="tooltip"
		   @click="addRecord('add_citizenservice-department', 'citizenservice/departments', $event)">
           <i class="icofont icofont-briefcase-alt-1 ico-3x"></i>
		   <span>Departamentos</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_citizenservice-department">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-briefcase-alt-1 ico-3x"></i>
							Departamentos
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
                                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
        						<div class="form-group is-required">
        							<label for="name">Nombre:</label>
        							<input type="text" id="name" placeholder="Nombre"
										   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								   class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
        								   title="Indique el nombre del departamento">
        							<input type="hidden" name="id" id="id" v-model="record.id">
        	                    </div>
                            </div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="description">Descripción:</label>
									<input type="text" id="description" placeholder="Descripción"
										   class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
										   title="Indique la descripción del departamento">
								</div>
							</div>
							<div v-if="isPayrollActive" class="col-md-6">
                				<div class="form-group is-required" name="category">
                  					<label>Director:</label>
                  					<select2 :options="payroll_staffs" id="director" data-toggle="tooltip"
                    					title="Seleccione el director responsable del Departamento (requerido)" v-model="record.director_id">
                  					</select2>
                				</div>
              				</div>
							  <div v-if="isPayrollActive" class="col-md-6">
                				<div class="form-group" name="category">
                  					<label>Coordinador:</label>
                  					<select2 :options="payroll_staffs" id="coordinator" data-toggle="tooltip"
                    					title="Seleccione el coordinador responsable del Departamento (requerido)" v-model="record.coordinator_id">
                  					</select2>
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
							<button type="button" @click="createRecord('citizenservice/departments')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="director_id" slot-scope="props">
                  				{{ props.row.department_director ?
									(props.row.department_director.last_name ? props.row.department_director.first_name + ' ' + props.row.department_director.last_name
										: props.row.department_director.first_name) : 'N/A'}}
                			</div>
							<div slot="coordinator_id" slot-scope="props">
                  				{{ props.row.department_coordinator ?
									(props.row.department_coordinator.last_name ? props.row.department_coordinator.first_name + ' ' + props.row.department_coordinator.last_name
										: props.row.department_coordinator.first_name) : 'N/A'}}
                			</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" v-has-tooltip type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'citizenservice/departments')"
										class="btn btn-danger btn-xs btn-icon btn-action"
										title="Eliminar registro" data-toggle="tooltip" v-has-tooltip
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
		props: [
			'isPayrollActive'
		],
		data() {
			return {
				record: {
					id: '',
					name: '',
					description: '',
					director_id: '',
					coordinator_id: '',
				},
				errors: [],
				records: [],
				payroll_staffs: [],
				columns: ['name', 'description', 'director_id', 'coordinator_id', 'id'],
				payroll: ""
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					name: '',
					description: '',
					director_id: '',
					coordinator_id: '',
				};
			},
			getPayrollStaffs() {
      			const vm = this;
      			axios.get(`${window.app_url}/payroll/get-staffs`).then(response => {
        			vm.payroll_staffs = response.data;
      			});
			},
			/**
        	* Método que sobrescribe el método que permite crear o actualizar un registro
        	*
        	* @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
        	* @author  Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
			*
        	* @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
        	* @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
        	*                         El valor por defecto es verdadero.
        	* @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
        	*                         El valor por defecto es verdadero.
        	*/
			async createRecord(url, list = true, reset = true) {
        	    const vm = this;
        	    url = vm.setUrl(url);

				vm.errors = [];

        	    if (vm.record.id) {
        	        vm.updateRecord(url);
        	    }
        	    else {
        	        vm.loading = true;
        	        var fields = {};

        	        for (var index in vm.record) {
        	            fields[index] = vm.record[index];
        	        }
        	        await axios.post(url, fields).then(response => {
        	            if (typeof (response.data.redirect) !== "undefined") {
        	                location.href = response.data.redirect;
        	            }
        	            else {
        	                vm.errors = [];
        	                if (reset) {
        	                    vm.reset();
        	                }
        	                if (list) {
        	                    vm.readRecords(url);
        	                }

        	                vm.showMessage('store');
        	            }
        	        }).catch(error => {
        	            vm.errors = [];

        	            if (typeof (error.response) != "undefined") {
        	                if (error.response.status == 403) {
        	                    vm.showMessage(
        	                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
        	                    );
        	                }
        	                for (var index in error.response.data.errors) {
        	                    if (error.response.data.errors[index]) {
        	                        vm.errors.push(error.response.data.errors[index][0]);
        	                    }
        	                }
        	            }

        	        });

        	        vm.loading = false;
        	    }

        	},
		},
		created() {
			this.table_options.headings = {
				'name': 'Nombre',
				'description': 'Descripción',
				'director_id': 'Director',
				'coordinator_id': 'Coordinador',
				'id': 'Acción'
			};
			this.table_options.sortable = ['name', 'director_id', 'coordinator_id'];
			this.table_options.filterable = ['name', 'director_id', 'coordinator_id'];
			this.table_options.columnsClasses = {
				'name': 'col-md-3',
				'description': 'col-md-2',
				'director_id': 'col-md-3',
				'coordinator_id': 'col-md-3',
				'id': 'col-md-1'
			};
		},
		mounted() {
			const vm = this;
			vm.getPayrollStaffs();
		},
	};
</script>
