<template>
	<div>
		<a class="btn btn-info btn-xs btn-icon btn-action"
		   href="#" title="Ver información del registro" data-toggle="tooltip"
		   @click="addRecord('view_movement', route_list , $event)">
			<i class="fa fa-eye"></i>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="view_movement">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-read-book ico-2x"></i>
							Información del Movimiento Registrado
						</h6>
					</div>

					<div class="modal-body">

						<div class="alert alert-danger" v-if="errors.length > 0">
							<ul>
								<li v-for="(error, index) in errors" :key="index">{{ error }}</li>
							</ul>
						</div>
						<ul class="nav nav-tabs custom-tabs justify-content-center" role="tablist">
	                        <li class="nav-item">
	                            <a class="nav-link active" data-toggle="tab" href="#general" id="info_movement" role="tab">
	                                <i class="ion-android-person"></i> Información General
	                            </a>
	                        </li>

	                        <li class="nav-item">
	                            <a class="nav-link" data-toggle="tab" href="#equipment" role="tab" @click="loadProducts()">
	                                <i class="ion-arrow-swap"></i> Insumos
	                            </a>
	                        </li>
	                    </ul>

	                    <div class="tab-content">
	                    	<div class="tab-pane active" id="general" role="tabpanel">
	                    		<div class="row">

							        <div class="col-md-6">
										<div class="form-group">
											<strong>Fecha de registro</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" id="date_init">
												</span>
											</div>
											<input type="hidden" id="id">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Estado del movimiento</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" id="state">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Almacén de origen</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" id="warehouse_initial">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Almacén destino</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" id="warehouse_end">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Descripción</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" id="description">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Observaciones</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" id="observations">
												</span>
											</div>
										</div>
									</div>
							    </div>
	                    	</div>

	                    	<div class="tab-pane" id="equipment" role="tabpanel">
	                    		<div class="modal-table">
									<v-client-table :columns="columns" :data="records" :options="table_options">
										<div slot="code" slot-scope="props" class="text-center">
											<span>
												{{ props.row.warehouse_inventory_product.code }}
											</span>
										</div>
										<div slot="quantity" slot-scope="props">
											<span>
												{{ props.row.quantity }}
												{{ (props.row.warehouse_inventory_product.warehouse_product.measurement_unit)
													? props.row.warehouse_inventory_product.warehouse_product.measurement_unit.acronym
													: ''
												}}
											</span>
										</div>
										<div slot="description" slot-scope="props">
											<span>
												{{ (props.row.warehouse_inventory_product.warehouse_product.description)?
														prepareText(
														props.row.warehouse_inventory_product.warehouse_product.description) : ''
												}}<br>
											</span>
										</div>
										<div slot="unit_value" slot-scope="props">
											<span>
												{{ props.row.warehouse_inventory_product.unit_value }}
												{{ (props.row.warehouse_inventory_product.currency)
													? props.row.warehouse_inventory_product.currency.symbol
													: ''
												}}
											</span>
										</div>
									</v-client-table>
	                    		</div>
	                    	</div>
	                    </div>
					</div>

	                <div class="modal-footer">

	                	<button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
	                			data-dismiss="modal">
	                		Cerrar
	                	</button>
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
				records: [],
				errors: [],
				columns: [
					'code',
					'warehouse_inventory_product.warehouse_product.name',
					'description',
					'quantity',
					'unit_value'
				],
			}
		},
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'warehouse_inventory_product.warehouse_product.name': 'Nombre',
				'description': 'Descripción',
				'quantity': 'Cantidad agregada',
				'unit_value': 'Valor por unidad'

			};
			this.table_options.sortable = [
				'code',
				'warehouse_inventory_product.name',
				'description',
				'quantity',
				'unit_value'
			];
			this.table_options.filterable = [
				'code',
				'warehouse_inventory_product.name',
				'description',
				'quantity',
				'unit_value'
			];

		},
		methods: {

			/**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
             */
            reset() {
            },

			prepareText(text) {
				return text.replace('<p>', '').replace('</p>', '');
			},

            initRecords(url, modal_id) {
				this.errors = [];
				this.reset();

				const vm = this;
            	var fields = {};

            	document.getElementById("info_movement").click();
            	axios.get(url).then(response => {
					if (typeof(response.data.records) !== "undefined") {
						fields = response.data.records;

						$(".modal-body #id").val( fields.id );
		            	document.getElementById('date_init').innerText = (fields.created_at)?vm.format_date(fields.created_at):'';
						if(fields.description){
						document.getElementById('description').innerText =fields.description.replace('<p>', '').replace('</p>', '');
					}else{
						document.getElementById('description').innerText ="";
					}
		            	document.getElementById('observations').innerText = (fields.description)?fields.observations:'';
		            	document.getElementById('warehouse_initial').innerText = (fields.warehouse_institution_warehouse_initial)?fields.warehouse_institution_warehouse_initial.warehouse.name:'';
		            	document.getElementById('warehouse_end').innerText = (fields.warehouse_institution_warehouse_end)?fields.warehouse_institution_warehouse_end.warehouse.name:'';
		            	document.getElementById('state').innerText = (fields.state)?fields.state:'No definido';
		            	this.records = fields.warehouse_inventory_product_movements;
					}
					if ($("#" + modal_id).length) {
						$("#" + modal_id).modal('show');
					}
				}).catch(error => {
					if (typeof(error.response) !== "undefined") {
						if (error.response.status == 403) {
							vm.showMessage(
								'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
							);
						}
						else {
							vm.logs('resources/js/all.js', 343, error, 'initRecords');
						}
					}
				});

			},

			/**
			 * Actualiza los productos asocados a la solicitud
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			loadProducts() {
				const vm = this;
				var index = $(".modal-body #id").val();

				axios.get('/warehouse/receptions/info/' + index).then(response => {
					this.records = response.data.records.warehouse_inventory_product_movements;
				});
			}
		},
	}
</script>
