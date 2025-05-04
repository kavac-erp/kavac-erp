<template>
    <section id="assetDepreciationMethodsComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="#" title="Registros del método de depreciación un Bien" data-toggle="tooltip" v-has-tooltip
           @click="addRecord('add_depreciation_method', 'asset/depreciation-methods', $event)">
            <i class="ion ion-arrow-graph-down-right ico-3x"></i>
            <span>Método de depreciación</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_depreciation_method" data-backdrop="static">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="ion ion-arrow-graph-down-right ico-3x"></i>
                            Método de depreciación de un Bien
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
                            <!-- Institución -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="institutions">Organización</label>
                                    <select2 :options="institutions"
                                             v-model="record.institution_id"></select2>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <!-- /Institución -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="date">Fecha de activación</label>
                                    <input type="date" class="form-control input-sm no-restrict" data-toggle="tooltip"
                                           title="Fecha de activación" v-model="record.activation_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de depreciación</label>
                                    <select2 :options="asset_depreciation_types"
                                             v-model="record.depreciation_type_id"></select2>
                                </div>
                            </div>
                            <!-- activa -->

                            <div class="col-md-2">
                                <div class=" form-group">
                                    <label>¿Activo?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                             title="Indique si el método de depreciación está activo">
                                            <input type="checkbox" class="custom-control-input"
                                                   id="depreciationActive" v-model="record.active"
                                                   :value="true">
                                            <label class="custom-control-label" for="depreciationActive"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ./activa -->
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
							<button type="button" @click="createRecord('asset/depreciation-methods')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">

                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action" v-has-tooltip
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.index, 'asset/depreciation-methods')"
                                        class="btn btn-danger btn-xs btn-icon btn-action" v-has-tooltip
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>

                            </div>
                            <div slot="active" slot-scope="props" class="text-center">
                                    <span v-if="props.row.active" class="text-success font-weight-bold">Si</span>
                                    <span v-else class="text-danger font-weight-bold">No</span>
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
                    id:             '',
                    depreciation_type_id:            '',
                    institution_id:  '',
                    active:          false,
                    activation_date: ''
                },
                asset_depreciation_types: [],
                institutions: [],
                errors: [],
                records: [],
                columns: ['activation_date', 'institution.name', 'depreciation_type', 'formula', 'active', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset()
            {
                this.record = {
                    id: '',
                    depreciation_type_id: '',
                    institution_id: '',
                    active: false,
                    activation_date: ''
                };
            },
            getAssetDepreciationTypes() {
			const vm = this;
			axios.get(`${window.app_url}/asset/get-depreciation-types`).then(response => {
				vm.asset_depreciation_types = response.data;
			});
		},
            deleteRecord(index, url) {
                const vm = this;
                var url = (url)?url:this.route_delete;
                var records = vm.records;
                var confirmated = false;
                var index = index - 1;

                bootbox.confirm({
                    title: "¿Eliminar registro?",
                    message: "¿Está seguro de eliminar este registro?",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            confirmated = true;
                            url = vm.setUrl(url);
                            axios.delete(url + '/' + records[index].id).then(response => {
                                if (typeof(response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                records.splice(index, 1);
                                vm.showMessage('destroy');
                            }).catch(error => {
                                 if (typeof(error.response) !="undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                }
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                        }
                    }
                });

                if (confirmated) {
                    this.records = records;
                    this.showMessage('destroy');
                }
            },
        },
        created() {
            this.table_options.headings = {
                'activation_date': 'Fecha de activación',
                'institution.name': 'Organización',
                'depreciation_type': 'Tipo de depreciación',
                'formula': 'Fórmula',
                'active': 'Activo',
                'id': 'Acción'
            };
            this.table_options.sortable = ['activation_date', 'institution.name', 'depreciation_type', 'formula', 'active'];
            this.table_options.filterable = ['activation_date', 'institution.name', 'depreciation_type', 'formula', 'active'];
            this.table_options.columnsClasses = {
                'activation_date': 'col-xs-2',
                'institution.name': 'col-xs-2',
                'depreciation_type': 'col-xs-2',
                'formula': 'col-xs-2',
                'active': 'col-xs-2',
                'id': 'col-xs-2'
            };
        },
        mounted() {
            const vm = this;
            $("#add_depreciation_method").on('show.bs.modal', function() {
                vm.getInstitutions();
                vm.getAssetDepreciationTypes();
            });
        },
    };
</script>