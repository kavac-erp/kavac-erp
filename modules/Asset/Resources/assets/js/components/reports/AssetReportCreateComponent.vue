<template>
    <section id="AssetReportForm">
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
                        <li v-for="error in errors" :key="error">{{ error }}</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <input type="hidden" v-model="record.id" />
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Organización:</label>
                        <select2 :options="institutions"
                                @input="getDepartments()"
                                v-model="record.institution_id"></select2>
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex flex-row">
                    <h3 class="h6" style="margin: 0 auto; padding-top: 2.2em;">Tipo de Reporte</h3>
                </div>
                <div class="col-12 col-md-6 d-flex flex-row">
                    <div class="form-group pr-3 px-md-5">
                        <label>General</label>
                        <div class="custom-control custom-switch">
                            <input
                                id="sel_general_report"
                                type="radio"
                                name="type_report"
                                value="general"
                                class="custom-control-input sel_type_report"
                                style="margin-bottom: 1rem !important;"
                                v-model="record.type_report"
                            />
                            <label
                                class="custom-control-label"
                                for="sel_general_report">
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Por clasificación</label>
                        <div class="custom-control custom-switch">
                            <input
                                type="radio"
                                name="type_report"
                                value="clasification"
                                v-model="record.type_report"
                                class="custom-control-input sel_type_report"
                                id="sel_clasification_report"
                            >
                            <label
                                class="custom-control-label"
                                for="sel_clasification_report">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <br v-show="this.record.type_report == 'clasification'">
            <br v-show="this.record.type_report == 'clasification'">
            <div :class="isClassification">
                <div v-show="this.record.type_report == 'clasification'" class="col-12 col-md-6 form-group" style="margin-top: 20px;">
                    <h3 class="h6">Tipo de Bien</h3>
                    <div class="row">
                        <div class="col-12 col-md-9 d-flex flex-row">
                            <div class="form-group pr-3 px-md-5">
                                <label>Mueble</label>
                                <div class="custom-control custom-switch">
                                    <input
                                        id="sel_furniture_report"
                                        type="radio"
                                        name="type_asset"
                                        value="furniture_active"
                                        class="custom-control-input sel_type_asset"
                                        v-model="record.type_asset"
                                    />
                                    <label
                                        class="custom-control-label"
                                        for="sel_furniture_report">
                                    </label>
                                </div>
                            </div>
                            <div class="form-group pr-3 px-md-5">
                                <label>Inmueble</label>
                                <div class="custom-control custom-switch">
                                    <input
                                        id="sel_property_report"
                                        type="radio"
                                        name="type_asset"
                                        value="property_active"
                                        class="custom-control-input sel_type_asset"
                                        v-model="record.type_asset"
                                    />
                                    <label
                                        class="custom-control-label"
                                        for="sel_property_report">
                                    </label>
                                </div>
                            </div>
                            <div class="form-group pr-3 px-md-5">
                                <label>Vehículo</label>
                                <div class="custom-control custom-switch">
                                    <input
                                        id="sel_vehicle_report"
                                        type="radio"
                                        name="type_asset"
                                        value="vehicle_active"
                                        class="custom-control-input sel_type_asset"
                                        v-model="record.type_asset"
                                    />
                                    <label
                                        class="custom-control-label"
                                        for="sel_vehicle_report">
                                    </label>
                                </div>
                            </div>
                            <div class="form-group pr-3 px-md-5">
                                <label>Semoviente</label>
                                <div class="custom-control custom-switch">
                                    <input
                                        id="sel_livestock_report"
                                        type="radio"
                                        name="type_asset"
                                        value="livestock_active"
                                        class="custom-control-input sel_type_asset"
                                        v-model="record.type_asset"
                                    />
                                    <label
                                        class="custom-control-label"
                                        for="sel_livestock_report">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br v-show="this.record.type_report != 'clasification'">
                <br v-show="this.record.type_report != 'clasification'">
                <div v-show="this.record.type_report != ''" v-if="this.record.type_report == 'clasification'" class="col-12 col-md-6">
                    <h3 class="h6 mt-3">Estado</h3>
                    <div class="col-12 col-md-6 form-group">
                        <label>Estatus de uso:</label>
                        <select2 :options="asset_status"
                                data-toggle="tooltip"
                                title="Seleccione un registro de la lista"
                                v-model="record.asset_status_id"></select2>
                    </div>
                </div>
                <div v-show="this.record.type_report != ''" v-else>
                    <h3 class="h6 mt-3">Estado</h3>
                    <div class="row">
                        <div class="col-12 col-md-3 form-group">
                            <label>Estatus de uso:</label>
                            <select2 :options="asset_status"
                                    data-toggle="tooltip"
                                    title="Seleccione un registro de la lista"
                                    v-model="record.asset_status_id"></select2>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div v-show="this.record.type_report != ''">
                <h3 v-if="this.record.type_report == 'clasification'" class="h6 mt-3 text-center">Tipo de Búsqueda</h3>
                <h3 v-else class="h6 mt-3">Tipo de Búsqueda</h3>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Búsqueda por periodo</label>
                        <div class="custom-control custom-switch">
                            <input
                                id="sel_search_date"
                                class="custom-control-input sel_type_search"
                                type="radio"
                                name="type_search"
                                value="date"
                                v-model="record.type_search"
                            >
                            <label
                                class="custom-control-label"
                                for="sel_search_date">
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class=" form-group">
                            <label>Búsqueda por mes</label>
                            <div class="custom-control custom-switch">
                                <input
                                    type="radio"
                                    name="type_search"
                                    value="mes"
                                    v-model="record.type_search"
                                    class="custom-control-input sel_type_search"
                                    id="sel_search_mes"
                                >
                                <label
                                    class="custom-control-label"
                                    for="sel_search_mes">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-show="this.record.type_search == 'mes'">
                    <div class="row">
                        <div class="col-md-2">
                        </div>
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
                                <input class="form-control input-sm" type="number"
                                    data-toggle="tooltip" min="0"
                                    title="Indique el año de busqueda"
                                    v-model="record.year" />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-show="this.record.type_search == 'date'">
                    <div class="row">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Desde:</label>
                                <div class="input-group input-sm">
                                    <span class="input-group-addon">
                                        <i class="now-ui-icons ui-1_calendar-60"></i>
                                    </span>
                                    <input
                                        type="date"
                                        data-toggle="tooltip"
                                        title="Indique la fecha minima de busqueda"
                                        class="form-control input-sm"
                                        v-model="record.start_date"
                                    >
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
                                    <input
                                        type="date"
                                        data-toggle="tooltip"
                                        title="Indique la fecha maxima de busqueda"
                                        class="form-control input-sm"
                                        v-model="record.end_date"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div v-show="this.record.type_report == 'clasification'">
                <h3 class="h6 mt-3 text-center">Categoría del Bien</h3>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tipo de bien:</label>
                        <select2
                            :options="asset_types"
                            @input="getAssetCategories()"
                            v-model="record.asset_type_id">
                        </select2>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Categoria general:</label>
                        <select2
                            :options="asset_categories"
                            @input="getAssetSubcategories()"
                            v-model="record.asset_category_id">
                        </select2>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Subcategoria:</label>
                        <select2
                            :options="asset_subcategories"
                            @input="getAssetSpecificCategories()"
                            v-model="record.asset_subcategory_id">
                        </select2>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Categoria específica:</label>
                        <select2
                            :options="asset_specific_categories"
                            v-model="record.asset_specific_category_id">
                        </select2>
                    </div>
                    <div class="col-4 col-md-3" id="helpAssetAssetGroupCode">
                    <div class="form-group is-required">
                        <label>Código interno</label>
                        <input type="text" placeholder="Código interno del bien" data-toggle="tooltip"
                            title="Indique el código interno del bien" class="form-control input-sm"
                            v-model="record.code"
                            @input="(event) =>
                            (record.code =
                                event.target.value.toUpperCase())
                                ">
                    </div>
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <button
                        type="button"
                        class="btn btn-sm btn-info btn-custom"
                        data-toggle="tooltip"
                        @click="filterRecords()"
                        v-show="this.record.type_report != ''"
                        title="Realizar la búsqueda de acuerdo a los filtros establecidos en el formulario"
                    >
                        Realizar búsqueda
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <hr>
            <v-server-table
                :columns="columns"
                :url="url"
                :options="table_options"
                ref="tableResults"
            >
                <div
                    slot="asset_status.name"
                    slot-scope="props"
                    class="text-center"
                >
                    <span>
                        {{
                            (props.row.asset_disincorporation_asset)
                            ? (`${props.row.asset_status.name}:
                            ${props.row.asset_disincorporation_asset.asset_disincorporation.asset_disincorporation_motive.name}`)
                            : props.row.asset_status.name
                        }}
                    </span>
                </div>
            </v-server-table>
        </div>

        <div id="helpParamButtons" class="card-footer text-right">
            <button
                class="btn btn-sm btn-primary btn-custom"
                type="button"
                @click="createRecord()"
            >
                <i class="fa fa-file-pdf-o"></i>
                <span>Generar reporte</span>
            </button>
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id: '',
                    type_search: '',
                    type_report: '',
                    type_asset: '',
                    furniture_active: '',
                    property_active: '',
                    vehicle_active: '',
                    livestock_active: '',
                    code: '',

                    asset_type_id: '',
                    asset_category_id: '',
                    asset_subcategory_id:'',
                    asset_specific_category_id: '',
                    asset_status_id: '',

                    department_id: '',
                    institution_id: '',

                    mes_id: '',
                    year: '',
                    start_date: '',
                    end_date: '',
                },
                url:"",
                refresh: false,
                errors: [],
                records: [],

                columns: [
                    'code_sigecof',
                    'asset_details.code',
                    'asset_specific_category.name',
                    'asset_status.name',
                    'department.name',
                    'asset_asignation_asset.asset_asignation.location_place'
                ],
                mes: [
                    {"id":"", "text": "Todos"},
                    {"id":"1",  "text": "Enero"},
                    {"id":"2",  "text": "Febrero"},
                    {"id":"3",  "text": "Marzo"},
                    {"id":"4",  "text": "Abril"},
                    {"id":"5",  "text": "Mayo"},
                    {"id":"6",  "text": "Junio"},
                    {"id":"7",  "text": "Julio"},
                    {"id":"8",  "text": "Agosto"},
                    {"id":"9",  "text": "Septiempre"},
                    {"id":"10", "text": "Octubre"},
                    {"id":"11", "text": "Noviembre"},
                    {"id":"12", "text": "Diciembre"}
                ],
                asset_types: [],
                asset_categories: [],
                asset_subcategories: [],
                asset_specific_categories: [],
                asset_status: [],
                institutions: [],
                departments: [],
                table_options: {
                    headings: {
                        'code_sigecof': 'Código SIGECOF',
                        'asset_details.code': 'Código interno',
                        'asset_specific_category.name': 'Descripción',
                        'asset_status.name': 'Estatus de uso',
                        'department.name': 'Unidad administrativa',
                        'asset_asignation_asset.asset_asignation.location_place': 'Ubicación',
                    },

                    sortable: [
                        'code_sigecof',
                        'asset_specific_category.name',
                        'asset_status.name',
                        'department.name'
                    ],
                    filterable: [
                        'code_sigecof',
                        'asset_details.code',
                        'asset_specific_category.name',
                        'asset_status.name',
                        'department.name',
                        'asset_asignation_asset.asset_asignation.location_place'
                    ],
                    perPage: 5,
                    perPageValues: [5, 10, 25, 50, 100],
                    pagination: {chunk: 5},
                }
            }
        },
        props: {
            institution_id: {
                type: Number,
                required: true,
                default: null
            },
        },
        computed: {
            isClassification: function() {
                return this.record.type_report == 'clasification' ? 'row' : '';
            }
        },
        created() {
            const vm = this;
            vm.getInstitutions();
            vm.getAssetTypes();
            vm.getAssetStatus();
            vm.table_options.sendInitialRequest = false;
            vm.table_options.requestFunction = function(data) {
            return axios.post(vm.url, {
                start_date: vm.record.start_date,
                end_date: vm.record.end_date,
                asset_status: vm.record.asset_status_id,
                institution: vm.record.institution_id,
                mes_id: vm.record.mes_id,
                year: vm.record.year,
                asset_type: vm.record.asset_type_id,
                asset_category: vm.record.asset_category_id,
                asset_subcategory: vm.record.asset_subcategory_id,
                asset_specific_category: vm.record.asset_specific_category_id,
                search:data.query,
                limit: data.limit,
                ascending: data.ascending,
                page: data.page,
                orderBy: data.orderBy,
                type_asset: vm.record.type_asset,
                code: vm.record.code,
            }).catch(error => {
                vm.errors = [];

                if (typeof(error.response) !="undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
                console.error(error);
            });
        };
        },
        watch: {
            'record.type_report': function(newVal, oldVal) {
                const vm = this;
                var url = `${window.app_url}/asset/registers/search`;
                if(newVal == 'general'){
                    vm.url = url + "/general"
                    vm.reset('clasification');
                }else{
                    vm.url = url + "/clasification/clasification"
                    vm.reset('general');
                }
            },

            'record.type_search': function (oldVal, newVal) {
                const vm = this;
                if (newVal == 'date') {
                    vm.reset('mes');
                }else{
                    vm.reset('date');
                }
            },
        },
        mounted() {
            const vm = this;
            this.switchHandler('type_report');
            this.switchHandler('type_search');

            // Selecciona la organización por defecto
            setTimeout(() => vm.record.institution_id = vm.institution_id, 2000);
        },
        methods: {
            reset(fields = 'all') {
                if (fields == 'all') {
                        this.record = {
                        id: '',
                        type_search: '',
                        type_report: '',
                        type_asset: '',
                        furniture_active: '',
                        property_active: '',
                        vehicle_active: '',
                        livestock_active: '',
                        code: '',

                        asset_type_id: '',
                        asset_category_id: '',
                        asset_subcategory_id:'',
                        asset_specific_category_id: '',
                        asset_status_id: '',

                        department_id: '',
                        institution_id: '',

                        mes_id: '',
                        year: '',
                        start_date: '',
                        end_date: '',
                    };
                }else if(fields == 'clasification'){
                    this.record.asset_type_id = '';
                    this.record.asset_category_id = '';
                    this.record.asset_subcategory_id ='';
                    this.record.asset_specific_category_id = '';
                    this.record.type_asset = '';
                    this.record.furniture_active = '';
                    this.record.property_active = '';
                    this.record.vehicle_active = '';
                    this.record.livestock_active = '';
                    this.record.code = '';
                    this.record.type_search = '';
                }else if(fields == 'general'){
                    this.record.type_search = '';
                }else if(fields == 'date'){
                    this.record.start_date = '';
                    this.record.end_date = '';
                }else if (fields == 'mes'){
                    this.record.mes_id = '';
                    this.record.year = '';
                }
            },
            /**
             * Obtiene los datos de los estatus de uso de los bienes institucionales
             *
             * @author Henry Paredes <hparedes@cenditel.gob.ve>
             */
            getAssetStatus() {
                const vm = this;
                vm.asset_status = [];
                axios.get(`${window.app_url}/asset/get-status`).then(response => {
                    vm.asset_status = response.data;
                });
            },
            /**
             * Crea el reporte de bienes institucionales
             *
             * @author Henry Paredes <hparedes@cenditel.gob.ve>
             *
             */
            async createRecord() {
                const vm = this;
                var fields = {};
                var url = `${window.app_url}/asset/reports`;

                if (vm.record.type_report == 'clasification' && vm.record.type_asset == '') {
                    bootbox.alert("Debe seleccionar un tipo de bien para generar el reporte");
                    return false;
                }
                if (vm.record.type_report == '') {
                    bootbox.alert("Debe seleccionar el tipo de reporte a generar");
                    return false;
                }
                if (vm.record.type_report == 'dependence') {
                    return false;
                }
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                await axios.post(url, fields).then(response => {
                    if (response.data.result == false) {
                        vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', 'No se pudo generar el reporte');
                    }
                    else if (typeof(response.data.redirect) !== "undefined") {
                        vm.showMessage('custom', '¡Éxito!', 'info', 'screen-ok', 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación');
                    }
                    else {
                        vm.reset();
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
                });
            },
            async filterRecords() {
                const vm = this;
                vm.$refs.tableResults.limit = vm.table_options.perPage;
                vm.$refs.tableResults.refresh();
            },
        }
    };
</script>
