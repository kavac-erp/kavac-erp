<template>
	<section id="WarehouseRequestForm">
		<div class="card-body">
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
				<div class="col-md-12">
					<b>Datos de la solicitud</b>
				</div>

				<div class="col-md-4" id="helpWarehouseRequestDate">
					<div class="form-group is-required">
						<label>Fecha de la solicitud</label>
						<input type="date" data-toggle="tooltip" title="Fecha de la solicitud" class="form-control input-sm"
							v-model="record.request_date">
						<input type="hidden" v-model="record.id">
					</div>
				</div>

				<div class="col-md-4" id="helpWarehouseRequestDepartment">
					<div class=" form-group is-required">
						<label>Dependencia solicitante</label>
						<select2 :options="departments" v-model="record.department_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpWarehouseRequestMotive">
					<div class="form-group is-required">
						<label>Motivo de la solicitud</label>
						<ckeditor :editor="ckeditor.editor" data-toggle="tooltip"
							title="Indique el motivo de la solicitud (requerido)" :config="ckeditor.editorConfig"
							class="form-control" tag-name="textarea" rows="3" v-model="record.motive">
						</ckeditor>

					</div>
				</div>
				<div class="col-md-6" id="helpWarehouseRequestProject">
					<div class=" form-group is-required">
						<label>Proyecto</label>
						<div class="custom-control custom-switch mb-4">
							<input type="radio" class="custom-control-input sel_pry_acc" id="sel_project"
								name="project_centralized_action" value="project">
							<label class="custom-control-label" for="sel_project"></label>
						</div>
						<select2 :options="budget_projects" id="budget_project_id"
							@input="getBudgetSpecificActions('Project')" disabled v-model="record.budget_project_id">
						</select2>

					</div>
				</div>
				<div class="col-md-6" id="helpWarehouseRequestCentralizedAction">
					<div class=" form-group is-required">
						<label>Acción centralizada</label>
						<div class="custom-control custom-switch mb-4">
							<input type="radio" class="custom-control-input sel_pry_acc" id="sel_centralized_action"
								name="project_centralized_action" value="centralized_action">
							<label class="custom-control-label" for="sel_centralized_action"></label>
						</div>
						<select2 :options="budget_centralized_actions" id="budget_centralized_action_id"
							@input="getBudgetSpecificActions('CentralizedAction')" disabled
							v-model="record.budget_centralized_action_id"></select2>
					</div>
				</div>
				<div class="col-md-12" id="helpWarehouseRequestSpecificAction">
					<div class=" form-group is-required">
						<label>Acción específica</label>
						<select2 :options="budget_specific_actions" id="budget_specific_action_id"
							v-model="record.budget_specific_action_id" disabled></select2>
					</div>
				</div>
			</div>

			<hr>
			<div class="col-12">
				<h6 class="card-title">Listado de solicitud de almacén</h6>
			</div>
			<v-client-table id="helpTable" @row-click="toggleActive" :columns="columns" :data="records" :options="table_options" >
				<div slot="h__check" class="text-center">
					<label class="form-checkbox">
						<input type="checkbox" v-model="selectAll" @click="select()" class="cursor-pointer">
					</label>
				</div>

				<div slot="check" slot-scope="props" class="text-center">
					<label class="form-checkbox">
						<input type="checkbox" class="cursor-pointer" :value="props.row.id" :id="'checkbox_' + props.row.id"
							v-model="selected">
					</label>
				</div>
				<div slot="code" slot-scope="props" class="text-center">
		            <span>
		                {{ props.row.code }}
		            </span>
		        </div>
				<div slot="description" slot-scope="props">
					<span>
						<b> {{ (props.row.warehouse_product) ?
							props.row.warehouse_product.name + ': ' : ''
						}} </b>
						{{ (props.row.warehouse_product) ?
							prepareText(props.row.warehouse_product.description)
							: ''
						}}<br>
						<b> Unidad: </b>
						{{ (props.row.warehouse_product) ?
							    props.row.warehouse_product.measurement_unit ?
							        props.row.warehouse_product.measurement_unit.name : ''
								: ''
						}} <br>
					</span>
					<span>
						<div v-for="(att, index) in props.row.warehouse_product_values" :key="index">
							<b>{{ att.warehouse_product_attribute.name + ":" }}</b> {{ att.value }}<br>
						</div>
						<b>Valor:</b> {{ props.row.unit_value }} {{ (props.row.currency) ? props.row.currency.name : '' }}
					</span>
				</div>
				<div slot="inventory" slot-scope="props">
					<span>
						<b>Almacén:</b> {{
							props.row.warehouse_institution_warehouse.warehouse.name
						}} <br>
						<b>Existencia:</b> {{ props.row.real }}<br>
						<b>Reservados:</b> {{ (props.row.reserved === null) ? '0' : props.row.reserved }}
						<br>
						<b>Solicitados:</b> {{ quantityProductRequests(props.row.code) }}

						<br>
						<b>Disponible para solicitar:</b> {{ numberDecimal(props.row.real - quantityProductRequests(props.row.code),2)  }}
					</span>
				</div>
				<div slot="requested" slot-scope="props">
					<div>
						<input type="text" class="form-control table-form input-sm" data-toggle="tooltip" min=0
						v-input-mask data-inputmask="
								'alias': 'numeric',
								'allowMinus': 'false',
								'digits': 2"
						@input="selectElement(props.row.id); validateInput(props.row.real, props.row.code, props.row.id)"
						:id="'request_product_' + props.row.id" onfocus="this.select()"
						v-model="input_values[props.row.id]">
					</div>
				</div>
			</v-client-table>
		</div>
		<div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
					<button type="button" @click=" reset() " class="btn btn-default btn-icon btn-round"
						title="Borrar datos del formulario">
						<i class="fa fa-eraser"></i>
					</button>

					<button type="button" @click=" redirect_back(route_list) "
						class="btn btn-warning btn-icon btn-round btn-modal-close" data-dismiss="modal"
						title="Cancelar y regresar">
						<i class="fa fa-ban"></i>
					</button>

					<button type="button" @click=" createRequest('warehouse/requests') "
						class="btn btn-success btn-icon btn-round btn-modal-save" title="Guardar registro">
						<i class="fa fa-save"></i>
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
				motive: '',
				institution_id: '1',
				department_id: '',
				budget_project_id: '',
				budget_centralized_action_id: '',
				budget_specific_action_id: '',
				request_date: '',
				warehouse_products: [],
			},

			editIndex: null,
			records: [],
			productsQuantity: [],
			enableInput: false,
			columns: [
				'check',
				'code',
				'description',
				'inventory',
				'requested',
			],
			errors: [],
			validateValue: [],
			selected: [],
			input_values: [],
			selectAll: false,

			departments: [],
			budget_projects: [],
			budget_centralized_actions: [],
			budget_specific_actions: [],

			table_options: {
				rowClassCallback(row) {
					var checkbox = document.getElementById('checkbox_' + row.id);
					return ((checkbox) && (checkbox.checked)) ? 'selected-row cursor-pointer' : 'cursor-pointer';
				},
				headings: {
					'code': 'Código',
					'description': 'Descripción',
					'inventory': 'Inventario',
					'requested': 'Cantidad solicitada'
				},
				sortable: [
					'code',
					'description',
					'inventory',
					'requested',
				],
				filterable: [
					'code',
					'unit_value',
					'warehouse_product_values',
					'currency.name',
					'warehouse_product.name',
					'warehouse_product.description',
					'warehouse_product.measurement_unit.name',
					'warehouse_institution_warehouse.warehouse.name',
				]
			}
		}
	},
	created() {
		this.getBudgetProjects();
		this.getBudgetCentralizedActions();
		//this.initForm('/warehouse/requests/vue-list-products');
		this.initForm('/warehouse/requests/vue-list-products/' + this.requestid);
	},

	props: {
		requestid: Number,
	},
	methods: {
		toggleActive({ row }) {
			const vm = this;
			var checkbox = document.getElementById('checkbox_' + row.id);

			if ((checkbox) && (checkbox.checked == false)) {
				var index = vm.selected.indexOf(row.id);
				if (index >= 0) {
					vm.selected.splice(index, 1);
				}
				else
					checkbox.click();
			}
			else if ((checkbox) && (checkbox.checked == true)) {
				var index = vm.selected.indexOf(row.id);
				if (index >= 0)
					checkbox.click();
				else
					vm.selected.push(row.id);
			}
		},
		prepareText(text) {
			return text.replace('<p>', '').replace('</p>', '');
		},
		reset() {
			this.record = {
				id: '',
				motive: '',
				institution_id: '1',
				department_id: '',
				budget_project_id: '',
				budget_centralized_action_id: '',
				budget_specific_action_id: '',
				request_date: '',
				warehouse_products: [],
			}

		},
		select() {
			const vm = this;
			vm.selected = [];
			$.each(vm.records, function (index, campo) {
				var checkbox = document.getElementById('checkbox_' + campo.id);

				if (!vm.selectAll)
					vm.selected.push(campo.id);
				else if (checkbox && checkbox.checked) {
					checkbox.click();
				}
			});
		},
		selectElement(id) {
			var input = document.getElementById('request_product_' + id);
			var checkbox = document.getElementById('checkbox_' + id);
			if ((input.value == '') || (input.value == 0)) {
				if (checkbox.checked) {
					checkbox.click();
				}
			}
			else if (!checkbox.checked) {
				checkbox.click();
			}
		},
		/**
	     * Validad que la cantidad de la solicitud de producto sea menor o igual a la disponible.
	     *
	     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
	     */
		validateInput(real, code, id) {
			const vm = this;
			vm.errors = [];
			var quantity = vm.quantityProductRequests(code);
			let value = document.getElementById("request_product_" + id).value;
			if ((real - quantity < value)) {
				vm.errors.push('La cantidad de producto a solicitar (Código: ' + code + ') es mayor a la cantidad disponible');
				if(!vm.searchCode(code)) {
					vm.validateValue.push(code);
				}
				vm.continue = false;
			}
			else {
				vm.deleteCode(code);
			}
			return;
		},

		/**
	     * Busca si el código del producto esta en la lista de productos que tiene problema de validación (solicitud > disponible para solicitar)
	     *
	     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
	     */
		searchCode(code) {
			const vm = this;
			var search = false;
			if(code) {
	      		$.each(vm.validateValue, function (index, campo) {
	      			if(campo == code) {
	      				search = true;
	      			}
	      		});
	      	}
	      	return search;
		},

		/**
	     * Busca si el código del producto esta en la lista de productos que tiene problema de validación (solicitud > disponible para solicitar) para ser eliminado
	     *
	     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
	     */
		deleteCode(code) {
			const vm = this;
			if(code) {
	      		$.each(vm.validateValue, function (index, campo) {
	      			if(campo == code) {
	      				vm.validateValue.splice(index,1);
	      			}
	      		});
	      	}
		},

		async initForm(url) {
			const vm = this;
			/**
			 *	Ajustar si esta activa unica institucion seleccionar la institucion x defecto
			 */
			vm.record.institution_id = '1';
			vm.getDepartments();
			await axios.get(url).then(function (response) {
				if (typeof (response.data.records) !== "undefined")
					vm.records = response.data.records;
					vm.productsQuantity = response.data.productsQuantity;
			});
		},
		async loadRequest(id) {
			const vm = this;
			var fields = {};

			await axios.get('/warehouse/requests/info/' + id).then(response => {
				if (typeof (response.data.records != "undefined")) {
					fields = response.data.records;
					let type = fields.budget_specific_action.specificable_type;
					let id = fields.budget_specific_action.specificable_id;

					vm.record = {
						id: fields.id,
						motive: fields.motive,
						institution_id: '1',
						department_id: fields.department_id,
						budget_project_id: (type.includes('BudgetProject')) ? id : '',
						budget_centralized_action_id: (type.includes('BudgetCentralizedAction')) ? id : '',
						budget_specific_action: fields.budget_specific_action,
						budget_specific_action_id: '',
						warehouse_products: fields.warehouse_inventory_product_requests,
						request_date: fields.request_date ? vm.format_date(fields.request_date, 'YYYY-MM-DD') :
							vm.format_date(fields.created_at, 'YYYY-MM-DD'),
					};
					$.each(fields.warehouse_inventory_product_requests, function (index, campo) {
						if (campo.warehouse_inventory_product_id) {
							vm.input_values[campo.warehouse_inventory_product_id] = campo.quantity;
							vm.selected.push(campo.warehouse_inventory_product_id);
						}
					});
				}
			});
		},
		createRequest(url) {
			const vm = this;
			vm.record.warehouse_products = [];
			var complete = true;
			if((vm.validateValue).length > 0) {
				$.each(vm.validateValue, function (index, campo) {
					bootbox.alert("La cantidad de producto a solicitar (Código: " + campo + ") es mayor a la cantidad disponible");
				});
				return false;
			}
			if (!vm.selected.length > 0) {
				bootbox.alert("Debe agregar al menos un elemento a la solicitud");
				return false;
			};
			$.each(vm.selected, function (index, campo) {
				if (vm.input_values[campo] == "") {
					bootbox.alert("Debe ingresar la cantidad solicitada para cada insumo seleccionado");
					complete = false;
					return;
				}
				vm.record.warehouse_products.push(
					{ id: campo, requested: vm.input_values[campo] });
			});
			if (complete == true)
				vm.createRecord(url)
		},

		/**
	     * Devuelve la cantidad solicitadas de un producto especifico
	     *
	     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
	     */
	    quantityProductRequests(Codeproduct) {
	    	const vm = this;
	    	var quantity = 0;
	      	if(Codeproduct) {
	      		$.each(vm.productsQuantity, function (index, campo) {
	      			if(campo['code'] == Codeproduct) {
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
		}
	},
	watch: {

		/**
		 * Función que permite monitorear modificaciones en el campo budget_specific_actions
		 *
		 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
		 */
		budget_specific_actions: function () {
			$("#budget_specific_action_id").attr('disabled', (this.budget_specific_actions.length <= 1));
		},

	},
	mounted() {

		/**
		 * Evento para determinar los datos a requerir según el tipo de formulación
		 * (por proyecto o acción centralizada)
		 */
		const vm = this;
		$('.sel_pry_acc').on('change', function (e) {
			$('#budget_project_id').attr('disabled', (e.target.id !== "sel_project"));
			$('#budget_centralized_action_id').attr('disabled', (e.target.id !== "sel_centralized_action"));

			if (e.target.id === "sel_project") {
				$("#budget_centralized_action_id").closest('.form-group').removeClass('is-required');
				$("#budget_project_id").closest('.form-group').addClass('is-required');
				vm.record.budget_centralized_action_id = '';
			}
			else if (e.target.id === "sel_centralized_action") {
				$("#budget_centralized_action_id").closest('.form-group').addClass('is-required');
				$("#budget_project_id").closest('.form-group').removeClass('is-required');
				vm.record.budget_project_id = '';
			}
		});

		if (this.requestid) {
			this.loadRequest(this.requestid);
		}
	}
};
</script>
