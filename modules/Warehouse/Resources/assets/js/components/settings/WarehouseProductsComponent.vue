<template>
    <section id="warehouseProductsFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
           href="#" title="Registros de insumos almacenables" data-toggle="tooltip" v-has-tooltip
           @click="addRecord('add_product', 'warehouse/products', $event)">
            <i class="icofont icofont-cubes ico-3x"></i>
            <span>Insumos</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_product">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-toggle="tooltip" v-has-tooltip
                                class="btn btn-primary btn-xs btn-icon btn-action"
                                style="margin-right: 3.5rem; margin-top: -.1rem;"
                                title="Presione para subir los registros mediante hoja de cálculo."
                                @click="setFile('importFile')">
                            <i class="fa fa-upload"></i>
                        </button>
                        <input  id="importFile" name="importFile"
                                type="file" style="display:none;"
                                @change="importData()">

                        <button type="button" data-toggle="tooltip" v-has-tooltip
                                class="btn btn-primary btn-xs btn-icon btn-action"
                                style="margin-right: 1.5rem; margin-top: -.1rem;"
                                title="Presione para descargar el documento con la información de los registros."
                                @click="exportData()">
                            <i class="fa fa-download"></i>
                        </button>
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
                            <div class="col-md-12">
                                <b>Datos del insumo</b>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre del insumo:</label>
                                    <input type="text" placeholder="Nombre del insumo" data-toggle="tooltip"
                                           v-has-tooltip title="Indique el nombre del nuevo insumo (requerido)"
                                           class="form-control input-sm" v-model="record.name">
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group is-required" data-toggle="tooltip" v-has-tooltip
                                     title="Indique una breve descripción del nuevo insumo (requerido)">
                                    <label>Descripción:</label>
                                    <ckeditor :editor="ckeditor.editor"
                                              :config="ckeditor.editorConfig" class="form-control" tag-name="textarea"
                                              rows="3" v-model="record.description"></ckeditor>
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
                            <!-- cuenta contables -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cuenta Contables</label>
                                    <select2 :options="budget_accounts"
                                             v-model="record.accounting_account_id"></select2>
                                </div>
                            </div>
                            <!-- ./cuenta contables -->
                            <!-- impuesto -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Impuesto:</label>
                                    <select2 :options="taxes" v-model="record.history_tax_id"></select2>
                                </div>
                            </div>
                            <!-- ./impuesto -->
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
                                <h6 class="card-title cursor-pointer" @click="addAttribute()" >
                                    Gestionar nuevo atributo <i class="fa fa-plus-circle"></i>
                                </h6>
                            </div>
                            <div class="row" style="margin: 20px 0">

                                <div class="col-6" v-for="(attribute, index) in record.warehouse_product_attributes"
                                     :key="index">

                                    <div class="d-inline-flex">
                                        <div class="col-10">
                                            <div class="form-group">
                                                <input type="text" placeholder="Nombre del nuevo atributo"
                                                       data-toggle="tooltip" v-has-tooltip
                                                       title="Indique el nombre del atributo del insumo que desee hacer seguimiento (opcional)"
                                                       v-model="attribute.name" class="form-control input-sm">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <button class="btn btn-sm btn-danger btn-action" type="button"
                                                        @click="removeRow(index, record.warehouse_product_attributes)"
                                                        data-toggle="tooltip" v-has-tooltip title="Eliminar este dato">
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
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
									@click="clearFilters" data-dismiss="modal">
								Cerrar
							</button>
							<button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
									@click="reset()">
								Cancelar
							</button>
							<button type="button" @click="createRecord('warehouse/products')"
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <hr>
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="attributes" slot-scope="props">
                                <div v-if="props.row.define_attributes">
                                    <div v-for="(att, index) in props.row.warehouse_product_attributes" :key="index">
                                        <span>
                                            {{ att.name }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div slot="description" slot-scope="props">
                                <span v-html="prepareText(props.row.description)"></span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="d-inline-flex">
                                    <button @click="initUpdate(props.row.id, $event)"
                                            class="btn btn-warning btn-xs btn-icon btn-action"
                                            title="Modificar registro" data-toggle="tooltip" type="button">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteRecord(props.row.id, 'warehouse/products')"
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
                    id:'',
                    name: '',
                    description: '',
                    define_attributes: false,
                    measurement_unit_id: '',
                    accounting_account_id: '',
                    history_tax_id: '',
                    warehouse_product_attributes: [],
                },

                errors: [],
                records: [],
                columns: ['name', 'description', 'attributes', 'id'],
                measurement_units: [],
                budget_accounts: [],
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
            reset()
            {
                const vm    = this;
                this.record = {
                    id:                           '',
                    name:                         '',
                    description:                  '',
                    define_attributes:            false,
                    measurement_unit_id:          '',
                    accounting_account_id:            '',
                    history_tax_id:                       '',
                    warehouse_product_attributes: []
                };
            },
            /**
             * Método que agrega un nuevo campo de atributo al formulario
             *
             * @author Henry Paredes <hparedes@cenditel.gob.ve>
             */
            addAttribute()
            {
                var field = {id: '', name: '', warehouse_product_id: ''};
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
            prepareText(text) {
                return text.replace(/(<([^>]+)>)/gi, "");
            },
            exportData() {
                //instrucciones para exportar registros
                location.href = `${window.app_url}/warehouse/products/export/all`;
            },
            importData() {
                //instrucciones para exportar registros
                const vm = this;
                var url = '/warehouse/products/import/all' ;
                var formData = new FormData();
                var importFile = document.querySelector('#importFile');
                formData.append("file", importFile.files[0]);
                vm.loading = true;
                axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    console.log('exit');
                    vm.loading = false;
                    vm.showMessage('store');
                }).catch(error => {
                    console.log('failure');
                    vm.loading = false;

                });
            },
            /**
             * Obtiene un listado de cuentas presupuestarias
             *
             * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            getBudgetAccounts() {
                const vm = this;
                vm.budget_accounts = [];
                axios.get(`${window.app_url}/accounting/accounts`).then(response => {
                    if (response.data.records.length > 0) {
                        vm.budget_accounts.push({
                            id:   '',
                            text: 'Seleccione...'
                        });
                        $.each(response.data.records, function() {
                            vm.budget_accounts.push({
                                id:   this.id,
                                text: `${this.code} - ${this.denomination}`,
                                disabled: `${this.code}`.split('.')[6] == '000' ? true : false
                            });
                        });
                    }
                }).catch(error => {
                    vm.logs('WarehouseProductsComponent', 258, error, 'getAccountingAccounts');
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
                'name':        'insumo',
                'description': 'Descripción',
                'attributes':  'Atributos',
                'id':          'Acción'
            };
            vm.table_options.sortable       = ['name', 'description'];
            vm.table_options.filterable     = ['name', 'description'];
            vm.table_options.columnsClasses = {
                'name':        'col-xs-2',
                'description': 'col-xs-4',
                'attributes':  'col-xs-4',
                'id':          'col-xs-2'
            };
        },
        mounted() {
            const vm = this;
            $("#add_product").on('show.bs.modal', function() {
                vm.getMeasurementUnits();
                vm.getBudgetAccounts();
                vm.getTaxes();
                vm.switchHandler('define_attributes');
            });
        }
    };
</script>
