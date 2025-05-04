<template>
    <section id="warehouseProductsFormComponent">
        <!-- <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="#" title="Registros de insumos almacenables" data-toggle="tooltip" v-has-tooltip>
        </a> -->
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_product">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-cubes ico-2x"></i>
                            Registros de insumos almacenables
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click.prevent="errors = []">
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
                            <div class="col-md-12">
                                <b>Datos del insumo</b>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre del insumo:</label>
                                    <input type="text" placeholder="Nombre del insumo" data-toggle="tooltip" v-has-tooltip title="Indique el nombre del nuevo insumo (requerido)" class="form-control input-sm" v-model="record.name">
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required" data-toggle="tooltip" v-has-tooltip title="Indique una breve descripción del nuevo insumo (requerido)">
                                    <label>Descripción:</label>
                                    <ckeditor :editor="ckeditor.editor" :config="ckeditor.editorConfig" class="form-control" tag-name="textarea" rows="3" v-model="record.description"></ckeditor>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Unidad de medida:</label>
                                    <select2 :options="measurement_units" v-model="record.measurement_unit_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Impuesto:</label>
                                    <select2 :options="taxes" v-model="record.history_tax_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label">Atributos personalizados</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip" 
                                             title="Establecer los atributos del insumo para gestionar las variantes">
                                            <input type="checkbox" class="custom-control-input" id="define_attributes" 
                                                   :value="true" v-model="record.define_attributes">
                                            <label class="custom-control-label" for="define_attributes"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-show="this.record.define_attributes">
                            <div class="row" style="margin: 10px 0">
                                <h6 class="card-title cursor-pointer" @click="addAttribute()">
                                    Gestionar nuevo atributo <i class="fa fa-plus-circle"></i>
                                </h6>
                            </div>
                            <div class="row" style="margin: 20px 0">
                                <div class="col-6" v-for="(attribute, index) in record.warehouse_product_attributes" :key="index">
                                    <div class="d-inline-flex">
                                        <div class="col-10">
                                            <div class="form-group">
                                                <input type="text" placeholder="Nombre del nuevo atributo" data-toggle="tooltip" v-has-tooltip title="Indique el nombre del atributo del insumo que desee hacer seguimiento (opcional)" v-model="attribute.name" class="form-control input-sm">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <button class="btn btn-sm btn-danger btn-action" type="button" @click="removeRow(index, record.warehouse_product_attributes)" data-toggle="tooltip" v-has-tooltip title="Eliminar este dato">
                                                    <i class="fa fa-minus-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <modal-form-buttons saveRoute="warehouse/products"></modal-form-buttons>
                        </div>
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
                id: '',
                name: '',
                description: '',
                define_attributes: false,
                measurement_unit_id: '',
                history_tax_id: '',
                warehouse_product_attributes: [],
            },

            errors: [],
            records: [],
            columns: ['name', 'description', 'attributes', 'id'],
            measurement_units: [],
            taxes: [],
            formImport: false,

        }
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                name: '',
                description: '',
                define_attributes: false,
                measurement_unit_id: '',
                history_tax_id: '',
                warehouse_product_attributes: []
            };
        },
        /**
         * Método que agrega un nuevo campo de atributo al formulario
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        addAttribute() {
            var field = { id: '', name: '', warehouse_product_id: '' };
            this.record.warehouse_product_attributes.push(field);
        },
        /**
         * Método que obtiene las unidades de medida del insumo
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        getMeasurementUnits() {
            const vm = this;
            vm.measurement_units = [];

            axios.get('/warehouse/get-measurement-units').then(response => {
                vm.measurement_units = response.data;
            });
        },
        /**
         * Reescribe elñ metodo para modificar su comportamiento por defecto
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

            vm.$parent.products.forEach(prod => {
                if (vm.record.name.toUpperCase() == prod.text.toUpperCase()) {
                    vm.record.id = prod.id;
                };
            });

            if (vm.record.id) {
                vm.updateRecord(url);
            } else {
                vm.loading = true;
                var fields = {};
                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                axios.post(url, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    } else {
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
                    vm.$parent.getWarehouseProducts();

                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) != "undefined") {
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
         * Reescribe elñ metodo para modificar su comportamiento por defecto
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

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
                if (typeof(response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                } else {
                    vm.readRecords(url);
                    vm.reset();
                    vm.loading = false;
                    vm.showMessage('update');
                    vm.$parent.getWarehouseProducts();
                }

            }).catch(error => {
                vm.errors = [];

                if (typeof(error.response) != "undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
                vm.loading = false;
            });
        },

        /**
         * Listado de impuestos
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getTaxes() {
            const vm = this;
            vm.taxes = [{ id: "", text: "Seleccione..." }];
            axios
                .get(`${window.app_url}/get-taxes`)
                .then((response) => {
                    if (response.data.records.length > 0) {
                        vm.taxesData = response.data.records;
                        for (let tax of vm.taxesData) {
                            vm.taxes.push({
                                id: tax.histories[tax.histories.length - 1].id,
                                text: tax.name + " " + tax.histories[tax.histories.length - 1].percentage + "%",
                            });
                        }
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },
    },
    created() {
        const vm = this;
        vm.table_options.headings = {
            'name': 'insumo',
            'description': 'Descripción',
            'attributes': 'Atributos',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['name', 'description'];
        vm.table_options.filterable = ['name', 'description'];
        vm.table_options.columnsClasses = {
            'name': 'col-xs-2',
            'description': 'col-xs-4',
            'attributes': 'col-xs-4',
            'id': 'col-xs-2'
        };
    },
    mounted() {
        const vm = this;
        vm.getMeasurementUnits();
        vm.getTaxes();
        vm.switchHandler('define_attributes');
    }
};
</script>
