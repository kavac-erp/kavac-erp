<template>
	<div class="text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href="javascript:void(0)" title="Registros de Subservicios"
			data-toggle="tooltip"
			@click="addRecord('add_sale_list_subservices_method', 'sale/list-subservices-method', $event)">
			<i class="icofont icofont-law-document ico-3x"></i>
			<span>Subservicios</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_sale_list_subservices_method">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-law-document ico-3x"></i>
							Subservicios
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
									<label>Tipo de Servicio:</label>
									<select2 :options="sale_type_goods" v-model="record.sale_type_good"></select2>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group is-required">
									<label for="name">Nombre:</label>
									<input type="text" id="name" placeholder="Nombre" class="form-control input-sm"
										v-model="record.name" data-toggle="tooltip" title="Indique el nombre (requerido)">
									<input type="hidden" name="id" id="id" v-model="record.id">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group is-required">
									<label for="description">Descripción:</label>
									<input type="text" id="description" placeholder="Descripción"
										class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
										title="Indique la descripción (requerido)">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<a data-toggle="tooltip"
										title="Establecer los atributos del tipo de bien para gestionar las variantes">
										<label for="" class="control-label">Atributos Personalizados</label>
										<div class="col-12">
											<div class="bootstrap-switch-mini">
												<input type="checkbox" class="form-control bootstrap-switch"
													id="define_attributes" name="define_attributes" data-on-label="Si"
													data-off-label="No" value="true" v-model="record.define_attributes">
											</div>
										</div>
									</a>
								</div>
							</div>
						</div>
						<div v-show="this.record.define_attributes">
							<div class="row" style="margin: 10px 0">
								<h6 class="card-title cursor-pointer" @click="addAttribute()">
									Gestionar nuevo atributo <i class="fa fa-plus-circle"></i>
								</h6>
							</div>
							<div class="row" style="margin: 20px 0">

								<div class="col-6" v-for="(attribute, index) in record.sale_list_subservices_attribute"
									:key="index">

									<div class="d-inline-flex">
										<div class="col-10">
											<div class="form-group">
												<input type="text" placeholder="Nombre del nuevo atributo"
													data-toggle="tooltip"
													title="Indique el nombre del atributo del tipo de bien que desee hacer seguimiento (opcional)"
													v-model="attribute.value" class="form-control input-sm">
											</div>
										</div>
										<div class="col-2">
											<div class="form-group">
												<button class="btn btn-sm btn-danger btn-action" type="button"
													@click="removeRow(index, record.sale_list_subservices_attribute)"
													title="Eliminar este dato" data-toggle="tooltip">
													<i class="fa fa-minus-circle"></i>
												</button>
											</div>
										</div>
									</div>
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
							<button type="button" @click="createRecord('sale/list-subservices-method')"
								class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
						</div>
					</div>
					<div class="modal-body modal-table">
						<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="attributes" slot-scope="props">
								<div v-if="props.row.define_attributes">
									<div v-for="(att, index) in props.row.sale_list_subservices_attribute" :key="index">
										<span>
											{{ att.value }}
										</span>
									</div>
								</div>
								<div v-else>
									<span>N/A</span>
								</div>
							</div>
							<div slot="id" slot-scope="props" class="text-center">
								<button @click="initUpdate(props.row.id, $event)"
									class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro"
									data-toggle="tooltip" type="button">
									<i class="fa fa-edit"></i>
								</button>
								<button @click="deleteRecord(props.row.id, 'sale/list-subservices-method')"
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
				id: '',
				name: '',
				description: '',
				sale_type_good: '',
				define_attributes: false,
				sale_list_subservices_attribute: [],
			},
			sale_type_goods: [],
			errors: [],
			records: [],
			columns: ['sale_type_good_name', 'name', 'description', 'attributes', 'id'],
		}
	},
	methods: {
		/**
		 * Método que borra todos los datos del formulario
		 *
		 * @author  Miguel Narvaez <mnarvaez@cenditel.gob.ve>
		 */
		reset() {
			this.record = {
				id: '',
				name: '',
				description: '',
				define_attributes: false,
				sale_list_subservices_attribute: [],
			};
		},
		addAttribute() {
			var field = { id: '', value: '', sale_list_subservices_id: '' };
			this.record.sale_list_subservices_attribute.push(field);
		},
		getSaleTypeGoods() {
			const vm = this;
			vm.sale_type_goods = [];

			axios.get('/sale/get-type-goods').then(response => {
				vm.sale_type_goods = response.data.records;
			});
		},
	},
	created() {
		this.table_options.headings = {
			'sale_type_good_name': 'Tipo de Servicio',
			'name': 'Nombre',
			'description': 'Descripción',
			'attributes': 'Atributos',
			'id': 'Acción'
		};
		this.table_options.sortable = ['sale_type_good_name','name', 'description'];
		this.table_options.filterable = ['sale_type_good_name', 'name', 'description'];
		this.table_options.columnsClasses = {
			'sale_type_good' : 'col-xs-2',
			'name': 'col-xs-2',
			'description': 'col-xs-3',
			'attributes': 'col-xs-3',
			'id': 'col-xs-2'
		};
		this.getSaleTypeGoods();
	},
	mounted() {
		const vm = this;
		vm.switchHandler('define_attributes');
	}
};
</script>