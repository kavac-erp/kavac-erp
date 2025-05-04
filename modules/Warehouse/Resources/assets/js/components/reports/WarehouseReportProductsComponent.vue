<template>
	<section id="WarehouseReportForm">
		<div class="card-body">
			<div class="alert alert-danger" v-if="errors.length > 0">
				<ul>
					<li v-for="(error, index) in errors" :key="index">{{ error }}</li>
				</ul>
			</div>
			<h3 class="h6">Filtros</h3>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label>Insumo:</label>
						<select2 :options="warehouse_products"
								 v-model="record.warehouse_product_id"></select2>
	                </div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>Organización:</label>
						<select2 :options="institutions"
								 v-model="record.institution_id"></select2>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>Almacén:</label>
						<select2 :options="warehouses"
								 v-model="record.warehouse_id"></select2>
	                </div>
				</div>
			</div>

			<div class="row text-center">
				<div class="col-md-6">
					<div class="form-group">
						<label>Busqueda por periodo</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_type_search" id="sel_search_date"
									   name="type_search" value="date"
									   v-model="record.type_search">
								<label class="custom-control-label" for="sel_search_date"></label>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class=" form-group">
						<label>Busqueda por mes</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_type_search" id="sel_search_mes"
									   name="type_search" value="mes"
									   v-model="record.type_search">
								<label class="custom-control-label" for="sel_search_mes"></label>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div v-show="this.record.type_search == 'mes'">
				<div class="row justify-content-center">
					<div class="col-md-4">
						<div class="form-group">
							<label>Mes:</label>
							<select2 :options="mes"
									 v-model="record.mes_id"></select2>
	                    </div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Año:</label>
							<input type="number" data-toggle="tooltip" min="0"
									   title="Indique el año de busqueda"
									   class="form-control input-sm" v-model="record.year">
	                    </div>
					</div>
				</div>
			</div>
			<div v-show="this.record.type_search == 'date'">
				<div class="row justify-content-center">
					<div class="col-md-4">
						<div class="form-group">
							<label>Desde:</label>
							<div class="input-group input-sm">
			                    <span class="input-group-addon">
			                        <i class="now-ui-icons ui-1_calendar-60"></i>
			                    </span>
			                    <input type="date" data-toggle="tooltip"
									   title="Indique la fecha minima de busqueda"
									   class="form-control input-sm" v-model="record.start_date">
			                </div>
	                    </div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Hasta:</label>
							<div class="input-group input-sm">
			                    <span class="input-group-addon">
			                        <i class="now-ui-icons ui-1_calendar-60"></i>
			                    </span>
			                    <input type="date" data-toggle="tooltip"
									   title="Indique la fecha maxima de busqueda"
									   class="form-control input-sm" v-model="record.end_date">
			                </div>
	                    </div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<button type="button" @click="loadInventoryProduct('inventory-products')"
                            class='btn btn-sm btn-info float-right' data-toggle="tooltip"
                            title="Realizar la búsqueda de acuerdo a los filtros establecidos en el formulario"
                    >
                        Realizar búsqueda
						<i class="fa fa-search"></i>
					</button>
				</div>
			</div>
			<hr>
			<v-client-table :columns="columns" :data="records" :options="table_options">
				<div slot="description" slot-scope="props">
					<span>
						<b> {{ (props.row.warehouse_product)?
								'Nombre: ':'' }} </b>
							{{ (props.row.warehouse_product)?
							props.row.warehouse_product.name + '.':''
						}} <br>
						{{ (props.row.warehouse_product)?
								prepareText(
								props.row.warehouse_product.description): ''
						}} <br>
						<b> Unidad: </b>
						{{ (props.row.warehouse_product) ?
							    props.row.warehouse_product.measurement_unit ?
							        props.row.warehouse_product.measurement_unit.name : ''
								: ''
						}} <br>
					</span>
					<span>
						<div v-for="(att, index) in props.row.warehouse_product_values" :key="index">
							<b>{{att.warehouse_product_attribute.name +": "}}</b> {{ att.value}} <br>
						</div>
						<b>Valor:</b> {{props.row.unit_value}} {{(props.row.currency)?props.row.currency.acronym:''}}
					</span>
				</div>
				<div slot="inventory" slot-scope="props">
					<span>
						<b>Almacén:</b> {{
							props.row.warehouse_institution_warehouse.warehouse.name
							}} <br>
						<b>Existencia:</b> {{ props.row.real }}<br>
						<b>Reservados:</b> {{ (props.row.reserved === null)? '0':props.row.reserved }}<br>
						<b>Solicitados:</b> {{ quantityProductRequests(props.row.code) }}
						<br>
						<b>Disponible para solicitar:</b> {{ numberDecimal(props.row.real - quantityProductRequests(props.row.code),2)  }}
					</span>
				</div>
			</v-client-table>
		</div>
		<div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
		        	<button type="button" class='btn btn-sm btn-primary btn-custom'
							@click="createReport('inventory-products')">
						<i class="fa fa-file-pdf-o"></i>
						<span>Generar reporte</span>
					</button>
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
					warehouse_product_id: '',
					warehouse_id: '',

					type_search: '',
					institution_id: '',

					mes_id: '',
					year: '',
					start_date: '',
					end_date: ''
				},
				warehouses: [],
				warehouse_products: [],
				records: [],
				productsQuantity: [],
				errors: [],
				columns: ['code', 'description', 'inventory'],
				mes: [
					{"id":"","text":"Todos"},
					{"id":1,"text":"Enero"},
					{"id":2,"text":"Febrero"},
					{"id":3,"text":"Marzo"},
					{"id":4,"text":"Abril"},
					{"id":5,"text":"Mayo"},
					{"id":6,"text":"Junio"},
					{"id":7,"text":"Julio"},
					{"id":8,"text":"Agosto"},
					{"id":9,"text":"Septiempre"},
					{"id":10,"text":"Octubre"},
					{"id":11,"text":"Noviembre"},
					{"id":12,"text":"Diciembre"}
				],
				institutions: []
			}
		},
		methods: {
			reset() {
				this.record = {
					id: '',
					warehouse_product_id: '',
					warehouse_id: '',

					type_search: '',
					institution_id: '',

					mes_id: '',
					year: '',
					start_date: '',
					end_date: ''
				}
			},
			prepareText(text) {
                return text.replace('<p>', '').replace('</p>', '');
            },
			getWarehouseProducts() {
				const vm = this;
				axios.get('/warehouse/get-warehouse-products').then(response => {
					vm.warehouse_products = response.data;
				});
			},
			createReport(current) {
				const vm = this;
				vm.loading = true;
				var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields["current"] = current;
				axios.post("/warehouse/reports/inventory-products/create", fields).then(response => {
                    if (response.data.result == false)
						location.href = response.data.redirect;
					else if (typeof(response.data.redirect) !== "undefined") {
						window.open(response.data.redirect, '_blank');
					}
					else {
						vm.reset();
					}
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) != "undefined") {
                        console.log("error");
                    }
                    vm.loading = false;
                });
			},
			loadInventoryProduct(current) {
				const vm = this;
				vm.loading = true;
				var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields["current"] = current;
				axios.post("/warehouse/reports/inventory-products/vue-list", fields).then(response => {
                    if (typeof(response.data.records) != "undefined") {
						vm.records = response.data.records;
						vm.productsQuantity = response.data.productsQuantity;
                    }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) != "undefined") {
                        console.log("error");
                    }
                    vm.loading = false;
                });
	        },
	        /**
		     * Devuelve la cantidad solicitadas de un producto especifico
		     *
		     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
		     */
		    quantityProductRequests(codeproduct) {
		    	const vm = this;
		    	var quantity = 0;
		      	if(codeproduct) {
		      		$.each(vm.productsQuantity, function (index, campo) {
		      			if(campo['code'] == codeproduct) {
		      				quantity = campo['quantity'];
		      			}
		      		});
		      	}
		      	return quantity;
	    	},
	    	/**
		     * Devuelve un numero decimal con un numero de decimales especifico
		     *
		     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
		     */
		    numberDecimal(num, dec) {
	  			var exp = Math.pow(10, dec || 2);
	  			return parseInt(num * exp, 10) / exp;
			},
		},
        props: {
            institution_id: {
                type: Number,
                required: true,
                default: null
            },
        },
		created() {
			this.table_options.headings = {
				'code':        'Código',
				'description': 'Descripción',
				'inventory':   'Inventario',
			};
			this.table_options.sortable = ['code', 'description', 'inventory'];
			this.table_options.filterable = ['code', 'description', 'inventory'];
		},
		mounted() {
			const vm = this;
			this.switchHandler('type_search');
			this.getInstitutions();
			this.getWarehouseProducts();
			this.getWarehouses();

            // Selecciona la organización por defecto
            setTimeout(() => vm.record.institution_id = vm.institution_id, 2000);
        }
	};
</script>
