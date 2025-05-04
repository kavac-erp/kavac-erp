<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="javascript:void(0)" title="Registro de municipios"
           data-toggle="tooltip" @click="addRecord('add_municipality', 'municipalities', $event)">
            <i class="icofont icofont-ui-map ico-3x"></i>
            <span>Municipios</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_municipality">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-ui-map inline-block"></i>
                            Municipios
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Pais:</label>
                                    <select2 :options="countries" @input="getEstates"
                                             v-model="record.country_id"></select2>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group" v-if="editMunicipalities=='false'">
                                    <label>Estados:</label>
                                    <select2 :options="estates" v-model="record.estate_id"></select2>
                                </div>
                                <div class="form-group" v-if="editMunicipalities == 'true'">
                                    <label>Estados:</label>
                                    <select id="estate" v-model="record.estate_id">
                                        <option :value="ste.id" :selected="ste.id == record.estate_id" 
                                                v-for="(ste, index) in estates" :key="index">
                                                {{ ste.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Código:</label>
                                    <input type="text" placeholder="Código de Municipio" data-toggle="tooltip"
                                           title="Indique el código del Municipio (requerido)"
                                           class="form-control input-sm" v-model="record.code" v-is-digits>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input type="text" placeholder="Nombre de Municipio" data-toggle="tooltip"
                                           title="Indique el nombre del Municipio (requerido)"
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
                            <button type="button" @click="createRecords('municipalities')" 
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
                                <button @click="deleteRecord(props.row.id, 'municipalities')"
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
                    country_id: '',
                    estate_id: '',
                    name: '',
                    code: ''
                },
                selectedEstateId: '',
                errors: [],
                records: [],
                countries: [],
                estates: [],
                columns: ['estate.name', 'name', 'code', 'id'],
                editMunicipalities: '', 
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
            selectedEstateId(newValue, oldValue) {
                const vm = this;
                if (newValue && newValue!==oldValue) {
                    setTimeout(() => {
                        vm.record.estate_id = vm.selectedEstateId.toString();
                        $("#estate").val(vm.selectedEstateId.toString());
                    }, 1000);
                }
            }
        },
        methods: {
            /**
             * Obtiene los Estados del Pais seleccionado
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getEstate(country_id) {
                const vm = this;
                vm.estates = [];
                if (country_id) {
                    axios.get(`/get-estates/${vm.record.country_id}`).then(response => {
                        vm.estates = response.data;
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
                vm.record = {
                    id: '',
                    country_id: '',
                    estate_id: '',
                    name: '',
                    code: ''
                };
                vm.selectedEstateId = '';
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
                vm.editMunicipalities = 'true';
                vm.errors = [];
                let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();
                vm.record = recordEdit;
                vm.record.country_id = recordEdit.estate.country_id;
                vm.getEstate(vm.record.country_id);
                vm.selectedEstateId = recordEdit.estate.id;
                vm.record.estate_id = vm.selectedEstateId;
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
            createRecords(url, list = true, reset = true) {
                const vm = this;
                url = vm.setUrl(url);
                
                if (vm.record.id) {
                    vm.updateRecord(url);
                }
                else {
                    vm.loading = true;
                    var fields = {};
                    for (var index in vm.record) {
                        fields[index] = vm.record[index];
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
        },
        created() {
            this.editMunicipalities = 'false';
            this.table_options.headings = {
                'estate.name': 'Estado',
                'name': 'Municipio',
                'code': 'Código',
                'id': 'Acción'
            };
            this.table_options.sortable = ['estate.name', 'name', 'code'];
            this.table_options.filterable = ['estate.name', 'name', 'code'];
            this.table_options.columnsClasses = {
                'estate.name': 'col-md-3',
                'name': 'col-md-6',
                'code': 'col-md-1',
                'id': 'col-md-2'
            };
        },
        mounted() {
            const vm = this;
            vm.editMunicipalities = 'false';
            $("#add_municipality").on('show.bs.modal', function() {
                vm.getCountries();
            });
            $("#estate").on('change', function() {
                vm.record.estate_id = $(this).val();
            });
        }
    };
</script>
