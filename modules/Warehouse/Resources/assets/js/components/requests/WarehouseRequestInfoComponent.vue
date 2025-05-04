<template>
	<div>
		<a class="btn btn-info btn-xs btn-icon btn-action"
		   href="#" title="Ver información del registro" data-toggle="tooltip"
		   @click="addRecord('view_warehouse_request' + infoid, route_list , $event)">
			<i class="fa fa-eye"></i>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" :id="'view_warehouse_request' + infoid">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-read-book ico-2x"></i>
							Información de la solicitud registrada
						</h6>
					</div>

					<div class="modal-body">
						<ul class="nav nav-tabs custom-tabs justify-content-center" role="tablist">
	                        <li class="nav-item">
	                            <a class="nav-link active" data-toggle="tab" :id="'info_general' + infoid" :href="'#general' + infoid" role="tab">
	                                <i class="ion-android-person"></i> Información General
	                            </a>
	                        </li>

	                        <li class="nav-item">
	                            <a class="nav-link" data-toggle="tab" :href="'#equipment' + infoid" role="tab" @click="loadProducts()">
	                                <i class="ion-arrow-swap"></i> Insumos Solicitados
	                            </a>
	                        </li>
	                    </ul>

	                    <div class="tab-content">
							<div class="tab-pane active" :id="'general' + infoid" role="tabpanel">
	                    		<div class="row">

									<div class="col-md-6">
										<div class="form-group">
											<strong>Fecha de registro</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'date_init' + infoid">
												</span>
											</div>
											<input type="hidden" :id="'id' + infoid">
										</div>
									</div>
									<div class="col-md-6" v-show="payroll_staff_name">
										<div class="form-group">
										<strong>Usuario solicitante</strong>
										<div class="row" style="margin: 1px 0">
											<span class="col-md-12" :id="'payroll_staff' + infoid">
											</span>
										</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Departamento solicitante</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'department' + infoid">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Motivo</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'motive' + infoid">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<strong>Estado de la solicitud</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'state' + infoid">
												</span>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<div class="form-group">
											<strong>Observaciones</strong>
											<div class="row" style="margin: 1px 0">
												<span class="col-md-12" :id="'observations' + infoid">
												</span>
											</div>
										</div>
									</div>
							    </div>
	                    	</div>

							<div class="tab-pane" :id="'equipment' + infoid" role="tabpanel">
	                    		<div class="modal-table">
									<v-client-table :columns="columns" :data="records" :options="table_options">
										<div slot="code" slot-scope="props" class="text-center">
											<span>
												{{ props.row.warehouse_inventory_product.code }}
											</span>
										</div>
										<div slot="quantity" slot-scope="props">
											<span>
												{{ parseFloat(props.row.quantity).toFixed(2) }}
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
												{{ parseFloat(props.row.warehouse_inventory_product.unit_value).toFixed(2) }}
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
				payroll_staff_name: '',
				errors: [],
				columns: ['code',
					'warehouse_inventory_product.warehouse_product.name',
					'description',
					'quantity',
					'unit_value'],
			}
		},
		props: {
			infoid:Number,
			request: Object,
		},
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'warehouse_inventory_product.warehouse_product.name': 'Nombre',
				'description': 'Descripción',
				'quantity': 'Cantidad Agregada',
				'unit_value': 'Valor por Unidad'
			};
			this.table_options.sortable = [
				'code',
				'warehouse_inventory_product.warehouse_product.name',
				'description',
				'quantity',
				'unit_value'
			];
			this.table_options.filterable = [
				'code',
				'warehouse_inventory_product.warehouse_product.name',
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
            /**
			 * Reescribe el método initRecords para cambiar su comportamiento por defecto
			 * Inicializa los registros base del formulario
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 * @param {string} url 		Ruta que obtiene los datos a ser mostrado en listados
		 	 * @param {string} modal_id Identificador del modal a mostrar con la información solicitada
			 */

            initRecords(url, modal_id) {
				const vm = this;

				this.errors = [];
				this.reset();
            	var fields = {};

				document.getElementById("info_general" + this.infoid).click();
            	axios.get(url).then(response => {
					if (typeof(response.data.records) !== "undefined") {
						fields = response.data.records;
						vm.payroll_staff_name = (fields.payroll_staff)?fields.payroll_staff.first_name + ' ' + fields.payroll_staff.last_name:'';

						$(".modal-body #id").val(fields.id);
						document.getElementById('date_init' + vm.infoid).innerText = (fields.request_date)?vm.format_date(fields.request_date):vm.format_date(fields.created_at);
						document.getElementById('department' + vm.infoid).innerText = (fields.department)?fields.department.name:'';
						document.getElementById('payroll_staff' + vm.infoid).innerText = (fields.payroll_staff)?fields.payroll_staff.first_name + ' ' + fields.payroll_staff.last_name:'';

                        if(fields.motive){
						document.getElementById('motive' + vm.infoid).innerText =fields.motive.replace('<p>', '').replace('</p>', '');
					}else{
						document.getElementById('motive' + vm.infoid).innerText ="";
					}

						document.getElementById('observations' + vm.infoid).innerText = (fields.observations)?fields.observations.replace(/(<([^>]+)>)/gi, ""):'No definido';
						document.getElementById('state' + vm.infoid).innerText = (fields.state)?fields.state:'';
		            	this.records = fields.warehouse_inventory_product_requests;
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
				axios.get('/warehouse/requests/info/' + vm.infoid).then(response => {
					this.records = response.data.records.warehouse_inventory_product_requests;
				});
			}
		},
	}
</script>
