<template>
	<section id="CitizenServiceReportForm">
		<div class="card-body">
			<div class="alert alert-danger" v-if="errors.length > 0">
				<ul>
					<li v-for="(error, index) in errors" :key="index">{{ error }}</li>
				</ul>
			</div>
			<div class="row">
				<div class="col-md-6" id="helpCitizenServiceReportRequestType">
					<div class="form-group">
						<label for="citizenserviceRequestTypes">Tipo de solicitud</label>
					    <v-multiselect :options="citizen_service_request_types" track_by="text"
								v-model="record.citizen_service_request_types"
							    :hide_selected="false">
					    </v-multiselect>
					</div>
				</div>
				<div class="col-md-6" id="helpCitizenServiceReportStates">
					<div class="form-group">
						<label>Estado de la solicitud:</label>
						<v-multiselect :options="citizen_service_states" track_by="text"
								v-model="record.citizen_service_states"
							    :hide_selected="false">
					    </v-multiselect>
		            </div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6"  id="helpCitizenServiceReportTypeSearch">
					<div class="form-group" style="text-align: right;">
						<label>Búsqueda por período</label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_type_search" id="sel_search_period"
									   name="type_search" value="period" v-model="record.type_search">
								<label class="custom-control-label" for="sel_search_period"></label>
							</div>
							<input type="hidden" v-model="record.type_search">
						</div>
					</div>
				</div>
				<div class="col-md-1,5" id="helpCitizenServiceReportSearchDate">
					<div class=" form-group">
						<label>Búsqueda por fecha </label>
						<div class="col-12">
							<div class="custom-control custom-switch">
								<input type="radio" class="custom-control-input sel_type_search" id="sel_search_date"
									   name="type_search" value="date" v-model="record.type_search">
								<label class="custom-control-label" for="sel_search_date"></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div v-show="this.record.type_search == 'period'">
				<div class="row">
					<div class="col-md-4 offset-2">
						<div class="form-group">
							<label>Desde:</label>
							<div class="input-group input-sm">
			                    <span class="input-group-addon">
			                        <i class="now-ui-icons ui-1_calendar-60"></i>
			                    </span>
			                    <input type="date" data-toggle="tooltip" title="Indique la fecha minima de busqueda"
									   class="form-control input-sm no-restrict" v-model="record.start_date">
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
			                    <input type="date" data-toggle="tooltip" title="Indique la fecha maxima de busqueda"
									   class="form-control input-sm no-restrict" v-model="record.end_date">
			                </div>
		                </div>
					</div>
				</div>
			</div>
			<div v-show="this.record.type_search == 'date'">
				<div class="col-md-4 offset-2">
					<div class="form-group">
						<label >Fecha</label>
						<input type="date" class="form-control input-sm" data-toggle="tooltip"
		                       title="Indique la fecha de solicitud" v-model="record.date">
					</div>
				</div>
			</div>

			<div class="row">
					<div class="col-md-12">
						<button type="button" @click="filterRecords()"
	                            class='btn btn-sm btn-info float-right' title="Buscar registro" data-toggle="tooltip">
							<i class="fa fa-search"></i>
						</button>
					</div>
			</div>
		<hr>
			<v-client-table :columns="columns" :data="records" :options="table_options">
				<div slot="code" slot-scope="props" class="text-center">
					<span>{{ props.row.code }}</span>
				</div>
				<div slot="id" slot-scope="props" class="text-center">
					<div class="d-inline-flex" v-if="props.row.id"></div>
				</div>
				<div slot="requested_by" slot-scope="props" class="text-center">
		            <span>{{ props.row.first_name + ' ' + props.row.last_name }}</span>
		    	</div>
		    	<div slot="citizen_service_request_type_id" slot-scope="props" class="text-center">
		            <span>{{ props.row.citizen_service_request_type ? props.row.citizen_service_request_type.name :''}}</span>
		    	</div>
			</v-client-table>
		</div>
		<div class="card-footer text-right">
			<div class="row">
				<div class="col-md-3 offset-md-9" id="helpParamButtons">
		        	<button type="button" class='btn btn-sm btn-primary btn-custom'
							@click="createReport()">
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
					citizen_service_request_types: [],
					citizen_service_states: [],
					type_search: '',
					date: '',
					start_date: '',
					end_date: '',
				},

				records: [],
				errors: [],
				columns: ['requested_by', 'citizen_service_request_type_id', 'state', 'date'],
				citizen_service_request_types: [],
				citizen_service_states: [
					{
						id: '',
						text:'Seleccione..'
					},
					{
						id: 'Aceptado',
						text:'Aceptado'
					},
					{
						id: 'Culminado',
						text:'Culminado'
					},
				]

			}
		},
		created() {
			this.table_options.headings = {
				'requested_by': 'Solicitado por',
				'citizen_service_request_type_id': 'Tipos de solicitud',
				'state': 'Estado de la solicitud',
				'date': 'Fecha de la solicitud'
			};

			this.table_options.sortable = ['requested_by', 'citizen_service_request_type_id', 'state', 'date'];
			this.table_options.filterable = ['requested_by', 'citizen_service_request_type_id', 'state', 'date'];


		},
		mounted() {
			 const vm = this;
			 vm.getCitizenServiceRequestTypes();
		},

		methods: {
			reset() {
				this.record = {
					id: '',
					citizen_service_request_types: [],
					citizen_service_states: [],
					type_search: '',
					date: '',
					start_date: '',
					end_date: '',
				};
			},

			createReport() {
				const vm = this;

				vm.loading = true;
				var fields = {};
				for (var index in this.record) {
					fields[index] = this.record[index];
				}
				let url = vm.setUrl('/citizenservice/reports/request/create');
				axios.post(url, fields).then(response => {
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
					vm.errors = [];

					if (typeof(error.response) !="undefined") {
						for (var index in error.response.data.errors) {
							if (error.response.data.errors[index]) {
								vm.errors.push(error.response.data.errors[index][0]);
							}
						}
					}
					vm.loading = false;
				});

			},
			filterRecords() {

				const vm = this;
				var url =  `${window.app_url}/citizenservice/reports/search`;
				url += '?type_search=' + vm.record.type_search + '&start_date=' + vm.record.start_date + '&end_date=' + vm.record.end_date + '&citizen_service_request_types=' + JSON.stringify(vm.record.citizen_service_request_types) + '&citizen_service_states=' + JSON.stringify(vm.record.citizen_service_states) +  '&date=' + vm.record.date;

				axios.get(url).then(response => {
					vm.records = response.data.records;

				});
			}
		},
    };
</script>
