<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary"
		   href="javascript:void(0)" title="Registro de monedas" data-toggle="tooltip"
		   @click="addRecord('add_currency', 'currencies', $event)">
			<i class="icofont icofont-cur-dollar ico-3x"></i>
			<span>Monedas</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_currency">
			<div class="modal-dialog vue-crud" role="currency">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-cur-dollar inline-block"></i>
							Moneda
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
						<div class="row">
							<div class="col-12 col-md-4">
								<div class="form-group">
									<label>Pais:</label>
									<select2 :options="countries" v-model="record.country_id"></select2>
									<input type="hidden" v-model="record.id">
			                    </div>
							</div>
							<div class="col-12 col-md-4">
								<div class="form-group is-required">
									<label>Nombre:</label>
									<input type="text" placeholder="Nombre de la moneda" data-toggle="tooltip"
										   title="Indique el nombre de la moneda (requerido)"
										   class="form-control input-sm" v-model="record.name" v-is-text>
			                    </div>
							</div>
							<div class="col-12 col-md-4">
								<div class="form-group is-required">
									<label>Nombre en plural:</label>
									<input type="text" placeholder="Nombre de la moneda en plural" data-toggle="tooltip"
										   title="Indique el nombre de la moneda en plural (requerido)"
										   class="form-control input-sm" v-model="record.plural_name" v-is-text>
			                    </div>
							</div>
							<div class="col-12 col-md-2">
								<div class="form-group is-required">
									<label>Símbolo:</label>
									<input
										type="text" placeholder="Símbolo" data-toggle="tooltip"
										title="Indique el símbolo de la moneda (requerido). Ejemplo: Bs., $, €, £, ₽, o en su defecto, las siglas de la moneda. Ejemplo: USD, EUR, GBP, etc."
										class="form-control input-sm" v-model="record.symbol"
										onpaste="return false;" v-has-symbols
									>
			                    </div>
							</div>
							<div class="col-12 col-md-2">
								<div class="form-group is-required">
									<label>Decimales</label>
									<input type="number" data-toggle="tooltip"
										   title="Indique la cantidad de decimales para la moneda a registrar"
                                           class="form-control input-sm" v-model="record.decimal_places" step="1"
										   min="2">
								</div>
							</div>
							<div class="col-12 col-md-2">
								<div class="form-group is-required">
									<label>Por defecto:</label>
									<div class="custom-control custom-switch" data-toggle="tooltip"
											title="Indique si es la moneda por defecto en la aplicación">
										<input type="checkbox" class="custom-control-input"
												id="defaultCurrency" v-model="record.default" :value="true">
										<label class="custom-control-label" for="defaultCurrency"></label>
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
							<button type="button" @click="createRecord('currencies')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
					<div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'currencies')"
										class="btn btn-danger btn-xs btn-icon btn-action"
										title="Eliminar registro" data-toggle="tooltip"
										type="button">
									<i class="fa fa-trash-o"></i>
								</button>
	                		</div>
	                		<div slot="default" slot-scope="props" class="text-center">
								<span v-if="props.row.default" class="text-success font-weight-bold">SI</span>
								<span v-else class="text-danger font-weight-bold">NO</span>
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
					country_id: '',
					symbol: '',
					default: false,
					name: '',
					plural_name: '',
					decimal_places: 0,
				},
				errors: [],
				records: [],
				countries: [],
				columns: ['country.name', 'symbol', 'name', 'default', 'id'],
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
					country_id: '',
					symbol: '',
					default: false,
					name: '',
					plural_name: '',
				};
			},
		},
		created() {
			this.table_options.headings = {
				'country.name': 'Pais',
				'symbol': 'Símbolo',
				'name': 'Nombre',
				'default': 'Por defecto',
				'id': 'Acción'
			};
			this.table_options.sortable = ['name', 'symbol', 'country.name'];
			this.table_options.filterable = ['name', 'symbol', 'country.name'];
			this.table_options.columnsClasses = {
				'country.name': 'col-md-3',
				'symbol': 'col-md-1',
				'name': 'col-md-5',
				'default': 'col-md-1',
				'id': 'col-md-2'
			};
		},
		mounted() {
			const vm = this;

			$("#add_currency").on('show.bs.modal', function() {
				vm.getCountries();
			});
		}
	};
</script>
