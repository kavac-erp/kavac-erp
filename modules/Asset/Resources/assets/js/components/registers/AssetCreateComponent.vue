<template>
	<section id="AssetForm">
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
				<div class="col-md-4" id="helpInstitution">
					<div class="form-group is-required">
						<label>Organización:</label>
						<select2 :options="institutions"
								data-toggle="tooltip"
								title="Seleccione un registro de la lista"
								v-model="record.institution_id"></select2>
						<input type="hidden" v-model="record.id">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4" id="helpAssetType">
					<div class="form-group is-required">
						<label>Tipo de bien:</label>
						<select2 :options="asset_types" id="asset_types_select"
								@input="getAssetCategories()"
								data-toggle="tooltip"
								title="Seleccione un registro de la lista"
								v-model="record.asset_type_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpAssetCategory">
					<div class="form-group is-required">
						<label>Categoría general:</label>
						<select2 :options="asset_categories" id="asset_categories_select"
								@input="getAssetSubcategories()"
								:disabled="(!this.record.asset_type_id != '')"
								data-toggle="tooltip"
								title="Seleccione un registro de la lista"
								v-model="record.asset_category_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpAssetSubCategory">
					<div class="form-group is-required">
						<label>Subcategoria:</label>
						<select2 :options="asset_subcategories" id="asset_subcategories_select"
								@input="getAssetSpecificCategories()"
								:disabled="(!this.record.asset_category_id != '')"
								data-toggle="tooltip"
								title="Seleccione un registro de la lista"
								v-model="record.asset_subcategory_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpAssetSpecificCategory">
					<div class="form-group is-required">
						<label>Categoría específica:</label>
						<select2 :options="asset_specific_categories"
								:disabled="(!this.record.asset_subcategory_id != '')"
								@input="getAssetRequired()"
								data-toggle="tooltip"
								title="Seleccione un registro de la lista"
								v-model="record.asset_specific_category_id"></select2>
					</div>
				</div>
				<div class="col-md-8" id="helpAssetSpecification">
					<div class="form-group">
						<label>Especificaciones</label>
                        <ckeditor :editor="ckeditor.editor" v-model="record.specifications" id="details"
                                  title="Indique las especificaciones del bien (opcional)" data-toggle="tooltip"
                                  :config="ckeditor.editorConfig" tag-name="textarea"></ckeditor>
					</div>
				</div>
			</div>
			<div class="row">
				<hr>
				<div class="col-md-3" id="helpAssetAcquisitionType">
					<div class="form-group is-required">
						<label>Forma de adquisición</label>
						<select2 :options="asset_acquisition_types"
								v-model="record.asset_acquisition_type_id"></select2>
					</div>
				</div>
				<div class="col-md-3" id="helpAssetAcquisitionYear">
					<div class="form-group">
						<label>Fecha de adquisición</label>
						<input type="date" placeholder="Fecha de Adquisición" data-toggle="tooltip"
							   title="Indique la fecha de adquisición"
							   class="form-control input-sm" v-model="record.acquisition_date">
					</div>
				</div>

				<div class="col-md-3" v-if="((record.asset_type_id == 1) || ((record.asset_type) && (record.asset_type.id == 1)))">
					<div class="form-group" id="helpAssetPurchaseSupplier">
						<label>Proveedor</label>
						<select2 :options="supplier"
								v-model="record.purchase_supplier_id"></select2>
					</div>
				</div>

				<div class="col-md-3" id="helpAssetCondition">
					<div class="form-group is-required">
						<label>Condición física</label>
						<select2 :options="asset_conditions"
								 data-toggle="tooltip"
								 title="Seleccione un registro de la lista"
								 v-model="record.asset_condition_id"></select2>
					</div>
				</div>

				<div class="col-md-3" id="helpAssetStatus">
					<div class="form-group is-required">
						<label>Estatus de uso</label>
						<select2 :options="asset_status"
								 data-toggle="tooltip"
								 title="Seleccione un registro de la lista"
								 v-model="record.asset_status_id"></select2>
					</div>
				</div>

				<div class="col-md-3" id="helpAssetUseFunction"
					v-if="required.use_function == true">
					<div class="form-group is-required">
						<label>Función de uso</label>
						<select2 :options="asset_use_functions"
								 data-toggle="tooltip"
								 title="Seleccione un registro de la lista"
								 v-model="record.asset_use_function_id"></select2>
					</div>
				</div>

				<div class="col-md-3" id="helpAssetSerial"
					v-if="required.serial == true">
					<div class="form-group is-required">
						<label>Serial</label>
						<input type="text" placeholder="Serial de Fabricación" data-toggle="tooltip"
							   title="Indique el serial de fabricación"
							   class="form-control input-sm" v-model="record.serial">
					</div>
				</div>
				<div class="col-md-3" id="helpAssetMarca"
					v-if="required.marca == true">
					<div class="form-group is-required">
						<label>Marca</label>
						<input type="text" placeholder="Marca" data-toggle="tooltip"
							   title="Indique la marca del bien"
							   class="form-control input-sm" v-model="record.marca">
					</div>
				</div>
				<div class="col-md-3" id="helpAssetModel"
					v-if="required.model == true">
					<div class="form-group is-required">
						<label>Modelo</label>
						<input type="text" placeholder="Modelo" data-toggle="tooltip"
							   title="Indique el modelo del bien"
							   class="form-control input-sm" v-model="record.model">
					</div>
				</div>
				<div class="col-md-3" id="helpAssetColor"
					v-if="record.asset_type_id == 1">
					<div class="form-group">
						<label>Color</label>
						<input type="text" placeholder="Color" data-toggle="tooltip"
							   title="Indique el color del bien"
							   class="form-control input-sm" v-model="record.color">
					</div>
				</div>
				<div class="col-md-3" id="helpAssetAsset_institutional_code"
					v-if="record.asset_type_id == 1">
					<div class="form-group is-required">
						<label>Código de bien organizacional</label>
						<input type="text" placeholder="Código de bien organizacional" data-toggle="tooltip"
							   title="Indique el código de bien organizacional"
							   class="form-control input-sm" v-model="record.asset_institutional_code">
					</div>
				</div>
				<div class="col-md-3" id="helpAssetValue">
					<div class="form-group">
						<label>Valor</label>
						<input type="decimal" min="0"
								placeholder="Precio por unidad" data-toggle="tooltip"
								title="Indique el precio del bien"
								class="form-control input-sm" v-model="record.value">
					</div>
				</div>
				<div class="col-md-3" id="helpAssetCurrency">
					<div class="form-group">
						<label>Moneda</label>
						<select2 :options="currencies"
								 v-model="record.currency_id"></select2>
					</div>
				</div>
			</div>
			<div v-if="required.address == true">
				<hr>
				<h6 class="card-title text-uppercase">Ubicación</h6>
				<div class="row">
					<div class="col-md-3" id="helpAssetCountry">
						<div class="form-group is-required">
							<label>Pais:</label>
							<select2 :options="countries" id="country_select"
									 @input="getEstates()"
									 v-model="record.country_id"></select2>
						</div>
					</div>
					<div class="col-md-3" id="helpAssetEstate">
						<div class="form-group is-required">
							<label>Estado:</label>
							<select2 :options="estates" id="estate_select"
									@input="getMunicipalities()"
									:disabled="(!this.record.country_id != '')"
									v-model="record.estate_id"></select2>
						</div>
					</div>
					<div class="col-md-3" id="helpAssetMunicipality">
						<div class="form-group is-required">
							<label>Municipio:</label>
							<select2 :options="municipalities" id="municipality_select"
									@input="getParishes()"
									:disabled="(!this.record.estate_id != '')"
									v-model="record.municipality_id"></select2>
						</div>
					</div>
					<div class="col-md-3" id="helpAssetParish">
						<div class="form-group is-required">
							<label>Parroquia:</label>
							<select2 :options="parishes" id="parish_select"
									 :disabled="(!this.record.municipality_id != '')"
									 v-model="record.parish_id"></select2>
						</div>
					</div>
					<div class="col-md-6" id="helpAssetAddress">
						<div class="form-group is-required">
							<label>Dirección</label>
                            <ckeditor :editor="ckeditor.editor" id="direction" data-toggle="tooltip"
                                      title="Indique dirección física del bien" :config="ckeditor.editorConfig"
                                      class="form-control" name="direction" tag-name="textarea" rows="3"
                                      v-model="record.address"></ckeditor>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
		        	<button type="button" @click="reset()"
							class="btn btn-default btn-icon btn-round"
							data-toggle="tooltip"
							title="Borrar datos del formulario">
							<i class="fa fa-eraser"></i>
					</button>

		        	<button type="button" @click="redirect_back(route_list)"
							class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
							title="Cancelar y regresar">
						<i class="fa fa-ban"></i>
		        	</button>

		        	<button type="button"  @click="createRecord('asset/registers')"
		        			class="btn btn-success btn-icon btn-round btn-modal-save"
		        			title="Guardar registro">
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
					asset_type_id: '',
					asset_category_id: '',
					asset_subcategory_id: '',
					asset_specific_category_id: '',

					asset_acquisition_type_id: '',
					acquisition_date: '',
					institution_id: '',
					proveedor_id: '',
					asset_condition_id: '',
					asset_status_id: '',
					asset_use_function_id: '',
					serial: '',
					marca: '',
					model: '',
					value: '',
					purchase_supplier_id: '',
					color: '',
					asset_institutional_code: '',


					country_id: '',
					estate_id: '',
					municipality_id: '',
					parish_id: '',
					address: '',

					specifications: '',
					currency_id: '',

				},
				required: {},

				records: [],
				errors: [],

				institutions: [],
				asset_types: [],
				asset_categories: [],
				asset_subcategories: [],
				asset_specific_categories: [],

				asset_acquisition_types: [],
				supplier: [],
				asset_conditions: [],
				asset_status: [],
				asset_use_functions: [],

				countries: [],
				estates: [],
				municipalities: [],
				parishes: [],
				currencies: [],
			}
		},
		props: {
			assetid: Number,
		},
		methods: {
			reset() {
				this.record = {
					id: '',
					asset_type_id: '',
					asset_category_id: '',
					asset_subcategory_id: '',
					asset_specific_category_id: '',

					asset_acquisition_type_id: '',
					acquisition_date: '',
					institution_id: '',
					proveedor_id: '',
					asset_condition_id: '',
					asset_status_id: '',
					asset_use_function_id: '',
					serial: '',
					marca: '',
					model: '',
					value: '',
					purchase_supplier_id: '',
					color: '',
					asset_institutional_code: '',


					country_id: '',
					estate_id: '',
					municipality_id: '',
					parish_id: '',
					address: '',

					specifications: '',
					currency_id: '',

				};

			},
			/**
			 * Obtiene los datos de las formas de adquisición de los bienes institucionales registrados
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			getAssetAcquisitionTypes() {
				const vm = this;
				vm.asset_acquisition_types = [];
				axios.get(`${window.app_url}/asset/get-acquisition-types`).then(response => {
					vm.asset_acquisition_types = response.data;
				});
			},
			/**
			 * Obtiene los datos de la condición física de los bienes institucionales
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			getAssetConditions() {
				const vm = this;
				vm.asset_conditions = [];
				axios.get(`${window.app_url}/asset/get-conditions`).then(response => {
					vm.asset_conditions = response.data;

				});
			},
			/**
			 * Obtiene los datos de los estatus de uso de los bienes institucionales
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			getAssetStatus() {
				const vm = this;
				vm.asset_status = [];
				axios.get(`${window.app_url}/asset/get-status`).then(response => {
					vm.asset_status = response.data.filter((item) => item.id !== 1
																	&& item.id !== 6 && item.id !== 11);
				});
			},

			/**
			 * Obtiene los datos de las funciones de uso de los bienes institucionales
			 *
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			getAssetUseFunctions() {
				const vm = this;
				vm.asset_use_functions = [];
				axios.get(`${window.app_url}/asset/get-use-functions`).then(response => {
					vm.asset_use_functions = response.data;
				});
			},
			/**
			 * Metodo que carga la información en el formulario de edición
			 *
			 * @param [Integer] $id Identificador único del registro a editar
			 * @author Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			async loadForm(id) {
				const vm = this;
				vm.loading = true;
          		await axios.get(`${window.app_url}/asset/registers/info/${id}`).then(response => {
              		if(typeof(response.data.records != "undefined")) {
              			let recordEdit = response.data.records;

                  		vm.record = recordEdit;
              		}
          		});

          		if (vm.record.parish) {
          			vm.record.country_id = vm.record.parish.municipality.estate.country.id;
          			vm.getEstates();
          		}
			},

			/**
			 * Reescribe el método getEstates para cambiar su comportamiento por defecto
			 * Obtiene los Estados del Pais seleccionado			 *
			 */
			async getEstates() {
				const vm = this;
				vm.estates = [];

				if (vm.record.country_id) {
					await axios.get(`${window.app_url}/get-estates/${this.record.country_id}`).then(response => {
						vm.estates = response.data;
					});
					if ((vm.record.parish) && (vm.record.id)) {
	          			vm.record.estate_id = vm.record.parish.municipality.estate.id;
	          			vm.getMunicipalities();
	          		}
				}
			},

			/**
			 * realizado por Francisco Escala fjescala@gmail.com
			 */
			async getSuppliers() {
				const vm = this;
				vm.supplier = [];

					await axios.get(`${window.app_url}/asset/suppliers-list`).then(response => {
						vm.supplier = response.data;

					});
			},

			/**
			 * Reescribe el método getMunicipalities para cambiar su comportamiento por defecto
			 * Obtiene los Municipios del Estado seleccionado
			 */
			async getMunicipalities() {
				const vm = this;
				vm.municipalities = [];

				if (vm.record.estate_id) {
					await axios.get(`${window.app_url}/get-municipalities/${this.record.estate_id}`).then(response => {
						vm.municipalities = response.data;
					});
				}
				if (vm.record.parish) {
          			vm.record.municipality_id = vm.record.parish.municipality.id;
          			vm.getParishes();
          		}
			},

			/**
			 * Reescribe el método getParishes para cambiar su comportamiento por defecto
			 * Obtiene las parroquias del municipio seleccionado
			 *
			 */
			async getParishes() {
				const vm = this;
				vm.parishes = [];

				if (this.record.municipality_id) {
					await axios.get(`${window.app_url}/get-parishes/${this.record.municipality_id}`).then(response => {
						vm.parishes = response.data;
					});
				}
				if (vm.record.parish) {
          			vm.record.parish_id = vm.record.parish.id;
          		}
			},
			getAssetRequired() {
				const vm = this;
				vm.required = {};

				if (vm.record.asset_specific_category_id) {
					axios.get(
						`${window.app_url}/asset/get-required/${this.record.asset_specific_category_id}`
					).then(response => {
						vm.required = response.data.record;
					});
				}
			},
		},
		created() {
			const vm = this;
			vm.getAssetTypes();
			vm.getInstitutions();
			vm.getAssetAcquisitionTypes();
			vm.getAssetConditions();
			vm.getAssetStatus();
			vm.getAssetUseFunctions();
			vm.getCountries();
			vm.getCurrencies();
			vm.getSuppliers();
		},
		mounted() {
			const vm = this;
			if (vm.assetid) {
				vm.loadForm(vm.assetid);
			}
		},
	};
</script>
