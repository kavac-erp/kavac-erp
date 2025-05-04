<template>
    <section id="AssetStorageFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registros de Depositos" data-toggle="tooltip" v-has-tooltip
           @click="addRecord('add_storage', 'asset/storages', $event)">
            <i class="icofont icofont-bag-alt ico-3x"></i>
            <span>Depósitos</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_storage">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-bag-alt ico-2x"></i>
                            Gestión de depósitos
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
                            <!-- <div class="col-md-4" v-if="multi_institution">
                                <div class="form-group is-required">
                                    <label>Organización que gestiona el almacén:</label>
                                    <select2 id="institutions_id" :options="institutions"
                                             @input="readRecords('warehouse/institution/' + record.institution_id)"
                                             v-model="record.institution_id">
                                    </select2>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Nombre del depósito:</label>
                                    <input type="text" placeholder="Nombre del depósito" data-toggle="tooltip"
                                           title="Indique el nombre del nuevo depósito (requerido)" v-has-tooltip
                                           class="form-control input-sm" v-model="record.name">
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label">Activo</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tolltip" 
                                             title="Indique si el estatus del depósito">
                                            <input type="checkbox" class="custom-control-input" id="active" 
                                                   :value="true" v-model="record.active">
                                            <label class="custom-control-label" for="active"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label">Es Deposito principal?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tolltip" 
                                             title="Indique si el Deposito es el principal">
                                            <input type="checkbox" class="custom-control-input" id="main" 
                                                   :value="true" v-model="record.main">
                                            <label class="custom-control-label" for="main"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>Ubicación del depósito</b>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Pais:</label>
                                    <select2 id="input_country" :options="countries" @input="getEstates"
                                             v-model="record.country_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Estado:</label>
                                    <select2 id="input_estate" :options="estates" @input="getMunicipalities()"
                                             v-model="record.estate_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Municipio:</label>
                                    <select2 :options="municipalities" @input="getParishes()"
                                             v-model="record.municipality_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Parroquia:</label>
                                    <select2 :options="parishes"
                                             v-model="record.parish_id"></select2>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group is-required">
                                    <label>Dirección:</label>
                                    <ckeditor :editor="ckeditor.editor" data-toggle="tooltip"
                                              title="Indique una breve dirección del nuevo depósito (requerido)"
                                              :config="ckeditor.editorConfig" class="form-control" tag-name="textarea"
                                              rows="3" v-model="record.address"></ckeditor>
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
							<button type="button" @click="createRecord('asset/storages')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <hr>
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="name" slot-scope="props">
                                <span>
                                    {{ (props.row.storage)?props.row.storage.name:'' }}
                                </span>
                            </div>
                            <div slot="country" slot-scope="props">
                                <span>
                                    {{ (props.row.storage.parish)?
                                        props.row.storage.parish.municipality.estate.country.name:'' }}
                                </span>
                            </div>
                            <div slot="estate" slot-scope="props">
                                <span>
                                    {{ (props.row.storage.parish)?
                                        props.row.storage.parish.municipality.estate.name:'' }}
                                </span>
                            </div>
                            <div slot="address" slot-scope="props">
                                <span v-html="prepareText(props.row.storage.address)"></span>
                            </div>
                            <div slot="institution" slot-scope="props">
                                <span>
                                    {{ (props.row.institution)?
                                        props.row.institution.acronym:'' }}
                                </span>
                            </div>
                            <div slot="active" slot-scope="props">
                                <span v-if="props.row.storage.active">Activo</span>
                                <span v-else>Inactivo</span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button @click="editRecord(props.index, $event)"
                                            class="btn btn-warning btn-xs btn-icon btn-action"
                                            title="Modificar registro" data-toggle="tooltip" type="button">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteRecord(props.row.id, 'asset/storages')"
                                            class="btn btn-danger btn-xs btn-icon btn-action"
                                            title="Eliminar registro" data-toggle="tooltip"
                                            type="button">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
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
                    id:              '',
                    name:            '',
                    main:            '',
                    address:         '',
                    institution_id:  '',
                    country_id:      '',
                    estate_id:       '',
                    municipality_id: '',
                    parish_id:       '',

                },

                errors:            [],
                records:           [],
                columns:           ['name', 'country', 'estate', 'address', 'institution','active', 'id'],
                institutions:      [],
                countries:         [],
                estates:           [],
                municipalities:    [],
                parishes:          [],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
             */
            reset()
            {
                const vm = this;
                vm.record = {
                    id:              '',
                    name:            '',
                    active:          '',
                    main:            '',
                    address:         '',
                    institution_id:  '',
                    country_id:      '',
                    estate_id:       '',
                    municipality_id: '',
                    parish_id:       '',
                };

                vm.errors = [];
            },
            prepareText(text) {
                return text.substr(3, text.length-4); 

            },

            /**
             * Método que obtiene actualiza la institución que gestiona un almacén
             *
             * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
             */
             storageManage(index)
            {
                const vm = this;
                var field = {};
                field = this.records[index-1];
                var url = '/asset/manage/' + field.id;

                axios.get(url).then(response => {
                    if (typeof(response.data.records) !== "undefined") {
                        vm.records = response.data.records;
                    }
                });
            },

            /**
             * Método que sobreescribe la función establecida en el mixins.js
             * para obtener los municipios del estado seleccionado.
             *
             * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
             */
            async getMunicipalities() {
                const vm = this;
                vm.municipalities = [
                    {id: '', text: 'Seleccione...'}
                ];
                if (vm.record.estate_id) {
                    const url = vm.setUrl(`/get-municipalities/${vm.record.estate_id}`);
                    await axios.get(url).then(response => {
                        vm.municipalities = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
                }
            },

            /**
             * Método que sobreescribe la función establecida en el mixins.js
             * para obtener las parroquias del municipio seleccionado.
             *
             * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
             */
            async getParishes() {
                const vm = this;
                vm.parishes = [
                    {id: '', text: 'Seleccione...'}
                ];
                if (vm.record.municipality_id) {
                    const url = vm.setUrl(`/get-parishes/${vm.record.municipality_id}`);
                    await axios.get(url).then(response => {
                        vm.parishes = response.data;
                    }).catch(error => {
                        console.error(error);
                    });
                }
            },

            /**
             * Método que carga los datos de un registro en el formulario para su edición
             *
             * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
             */
            editRecord(index, event)
            {
                const vm  = this;
                vm.loading = true;
                vm.errors = [];
                var field = vm.records[index - 1];
                vm.record = field.storage;
                vm.record.institution_id = field.institution_id;
                vm.record.country_id = field.storage.parish.municipality.estate.country_id;
                setTimeout(() => {
                    vm.record.estate_id = field.storage.parish.municipality.estate_id;
                }, 1000);
                setTimeout(() => {
                    vm.record.municipality_id = field.storage.parish.municipality_id;
                }, 1500);
                setTimeout(() => { 
                    vm.record.parish_id = field.storage.parish.id;
                }, 2000);
                var elements = {
                    active: vm.record.active,
                    main: field.main,
                };
                event.preventDefault();
                vm.loading = false;
            },
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'name':        'Nombre',
                'country':     'Pais',
                'estate':      'Estado',
                'address':     'Dirección',
                'institution': 'Gestionado por',
                'active':      'Estatus',
                'id':          'Acción'
            };

            vm.table_options.sortable       = [
                'storage.name', 'storage.parish.municipality.estate.country.name',
                'storage.parish.municipality.estate.name', 'storage.address', 'institution.acronym', 'active'
            ];
            vm.table_options.filterable     = [
                'storage.name', 'storage.parish.municipality.estate.country.name',
                'storage.parish.municipality.estate.name', 'storage.address', 'institution.acronym', 'active'
            ];
            vm.table_options.columnsClasses = {
                'name':        'col-xs-1',
                'country':     'col-xs-2',
                'estate':      'col-xs-2',
                'address':     'col-xs-2',
                'institution': 'col-xs-2',
                'active':      'col-xs-2',
                'id':          'col-xs-1'
            };

            vm.getCountries();
            vm.getInstitutions();

        },
        mounted() {
            const vm = this;
            vm.switchHandler('main');
            vm.switchHandler('active');
        }
    }
</script>
