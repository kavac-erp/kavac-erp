<template>
    <div class="form-horizontal">
        <div class="card-body">
            <purchase-show-errors ref="purchaseShowError" />
            <div class="row">
                <div class="col-3" v-if="record.code" id="HelpCode">
                    <div class="form-group">
                        <label class="control-label">
                            Código del requerimiento
                        </label>
                        <br>
                        <label class="control-label">
                            <h5>{{ record.code }}</h5>
                        </label>
                    </div>
                </div>
                <div class="col-6" id="helpDate">
                    <div class="form-group is-required">
                        <label class="control-label">
                            Fecha de generación
                        </label><br>
                        <input
                            type="date"
                            class="form-control fiscal-year-restrict"
                            v-model="record.date"
                        >
                    </div>
                </div>
                <div class="col-6" id="helpInstitutions">
                    <div class="form-group is-required">
                        <label
                            class="control-label"
                            for="institutions"
                        >
                            Institución
                        </label>
                        <br>
                        <select2
                            :options="institutions"
                            id="institutions"
                            v-model="record.institution_id"
                            v-has-tooltip
                            @input="getDepartmentsAndFiscalYear()"
                        ></select2>
                    </div>
                </div>
                <div class="col-6" id="helpTypeRequirement">
                    <div class="form-group is-required">
                        <label
                            class="control-label"
                            for="typeRequirements"
                        >
                            Tipo de requerimiento
                        </label>
                        <br>
                        <select2
                            :options="requirement_types"
                            id="typeRequirements"
                            v-model="record.requirement_type"
                        ></select2>
                    </div>
                </div>
                <div class="col-6" id="helpDepartments1">
                    <div class="form-group is-required">
                        <label
                            class="control-label"
                            for="departments1"
                        >
                            Unidad contratante
                        </label>
                        <br>
                        <select2
                            :options="(requirement_edit) ?
                                department_list : departments"
                            id="departments1"
                            v-model="record.contracting_department_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-6" id="helpDepartments2">
                    <div class="form-group is-required">
                        <label
                            class="control-label"
                            for="departments2"
                        >
                            Unidad usuario
                        </label>
                        <br>
                        <select2
                            :options="(requirement_edit) ?
                                department_list : departments"
                            id="departments2"
                            v-model="record.user_department_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-6" id="helpPurchaseSupplierObject">
                    <div class="form-group is-required">
                        <label for="purchase_supplier_objects">Tipo</label>
                        <select2
                            :options="purchase_supplier_objects"
                            id="purchase_supplier_objects"
                            v-model='record.purchase_supplier_object_id'
                        ></select2>
                    </div>
                </div>
                <div class="col-6" id="helpDescription">
                    <div class="form-group is-required">
                        <label for="description">Descripción</label>
                        <input
                            type="text"
                            id="description"
                            v-model="record.description"
                            title="Descripción del requerimiento"
                            data-toggle="tooltip"
                            v-has-tooltip class="form-control"
                        >
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-6" id="helpProduct" v-if="record.institution_id">
                    <div class="form-group is-required">
                        <label>
                            Producto
                        </label>
                        <v-multiselect
                            :options="products"
                            track_by="text"
                            :hide_selected="false"
                            v-model="product"
                            :multiple="false"
                            :options_limit="Infinity"
                            :search_change="
                                (query) => applyFunctionDebounce(
                                    query, searchProducts
                                )
                            "
                            :internal_search="false"
                            :searchable="true"
                            style="margin-top: -25px;"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-6" id="helpProduct" v-if="record.institution_id">
                    <div class="form-group is-required">
                        <label>Impuesto</label>
                        <select2
                            :options="taxes"
                            v-model="product_history_tax_id"
                            data-toggle="tooltip"
                            title="Seleccione un registro de la lista"
                        >
                        </select2>
                    </div>
                </div>
                <div class="col-6" id="helpProduct" v-if="record.institution_id">
                    <div class="form-group is-required">
                        <label>
                            Unidad de medida
                        </label>
                        <select2
                            :options="measurement_units"
                            v-model="product_measurement_unit_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-6 text-right" v-if="record.institution_id">
                    <div class="d-inline-flex mt-4">
                        <button
                            type="button"
                            @click="addProduct($event)"
                            class="btn btn-sm btn-primary btn-custom"
                            title="Agregar producto a la lista"
                            data-toggle="tooltip"
                        >
                            <i class="fa fa-plus-circle"></i>
                            Agregar
                        </button>
                    </div>
                </div>
            </div>
            <v-client-table
                class="mt-3"
                :columns="columns"
                :data="record_products"
                :options="table_options"
                v-if="record.institution_id"
                id="helpProductList"
            >
                <div
                    slot="measurement_unit"
                    slot-scope="props"
                    class="text-center"
                >
                    <p>
                        {{
                            props.row.measurement_unit &&
                            props.row.measurement_unit.name ?
                            props.row.measurement_unit.name :
                            props.row.measurement_unit ?
                            props.row.measurement_unit :
                            'N/A'
                        }}
                    </p>
                </div>
                <div
                    slot="technical_specifications"
                    slot-scope="props"
                    class="text-center"
                >
                    <span>
                        <input
                            type="text"
                            :id="props.index"
                            v-model="props.row.technical_specifications"
                            class="form-control"
                            @input="changeTecnicalSpecifications"
                        >
                    </span>
                </div>
                <div slot="quantity" slot-scope="props">
                    <span>
                        <input
                            type="text"
                            :id="props.index"
                            v-model="props.row.quantity"
                            class="form-control"
                            min="1"
                            @input="changeQty"
                            oninput="
                                this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');
                            "
                        >
                    </span>
                </div>
                <div slot="id" slot-scope="props" class="text-center">
                    <div class="d-inline-flex">
                        <button
                            @click="editProduct(props.index, $event)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            type="button">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            @click="removeProduct(props.index, $event)"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                            type="button"
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                </div>
            </v-client-table>
            <!-- Firmas autorizadas -->
            <div
                class="col-12 row"
                v-show="record.institution_id"
                id="helpAuthorizedSignatures"
            >
                <div class="col-12">
                    <br>
                    <h6 class="card-title">Firmas autorizadas</h6>
                </div>
                <div class="col-3" id="helpPreparedBy">
                    <div class="form-group is-required">
                        <label
                            class="control-label"
                            for="prepared_by_id"
                        >
                            Preparado por
                        </label>
                        <br>
                        <select2
                            :options="employments"
                            id="prepared_by_id"
                            v-model="record.prepared_by_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-3" id="helpReviewedBy">
                    <div class="form-group">
                        <label
                            class="control-label"
                            for="reviewed_by_id"
                        >
                            Revisado por
                        </label>
                        <br>
                        <select2
                            :options="employments"
                            id="reviewed_by_id"
                            v-model="record.reviewed_by_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-3" id="helpVerifiedBy">
                    <div class="form-group">
                        <label
                            class="control-label"
                            for="verified_by_id"
                        >
                            Verificado por
                        </label>
                        <br>
                        <select2
                            :options="employments"
                            id="verified_by_id"
                            v-model="record.verified_by_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-3" id="helpFirstSignature">
                    <div class="form-group">
                        <label
                            class="control-label"
                            for="first_signature_id"
                        >
                            Firmado por
                        </label>
                        <br>
                        <select2
                            :options="employments"
                            id="first_signature_id"
                            v-model="record.first_signature_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-3" id="helpSecondSignature">
                    <div class="form-group">
                        <label
                            class="control-label"
                            for="second_signature_id"
                        >
                            Firmado por
                        </label>
                        <br>
                        <select2
                            :options="employments"
                            id="second_signature_id"
                            v-model="record.second_signature_id"
                        ></select2>
                    </div>
                </div>
            </div>
            <!-- ./Firmas autorizadas -->
        </div>
        <div class="card-footer text-right">
            <button
                type="button"
                @click="reset()"
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                title="Borrar datos del formulario"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                @click="redirect_back(url_list)"
                class="btn btn-warning btn-icon btn-round btn-modal-close"
                data-dismiss="modal"
                title="Cancelar y regresar"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createRecord('/purchase/requirements')"
                class="btn btn-success btn-icon btn-round btn-modal-save"
                title="Guardar registro"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        institutions: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
        purchase_supplier_objects: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
        measurement_units: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
        requirement_edit: {
            type: Object,
            default: function() {
                return null
            }
        },
        department_list: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }

        },
        employments: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
    },
    data() {
        return {
            url_list: `${window.app_url}/purchase/requirements`,
            record: {
                code:'',
                date:'',
                institution_id: '',
                contracting_department_id: '',
                user_department_id: '',
                warehouse_id: '',
                purchase_supplier_object_id: '',
                description: '',
                fiscal_year_id: '',
                year: '',
                products: [],
                // variables para firmas
                prepared_by_id: '',
                reviewed_by_id: '',
                verified_by_id: '',
                first_signature_id: '',
                second_signature_id: '',
                requirement_type: '',
            },
            requirement_types: [
                { id: '', text: 'Seleccione...' },
                { id: 'Producto', text: 'Producto' },
                { id: 'Bien', text: 'Bien' },
                { id: 'Servicio', text: 'Servicio' },
            ],
            fiscalYear: null,
            product_requirement_id: '',
            product: {},
            products: [],
            compare_contracting_department_id: '',
            product_history_tax_id: '',
            product_measurement_unit_id: '',
            departments: [],
            record_products: [],
            toDelete: [],
            taxes: [],
            editIndex: false,
            columns: [
                'name',
                'measurement_unit',
                'technical_specifications',
                'quantity',
                'id'
            ],
        }
    },
    created() {
        this.table_options.headings = {
            'name': 'Producto',
            'measurement_unit': 'Unidad de Medida',
            'technical_specifications': 'Especificaciones técnicas',
            'quantity': 'Cantidad',
            'id': 'ACCIÓN'
        };
        this.table_options.columnsClasses = {
            'name': 'col-xs-4',
            'measurement_unit': 'col-xs-1',
            'technical_specifications': 'col-xs-4',
            'quantity': 'col-xs-2',
            'id': 'col-xs-1'
        };
        if (this.requirement_edit) {
            this.departments = this.department_list;
        }
    },
    mounted() {
        const vm = this;

        if (vm.requirement_edit) {
            // asignara la institucion por medio del usuario
            // vm.record.code = vm.requirement_edit.code;
            vm.record.date = vm.requirement_edit.date;
            vm.record.description = vm.requirement_edit.description;
            vm.record.institution_id = vm.requirement_edit.institution_id;
            vm.record.contracting_department_id = vm.requirement_edit.contracting_department_id;
            vm.record.user_department_id = vm.requirement_edit.user_department_id;
            vm.record.purchase_supplier_object_id = vm.requirement_edit.purchase_supplier_object_id;
            vm.record.fiscal_year_id = vm.requirement_edit.fiscal_year_id;
            vm.record_products = vm.requirement_edit.purchase_requirement_items;
            vm.record.prepared_by_id = vm.requirement_edit.prepared_by_id;
            vm.record.reviewed_by_id = vm.requirement_edit.reviewed_by_id;
            vm.record.verified_by_id = vm.requirement_edit.verified_by_id;
            vm.record.first_signature_id = vm.requirement_edit.first_signature_id;
            vm.record.second_signature_id = vm.requirement_edit.second_signature_id;
            vm.record.requirement_type = vm.requirement_edit.requirement_type;
            vm.getDepartments();
        }
        vm.getFiscalYear();
    },
    methods: {
        reset() {
            const vm = this;
            vm.record = {
                institution_id: '',
                contracting_department_id: '',
                user_department_id: '',
                warehouse_id: '',
                purchase_supplier_object_id: '',
                description: '',
                products: [],
                prepared_by_id: '',
                reviewed_by_id: '',
                verified_by_id: '',
                first_signature_id: '',
                second_signature_id: '',
                requirement_type: '',
            };
            vm.errors = [];
            product_requirement_id = '',
            vm.editIndex = false;
            vm.$refs.purchaseShowError.reset();
        },
        createRecord() {
            const vm = this;
            vm.record.products = vm.record_products;
            vm.loading = true;

            var url = vm.setUrl('/purchase/requirements/');

            if (vm.requirement_edit) {
                vm.record.toDelete = vm.toDelete;
                axios.put(url + vm.requirement_edit.id, vm.record).then(response => {
                    vm.loading = false;
                    vm.showMessage('update');
                    setTimeout(function() {
                        //location.href = url;
                        location.href = `${window.app_url}/purchase/requirements`;
                    }, 2000);
                }).catch(error => {
                    vm.errors = [];
                    if (typeof(error.response) != 'undefined') {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }

                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.$refs.purchaseShowError.refresh();
                    vm.loading = false;
                });
            } else {
                axios.post(url, vm.record).then(response => {
                    vm.loading = false;
                    vm.showMessage('store');
                    setTimeout(function() {
                        location.href = `${window.app_url}/purchase/requirements`;

                    }, 2000);
                }).catch(error => {
                    vm.errors = [];
                    if (typeof(error.response) != 'undefined') {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }

                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.$refs.purchaseShowError.refresh();
                    vm.loading = false;
                });
            }
        },

        getDepartmentsAndFiscalYear(){
            this.getExecutionYear();
            this.getDepartments();
        },

        getExecutionYear(){
            const vm = this;
            axios
            .get(`${window.app_url}/get-execution-year/${vm.record.institution_id}`)
            .then(response => {
                vm.getFiscalYear();
            });
        },

        getDepartments() {
            const vm = this;
            vm.departments = [];

            if (vm.record.institution_id != '') {
                axios.get(`${window.app_url}/get-departments/${vm.record.institution_id}`)
                .then(response => {
                    vm.departments = response.data;
                    vm.getTaxes();
                });
            }
        },

        getFiscalYear() {
            const vm = this;
            axios.get('/purchase/get-fiscal-year').then(response => {
                if (response.data.fiscal_year) {
                    vm.fiscalYear = response.data.fiscal_year;
                    vm.record.fiscal_year_id = vm.fiscalYear.id;
                }
            });
        },

        removeProduct(index, event) {
            var v = this.record_products.splice(index - 1, 1)[0];
            if (v['updated_at']) {
                this.toDelete.push(v['id']);
            }
        },

        changeQty({ type, target }) {
            this.record_products[target.id - 1].quantity = target.value;
        },

        changeTecnicalSpecifications({ type, target }) {
            this.record_products[target.id - 1].technical_specifications = target.value;
        },

        /**
         * Método que realiza una consulta para obtener todos los receptores que coincidan
         * con el query de la búsqueda
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        searchProducts (query) {
            const vm = this;
            vm.products = [];

            axios.get(`${window.app_url}/purchase/get-products`, {params: {query:query}}).then(response => {
                vm.products = response.data;
            });
        },

        /**
         * Listado de impuestos
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getTaxes() {
            const vm = this;
            axios.get(`${window.app_url}/get-taxes`).then((response) => {
                if (response.data.records.length > 0) {
                    vm.taxesData = response.data.records;
                    vm.taxes = [{ id: "", text: "Seleccione..." }];
                    for (let tax of vm.taxesData) {
                        vm.taxes.push({
                            id: tax.histories[tax.histories.length - 1].id,
                            text: tax.name + " " + tax.histories[tax.histories.length - 1].percentage + "%",
                        });
                    }
                }
            }).catch((error) => {
                console.error(error);
            });
        },

        /**
         * Agrega el producto
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        addProduct(){
            const vm = this;
            if (vm.product.id) {
                let measurement_unit_name = '';

                for (let unit of vm.measurement_units) {
                    if (unit.id == vm.product_measurement_unit_id
                        && vm.product_measurement_unit_id != '') {
                        measurement_unit_name = unit.text;
                        break;
                    }
                }

                if(vm.editIndex === false){
                    vm.record_products.push({
                        id: vm.product.id,
                        name: vm.product.text,
                        quantity: 0,
                        technical_specifications: '',
                        measurement_unit: measurement_unit_name
                            != '' ? measurement_unit_name : 'N/A',
                        measurement_unit_id: vm.product_measurement_unit_id,
                        history_tax_id: vm.product_history_tax_id,
                    });
                } else if (vm.editIndex >= 0) {
                    vm.record_products.splice(vm.editIndex, 1);
                    vm.record_products.push({
                        id: vm.product.id,
                        name: vm.product.text,
                        quantity: !0 ? vm.record_products.quantity : 0,
                        technical_specifications: !'' ? vm.record_products.technical_specifications : '',
                        measurement_unit: measurement_unit_name
                        != '' ? measurement_unit_name : 'N/A',
                        measurement_unit_id: vm.product_measurement_unit_id,
                        history_tax_id: vm.product_history_tax_id,
                        product_requirement_id: vm.product_requirement_id,
                    });
                    vm.editIndex=false;
                }
                vm.product = '';
                vm.product_measurement_unit_id = '';
                vm.product_history_tax_id = '';
            }
        },

        /**
         * Edita el producto
         *
         * @author     Pedro Contreras <pmcontreras@cenditel.gob.ve>
         */
         editProduct(index, event){
            const vm = this;
            vm.product = '';
            vm.product_measurement_unit_id = '';
            vm.product_history_tax_id = '';
            vm.product_requirement_id = '';
            vm.editIndex = index-1;
            vm.product = {
                id: vm.record_products[index - 1].purchase_product ?
                    vm.record_products[index - 1].purchase_product.id :
                    vm.record_products[index - 1].id,
                text: vm.record_products[index - 1].name
            };
            if(vm.record_products[index - 1].purchase_product) {
                vm.product_requirement_id = vm.record_products[index - 1].purchase_product ?
                    vm.record_products[index - 1].id :
                    'false';
            } else {
                vm.product_requirement_id = vm.record_products[index - 1] ?
                    vm.record_products[index - 1].id :
                    'false';
            }
            vm.product_history_tax_id = vm.record_products[index - 1].history_tax_id;
            vm.product_measurement_unit_id = vm.record_products[index - 1].measurement_unit_id;
            vm.record_products.quantity = vm.record_products[index - 1].quantity;
            vm.record_products.technical_specifications = vm.record_products[index - 1].technical_specifications;

            event.preventDefault();

        }
    },
};
</script>
