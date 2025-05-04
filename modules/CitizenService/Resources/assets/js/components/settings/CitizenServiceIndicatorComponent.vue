<template>
	<div class="col-xs-2 text-center">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href="#"
		   title="Registros de Indicadores" data-toggle="tooltip"
		   @click="addRecord('add_citizenservice-indicators', 'citizenservice/indicators', $event); getEffectType()">
           <i class="icofont icofont-stock-search ico-3x"></i>
		   <span>Indicadores</span>
		</a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_citizenservice-indicators">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-stock-search ico-3x"></i>
							Indicadores
						</h6>
					</div>
					<div class="modal-body">
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
                            <div class="col-md-4">
        						<div class="form-group is-required">
        							<label for="name">Nombre:</label>
        							<input type="text" id="name" placeholder="Nombre"
										   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
        								   class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
        								   title="Indique el nombre del indicador">
        							<input type="hidden" name="id" id="id" v-model="record.id">
        	                    </div>
                            </div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="description">Descripción:</label>
									<input type="text" id="description" placeholder="Descripción"
										   v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*"
										   class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
										   title="Indique la descripción del indicador">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group is-required">
									<label for="effect_types_id">Tipo de impacto:</label>
									<select2 :options="effectTypeList" id="effect_types_id" placeholder="Tipo de impacto"
                                        class="form-control input-sm" v-model="record.effect_types_id" data-toggle="tooltip"
                                        title="Indique el tipo de impacto"></select2>
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
							<button type="button" @click="createRecord('citizenservice/indicators')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
	                		<div slot="effect_types_id" slot-scope="props">
                                <span> {{ getEffectTypeName(props.row.effect_types_id) }} </span>
                            </div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" v-has-tooltip type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'citizenservice/indicators')"
										class="btn btn-danger btn-xs btn-icon btn-action"
										title="Eliminar registro" data-toggle="tooltip" v-has-tooltip
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
					name: '',
					description: '',
					effect_types_id: ''
				},
				errors: [],
				records: [],
				columns: ['name', 'description', 'effect_types_id', 'id'],
				effectTypeList: [],
			}
		},
		methods: {
			/**
			 * Método que borra todos los datos del formulario
			 */
			reset() {
				this.record = {
					id: '',
					name: '',
					description: '',
					effect_types_id: ''
				};
			},
			getEffectType() {
	            const vm = this;
	            axios.get(`${window.app_url}/citizenservice/get-effect-types`).then(response => {
	                vm.effectTypeList = response.data;
	            });
        	},
        	getEffectTypeName(effect_types_id) {
        		const vm = this;
                let value = '';
                $.each(vm.effectTypeList, function(index, field) {
                    if (field['id'] == effect_types_id) {
                        value = field['text'];
                    }
                });
                return value;
            },
		},
		created() {
			const vm = this;
			vm.table_options.headings = {
				'name': 'Nombre',
				'description': 'Descripción',
				'effect_types_id':'Tipo de impacto',
				'id': 'Acción'
			};
			vm.table_options.sortable = ['name'];
			vm.table_options.filterable = ['name'];
			vm.table_options.columnsClasses = {
				'name': 'col-md-3',
				'description': 'col-md-4',
				'effect_types_id': 'col-md-3',
				'id': 'col-md-2'
			};
		},
	};
</script>