<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registros de Parroquias de un Municipio" data-toggle="tooltip"
           @click="addRecord('add_parish', 'parishes', $event)">
            <i class="icofont icofont-map-pins ico-3x"></i>
            <span>Parroquias</span>
        </a>
        <div id="add_parish" class="modal fade text-left" tabindex="-1" role="dialog">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-map-pins inline-block"></i>
                            Parroquias
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>País:</label>
                                    <select2 :options="countries" @input="getEstates"
                                             v-model="data.country_id"></select2>
                                    <input type="hidden" v-model="data.id">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group" v-if="editEstate=='false'">
                                    <label>Estados:</label>
                                    <select2 :options="estates" v-model="data.estate_id" @input="getMunicipalities">
                                    </select2>
                                </div>
                                <div class="form-group" v-if="editEstate == 'true'">
                                    <label>Estado:</label>
                                    <select id="estate" class="form-control" v-model="data.estate_id">
                                        <option :value="ste.id" :selected="ste.id == data.estate_id"
                                            v-for="(ste, index) in estates" :key="index">
                                            {{ ste.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group" v-show="editMunicipalities=='false'">
                                    <label>Municipio:</label>
                                    <select2 :options="municipalities" v-model="data.municipality_id">
                                    </select2>
                                </div>
                                <div class="form-group" v-show="editMunicipalities == 'true'">
                                    <label>Municipio:</label>
                                    <select id="municipality" class="form-control" v-model="data.municipality_id">
                                        <option v-for="(mty, index) in municipalities" :value="mty.id" :key="index">
                                            {{ mty.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Código:</label>
                                    <input class="form-control input-sm" type="text" data-toggle="tooltip"
                                           maxlength="10" placeholder="Código de Parroquia"
                                           title="Indique el código de la Parroquia (requerido)"
                                           v-is-digits v-model="data.code" />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input class="form-control input-sm" type="text" data-toggle="tooltip"
                                           placeholder="Nombre de Parroquia"
                                           title="Indique el nombre de la Parroquia (requerido)"
                                           v-is-text v-model="data.name" />
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
                            <button type="button" @click="createRecord('parishes')"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-server-table :url="'parishes'" :columns="columns" :options="table_options"
                                        ref="tableResults">
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'parishes')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-server-table>
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
                data: {
                    id: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id,
                    name: '',
                    code: ''
                },
                selectedEstateId: '',
                selectedMunicipalityId: '',
                errors: [],
                records: [],
                countries: [],
                estates: ['0'],
                municipalities: ['0'],
                columns: ['municipality.estate.name', 'municipality.name', 'name', 'code', 'id'],
                editEstate: '',
                editMunicipalities: '',
            }
        },
        watch: {
            selectedEstateId(newValue, oldValue) {
                const vm = this;
                if (newValue && newValue!==oldValue) {
                    setTimeout(() => {
                        vm.data.estate_id = vm.selectedEstateId.toString();
                        $("#estate").val(vm.selectedEstateId.toString());
                    }, 1000);
                }
            },
        },
        methods: {
            /**
             * Obtiene los Estados del Pais seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getEstates() {
                const vm = this;
                vm.estates = [
                    {id: '', text: 'Seleccione...'}
                ];
                if (vm.data.country_id) {
                    axios.get(`/get-estates/${vm.data.country_id}`).then(response => {
                        if (response.data) {
                            vm.estates = response.data;
                        }
                    });
                }
            },
            /**
             * Obtiene los Municipios del Estado seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getMunicipalities() {
                const vm = this;
                vm.municipalities = [];
                if (vm.data.estate_id) {
                    axios.get(`/get-municipalities/${vm.data.estate_id}`).then(response => {
                        vm.municipalities = response.data;
                    });
                }
            },
            /**
             * Obtiene los Estados del Pais seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getEstate(country_id) {
                const vm = this;
                vm.estates = [];
                if (country_id) {
                    axios.get(`/get-estates/${vm.data.country_id}`).then(response => {
                        vm.estates = response.data;
                    });
                }
            },
            /**
             * Obtiene los Municipios del Estado seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getMunicipalitie(state_id) {
                const vm = this;
                vm.municipalities = [];
                if (state_id) {
                    axios.get(`/get-municipalities/${vm.data.estate_id}`).then(response => {
                        vm.municipalities = response.data;
                    });
                }
            },
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                const vm = this;
                vm.data = {
                    id: '',
                    country_id: '',
                    estate_id: '',
                    municipality_id: '',
                    name: '',
                    code: ''
                };
                vm.selectedEstateId = '';
                vm.selectedMunicipalityId = '';
                vm.editEstate = 'false';
                vm.editMunicipalities = 'false';
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
                const vm = this;
                vm.errors = [];
                vm.editEstate = 'true';
                vm.editMunicipalities = 'true';
                let recordEdit = JSON.parse(JSON.stringify(vm.$refs.tableResults.data.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();
                vm.data = recordEdit;
                vm.data.country_id = recordEdit.municipality.estate.country.id;
                vm.getEstate(vm.data.country_id);
                vm.data.estate_id = recordEdit.municipality.estate_id;
                vm.getMunicipalitie(vm.data.estate_id);
                vm.selectedEstateId = vm.data.estate_id;
                vm.data.municipality_id = recordEdit.municipality.id;
                vm.selectedMunicipalityId = vm.data.municipality_id;
                setTimeout(() => {
                    let selected = vm.selectedMunicipalityId;
                    vm.data.municipality_id = selected;
                    // document.querySelector('#municipality').value = selected;
                }, 1000);
                event.preventDefault();
            },
            /**
             * Método que permite crear o actualizar un registro
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
             * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
             *                         El valor por defecto es verdadero.
             * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
             *                         El valor por defecto es verdadero.
             */
            createRecord(url, list = true, reset = true) {
                const vm = this;
                url = vm.setUrl(url);

                if (vm.data.id) {
                    vm.updateRecord(url);
                }
                else {
                    vm.loading = true;
                    var fields = {};

                    for (var index in vm.data) {
                        fields[index] = vm.data[index];
                    }
                    axios.post(url, fields).then(response => {
                        if (typeof(response.data.redirect) !== "undefined") {
                            location.href = response.data.redirect;
                        }
                        else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.readRecords(url);
                            }
                            vm.loading = false;
                            vm.showMessage('store');
                        }

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
                }
            },
            /**
             * Método que permite actualizar información
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {string} url Ruta de la acci´on que modificará los datos
             */
            updateRecord(url) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                url = vm.setUrl(url);

                for (var index in vm.data) {
                    fields[index] = vm.data[index];
                }
                axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.data.id}`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.loading = false;
                        vm.showMessage('update');
                    }

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
        },
        created() {
            this.editEstate = 'false';
            this.editMunicipalities = 'false';
            this.table_options.headings = {
                'municipality.estate.name': 'Estado',
                'municipality.name': 'Municipio',
                'name': 'Parroquia',
                'code': 'Código',
                'id': 'Acción'
            };
            this.table_options.sortable = ['municipality.estate.name', 'municipality.name', 'name', 'code'];
            this.table_options.filterable = ['municipality.estate.name', 'municipality.name', 'name', 'code'];
            this.table_options.columnsClasses = {
                'municipality.estate.name': 'col-md-3',
                'municipality.name': 'col-md-3',
                'name': 'col-md-3',
                'code': 'col-md-1',
                'id': 'col-md-2'
            };
        },
        mounted() {
            const vm = this;
            vm.editEstate = 'false';
            vm.editMunicipalities = 'false';
            //await vm.$nextTick();
            $("#add_parish").on('show.bs.modal', function() {
                vm.getCountries();
            });
        }
    };
</script>
