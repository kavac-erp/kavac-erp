<template>
    <section id="WarehouseRequestStaffForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
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
                    <b>Datos de la solicitud</b>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestDate">
                    <div class="form-group is-required">
                        <label>Fecha de la solicitud</label>
                        <input
                            type="date"
                            data-toggle="tooltip"
                            title="Fecha de la solicitud"
                            class="form-control input-sm"
                            v-model="record.request_date"
                        >
                        <input type="hidden" v-model="record.id">
                    </div>
                </div>
                <div class="col-md-8" id="helpWarehouseRequestMotive">
                    <div class="form-group is-required">
                        <label>Motivo de la solicitud</label>
                        <ckeditor
                            :editor="ckeditor.editor"
                            data-toggle="tooltip"
                            title="Indique el motivo de la solicitud (requerido)"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.motive"
                        ></ckeditor>
                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestDepartment">
                    <div class="form-group is-required">
                        <label>Solicitante</label>
                        <select2 :options="payroll_staffs" v-model="record.payroll_staff_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpDepartment">
                    <div class="form-group is-required">
                        <label>Departamento</label>
                        <select2 :options="departments" v-model="record.department_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestPosition">
                    <div class="form-group is-required">
                        <label>Cargo</label>
                        <select2
                            :options="payroll_positions"
                            v-model="record.payroll_position_id"
                        ></select2>
                    </div>
                </div>
            </div>

            <hr>
            <div class="col-12">
                <h6 class="card-title">Listado de solicitud de almacén</h6>
            </div>
            <v-client-table
                id="helpTable"
                @row-click="toggleActive"
                :columns="columns"
                :data="records"
                :options="table_options"
            >
                <div slot="h__check" class="text-center">
                    <label class="form-checkbox">
                        <input
                            type="checkbox"
                            v-model="selectAll"
                            @click="select()"
                            class="cursor-pointer"
                        >
                    </label>
                </div>
                <div>
                    <b>
                        records
                    </b>
                </div>
                <div slot="check" slot-scope="props" class="text-center">
                    <label class="form-checkbox">
                        <input
                            type="checkbox"
                            class="cursor-pointer"
                            :value="props.row.id"
                            :id="'checkbox_' + props.row.id"
                            v-model="selected"
                        >
                    </label>
                </div>
                <div slot="description" slot-scope="props">
                    <span>
                        <b> {{ (props.row.warehouse_product) ?
                            props.row.warehouse_product.name + ': ' : ''
                        }} </b>
                        {{ (props.row.warehouse_product) ?
                            prepareText(
                                props.row.warehouse_product.description)
                            : ''
                        }}<br>
                        <b> Unidad: </b>
                        {{ (props.row.warehouse_product) ?
                                props.row.warehouse_product.measurement_unit ?
                                    props.row.warehouse_product.measurement_unit.name : ''
                                : ''
                        }} <br>
                    </span>
                    <span>
                        <div v-for="(att, index) in props.row.warehouse_product_values" :key="index">
                            <b>{{ att.warehouse_product_attribute.name + ":" }}</b> {{ att.value }}
                        </div>
                        <b>Valor:</b> {{ props.row.unit_value }} {{ (props.row.currency) ? props.row.currency.name : '' }}
                    </span>
                </div>
                <div slot="inventory" slot-scope="props">
                    <span>
                        <b>Almacén:</b> {{
                            props.row.warehouse_institution_warehouse.warehouse.name
                        }} <br>
                        <b>Existencia:</b> {{ props.row.real }}<br>
                        <b>Reservados:</b> {{ (props.row.reserved === null) ? '0' : props.row.reserved }}
                        <br>
                        <b>Solicitados:</b> {{ quantityProductRequests(props.row.code) }}
                        <br>
                        <b>Disponible para solicitar:</b> {{ numberDecimal(props.row.real - quantityProductRequests(props.row.code),2)  }}
                    </span>
                </div>
                <div slot="requested" slot-scope="props">
                    <div>
                        <input
                            type="text" class="form-control table-form input-sm"
                            data-toggle="tooltip" min=0
                            v-input-mask data-inputmask="
                                'alias': 'numeric',
                                'allowMinus': 'false',
                                'digits': 2"
                            :id="'request_product_' + props.row.id"
                            onfocus="this.select()"
                            @input="
                                selectElement(props.row.id);
                                validateInput(props.row.real, props.row.code, props.row.id)
                            "
                            v-model="input_values[props.row.id]"
                        >
                    </div>
                </div>
            </v-client-table>
        </div>
        <div class="card-footer text-right">
            <div class="row">
                <div class="col-md-3 offset-md-9" id="helpParamButtons">
                    <button
                        type="button"
                        @click="reset()"
                        class="btn btn-default btn-icon btn-round"
                        v-has-tooltip
                        title="Borrar datos del formulario"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        type="button"
                        @click="
                        redirect_back(route_list) "
                        class="
                            btn btn-warning btn-icon btn-round btn-modal-close
                        "
                        data-dismiss="modal"
                        title="Cancelar y regresar"
                    >
                        <i class="fa fa-ban"></i>
                    </button>
                    <button
                        type="button"
                        @click="createRequest('warehouse/requests/staff')"
                        class="btn btn-success btn-icon btn-round btn-modal-save"
                        title="Guardar registro"
                    >
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
                motive: '',
                institution_id: '1',
                department_id: '',
                payroll_position_id: '',
                payroll_staff_id: '',
                request_date: '',
                warehouse_products: [],
            },
            editIndex: null,
            records: [],
            productsQuantity: [],
            columns: [
                'check',
                'code',
                'description',
                'inventory',
                'requested'
            ],
            errors: [],
            validateValue: [],
            selected: [],
            input_values: [],
            selectAll: false,
            departments: [],
            payroll_positions: [],
            payroll_staffs: [],
            table_options: {
                rowClassCallback(row) {
                    var checkbox = document.getElementById('checkbox_' + row.id);
                    return ((checkbox) && (checkbox.checked)) ? 'selected-row cursor-pointer' : 'cursor-pointer';
                },
                headings: {
                    'code': 'Código',
                    'description': 'Descripción',
                    'inventory': 'Inventario',
                    'requested': 'Cantidad solicitada',
                },
                sortable: [
                    'code',
                    'description',
                    'inventory',
                    'requested'
                ],
                filterable: [
                    'code',
                    'unit_value',
                    'warehouse_product_values',
                    'currency.name',
                    'warehouse_product.name',
                    'warehouse_product.description',
                    'warehouse_product.measurement_unit.name',
                    'warehouse_institution_warehouse.warehouse.name',
                ]
            }
        }
    },
    created() {
        this.getPayrollStaffs();
        //this.initForm('/warehouse/requests/vue-list-products');
        this.initForm('/warehouse/requests/vue-list-products/' + this.requestid);
    },
    props: {
        requestid: Number,
    },
    watch: {
        'record.payroll_staff_id'(new_id) {
            this.getPayrollStaffInfo(new_id);
        },
    },
    methods: {
        toggleActive({ row }) {
            const vm = this;
            var checkbox = document.getElementById('checkbox_' + row.id);

            if ((checkbox) && (checkbox.checked == false)) {
                var index = vm.selected.indexOf(row.id);
                if (index >= 0) {
                    vm.selected.splice(index, 1);
                }
                else
                    checkbox.click();
            }
            else if ((checkbox) && (checkbox.checked == true)) {
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
                motive: '',
                institution_id: '1',
                department_id: '',
                payroll_position_id: '',
                payroll_staff_id: '',
                request_date: '',
                warehouse_products: [],
            }
        },

        select() {
            const vm = this;
            vm.selected = [];
            $.each(vm.records, function (index, campo) {
                var checkbox = document.getElementById('checkbox_' + campo.id);

                if (!vm.selectAll)
                    vm.selected.push(campo.id);
                else if (checkbox && checkbox.checked) {
                    checkbox.click();
                }
            });
        },

        selectElement(id) {
            var input = document.getElementById('request_product_' + id);
            var checkbox = document.getElementById('checkbox_' + id);
            if ((input.value == '') || (input.value == 0)) {
                if (checkbox.checked) {
                    checkbox.click();
                }
            }
            else if (!checkbox.checked) {
                checkbox.click();
            }
        },
        /**
         * Validad que la cantidad de la solicitud de producto sea menor o igual
         * a la disponible.
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        validateInput(real, code, id) {
            const vm = this;
            vm.errors = [];
            var quantity = vm.quantityProductRequests(code);
            let value = document.getElementById("request_product_" + id).value;
            if ((real - quantity < value)) {
                vm.errors.push(
                    'La cantidad de producto a solicitar (Código: ' + code
                    + ') es mayor a la cantidad disponible'
                );
                if(!vm.searchCode(code)) {
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
         * Busca si el código del producto esta en la lista de productos que
         * tiene problema de validación (solicitud > disponible para solicitar).
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        searchCode(code) {
            const vm = this;
            var search = false;
            if(code) {
                $.each(vm.validateValue, function (index, campo) {
                    if(campo == code) {
                        search = true;
                    }
                });
            }
            return search;
        },

        /**
         * Busca si el código del producto esta en la lista de productos que
         * tiene problema de validación (solicitud > disponible para solicitar)
         * para ser eliminado.
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        deleteCode(code) {
            const vm = this;
            if(code) {
                $.each(vm.validateValue, function (index, campo) {
                    if(campo == code) {
                        vm.validateValue.splice(index,1);
                    }
                });
            }
        },

        async initForm(url) {
            const vm = this;
            /**
             *    Ajustar si esta activa unica institucion seleccionar la institucion x defecto
             */
            vm.record.institution_id = '1';
            await axios.get(url).then(function (response) {
                if (typeof (response.data.records) !== "undefined")
                    vm.records = response.data.records;
                    vm.productsQuantity = response.data.productsQuantity;
            });
        },
        async loadRequest(id) {
            const vm = this;
            var fields = {};

            await axios.get('/warehouse/requests/staff/info/' + id).then(response => {
                if (typeof (response.data.records != "undefined")) {
                    fields = response.data.records;
                    vm.record = {
                        id: fields.id,
                        motive: fields.motive,
                        institution_id: '1',
                        department_id: fields.department_id,
                        payroll_position_id: (fields.payroll_staff)
                            ? fields.payroll_staff.payroll_employment.payroll_position_id : '',
                        payroll_staff_id: fields.payroll_staff_id,
                        request_date: fields.request_date
                            ? vm.format_date(fields.request_date, 'YYYY-MM-DD')
                            : vm.format_date(fields.created_at, 'YYYY-MM-DD'),
                    };
                    $.each(fields.warehouse_inventory_product_requests, function (index, campo) {
                        if (campo.warehouse_inventory_product_id) {
                            vm.input_values[campo.warehouse_inventory_product_id] = campo.quantity;
                            vm.selected.push(campo.warehouse_inventory_product_id);
                        }
                    });
                }
            });
        },

        createRequest(url) {
            const vm = this;
            vm.record.warehouse_products = [];
            var complete = true;
            if((vm.validateValue).length > 0) {
                $.each(vm.validateValue, function (index, campo) {
                    bootbox.alert(
                        "La cantidad de producto a solicitar (Código: " + campo + ") es mayor a la cantidad disponible"
                    );
                });
                return false;
            }
            if (!vm.selected.length > 0) {
                bootbox.alert(
                    "Debe agregar al menos un elemento a la solicitud"
                );
                return false;
            };
            $.each(vm.selected, function (index, campo) {
                if (vm.input_values[campo] == "") {
                    bootbox.alert(
                        "Debe ingresar la cantidad solicitada para cada insumo seleccionado"
                    );
                    complete = false;
                    return;
                }
                vm.record.warehouse_products.push(
                    { id: campo, requested: vm.input_values[campo] });
            });
            if (complete == true)
                vm.createRecord(url)
        },

        async getPayrollStaffInfo(id) {
            const vm = this;

            await axios.get(`${window.app_url}/payroll/staffs/${id}`).then(response => {
                let data = response.data.record;

                if (data && data.payroll_employment) {
                    if (data.payroll_employment.department_id) {
                        vm.getDepartments(data.payroll_employment.department_id);

                        setTimeout(() => {
                            vm.record.department_id = data.payroll_employment.department_id;
                        }, 1500);
                    }

                    if (data.payroll_employment) {
                        vm.getPayrollPositions(data.payroll_employment.payroll_position_id);

                        setTimeout(() => {
                            vm.record.payroll_position_id = data.payroll_employment.payroll_position_id;
                        }, 1500);
                    }
                } else {
                    vm.getDepartments();
                    vm.getPayrollPositions();
                }
            });
        },

        async getPayrollPositions(id) {
            const vm = this;
            vm.payroll_positions = [];
            await axios.get('/warehouse/get-payroll-positions').then(response => {
                vm.payroll_positions = (typeof(id) === "undefined" || !id)
                    ? response.data
                    : response.data.filter((position) => {
                    return position.id === id;
                });
            }).catch(error => {
                console.error(error);
            });
        },

        /**
         * Obtiene los departamentos o unidades de la organización
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} id Identificador del departamento a filtrar (opcional)
         */
        async getDepartments(id) {
            let vm = this;
            vm.departments = [];
            if (typeof(vm.record.institution_id) !== "undefined" && vm.record.institution_id !== '') {
                await axios.get(`/get-departments/${vm.record.institution_id}`).then(response => {
                    /** Obtiene los departamentos */
                    vm.departments = (typeof(id) === "undefined" || !id)
                    ? response.data
                    : response.data.filter((department) => {
                    return department.id === id;
                    });
                }).catch(error => {
                    console.error(error);
                });
            }
        },

        /**
         * Devuelve la cantidad solicitadas de un producto especifico
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        quantityProductRequests(Codeproduct) {
            const vm = this;
            var quantity = 0;
            if(Codeproduct) {
                $.each(vm.productsQuantity, function (index, campo) {
                    if(campo['code'] == Codeproduct) {
                        quantity = campo['quantity'];
                    }
                });
            }
            return quantity;
        },

        /**
         * Devuelve un numero decimal con un numero de decimales especifico
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         */
        numberDecimal(num, dec) {
            var exp = Math.pow(10, dec || 2);
            return parseInt(num * exp, 10) / exp;
        }
    },
    mounted() {
        if (this.requestid) {
            this.loadRequest(this.requestid);
        }
    },
};
</script>
