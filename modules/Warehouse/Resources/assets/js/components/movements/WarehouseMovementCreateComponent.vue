<template>
    <section id="WarehouseMovementForm">
        <div class="card-body">
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
                <div class="col-md-6">
                    <b>Origen de los insumos</b>
                    <div class="col-12 d-inline-flex">
                        <div class="col-6" id="helpInstitutionInitial"
                            v-if="movementid == null">
                            <div class="form-group is-required">
                                <label>Nombre de la organización:</label>
                                <select2 :options="institutions" @input="getWarehouseStart(record.initial_institution_id)"
                                         v-model="record.initial_institution_id"></select2>
                                <input type="hidden" v-model="record.id">
                            </div>
                        </div>
                        <div class="col-6" id="helpInstitutionInitial"
                            v-else>
                            <div class="form-group is-required">
                                <label>Nombre de la organización:</label>
                                <input type="text" data-toggle="tooltip"
                                                class="form-control"
                                                id="institution_start"
                                                readonly="">
                            </div>
                        </div>

                        <div class="col-6" id="helpWarehouseInitial"
                            v-if="movementid == null">
                            <div class="form-group is-required">
                                <label>Nombre del almacén:</label>
                                <select2 id="initial_warehouse_select"
                                         :options="initial_warehouses" @input="getWarehouseProducts()"
                                         :disabled="(record.initial_institution_id == '')"
                                         v-model="record.initial_warehouse_id"></select2>
                            </div>
                        </div>
                        <div class="col-6" id="helpWarehouseInitial"
                            v-else>
                            <div class="form-group is-required">
                                <label>Nombre del almacén:</label>
                                <input type="text" data-toggle="tooltip"
                                                class="form-control"
                                                id="warehouse_start"
                                                readonly="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <b>Destino de los insumos</b>
                    <div class="col-12 d-inline-flex">
                        <div class="col-6" id="helpInstitutionEnd">
                            <div class="form-group is-required">
                                <label>Nombre de la organización:</label>
                                <select2 :options="institutions"
                                         @input="getWarehouseFinish(record.end_institution_id)"
                                         v-model="record.end_institution_id"></select2>
                            </div>
                        </div>

                        <div class="col-6"  id="helpWarehouseEnd">
                            <div class="form-group is-required">
                                <label>Nombre del almacén:</label>
                                <select2 id="end_warehouse_select"
                                         :options="end_warehouses"
                                         @input="getWarehouseProducts('warehouseProductFinish')"
                                         :disabled="(record.end_institution_id == '')"
                                         v-model="record.end_warehouse_id"></select2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6" id="helpWarehouseMovementDescription">
                    <div class="form-group is-required">
                        <label>Descripción:</label>
                        <ckeditor :editor="ckeditor.editor" data-toggle="tooltip"
                                  title="Indique alguna descripción referente al movimiento de almacén (requerido)"
                                  :config="ckeditor.editorConfig" class="form-control" tag-name="textarea" rows="3"
                                  v-model="record.description"></ckeditor>
                    </div>
                </div>
            </div>

            <hr>
            <v-client-table id="helpTable"
                @row-click="toggleActive" :columns="columns" :data="records" :options="table_options">
                <div slot="h__check" class="text-center">
                    <label class="form-checkbox">
                        <input type="checkbox" v-model="selectAll" @click="select()" class="cursor-pointer">
                    </label>
                </div>

                <div slot="check" slot-scope="props" class="text-center">
                    <label class="form-checkbox">
                        <input type="checkbox" class="cursor-pointer" :value="props.row.id" :id="'checkbox_'+props.row.id" v-model="selected">
                    </label>
                </div>
                <div slot="description" slot-scope="props">
                    <span>
                        <b> {{ (props.row.warehouse_product)?
                            props.row.warehouse_product.name+': ':''
                        }} </b>
                    </span>
                    <span v-html="prepareText(props.row.warehouse_product.description)">
                    </span><br>
                    <b> Unidad: </b>
                        {{ (props.row.warehouse_product) ?
                                props.row.warehouse_product.measurement_unit ?
                                    props.row.warehouse_product.measurement_unit.name : ''
                                : ''
                        }} <br>
                    <span>
                        <div v-for="(att, index) in props.row.warehouse_product_values" :key="index">
                            <b>{{att.warehouse_product_attribute.name +":"}}</b> {{ att.value}}
                        </div>
                            <b>Valor:</b> {{props.row.unit_value}} {{(props.row.currency)?props.row.currency.name:''}}
                    </span>
                </div>
                <div slot="inventory" slot-scope="props">
                    <span>
                        <b>Almacén:</b> {{
                            props.row.warehouse_institution_warehouse.warehouse.name
                            }} <br>
                        <b>Existencia:</b> {{ props.row.real }}<br>
                        <b>Reservados:</b> {{ (props.row.reserved === null)? '0':props.row.reserved }}
                        <br>
                        <b>Solicitados:</b> {{ quantityProductRequests(props.row.code) }}
                        <br>
                        <b>Disponible para solicitar:</b> {{ numberDecimal(props.row.real - quantityProductRequests(props.row.code),2)  }}
                    </span>
                </div>
                <div slot="id" slot-scope="props">
                    <div>
                        <input type="number" class="form-control table-form input-sm" data-toggle="tooltip" min=0 :max="props.row.exist" :id="'movement_product_'+props.row.id" onfocus="this.select()" @input="selectElement(props.row.id)">
                    </div>
                </div>
                <div slot="destiny" slot-scope="props">
                    <div>
                        <b>Almacén:</b>
                        <span v-for="(warehouse, index) in end_warehouses" :key="index"
                              v-if="(warehouse.id == record.end_warehouse_id) && (warehouse.id != '')">
                            {{ warehouse.text }}
                        </span>
                        <br>
                    </div>
                    <div>
                        <b>Código:</b>
                        <span> {{ getDestinyProduct(props.row) }} </span>
                        <br>
                    </div>
                    <div class="d-inline-flex">
                        <b class="mt-1">Minimo:</b>
                        <div class="ml-2 mb-2">
                            <input type="text" class="form-control table-form input-sm"
                                data-toggle="tooltip" :id="'movement_product_minimum_'+props.row.id"
                                v-input-mask data-inputmask="
                                    'alias': 'numeric',
                                    'allowMinus': 'false',
                                    'digits': 0"
                                onfocus="this.select()">
                        </div>
                    </div>
                     <br>
                    <div class="d-inline-flex">
                        <b class="mt-1">Maximo:</b>
                        <div class="ml-2 mb-2">
                            <input type="text" class="form-control table-form input-sm"
                                data-toggle="tooltip" :id="'movement_product_maximum_'+props.row.id"
                                v-input-mask data-inputmask="
                                    'alias': 'numeric',
                                    'allowMinus': 'false',
                                    'digits': 0"
                                onfocus="this.select()">
                        </div>
                    </div>
                </div>
                <div slot="id" slot-scope="props">
                    <div>
                        <input type="number" class="form-control table-form input-sm" data-toggle="tooltip" min=0 :max="props.row.exist" :id="'movement_product_'+props.row.id" onfocus="this.select()" @input="selectElement(props.row.id);
                            validateInput(props.row.real, props.row.code, props.row.id)">
                    </div>
                </div>
            </v-client-table>
        </div>
        <div class="card-footer text-right">
            <div class="row">
                <div class="col-md-3 offset-md-9" id="helpParamButtons">
                    <button type="button" @click="reset()"
                            class="btn btn-default btn-icon btn-round"
                            title ="Borrar datos del formulario">
                            <i class="fa fa-eraser"></i>
                    </button>

                    <button type="button" @click="redirect_back(route_list)"
                            class="btn btn-warning btn-icon btn-round btn-modal-close"
                            data-dismiss="modal"
                            title="Cancelar y regresar">
                            <i class="fa fa-ban"></i>
                    </button>

                    <button type="button"  @click="createMovement('warehouse/movements')"
                            class="btn btn-success btn-icon btn-round btn-modal-save"
                            title="Guardar registro">
                        <i class="fa fa-save"></i>
                    </button>
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
                    description:'',
                    initial_warehouse_id:'',
                    initial_institution_id:'',
                    end_warehouse_id:'',
                    end_institution_id:'',
                    warehouse_inventory_products: [],
                },
                columns: ['check', 'code', 'description', 'inventory', 'destiny', 'id'],
                records: [],
                productsQuantity: [],
                warehouseProductFinish: [],
                errors: [],
                validateValue: [],
                institutions: [],
                initial_warehouses: [],
                end_warehouses: [],
                selected: [],
                selectAll: false,
                table_options: {
                    rowClassCallback(row) {
                        var checkbox = document.getElementById('checkbox_' + row.id);
                        return ((checkbox)&&(checkbox.checked))? 'selected-row cursor-pointer' : 'cursor-pointer';
                    },

                    headings: {
                        'code': 'Código',
                        'description': 'Descripción',
                        'inventory': 'Inventario origen',
                        'destiny': 'Reglas destino',
                        'id': 'Solicitados',
                    },
                    sortable: ['code', 'description', 'inventory', 'destiny', 'id'],
                    filterable: [
                        'code',
                        'unit_value',
                        'warehouse_product_values',
                        'currency.name',
                        'warehouse_product.name',
                        'warehouse_product.description',
                        'warehouse_product.measurement_unit.name',
                        'warehouse_institution_warehouse.warehouse.name',
                    ],
                }
            }
        },
        props: {
            movementid: Number,
        },
        methods: {
            toggleActive({ row }) {
                const vm = this;
                var checkbox = document.getElementById('checkbox_' + row.id);

                if ((checkbox)&&(checkbox.checked == false)){
                    var index = vm.selected.indexOf(row.id);
                    if (index >= 0){
                        vm.selected.splice(index,1);
                    }
                    else
                        checkbox.click();
                }
                else if ((checkbox)&&(checkbox.checked == true)) {
                    var index = vm.selected.indexOf(row.id);
                    if (index >= 0)
                        checkbox.click();
                    else
                        vm.selected.push(row.id);
                }
            },

            prepareText(text) {
                return text.replace('<p>', '').replace('</p>', '');
            },

            reset() {
                this.record = {
                    id: '',
                    description:'',
                    initial_warehouse_id:'',
                    initial_institution_id:'',
                    end_warehouse_id:'',
                    end_institution_id:'',
                    warehouse_inventory_products: [],
                };

                this.initial_warehouses = [];
                this.end_warehouses = [];

                this.selected = [];
                this.selectAll = false;

            },
            select() {
                const vm = this;
                vm.selected = [];
                $.each(vm.records, function(index,campo){
                    var checkbox = document.getElementById('checkbox_' + campo.id);
                    var input = document.getElementById('movement_product_' + campo.id);
                    if (!vm.selectAll)
                        vm.selected.push(campo.id);
                    else if (checkbox && checkbox.checked){
                        checkbox.click();
                        if (input)
                            input.value = "";
                    }
                });
            },
            selectElement(id) {
                var input = document.getElementById('movement_product_' + id);
                var checkbox = document.getElementById('checkbox_' + id);
                if ((input.value == '') || (input.value == 0)) {
                    if (checkbox.checked)
                        checkbox.click();
                } else if (!checkbox.checked)
                    checkbox.click();
            },
            async getWarehouseProducts(field = 'records') {
                const vm = this;
                if (field != 'records') {
                    var warehouse = vm.record.end_warehouse_id;
                    var institution = vm.record.end_institution_id;

                    let init = document.getElementById('initial_warehouse_select');
                    if (init) {
                        $.each($(Object.values(init.options)), function(i, f) {
                            if ((vm.record.end_warehouse_id == f.value) && (f.value != '')) {
                                $(Object.values(init.options)[i]).prop("disabled", 'disabled');
                            } else {
                                $(Object.values(init.options)[i]).prop("disabled", false);
                            }
                        })
                    }
                } else {
                    var warehouse = vm.record.initial_warehouse_id;
                    var institution = vm.record.initial_institution_id;

                    let end = document.getElementById('end_warehouse_select');
                    if (end) {
                        $.each($(Object.values(end.options)), function(i, f) {
                            if ((vm.record.initial_warehouse_id == f.value) && (f.value != '')) {
                                $(Object.values(end.options)[i]).prop("disabled", 'disabled');
                            } else {
                                $(Object.values(end.options)[i]).prop("disabled", false);
                            }
                        })
                    }
                }

                var url = "/warehouse/movements/vue-list-products/";
                vm[field] = [];
                if (( warehouse != '') && (institution != '')){
                    await axios.get(url + warehouse + '/' + institution + '/' + vm.movementid).then(response => {
                        if (typeof(response.data.records) != "undefined") {
                            if (field == 'records') {
                                vm.productsQuantity = response.data.productsQuantity;
                            }
                            vm[field] = (field != 'records') ? response.data.records : response.data.records.filter(data => {
                        return data.exist != null;
                    });
                        }
                    });
                }

                if (field != 'records') {
                    $.each(vm[field], function(i, f) {
                        if (f.warehouse_inventory_rule) {
                            let min = document.getElementById('movement_product_minimum_' + f.id);
                            let max = document.getElementById('movement_product_maximum_' + f.id);
                            if (min) {
                                min.value = f.warehouse_inventory_rule.minimum;
                            }
                            if (max) {
                                max.value = f.warehouse_inventory_rule.maximum;
                            }
                        }
                    });
                }
            },
            async getWarehouseStart(id) {
                const vm = this;
                var url = '/warehouse/get-warehouses/';
                vm.warehouse_start = [];
                vm.selected = [];
                if (id != '') {
                    await axios.get(url + id).then(response => {
                        if(typeof(response.data) != "undefined")
                            vm.initial_warehouses = response.data;
                    });
                }
                if (vm.movementid) {
                    $.each(vm.record.warehouse_inventory_products, function (index, campo) {
                        var element = document.getElementById("movement_product_" + campo.warehouse_inventory_product_id);
                        if (element) {
                            element.value = campo.quantity;
                            vm.selected.push(campo.warehouse_inventory_product_id);
                        }
                    });
                }
                let init = document.getElementById('initial_warehouse_select');
                $.each($(Object.values(init.options)), function(i, f) {
                    if ((vm.record.end_warehouse_id == f.value) && (f.value != '')) {
                        $(Object.values(init.options)[i]).prop("disabled", 'disabled');
                    } else {
                        $(Object.values(init.options)[i]).prop("disabled", false);
                    }
                })
            },
            async getWarehouseFinish(id) {
                const vm = this;
                var url = '/warehouse/get-warehouses/';
                vm.warehouse_finish = [];
                if (id != '') {
                    await axios.get(url + id).then(response => {
                        if(typeof(response.data) != "undefined")
                            vm.end_warehouses = response.data;
                    });
                }
                let end = document.getElementById('end_warehouse_select');
                $.each($(Object.values(end.options)), function(i, f) {
                    if ((vm.record.initial_warehouse_id == f.value) && (f.value != '')) {
                        $(Object.values(end.options)[i]).prop("disabled", 'disabled');
                    } else {
                        $(Object.values(end.options)[i]).prop("disabled", false);
                    }
                });
                if (vm.movementid) {
                    if ((vm.record.warehouse_institution_warehouse_end) &&(vm.record.end_warehouse_id == '')) {
                        vm.record.end_warehouse_id = vm.record.warehouse_institution_warehouse_end.warehouse_id;
                    }
                }
            },

            createMovement(url){
                const vm = this;
                vm.record.warehouse_inventory_products = [];
                if ((vm.validateValue).length > 0) {
                    $.each(vm.validateValue, function (index, campo) {
                        bootbox.alert("La cantidad de producto a movilizar (Código: " + campo + ") es mayor a la cantidad disponible");
                    });
                    return false;
                }
                if (!vm.selected.length > 0){
                    bootbox.alert("Debe agregar al menos un elemento a la solicitud");
                    return false;
                };
                $.each(vm.selected,function(index,campo){
                    var element = document.getElementById("movement_product_"+campo);
                    var elementMin = document.getElementById("movement_product_minimum_" + campo);
                    var elementMax = document.getElementById("movement_product_maximum_" + campo);
                    if (element) {
                        var value = element.value;
                        if (value == "") {
                            bootbox.alert("Debe ingresar la cantidad solicitada para cada insumo seleccionado");
                            return false;
                        }
                        vm.record.warehouse_inventory_products.push({
                            id:         campo,
                            movemented: value,
                            minimum:    elementMin ? elementMin.value : '',
                            maximum:    elementMax ? elementMax.value : ''
                        });
                    }
                });
                vm.createRecord(url);
            },

            async loadMovement(id){
                const vm = this;
                var fields = {};

                await axios.get('/warehouse/movements/info/'+id).then(response => {
                    if (typeof(response.data.records != "undefined")){
                        fields = response.data.records;
                        vm.record = {
                            id: fields.id,
                            description: fields.description,
                            initial_institution_id: (fields.warehouse_institution_warehouse_initial)?
                                fields.warehouse_institution_warehouse_initial.institution_id:'',
                            end_institution_id: (fields.warehouse_institution_warehouse_end)?
                                fields.warehouse_institution_warehouse_end.institution_id:'',
                            initial_warehouse_id: (fields.warehouse_institution_warehouse_initial)?
                                fields.warehouse_institution_warehouse_initial.warehouse_id:'',
                            end_warehouse_id: (fields.warehouse_institution_warehouse_end)?
                                fields.warehouse_institution_warehouse_end.warehouse_id:'',
                            warehouse_institution_warehouse_end: fields.warehouse_institution_warehouse_end,
                            warehouse_inventory_products: [],
                        };
                        $(".card-body #institution_start").val((fields.warehouse_institution_warehouse_initial)?
                                fields.warehouse_institution_warehouse_initial.institution.acronym:'' );
                        $(".card-body #warehouse_start").val((fields.warehouse_institution_warehouse_initial)?
                                fields.warehouse_institution_warehouse_initial.warehouse.name:'' );
                    }
                });
                await vm.getWarehouseProducts();
                $.each(fields.warehouse_inventory_product_movements, function(index,campo) {
                    let element = document.getElementById("movement_product_"+campo.warehouse_initial_inventory_product_id);
                    if (element){
                        element.value = campo.quantity;
                        vm.selected.push(campo.warehouse_initial_inventory_product_id);
                        let checkbox = document.getElementById('checkbox_' + campo.warehouse_initial_inventory_product_id);
                        checkbox.click();
                    }
                });
            },
            getDestinyProduct(initialProduct) {
                const vm = this;
                let endProduct = '';
                $.each(vm.warehouseProductFinish, function(index, field) {
                    /** Si el insumo es el mismo */
                    if (initialProduct.warehouse_product_id == field.warehouse_product_id) {
                        /** Si el valor del insumo es el mismo */
                        if (initialProduct.unit_value == field.unit_value) {
                            endProduct = field;
                            let min = document.getElementById('movement_product_minimum_' + initialProduct.id);
                            let max = document.getElementById('movement_product_maximum_' + initialProduct.id);
                            if (min) {
                                min.value = (min.value != '') ? min.value : field.warehouse_inventory_rule.minimum;
                            }
                            if (max) {
                                max.value = (max.value != '') ? max.value : field.warehouse_inventory_rule.maximum;
                            }
                        }
                    }

                });
                return vm.record.end_warehouse_id ? (endProduct ? endProduct.code : 'N/A') : '';
            },
            /**
             * Devuelve la cantidad solicitada de un producto especifico
             *
             * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            quantityProductRequests(Codeproduct) {
                const vm = this;
                var quantity = 0;
                if (Codeproduct) {
                    $.each(vm.productsQuantity, function (index, campo) {
                        if (campo['code'] == Codeproduct) {
                            quantity = campo['quantity'];
                        }
                    });
                }
                return quantity;
            },
            /**
             * Devuelve un número con un determinado número de decimales.
             *
             * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            numberDecimal(num, dec) {
                var exp = Math.pow(10, dec || 2);
                return parseInt(num * exp, 10) / exp;
            },
            /**
             * Validad que la cantidad de la solicitud de producto sea menor o igual a la disponible.
             *
             * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            validateInput(real, code, id) {
                const vm = this;
                vm.errors = [];
                var quantity = vm.quantityProductRequests(code);
                let value = document.getElementById("movement_product_" + id).value;
                if ((real - quantity < value)) {
                    vm.errors.push('La cantidad de producto a movilizar (Código: ' + code + ') es mayor a la cantidad disponible');
                    if (!vm.searchCode(code)) {
                        vm.validateValue.push(code);
                    }
                    vm.continue = false;
                }
                else {
                    vm.deleteCode(code);
                }
                return;
            },
            /**
             * Busca si el código del producto esta en la lista de productos que tiene problema de validación (solicitud > disponible para solicitar)
             *
             * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            searchCode(code) {
                const vm = this;
                var search = false;
                if (code) {
                    $.each(vm.validateValue, function (index, campo) {
                        if (campo == code) {
                            search = true;
                        }
                    });
                }
                return search;
            },

            /**
             * Busca si el código del producto esta en la lista de productos que tiene problema de validación (solicitud > disponible para solicitar) para ser eliminado
             *
             * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
             */
            deleteCode(code) {
                const vm = this;
                if (code) {
                    $.each(vm.validateValue, function (index, campo) {
                        if (campo == code) {
                            vm.validateValue.splice(index,1);
                        }
                    });
                }
            },
        },
        mounted() {
            this.getInstitutions();
            if (this.movementid){
                this.loadMovement(this.movementid);
            }
        },
    };
</script>
