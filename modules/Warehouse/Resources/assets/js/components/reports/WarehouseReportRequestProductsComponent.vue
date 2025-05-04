<template>
    <section id="WarehouseReportForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <ul>
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Organización:</label>
                        <select2 :options="institutions"
                                 @input="getDepartments()"
                                 v-model="record.institution_id"></select2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Departamento/Dependencia:</label>
                        <select2 :options="departments"
                                v-model="record.department_id"></select2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <strong>Filtros</strong>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestProject">
                    <div class=" form-group">
                        <label>Proyecto</label>
                        <div class="custom-control custom-switch mb-4">
                            <input type="radio" class="custom-control-input sel_pry_acc" id="sel_project"
                                    name="project_centralized_action" value="project">
                            <label class="custom-control-label" for="sel_project"></label>
                        </div>
                        <div v-if="show_case_project" class="is-required">
                            <select2 :options="budget_projects" id="budget_project_id" @input="getBudgetSpecificActions('Project')"
                                v-model="record.budget_project_id"></select2>
                        </div>

                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestCentralizedAction">
                    <div class=" form-group">
                        <label>Acción centralizada</label>
                        <div class="custom-control custom-switch mb-4">
                            <input type="radio" class="custom-control-input sel_pry_acc" id="sel_centralized_action"
                                    name="project_centralized_action" value="centralized_action">
                            <label class="custom-control-label" for="sel_centralized_action"></label>
                        </div>
                        <div v-if="show_case_cent_acc" class="is-required">
                            <select2 :options="budget_centralized_actions" id="budget_centralized_action_id" @input="getBudgetSpecificActions('CentralizedAction')"
                                v-model="record.budget_centralized_action_id"></select2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="helpWarehouseRequestStaff">
                    <div class=" form-group">
                        <label>Trabajador</label>
                        <div class="custom-control custom-switch mb-4">
                            <input type="radio" class="custom-control-input sel_pry_acc" id="sel_staff"
                                    name="project_centralized_action" value="staff">
                            <label class="custom-control-label" for="sel_staff"></label>
                        </div>
                        <div v-if="show_case_staff" class="is-required">
                            <select2 :options="payroll_staffs" id="budget_centralized_action_id"
                                v-model="record.payroll_staff_id"></select2>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="helpWarehouseRequestSpecificAction" v-if="show_case_project || show_case_cent_acc">
                    <div class=" form-group is-required">
                        <label>Acción Específica</label>
                        <select2 :options="budget_specific_actions" id="budget_specific_action_id"
                            v-model="record.budget_specific_action_id" disabled></select2>
                    </div>
                </div>
            </div>

            <div class="row text-center">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Busqueda por Periodo</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="radio" class="custom-control-input sel_type_search" id="sel_search_date"
                                        name="type_search" value="date" v-model="record.type_search">
                                <label class="custom-control-label" for="sel_search_date"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class=" form-group">
                        <label>Busqueda por Mes</label>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="radio" class="custom-control-input sel_type_search" id="sel_search_mes"
                                        name="type_search" value="mes" v-model="record.type_search">
                                <label class="custom-control-label" for="sel_search_mes"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="this.record.type_search == 'mes'">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Mes:</label>
                            <select2 :options="mes"
                                     v-model="record.mes_id"></select2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Año:</label>
                            <input type="number" data-toggle="tooltip" min="0"
                                       title="Indique el año de busqueda"
                                       class="form-control input-sm" v-model="record.year">
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="this.record.type_search == 'date'">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Desde:</label>
                            <div class="input-group input-sm">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_calendar-60"></i>
                                </span>
                                <input type="date" data-toggle="tooltip"
                                       title="Indique la fecha minima de busqueda"
                                       class="form-control input-sm" v-model="record.start_date">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hasta:</label>
                            <div class="input-group input-sm">
                                <span class="input-group-addon">
                                    <i class="now-ui-icons ui-1_calendar-60"></i>
                                </span>
                                <input type="date" data-toggle="tooltip"
                                       title="Indique la fecha maxima de busqueda"
                                       class="form-control input-sm" v-model="record.end_date">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" @click="loadInventoryProduct('request-products')"
                            class='btn btn-sm btn-info float-right' data-toggle="tooltip"
                            title="Realizar la búsqueda de acuerdo a los filtros establecidos en el formulario"
                    >
                        Realizar búsqueda
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <hr>
            <v-client-table :columns="columns" :data="records" :options="table_options">
                <div slot="code" slot-scope="props">
                    <span>
                        {{ props.row.warehouse_inventory_product.code }}
                    </span>
                </div>
                <div slot="description" slot-scope="props">
                    <span>
                        <b> {{ props.row.warehouse_inventory_product.warehouse_product
                                ? 'Nombre: '
                                : ''
                        }} </b>
                        {{ props.row.warehouse_inventory_product.warehouse_product
                                ? props.row.warehouse_inventory_product.warehouse_product.name + '.'
                                : ''
                        }} <br>
                        {{ props.row.warehouse_inventory_product.warehouse_product
                                ? prepareText(
                                props.row.warehouse_inventory_product.warehouse_product.description)
                                : ''
                        }} <br>
                    </span>
                    <span>
                        <div v-for="(att, index) in props.row.warehouse_inventory_product.warehouse_product_values" :key="index">
                            <b>{{att.warehouse_product_attribute.name +": "}}</b> {{ att.value}} <br>
                        </div>
                        <b>Valor:</b> {{props.row.warehouse_inventory_product.unit_value}}
                            {{ props.row.warehouse_inventory_product.currency
                                    ? props.row.warehouse_inventory_product.currency.acronym
                                    : ''
                            }}
                    </span>
                </div>
                <div slot="inventory" slot-scope="props">
                    <span>
                        <b>Almacén:</b>
                            {{
                                props.row.warehouse_inventory_product.warehouse_institution_warehouse
                                    ? (props.row.warehouse_inventory_product.warehouse_institution_warehouse.warehouse)
                                        ? (props.row.warehouse_inventory_product.warehouse_institution_warehouse.warehouse.name)
                                        : ''
                                    : ''
                            }} <br>
                        <b>Existencia:</b>
                            {{
                                props.row.warehouse_inventory_product.real
                            }} <br>
                        <b>Reservados:</b> {{ (props.row.warehouse_inventory_product.reserved === null)? '0':props.row.warehouse_inventory_product.reserved }}<br>
                        <b>Solicitados:</b> {{ quantityProductRequests(props.row.warehouse_inventory_product.code) }}

                        <br>
                    </span>
                </div>
            </v-client-table>
        </div>
        <div class="card-footer text-right">
            <div class="row">
                <div class="col-md-3 offset-md-9" id="helpParamButtons">
                    <button type="button" class='btn btn-sm btn-primary btn-custom'
                            @click="createReport('request-products')">
                        <i class="fa fa-file-pdf-o"></i>
                        <span>Generar Reporte</span>
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
                    budget_project_id: '',
                    budget_centralized_action_id: '',
                    budget_specific_action_id: '',
                    payroll_staff_id: '',

                    type_search: '',
                    department_id: '',
                    institution_id: '',

                    mes_id: '',
                    year: '',
                    start_date: '',
                    end_date: ''
                },
                show_case_project: false,
                show_case_cent_acc: false,
                show_case_staff: false,
                records: [],
                productsQuantity: [],
                errors: [],
                columns: ['code', 'description', 'inventory'],

                budget_projects: [],
                budget_centralized_actions: [],
                budget_specific_actions: [],
                payroll_staffs: [],
                mes: [
                    {"id":"","text":"Todos"},
                    {"id":1,"text":"Enero"},
                    {"id":2,"text":"Febrero"},
                    {"id":3,"text":"Marzo"},
                    {"id":4,"text":"Abril"},
                    {"id":5,"text":"Mayo"},
                    {"id":6,"text":"Junio"},
                    {"id":7,"text":"Julio"},
                    {"id":8,"text":"Agosto"},
                    {"id":9,"text":"Septiempre"},
                    {"id":10,"text":"Octubre"},
                    {"id":11,"text":"Noviembre"},
                    {"id":12,"text":"Diciembre"}
                ],
                institutions: [],
                departments: [],

            }
        },
        props: {
            institution_id: {
                type: Number,
                required: true,
                default: null
            },
        },
        methods: {
            reset() {
                this.record = {
                    id: '',
                    budget_project_id: '',
                    budget_centralized_action_id: '',
                    budget_specific_action_id: '',
                    payroll_staff_id: '',

                    type_search: '',
                    department_id: '',
                    institution_id: '',

                    mes_id: '',
                    year: '',
                    start_date: '',
                    end_date: ''
                }
            },
            prepareText(text) {
                return text.replace('<p>', '').replace('</p>', '');
            },
            createReport(current) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields["current"] = current;
                axios.post("/warehouse/reports/inventory-products/create", fields).then(response => {
                    if (response.data.result == false)
                        location.href = response.data.redirect;
                    else if (typeof(response.data.redirect) !== "undefined") {
                        window.open(response.data.redirect, '_blank');
                    }
                    else {
                        vm.reset();
                    }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        else {
                            vm.logs('Warehouse/Resources/assets/js/_all.js', 343, error, 'createReport');
                        }
                    }
                    vm.loading = false;
                });
            },
            loadInventoryProduct(current) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                vm.records = [];
                fields["current"] = current;
                axios.post("/warehouse/reports/inventory-products/vue-list", fields).then(response => {
                    if (typeof(response.data.records) != "undefined") {
                        vm.records = response.data.records;
                        vm.productsQuantity = response.data.productsQuantity;
                    }else{
                        vm.records = [];
                    }
                    vm.loading = false;
                }).catch(error => {
                    if (typeof(error.response) != "undefined") {
                        console.log("error");
                    }
                    vm.loading = false;
                });
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
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'description': 'Descripción',
                'inventory': 'Inventario',
            };
            this.table_options.sortable = ['code', 'description', 'inventory'];
            this.table_options.filterable = ['code', 'description', 'inventory'];
        },
        watch: {

            /**
             * Función que permite monitorear modificaciones en el campo budget_specific_actions
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            budget_specific_actions: function() {
                $("#budget_specific_action_id").attr('disabled', (this.budget_specific_actions.length <= 1));
            },

        },
        mounted() {
            const vm = this;
            vm.switchHandler('type_search');
            vm.getInstitutions();
            vm.getBudgetProjects();
            vm.getBudgetCentralizedActions();
            vm.getPayrollStaffs();

            /**
             * Evento para determinar los datos a requerir según el tipo de formulación
             * (por proyecto o acción centralizada)
             */
            $('.sel_pry_acc').on('change', function(e) {
                if (e.target.id === "sel_project") {
                    vm.show_case_project = true;
                    vm.show_case_cent_acc = false;
                    vm.show_case_staff = false;
                    vm.record.budget_centralized_action_id = '';
                    vm.record.budget_specific_action_id = '';
                    vm.record.payroll_staff_id = '';
                }
                else if (e.target.id === "sel_centralized_action") {
                    vm.show_case_project = false;
                    vm.show_case_staff = false;
                    vm.show_case_cent_acc = true;
                    vm.record.budget_project_id = '';
                    vm.record.budget_specific_action_id = '';
                    vm.record.payroll_staff_id = '';
                }
                else if (e.target.id === "sel_staff") {
                    vm.show_case_project = false;
                    vm.show_case_cent_acc = false;
                    vm.show_case_staff = true;
                    vm.record.budget_project_id = '';
                    vm.record.budget_specific_action_id = '';
                    vm.record.budget_centralized_action_id = '';
                }
            });

            // Selecciona la organización por defecto
            setTimeout(() => vm.record.institution_id = vm.institution_id, 2000);
        }
    };
</script>
