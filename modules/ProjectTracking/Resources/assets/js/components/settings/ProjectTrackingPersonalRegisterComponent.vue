<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center"
		id="projecttracking_personal_register">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)"
			title="Registros del personal" data-toggle="tooltip"
			@click="addRecord('add_projecttracking_personal_register', 'projecttracking/personal-register', $event)">
			<i class="icofont ion-person-stalker ico-3x"></i>
			<span>Personal</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_projecttracking_personal_register">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont ion-person-stalker ico-3x"></i>
							Personal
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
							<div class="col-md-6">
								<div class="form-group is-required">
									<label for="name">Nombre:</label>
									<input type="text" id="name" placeholder="Nombre" class="form-control input-sm"
										data-toggle="tooltip" title="Indique el nombre de la persona (requerido)"
										v-model="record.name">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group is-required">
									<label for="last_name">Apellido:</label>
									<input type="text" id="last_name" placeholder="Apellido"
										class="form-control input-sm" data-toggle="tooltip"
										title="Indique el apellido de la persona (requerido)"
										v-model="record.last_name">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group is-required">
									<label for="id_number">Cédula:</label>
									<input type="text" id="id_number" placeholder="Cédula"
										class="form-control input-sm numeric" data-toggle="tooltip"
										title="Indique el número de Cédula (requerido)" v-model="record.id_number">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group is-required" name="category">
									<label>Cargo:</label>
									<select2 :options="positions_list" data-toggle="tooltip"
										title="Seleccione el cargo que ejerce la persona" v-model="record.position_id">
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
							<button type="button" @click="createRecord('projecttracking/personal-register')"
								class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
						</div>
					</div>
					<div class="modal-body modal-table text-center">
						<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="id" slot-scope="props" class="text-center">
								<button @click="initUpdate(props.row.id, $event)"
									class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
									data-toggle="tooltip" type="button">
									<i class="fa fa-edit"></i>
								</button>
								<button @click="deleteRecord(props.row.id, 'projecttracking/personal-register')"
									class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro"
									data-toggle="tooltip" type="button">
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
				name: '',
				last_name: '',
				id_number: '',
				position_id: ''
			},
			errors: [],
			records: [],
			positions_list: [],
			columns: ['name', 'last_name', 'id_number', 'position_name', 'id'],
		}
	},
	methods: {
		/**
		 * Método que borra todos los datos del formulario
		 *
		 * @author  Oscar González <xmaestroyixx@gmail.com>
		 */
		reset() {
			this.record = {
				name: '',
				last_name: '',
				id_number: '',
				position_id: ''
			};
		},
		getPositions() {
			const vm = this;
			axios.get(`${window.app_url}/projecttracking/get-positions`).then(response => {
				vm.positions_list = response.data;
			});
		},
	},
	created() {
		this.table_options.headings = {
			name: 'Nombre',
			last_name: 'Apellido',
			id_number: 'Cédula',
			position_name: 'Cargo',
			id: 'Acción'
		};
		this.table_options.sortable = ['name', 'last_name', 'id_number', 'position_name'];
		this.table_options.filterable = ['name', 'last_name', 'id_number', 'position_name'];
		this.table_options.columnsClasses = {
			name: 'col-md-3',
			last_name: 'col-md-3',
			id_number: 'col-md-2',
			position_name: 'col-md-3',
			id: 'col-md-1'
		};
	},
	mounted() {
		const vm = this;
		$("#add_projecttracking_personal_register").on('show.bs.modal', function () {
			vm.reset();
		});
		vm.getPositions();
	},

}
</script>