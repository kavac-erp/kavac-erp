<template>
	<section id="CitizenServiceRequestForm">
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
                <div class="col-md-4" id="helpCitizenServiceRequestDate">
                    <div class="form-group is-required">
                        <label for="date">Fecha</label>
                        <input type="date" id="date" class="form-control input-sm no-restrict" data-toggle="tooltip"
                               title="Indique la fecha de solicitud" v-model="record.date">
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceRequestFirstName">
                    <div class="form-group is-required">
                        <label for="first_name">Nombres</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique los nombres del solicitante" v-model="record.first_name">
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceRequestLastName">
                    <div class="form-group is-required">
                        <label for="last_name">Apellidos</label>
                        <input type="text" id="apellido" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique los apellidos del solicitante" v-model="record.last_name">
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceRequestIdNumber">
                    <div class="form-group is-required">
                        <label for="id_number">Cédula de identidad</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
                               title="Indique la cédula de identidad del solicitante" v-model="record.id_number">
                    </div>
                </div>
				<div class="col-md-4" id="helpCitizenServiceRequestBirthDate">
                    <div class="form-group">
                        <label for="birth_date">Fecha de nacimiento</label>
                        <input type="date" id="birth_date" placeholder="Fecha de nacimiento"
                            class="form-control input-sm" data-toggle="tooltip"
                            title="Indique la fecha de nacimiento" v-model="record.birth_date"
							:min="mindate" :max="maxdate"
							@change="setAge">
                    </div>
                </div>
				<div class="col-md-4" id="helpCitizenServiceRequestAge">
                    <div class="form-group">
                        <label for="age">Edad:</label>
                        <input type="text" id="age" data-toggle="tooltip"
                            title="Indique la edad de la persona solicitante" disabled class="form-control input-sm"
                            v-input-mask data-inputmask="
                                       'alias': 'numeric',
                                       'allowMinus': 'false',
                                       'digits': 0"
                            v-model="record.age" />
                    </div>
                </div>
                <div class="col-md-4" id="helpCitizenServiceEmail">
                    <div class="form-group is-required">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" class="form-control input-sm" data-toggle="tooltip"
                               title="Indique el correo electrónico del solicitante" v-model="record.email">
                    </div>
                </div>
				<div v-if="isPayrollActive" class="col-md-4" id="helpCitizenGenderId">
					<div class="form-group is-required">
						<label for="gender_id">Género</label>
						<select2  id="input_gender_id" :options="genders" v-model="record.gender_id"></select2>
					</div>
				</div>
				<div v-else class="col-md-4" id="helpCitizenServiceRequestGender">
                    <div class="form-group is-required">
                        <label for="gender">Género</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique el género del solicitante" v-model="record.gender">
                    </div>
                </div>
				<div v-if="isPayrollActive" class="col-md-4" id="helpCitizenNationalityId">
					<div class="form-group is-required">
						<label for="nationality_id">Nacionalidad</label>
						<select2  id="input_nationality_id" :options="nationalities" v-model="record.nationality_id"></select2>
					</div>
				</div>
				<div v-else class="col-md-4" id="helpCitizenServiceRequestNationality">
                    <div class="form-group is-required">
                        <label for="nationality">Nacionalidad</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique la nacionalidad del solicitante" v-model="record.nationality">
                    </div>
                </div>
            </div>
            <h6 class="card-title">
                Números Telefónicos <i class="fa fa-plus-circle cursor-pointer" @click="addPhone"></i>
            </h6>
            <div class="row" v-for="(phone, index) in record.phones" :key="index">
                <div class="col-3" id="helpCitizenServicePhones">
                    <div class="form-group is-required">
                        <select data-toggle="tooltip" v-model="phone.type" class="select2"
                                title="Seleccione el tipo de número telefónico">
                            <option value="">Seleccione...</option>
                            <option value="M">Móvil</option>
                            <option value="T">Teléfono</option>
                            <option value="F">Fax</option>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group is-required">
                        <input type="text" placeholder="Cod. Area" data-toggle="tooltip"
                               title="Indique el código de área" v-model="phone.area_code"
                               class="form-control input-sm">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group is-required">
                        <input type="text" placeholder="Número" data-toggle="tooltip"
                               title="Indique el número telefónico"
                               v-model="phone.number" class="form-control input-sm">
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <input type="text" placeholder="Extensión" data-toggle="tooltip"
                               title="Indique la extención telefónica (opcional)"
                               v-model="phone.extension" class="form-control input-sm">
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-group">
                        <button class="btn btn-sm btn-danger btn-action" type="button"
                                @click="removeRow(index, record.phones)"
                                title="Eliminar este dato" data-toggle="tooltip">
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr>
			<h6 class="card-title">
                Datos de Ubicación del Solicitante
            </h6>
			<div class="row">
		    	<div class="col-md-4">
					<div class="form-group is-required" id="helpCitizenServiceCountry">
						<label for="countries">País</label>
						<select2 id="input_country" :options="countries" @input="getEstates()" v-model="record.country_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceEstate">
					<div class="form-group is-required">
						<label for="estates">Estado</label>
						<select2  id="input_estate" :options="estates" @input="getCities()" v-model="record.estate_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceCity">
					<div class="form-group is-required">
						<label for="cities">Ciudad</label>
						<select2  id="input_city" :options="cities" @input="getMunicipalities()" v-model="record.city_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceMunicipality">
					<div class="form-group is-required">
						<label for="municipalities">Municipio</label>
						<select2 id="input_municipality" :options="municipalities" @input="getParishes()" v-model="record.municipality_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceParish">
					<div class="form-group is-required">
						<label for="parishes">Parroquia</label>
						<select2 :options="parishes" v-model="record.parish_id"></select2>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceAddress">
					<div class="form-group is-required">
						<label for="address">Dirección</label>
    					<input type="text" id="address" class="form-control input-sm" data-toggle="tooltip"
                               title="Indique la dirección" v-model="record.address">
					</div>
				</div>
				<div class="col-md-12" id="helpCitizenServiceRequestCommunity">
                    <div class="form-group">
                        <label>Comunidad:</label>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch" data-toggle="tooltip"
                                 title="Indique si la persona solitiante pertenece a una comunidad">
                                <input type="radio" class="custom-control-input" @click="resetInfo()" id="active_community" name="active_community"
                                       v-model="record.community" value="community">
                                <label class="custom-control-label" for="active_community"></label>
                            </div>
                        </div>
                    </div>
                </div>
				<div v-if="record.community == 'community'" class="col-md-12">
						<b>Datos de la Comunidad</b>
				</div>
				<div v-if="record.community == 'community'" class="col-md-4">
                    <div class="form-group is-required">
                        <label for="location">Ubicación</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique la ubicación del solicitante" v-model="record.location">
                    </div>
                </div>
				<div v-if="record.community == 'community'" class="col-md-4">
                    <div class="form-group is-required">
                        <label for="commune">Comuna</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique la comuna al que pertenece el solicitante" v-model="record.commune">
                    </div>
                </div>
				<div v-if="record.community == 'community'" class="col-md-4">
                    <div class="form-group is-required">
                        <label for="communal_council">Consejo Comunal</label>
                        <input type="text" class="form-control input-sm" data-toggle="tooltip"
							   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
                               title="Indique el consejo comunal al que pertenece el solicitante" v-model="record.communal_council">
                    </div>
                </div>
				<div class="col-md-12">
					<div v-if="record.community == 'community'" class="col-md-4">
						<div class="form-group is-required">
							<label for="population_size">Cantidad de habitantes</label>
							<input type="number" class="form-control input-sm" :step="1" min="1" data-toggle="tooltip"
								   title="Indique la cantidad de habitantes de la comunidad" v-model="record.population_size">
						</div>
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceTypeInstitution">
    				<div class="form-group">
    					<label>Institución</label>
    					<div class="col-md-12">
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="type_institution"
									   name="type_institution" v-model="record.type_institution" :value="true">
								<label class="custom-control-label" for="type_institution"></label>
							</div>
    					</div>
    				</div>
    			</div>
				<div class="row col-md-12" v-show="this.record.type_institution">
					<div class="col-md-12">
						<b>Datos de la institución</b>
					</div>
            	        <div class="col-md-4">
							<div class="form-group is-required">
								<label for="institution_name">Nombre de la institución</label>
        						<input type="text" id="institution_name" class="form-control input-sm" data-toggle="tooltip"
        						 	   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
            	                       title="Indique el nombre de la institución" v-model="record.institution_name">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group is-required">
								<label for="rif">RIF</label>
        						<input type="text" id="rif" class="form-control input-sm" data-toggle="tooltip"
									   placeholder="J000000000"
            	                       title="Indique el rif de la institución" v-model="record.rif">
            	            </div>
						</div>
						<div class="col-md-4">
							<div class="form-group is-required">
								<label for="institution_address">Dirección de la institución</label>
        						<input type="text" id="institution_address" class="form-control input-sm"
            	                       data-toggle="tooltip" title="Indique la dirección de la institución"
            	                       v-model="record.institution_address">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="web">Dirección web</label>
        						<input type="url" id="web" class="form-control input-sm" data-toggle="tooltip"
            	                       title="Indique la dirección web" v-model="record.web">
							</div>
						</div>
				</div>
			</div>
			<hr>
			<h6 class="card-title">
                Datos de la Solicitud
            </h6>
			<div class="row">
				<div class="col-md-4" id="helpCitizenServiceMotiveRequest">
					<div class="form-group is-required">
						<label for="motive_request">Motivo de la solicitud</label>
    					<input type="text" id="motive_request" class="form-control input-sm" data-toggle="tooltip"
                               title="Indique el motivo de la solicitud" v-model="record.motive_request">
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceAttribute">
					<div class="form-group is-required">
						<label for="attribute">Descripción de la solicitud</label>
    					<input type="text" id="attribute" class="form-control input-sm" data-toggle="tooltip"
                               title="Indique la descripción de la solicitud" v-model="record.attribute">
					</div>
				</div>
				<div class="col-md-4" id="helpCitizenServiceRequestType">
					<div class="form-group is-required">
						<label for="citizenserviceRequestTypes">Tipo de solicitud</label>
						<select2 :options="citizen_service_request_types"
								  @input="getCitizenServiceRequestType()"
								  v-model="record.citizen_service_request_type_id"></select2>
                    </div>
				</div>
			</div>
			<div v-if="citizenServiceRequestType == 'Soporte técnico'">
				<div class="col-md-12">
					<b>Datos del equipo</b>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label for="type_team">Tipo de equipo</label>
        					<input type="text" id="type_team" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el tipo de equipo" v-model="record.type_team"/>
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="brand">Marca</label>
        					<input type="text" id="brand" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique la marca del equipo" v-model="record.brand"/>
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="model">Modelo</label>
        					<input type="text" id="model" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el modelo del equipo" v-model="record.model"/>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="serial">Serial</label>
        					<input type="text" id="serial" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el serial del equipo" v-model="record.serial"/>
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="color">Color</label>
        					<input type="text" id="color" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el color del equipo" v-model="record.color"/>
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="transfer">Motivo de traslado</label>
        					<input type="text" id="transfer" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el motivo de traslado" v-model="record.transfer"/>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="inventory_code">Código de inventario</label>
        					<input type="text" id="inventory_code" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique el código de inventario" v-model="record.inventory_code"/>
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="entryhour">Hora de entrada</label>
        					<input type="time" id="entryhour" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique la hora de entrada del equipo" v-model="record.entryhour"/>
						</div>
					</div>
                    <div class="col-md-4">
						<div class="form-group">
							<label for="exithour">Hora de salida</label>
        					<input type="time" id="exithour" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique la hora de salida del equipo" v-model="record.exithour"/>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="informationteam">Información adicional del equipo</label>
        					<input type="text" id="informationteam" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique la información adicional del equipo" v-model="record.informationteam"/>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="other">Otros</label>
        					<input type="text" id="other" class="form-control input-sm" data-toggle="tooltip"
                                   title="Indique otra información no referente al equipo" v-model="record.other"/>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4" id="helpCitizenServiceDepartment">
					<div class="form-group is-required">
						<label for="citizenserviceDepartment">Departamento</label>
						<select2 :options="citizen_service_departments" v-model="record.citizen_service_department_id" @input="setDirector()"></select2>
					</div>
				</div>
				<div v-if="isPayrollActive" class="col-md-4" id="helpCitizenDirectorId">
					<div class="form-group is-required">
						<label for="director_id">Director y/o responsable de la solicitud</label>
						<select2  id="input_director_id" :options="payroll_staffs" v-model="record.director_id" disabled></select2>
					</div>
				</div>
			</div>
		</div>
		<div class="card-footer text-right">
        	<button type="button" @click="reset()" class="btn btn-default btn-icon btn-round"
					data-toggle="tooltip" v-has-tooltip
                    title ="Borrar datos del formulario">
					<i class="fa fa-eraser"></i>
			</button>
        	<button type="button" @click="redirect_back(route_list)"
                        class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                        title="Cancelar y regresar">
                    <i class="fa fa-ban"></i>
            </button>
			<button type="button"  @click="createRecord('citizenservice/requests')"
					class="btn btn-success btn-icon btn-round btn-modal-save"
					title="Guardar registro">
				<i class="fa fa-save"></i>
            </button>
        </div>
   	</section>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					date: '',
					gender_id: '',
					gender: '',
					nationality_id: '',
					nationality: '',
					community: 'notCommunity',
					location: '',
					commune: '',
					communal_council: '',
					population_size: '',
					director_id: '',
					first_name: '',
					last_name: '',
					id_number: '',
					email: '',
					birth_date: '',
					age: '',
					phones: [],
					city_id: '',
        			municipality_id: '',
        			parish_id: '',
        			address: '',
        			motive_request: '',
        			attribute: '',
        			citizen_service_request_type_id: '',
        			citizen_service_department_id: '',

        			type_institution: '',
					institution_name: '',
					rif: '',
        			institution_address: '',
        			web: '',

                    // Datos del equipo
        			type_team: '',
        			brand: '',
        			model: '',
        			serial: '',
        			color: '',
        			transfer: '',
        			inventory_code: '',
        			entryhour: '',
        			exithour: '',
        			informationteam: '',
        			other: ''
				},
				errors: [],
				records: [],
				genders: [],
				nationalities: [],
				countries: [],
				estates: [],
				cities: [],
				municipalities: [],
				parishes: [],
				citizenServiceRequestType: '',
				citizen_service_request_types: [],
				citizen_service_departments: [],
				citizen_service_documents: [],
				payroll_staffs: [],
				department_info: [],
				director_info: [],
				payroll: "",

				mindate: "1900-01-01",
				maxdate: "2099-12-31",
			}
		},
		methods: {
			async loadForm(id){
				const vm = this;

	            await axios.get(`${window.app_url}/citizenservice/requests/vue-info/${id}`).then(response => {
	                if(typeof(response.data.record != "undefined")){
						vm.record = response.data.record;
						vm.record.country_id = vm.record.parish.municipality.estate.country_id;
	                }
	            });
			},
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					date: '',
					gender_id: '',
					gender: '',
					nationality_id: '',
					nationality: '',
					community: 'notCommunity',
					location: '',
					commune: '',
					communal_council: '',
					population_size: '',
					first_name: '',
					last_name: '',
					id_number: '',
					email: '',
					birth_date: '',
					age: '',
					phones: [],
					city_id: '',
        			municipality_id: '',
        			parish_id: '',
        			address: '',
        			motive_request: '',
        			attribute: '',
					citizen_service_request_type_id: '',
					citizen_service_department_id: '',

					type_institution: false,
					institution_name: '',
        			rif: '',
        			institution_address: '',
        			web: '',


        			type_team: '',
        			brand: '',
        			model: '',
        			serial: '',
        			color: '',
        			transfer: '',
        			inventory_code: '',
        			entryhour: '',
        			exithour: '',
        			informationteam: '',
        			other: ''
				};
				this.citizenServiceRequestType = '';
			},
			resetInfo() {
				const vm = this;
				if (vm.record.community == 'community') {
					vm.record.community = 'notCommunity';
				}
			},
			getCitizenServiceDepartments() {
				this.citizen_service_departments = [];
				axios.get(`${window.app_url}/citizenservice/get-departments`).then(response => {
					this.citizen_service_departments = response.data;
					this.department_info = response.data;
				});

			},
			setDirector() {
				const vm = this;
				vm.director_info = Object.values(vm.department_info).find(
					department => department.id == vm.record.citizen_service_department_id);
				if (vm.director_info) {
					vm.record.director_id = vm.director_info.director_id;
				}
			},
			getGenders() {
				const vm = this;
				axios.get(`${window.app_url}/get-genders`).then(response => {
        			vm.genders = response.data;
      			});
			},
			getNationalities() {
				const vm = this;
				axios.get(`${window.app_url}/payroll/get-nationalities`).then(response => {
        			vm.nationalities = response.data;
      			});
			},
			getPayrollStaffs() {
      			const vm = this;
      			axios.get(`${window.app_url}/payroll/get-staffs`).then(response => {
        			vm.payroll_staffs = response.data;
      			});
			},
			getCitizenServiceRequestType() {
                const vm = this;
                $.each(vm.citizen_service_request_types, function(index, field) {
                    if (field['id'] == '') {
                        vm.citizenServiceRequestType = '';
                    } else if (field['id'] == vm.record.citizen_service_request_type_id) {
                        vm.citizenServiceRequestType = field['text'];
                    }
                });
            },

			setAge() {
				const vm = this;
				let age = moment().diff(vm.record.birth_date, "years", false);
				vm.record.age = age > -1 ? age : "";
			}
		},
		mounted() {
			const vm = this;
			vm.getGenders();
			vm.getNationalities();
			vm.getPayrollStaffs();

			if(this.requestid){
				this.loadForm(this.requestid);
			}
		},
		props: {
			requestid: {
                type: Number
            },
			isPayrollActive: {
				type: String
			}
		},
		created() {
			const vm = this;
			vm.getCountries();
			vm.getCitizenServiceRequestTypes();
			vm.getCitizenServiceDepartments();
            vm.record.phones = [];
            this.record.type_institution = false;
		},
	};
</script>
