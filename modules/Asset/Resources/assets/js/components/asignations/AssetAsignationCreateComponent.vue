<template>
	<section id="AssetAsignationForm">
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
						<li v-for="error in errors" :key="error">{{ error }}</li>
					</ul>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<b>Información del trabajador responsable del bien</b>
				</div>
				<div class="col-md-4" id="helpInstitution">
					<div class="form-group is-required">
						<label>Organización:</label>
						<select2 :options="institutions" :disabled="!is_admin" v-model="record.institution_id"></select2>
					</div>

				</div>
				<div class="col-md-4" id="helpStaff">
					<div class="form-group">
						<label>Trabajador:</label>
						<select2 :options="payroll_staffs" v-model="record.payroll_staff_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpDepartment">
					<div class="form-group">
						<label>Departamento:</label>
						<select2 :options="departments" v-model="record.department_id"></select2>
					</div>

				</div>

				<div class="col-md-4" id="helpPositionType">
					<div class="form-group">
						<label>Tipo de cargo:</label>
						<select2 :options="payroll_position_types" v-model="record.payroll_position_type_id"></select2>
						<input type="hidden" v-model="record.id">
					</div>
				</div>
				<div class="col-md-4" id="helpPosition">
					<div class="form-group">
						<label>Cargo:</label>
						<select2 :options="payroll_positions" v-model="record.payroll_position_id"></select2>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-12">
					<b>Información de ubicación del bien</b>
				</div>
				<!-- 				<div class="col-md-3" id="helpLocationPlace">
					<div class="form-group is-required">
						<label>Lugar de ubicación:</label>
						<input type="text" placeholder="Lugar de ubicación" data-toggle="tooltip"
							title="Indique el lugar de ubicación del bien a ser asignado" class="form-control input-sm"
							v-model="record.location_place">
					</div>
				</div> -->
				<div class="col-md-3">
					<div class="form-group is-required">
						<label for="input_buildings">Edificación:</label>
						<select2 id="input_buildings" :options="buildings" v-model="record.building_id"
							@input="getBuildingFloors()">
						</select2>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group is-required">
						<label for="input_floors">Nivel:</label>
						<select2 id="input_floors" :options="floors" v-model="record.floor_id"
							@input="getFloorSections('')">
						</select2>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group is-required">
						<label for="input_sections">Sección:</label>
						<select2 id="input_sections" :options="sections" v-model="record.section_id">
						</select2>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-12">
					<b>Información de conformación de la asignación</b>
				</div>
				<div class="col-md-4" id="authorized_by">
					<div class="form-group is-required">
						<label>Autorizado por:</label>
						<select2 :options="payroll_staffs" v-model="record.authorized_by_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="formed_by">
					<div class="form-group is-required">
						<label>Conformado por:</label>
						<select2 :options="payroll_staffs" v-model="record.formed_by_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="delivered_by">
					<div class="form-group is-required">
						<label>Entregado por:</label>
						<select2 :options="payroll_staffs" v-model="record.delivered_by_id"></select2>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-12">
					<b>Información de los bienes a ser asignados</b>
				</div>
			</div>
			<div class="row" style="margin: 10px 0">
				<div class="col-md-12">
					<b>Filtros</b>
				</div>
			</div>

			<div class="row">
				<div class="col-md-3" id="helpSearchAssetType">
					<div class="form-group">
						<label>Tipo de bien</label>
						<select2 :options="asset_types" @input="getAssetCategories()" v-model="record.asset_type_id">
						</select2>
					</div>
				</div>

				<div class="col-md-3" id="helpSearchAssetCategory">
					<div class="form-group">
						<label>Categoria general</label>
						<select2 :options="asset_categories" @input="getAssetSubcategories()"
							v-model="record.asset_category_id" title="Indique la categoria general del bien"></select2>
					</div>
				</div>
				<div class="col-md-3" id="helpSearchAssetSubCategory">
					<div class="form-group">
						<label>Subcategoria</label>
						<select2 :options="asset_subcategories" @input="getAssetSpecificCategories()"
							v-model="record.asset_subcategory_id" title="Indique la subcategoria del bien"></select2>
					</div>
				</div>

				<div class="col-md-3" id="helpSearchAssetSpecificCategory">
					<div class="form-group">
						<label>Categoria específica</label>
						<select2 :options="asset_specific_categories" v-model="record.asset_specific_category_id"
							title="Indique la categoria específica del bien"></select2>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<button type="button" id="helpSearchButton" @click="filterRecords()"
						class="btn btn-sm btn-primary btn-info float-right" title="Buscar registros" data-toggle="tooltip">
						<i class="fa fa-search"></i>
					</button>
				</div>
			</div>

			<hr>
			<v-server-table :url="route_asset" @row-click="toggleActive" :columns="columns" :options="table_options"
				ref="tableResults">
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
				<div slot="asset_details" slot-scope="props">
					<span>
						<div v-for="(att, index) in props.row.asset_details" :key="index">
							<b>{{ att.label + ":" }}</b> {{ att.value }}
						</div>
					</span>
				</div>

			</v-server-table>
		</div>
		<div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
					<button type="button" @click="reset()" class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
						title="Borrar datos del formulario">
						<i class="fa fa-eraser"></i>
					</button>

					<button type="button" @click="redirect_back(route_list)"
						class="btn btn-warning btn-icon btn-round btn-modal-close" data-dismiss="modal"
						title="Cancelar y regresar">
						<i class="fa fa-ban"></i>
					</button>

					<!-- 					<button type="button" @click="createForm('asset/asignations')"
						class="btn btn-success btn-icon btn-round btn-modal-save" title="Guardar registro">
						<i class="fa fa-save"></i>
					</button> -->
					<button type="button" @click="createForm('asset/asignations')"
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
				payroll_position_type_id: '',
				payroll_position_id: '',
				payroll_staff_id: '',
				institution_id: '',
				department_id: '',
				asset_type_id: '',
				asset_category_id: '',
				asset_subcategory_id: '',
				asset_specific_category_id: '',
				authorized_by_id: '',
				formed_by_id: '',
				delivered_by_id: '',
				building_id: '',
				floor_id: '',
				section_id: '',
			},
			getStaffIdInfo: '',
			errors: [],
			records: [],

			columns: [
				'check',
				'asset_institutional_code.name',
				'asset_specific_category.name',
				'asset_condition.name',
				'asset_status.name',
				'asset_details',
			],

			payroll_position_types: [],
			payroll_positions: [],
			payroll_staffs: [],
			institutions: [],
			departments: [],

			asset_types: [],
			asset_categories: [],
			asset_subcategories: [],
			asset_specific_categories: [],

			buildings: [],
			floors: [],
			sections: [],

			selected: [],
			selectAll: false,

			table_options: {
				rowClassCallback(row) {
					var checkbox = document.getElementById('checkbox_' + row.id);
					return ((checkbox) && (checkbox.checked)) ? 'selected-row cursor-pointer' : 'cursor-pointer';
				},
				headings: {
					'asset_institutional_code.name': 'Código',
					'asset_specific_category.name': 'Categoría Específica',
					'asset_condition.name': 'Condición Física',
					'asset_status.name': 'Estatus de Uso',
					'asset_details': 'Detalles'
				},
				sortable: [
					'asset_institutional_code.name',
					'asset_specific_category.name',
					'asset_condition.name',
					'asset_status.name',

				],
				filterable: [
					'asset_institutional_code.name',
					'asset_specific_category.name',
					'asset_condition.name',
					'asset_status.name',
					'asset_details',
				]
			}
		}
	},
	props: {
		asignationid: Number,
		assetid: Number,
		institution_id: {
			type: Number,
			required: true,
			default: null
		},
		is_admin: {
			type: Boolean,
			required: true,
			default: false
		},
		route_asset: {
			type: String,
			required: true,
			default: ''
		}
	},
	watch: {
		'record.payroll_staff_id'(new_id) {
			this.getPayrollStaffInfo(new_id);
		},
	},
	created() {
		const vm = this;
		vm.getInstitutions(vm.institution_id);
		vm.getPayrollStaffs();
		vm.getAssetTypes();
		vm.getBuildings();
	},
	mounted() {
		const vm = this;
		let url = `${window.app_url}/asset/registers/vue-list`;
		url += (vm.asignationid != null) ? '/asignations/' + vm.asignationid : '/asignations';
		vm.readRecords(url);

		if ((this.asignationid) && (!this.assetid)) {
			this.loadForm(this.asignationid);
		}
		else if ((!this.asignationid) && (this.assetid)) {
			this.selected.push(this.assetid);
		}

		// Selecciona la organización por defecto
		setTimeout(() => {
			vm.record.institution_id = vm.institution_id;
		}, 2000);
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
				else {
					checkbox.click();
				}
			}
			else if ((checkbox) && (checkbox.checked == true)) {
				var index = vm.selected.indexOf(row.id);
				if (index >= 0) {
					checkbox.click();
				}
				else {
					vm.selected.push(row.id);
				}
			}
		},
		reset() {
			this.record = {
				id: '',
				payroll_position_type_id: '',
				payroll_position_id: '',
				payroll_staff_id: '',
				institution_id: '',
				department_id: '',
				asset_type_id: '',
				asset_category_id: '',
				asset_subcategory_id: '',
				asset_specific_category_id: '',
				authorized_by: '',
				formed_by: '',
				delivered_by: '',
				building_id: '',
				floor_id: '',
				section_id: '',
			};
			this.selected = [];
			this.selectAll = false;
		},
		select() {
			const vm = this;
			vm.selected = [];
			$.each(vm.records, function (index, campo) {
				var checkbox = document.getElementById('checkbox_' + campo.id);

				if (!vm.selectAll) {
					vm.selected.push(campo.id);
				}
				else if (checkbox && checkbox.checked) {
					checkbox.click();
				}
			});
		},
		createForm(url) {
			const vm = this;
			vm.errors = [];
			if (!vm.selected.length > 0) {
				bootbox.alert("Debe agregar al menos un elemento a la solicitud");
				return false;
			};
			vm.record.assets = vm.selected;
			vm.createRecord(url);
		},
		async loadForm(id) {
			const vm = this;
			var fields = {};

			await axios.get(`${window.app_url}/asset/asignations/vue-info/${id}`).then(async (response) => {
				if (typeof (response.data.records != "undefined")) {

					vm.record = response.data.records;

					fields = response.data.records.asset_asignation_assets;
					$.each(fields, function (index, campo) {
						vm.selected.push(campo.asset.id);
					});
					vm.building_id = response.data.records.building.id;
					vm.floor_id = response.data.records.floor.id;
					vm.section_id = response.data.records.section.id;
					vm.record.building_id = vm.building_id;
					setTimeout(() => {
						vm.record.floor_id = vm.floor_id;
						setTimeout(() => {
							vm.record.section_id = vm.section_id;
						}, 2500);
					}, 2000);
				}
			});
		},
		filterRecords() {
			const operation = 'assignation';
			const vm = this;
			var url = `${window.app_url}/asset/registers/search/clasification/${operation}`;

			var filters = {
				asset_type: vm.record.asset_type_id,
				asset_category: vm.record.asset_category_id,
				asset_subcategory: vm.record.asset_subcategory_id,
				asset_specific_category: vm.record.asset_specific_category_id,
				institution_id: vm.record.institution_id,
			};

			axios.post(url, filters).then(response => {
				vm.records = response.data.records;
			});

		},
		/**
		 * Obtiene un arreglo con las organizaciones registradas
		 *
		 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
		 *
		 * @param  {integer} id Identificador de la organización a buscar, este parámetro es opcional
		 */
		async getInstitutions(id) {
			const vm = this;
			let institution_id = (typeof (id) !== "undefined") ? '/' + id : '';
			const url = vm.setUrl(`get-institutions${institution_id}`);
			vm.institutions = [];
			await axios.get(url).then(response => {
				vm.institutions = [];
				if (id != 'null') {
					vm.institutions.push(response.data[1])
					setTimeout(() => {
						vm.record.institution_id = response.data[1].id
					}, 1000);
				} else {
					vm.institutions = response.data
				}
			}).catch(error => {
				console.error(error);
			});
		},
	}
};
</script>
