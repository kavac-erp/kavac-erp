<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Tipos de Productos" data-toggle="tooltip"
           @click="addRecord('add_product', 'projecttracking/type-products', $event)">
            <i class="icofont icofont-tags ico-3x"></i>
            <span>Tipos de Productos</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_product">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-tags inline-block"></i>
                            Tipos de Productos
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
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input type="text" placeholder="Nombre" data-toggle="tooltip"
                                           title="Indique el nombre del tipo de producto (requerido)"
                                           class="form-control input-sm" v-model="record.name">
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label for="description">Descripción:</label>
                                    <input type="text" id="description" placeholder="Descripción"
                                           class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
                                           title="Indique la descripción del cargo">
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
							<button type="button" @click="createRecord('projecttracking/type-products')"
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
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action" v-has-tooltip
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'projecttracking/type-products')"
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
                    name: '',
                    description: '',

                },
                errors: [],
                records: [],
                accounting_accounts: [],
                columns: ['name', 'description','id'],
            }
        },
        props: {

        },
        methods: {

            reset() {
                this.record = {
                    id: '',
                    name: '',
                    description: '',
                };
            },


        },
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'description': 'Descripción',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name', 'description'];
            this.table_options.filterable = ['name', 'description'];
            this.table_options.columnsClasses = {
                'name': 'col-md-3',
                'description': 'col-md-7',
                'id': 'col-md-2'
            };
        },
        mounted() {


        }
    };
</script>
