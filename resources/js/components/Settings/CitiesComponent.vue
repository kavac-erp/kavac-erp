<template>
	<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary"
		   href="javascript:void(0)" title="Registro de ciudades"
		   data-toggle="tooltip" @click="addRecord('add_city', 'cities', $event)">
			<i class="icofont icofont-5-star-hotel ico-3x"></i>
			<span>Ciudades</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_city">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-5-star-hotel inline-block"></i>
							Ciudades
						</h6>
					</div>
					<div class="modal-body">
						<form-errors :listErrors="errors"></form-errors>
						<div class="row">
							<div class="col-12 col-md-6">
								<div class="form-group is-required">
									<label>País:</label>
									<select2 :options="countries" @input="getEstates"
											 v-model="record.country_id"></select2>
									<input type="hidden" v-model="record.id">
			                    </div>
							</div>
							<div class="col-12 col-md-6">
								<div class="form-group" v-show="editCities=='false'">
                                    <label>Estados:</label>
                                    <select2 :options="estates" v-model="record.estate_id"></select2>
                                </div>
                                <div class="form-group" v-show="editCities == 'true'">
                                    <label>Estados:</label>
                                    <select class="form-control select2 pb-2" v-model="record.estate_id">
                                        <option :value="ste.id" :selected="ste.id == record.estate_id"
												v-for="ste in estates" :key="ste.id">
                                                {{ ste.text }}
                                        </option>
                                    </select>
                                </div>
							</div>
							<div class="col-12">
								<div class="form-group is-required">
									<label>Nombre:</label>
									<input type="text" placeholder="Nombre de la Ciudad" data-toggle="tooltip"
										   title="Indique el nombre de la ciudad (requerido)"
										   class="form-control input-sm" v-model="record.name" v-is-text>
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
							<button type="button" @click="createRecord('cities')"
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
		                		<button @click="deleteRecord(props.row.id, 'cities')"
										class="btn btn-danger btn-xs btn-icon btn-action"
										title="Eliminar registro" data-toggle="tooltip"
										type="button">
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
					country_id: '0',
					estate_id: '',
					name: '',
				},
				errors: [],
				records: [],
				countries: [],
				estates: ['0'],
				columns: ['estate.name', 'name', 'id'],
				editCities: '',
			}
		},
		 watch: {
            record: {
                deep: true,
                handler: function(newValue, oldValue) {
                    const vm = this;
                    if (vm.record.id) {
                        vm.record.estate_id = vm.selectedEstateId;
                    }
                }
            },
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
					country_id: '0',
					estate_id: '',
					name: '',
				};
				this.editCities = 'false';
			},

			/**
             * Método que carga el formulario con los datos a modificar
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} index Identificador del registro a ser modificado
             * @param {object} event   Objeto que gestiona los eventos
             */
            initUpdate(id, event) {
                let vm = this;
                vm.editCities = 'true';
                vm.errors = [];
                let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();
                vm.record = recordEdit;
                vm.record.country_id = recordEdit.estate.country_id;
                vm.getEstates(vm.record.country_id);
                vm.record.estate_id = recordEdit.estate.id;
                vm.selectedEstateId = recordEdit.estate.id;
                event.preventDefault();
            }
		},
		created() {
			this.editCities = 'false';
			this.table_options.headings = {
				'estate.name': 'Estado',
				'name': 'Ciudad',
				'id': 'Acción'
			};
			this.table_options.sortable = ['estate.name', 'name'];
			this.table_options.filterable = ['estate.name', 'name'];
			this.table_options.columnsClasses = {
				'estate.name': 'col-md-3',
				'name': 'col-md-7',
				'id': 'col-md-2'
			};
		},
		mounted() {
			const vm = this;
			vm.editCities = 'false';
			$("#add_city").on('show.bs.modal', function() {
				vm.getCountries();
			});
		}
	};
</script>
