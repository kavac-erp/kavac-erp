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
            <h3 class="h6">Tipo de Reporte</h3>
            <div class="row justify-content-start pb-2">
                <div class="form-group col-16 col-md-3">
                    <label>Organización:</label>
                    <select2 :options="institutions" @input="getDepartments()" v-model="record.institution_id"></select2>
                </div>
                <div class="form-group px-3 px-md-5">
                    <label class="pr-2">General</label>
                    <div class="custom-control custom-switch">
                        <input id="sel_general_report" type="radio" name="type_report" value="general"
                            class="custom-control-input sel_type_report" v-model="record.type_report" />
                        <label class="custom-control-label" for="sel_general_report">
                        </label>
                    </div>
                </div>
                <div class="form-group px-3 px-md-5">
                    <label class="pr-2">Específico</label>
                    <div class="custom-control custom-switch">
                        <input type="radio" name="type_report" value="specific" v-model="record.type_report"
                            class="custom-control-input sel_type_report" id="sel_clasification_report">
                        <label class="custom-control-label" for="sel_clasification_report">
                        </label>
                    </div>
                </div>
            </div>
            <div v-show="record.type_report == 'specific'" class="row justify-content-center">
                <div class="col-4 col-md-3" id="helpAssetAssetGroupCode">
                    <div class="form-group is-required">
                        <label>Código interno</label>
                        <input type="text" placeholder="Código interno del bien" data-toggle="tooltip"
                            title="Indique el código interno del bien" class="form-control input-sm" v-model="record.code"
                            @input="(event) =>
                            (record.code =
                                event.target.value.toUpperCase())
                                ">
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-2 form-group">
                        <button type="button" aria-label="Buscar" title="Buscar"
                            class="btn btn-info btn-icon btn-xs-responsive px-3" @click="filterRecords()">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-show="record.type_report == 'specific'">
                <hr>
                <div class="row justify-content-center">
                    <h6 class="card-title text-uppercase">Registro</h6>
                </div>
                <div class="colunm justify-content-center">
                    <v-client-table :columns="columns" :data="records" :options="table_options">

                        <!--                         <div slot="id" slot-scope="props">
                            {{ props.row.acquisition_value.name }} {{ " " + props.row.currency.symbol + "." }}
                        </div>
                        <div slot="acummulated_depreciation" slot-scope="props">
                            {{ lastDepreciation(props.row.depreciation).acumulated_amount }} {{ " " +
                                props.row.currency.symbol
                                + "." }}
                        </div> -->
                    </v-client-table>
                </div>
            </div>
            <div id="helpParamButtons" class="card-footer text-right">
                <button class="btn btn-sm btn-primary btn-custom" type="button" @click="getPdf()">
                    <i class="fa fa-file-pdf-o"></i>
                    <span>Generar reporte</span>
                </button>
            </div>
        </div>
    </section>
</template>
<script>
export default {
    data() {
        return {
            record: {
                institution_id: '',
                type_report: '',
                code: '',
            },
            errors: [],
            institutions: [],
            records: [],
            columns: [
                'asset_institutional_code',
                'asset_subcategory',
                'asset_specific_category',
                'acquisition_date',
                'acquisition_value',
                'depresciation_years',
                'acumulated_depreciation',
            ],
            table_options: {
                headings: {
                    'asset_institutional_code': 'Código interno',
                    'asset_subcategory': 'Subcategoría',
                    'acumulated_depreciation.asset_specific_category': 'Categoría específica',
                    'acquisition_date': 'Fecha de compra',
                    'acquisition_value': 'Valor de compra',
                    'depresciation_years': 'Años de vida util',
                    'acumulated_depreciation': 'Depreciación acumulada',
                },
                sortable: [],
                columnsClasses: {
                    'asset_institutional_code.name': 'text-center',
                    'asset_subcategory.name': 'text-center',
                    'asset_specific_category.name': 'text-center',
                    'acquisition_date.name': 'text-center',
                    'acquisition_value': 'text-center',
                    'depresciation_years.name': 'text-center',
                    'acummulated_depreciation': 'text-center',

                }
            }
        }
    },
    props: {},
    watch: {
        record: {
            handler: function (newValue) {
                if (newValue.type_report == 'general') {
                    this.record.code = '';
                    this.records = [];
                }
            },
            deep: true,
        },
    },
    created() {

        this.getInstitutions();

    },
    mounted() {
        const vm = this;
        vm.switchHandler('type_report');
    },
    methods: {
        reset() {
            this.record = {
                institution_id: '',
                type_report: '',
                code: '',
            };
        },
        async loadAssets(url, fields) {
            const vm = this;
            await axios.post(url, fields).then(response => {
                if (typeof (response.data.records) !== "undefined") {
                    vm.records = response.data.records[0]['acumulated_depreciation'];
                }
            });
        },
        getPdf() {
            const vm = this;
            let error_message = []
            const url = `${window.app_url}/asset/reports/depreciation-pdf/accumulated/${vm.record.institution_id}`;

            if (vm.record.type_report === '') {
                error_message.push("Debe seleccionar el tipo de reporte a generar");
            }

            if (vm.record.type_report === 'specific' && vm.record.code === '') {
                error_message.push("Debe Ingresar código interno valido para generar el reporte");
            } else if (vm.record.type_report === 'specific' && this.records.length === 0) {
                error_message.push("El codigo interno no existe");

            }
            if (vm.record.institution_id === '') {
                error_message.push("Debe seleccionar la organización");
            }

            if (error_message.length > 0) {
                vm.errors = error_message;
                return false;
            }

            vm.loading = true;
            if (vm.record.type_report === 'specific') {
                window.open(`${url}/${vm.record.code}`, '_blank');
            } else {
                window.open(url, '_blank');
            }
            vm.loading = false;
            vm.reset();
        },
        async filterRecords() {
            const vm = this;
            var url = `${window.app_url}/asset/registers/search/code`;

            var fields = {};

            if (vm.record.type_report == 'specific') {
                fields = {
                    code: vm.record.code
                }
                await vm.loadAssets(url, fields);
            }
        },
        lastDepreciation(depreciations) {
            return depreciations[depreciations.length - 1];
        },
    }
};
</script>
