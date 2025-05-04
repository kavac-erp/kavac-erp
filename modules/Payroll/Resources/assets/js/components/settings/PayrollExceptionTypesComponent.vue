<template>
	<section id="payrollExceptionTypesFormComponent">
		<a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de categorías de la hoja de tiempo" data-toggle="tooltip"
           @click="addRecord('add_payroll_exception_type', 'payroll/exception-types', $event)">
           <i class="icofont icofont-ui-block ico-3x"></i>
           <span>Categorías <br>Hoja de Tiempo</span>
        </a>
		<div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_exception_type">
			<div class="modal-dialog vue-crud" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-ui-block ico-3x"></i>
							Categorías de la Hoja de Tiempo
						</h6>
					</div>
					<div class="modal-body">
                        <!-- mensajes de error -->
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
                        <!-- ./mensajes de error -->
                        <div class="row">
                            <div class="col-md-6">
                                <!-- nombre -->
        						<div class="form-group is-required">
        							<label for="name">Nombre:</label>
        							<input type="text" id="name" placeholder="Nombre"
        								   class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
        								   title="Indique el nombre de categoría de la hoja de tiempo (requerido)">
        							<input type="hidden" name="id" id="id" v-model="record.id">
        	                    </div>
                                <!-- ./nombre -->
								<!-- valor máximo -->
                                <div class="form-group">
                                    <label for="value_max">Valor máximo por período:</label>
                                    <input id="value_max" class="form-control input-sm" type="text"
                                           data-toggle="tooltip" placeholder="Valor máximo"
                                           title="Indique el valor máximo del parámetro"
                                           v-model="record.value_max"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false'
                                           "
                                    />
                                </div>
                            	<!-- ./valor máximo -->
                            </div>
                            <div class="col-md-6">
                                <!-- descripción -->
                                <div class="form-group">
                                    <label for="description">Descripción:</label>
                                    <ckeditor :editor="ckeditor.editor" id="description" data-toggle="tooltip"
                                              title="Indique la descripción de la categoría de la hoja de tiempo"
                                              :config="ckeditor.editorConfig" class="form-control"
                                              name="description" tag-name="textarea"
                                              v-model="record.description"></ckeditor>
                                </div>
                                <!-- ./descripción -->
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
							<button type="button" @click="createRecord('payroll/exception-types')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
	                	</div>
	                </div>
	                <div class="modal-body modal-table">
	                	<v-client-table :columns="columns" :data="records" :options="table_options">
							<div slot="description" slot-scope="props">
								<span v-html="props.row.description"></span>
							</div>
	                		<div slot="id" slot-scope="props" class="text-center">
	                			<button @click="initUpdate(props.row.id, $event)"
		                				class="btn btn-warning btn-xs btn-icon btn-action"
		                				title="Modificar registro" data-toggle="tooltip" type="button">
		                			<i class="fa fa-edit"></i>
		                		</button>
		                		<button @click="deleteRecord(props.row.id, 'payroll/exception-types')"
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
	</section>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id:          '',
                    name:        '',
                    description: '',
					sign:        '',
					affect_id:   '',
					value_max:   '',
				},
				exception_types: [],
				exceptionTypes: [],
				signs:   [
                    {"id": "",  "text":  "Seleccione..."},
                    {"id": "+",  "text": "+"},
                    {"id": "-",  "text": "-"}
                ],
				errors:          [],
				records:         [],
				columns:         ['name', 'description', 'id'],
			}
		},
		methods: {
			async initUpdate(id, event) {
				let vm = this;
				vm.errors = [];

				let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
					return rec.id === id;
				})[0])) || vm.reset();

				vm.exceptionTypes = await vm.exception_types.filter(function ($item) {
					return (($item.id !== recordEdit.id) || ('' === $item.id));
				});
				vm.record = await recordEdit;

				event.preventDefault();
			},
			/**
			 * Método que borra todos los datos del formulario
			 *
			 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
			 */
			reset() {
				const vm = this;
				vm.errors = [];
				vm.record = {
					id:          '',
                    name:        '',
                    description: '',
					sign:        '',
					affect_id:   '',
					value_max:   '',
				};
				vm.getPayrollExceptionTypes();
			},
			async getPayrollExceptionTypes() {
				const vm = this;
				await axios.get(`${window.app_url}/payroll/get-exception-types`).then(response => {
					vm.exceptionTypes = vm.exception_types = response.data;
				});
			},
		},
		created() {
			const vm = this;
            vm.table_options.headings = {
                'name':        'Nombre',
                'description': 'Descripción',
                'id':          'Acción'
            };
            vm.table_options.sortable       = ['name', 'description'];
            vm.table_options.filterable     = ['name', 'description'];
            vm.table_options.columnsClasses = {
                'name':        'col-xs-5',
                'description': 'col-xs-5',
                'id':          'col-xs-2'
            };
		},
		mounted () {
			const vm = this;
			$("#add_payroll_exception_type").on('show.bs.modal', function () {
                vm.reset();
            });
		},
	};
</script>
