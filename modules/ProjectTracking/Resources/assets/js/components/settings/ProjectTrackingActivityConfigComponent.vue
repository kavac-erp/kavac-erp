<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Actividades" data-toggle="tooltip"
           @click="addRecord('add_activity', 'projecttracking/activity', $event)">
            <i class="icofont icofont-listing-box ico-3x"></i>
            <span>Actividades</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_activity">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-tags inline-block"></i>
                            Flujo de Actividades
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
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>col-6
                        </div>
                        <div class="row">
                            <div class="col-6 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre de la Actividad:</label>
                                    <input type="text" placeholder="Nombre de la Actividad" data-toggle="tooltip"
                                           title="Indique el Nombre de la Actividad"
                                           class="form-control input-sm" v-model="record.name_activity">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Orden:</label>
                                    <input type="text" placeholder="Orden"
                                        data-toggle="tooltip"
                                        title="Indique el Orden"
                                        class="form-control input-sm" v-model="record.orden" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de Proyecto:</label>
                                    <select2 :options="type_projects"
                                        data-toggle="tooltip"
                                        title="Seleccione un Tipo de Producto"
                                        v-model="record.project_tracking_project_types_id">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
			                    <div class="form-group">
				                 	<label>Tipo de Producto:</label>
			                        <select2 :options="type_products"
					                   	data-toggle="tooltip"
					                	title="Seleccione un Tipo de Producto"
						               v-model="record.project_tracking_type_products_id">
                                    </select2>
			                  </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <input type="text" placeholder="Descripción de la Actividad" data-toggle="tooltip"
                                           title="Indique la descripción de la actividad"
                                           class="form-control input-sm" v-model="record.description">
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
							<button type="button" @click="createRecord('projecttracking/activity')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table text-center">
                           <v-client-table :columns="columns" :data="records" :options="table_options">
                              <div slot="description" slot-scope="props" class="text-center">
                                <div class="mt-3" v-html="props.row.description"></div>
                            </div>
                            <div slot="id" slot-scope="props">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action" v-has-tooltip
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'projecttracking/activity')"
                                        class="btn btn-danger btn-xs btn-icon btn-action" v-has-tooltip
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
                    name_activity:'',
                    description: '',
                    orden: '',
                    project_tracking_type_products_id:'',
                    project_tracking_project_types_id:'',

                },
                errors: [],
                records: [],
                type_products: [],
                type_projects: [],
                columns: ['name_activity', 'description', 'orden', 'id'],
            }
        },
        props: {

        },
        methods: {

            reset() {
                this.record = {
                    id: '',
                    //name: '',
                    name_activity:'',
                    description: '',
                    orden: '',
                    project_tracking_type_products_id:'',
                    project_tracking_project_types_id:'',

                };
            },

            getTypesProducts() {
				const vm = this;
				axios.get(`${window.app_url}/projecttracking/get-type-products`).then(response => {
					vm.type_products = response.data;
				});
			},
            getTypesProjects() {
                const vm = this;
                axios.get(`${window.app_url}/projecttracking/get-type-projects`).then(response => {
                    vm.type_projects = response.data;
                });
            },
        },
        created() {
            const vm = this;
            this.table_options.headings = {
                'name_activity': 'Nombre de la Actividad',
                'description': 'Descripción',
                'orden': 'Orden',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name_activity', 'description', 'orden'];
            this.table_options.filterable = ['name_activity', 'description', 'orden'];
            this.table_options.columnsClasses = {
                'name_activity': 'col-md-3',
                'description': 'col-md-3',
                'orden': 'col-md-3',
                'id': 'col-md-2'
            };
        },
        mounted() {
           	const vm = this;
            vm.getTypesProducts();
            vm.getTypesProjects();

        }
    };
</script>
