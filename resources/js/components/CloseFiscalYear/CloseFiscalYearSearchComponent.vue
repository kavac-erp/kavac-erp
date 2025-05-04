<template>
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardSearchCloseFiscalYearForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ exist_accounting ? 'Buscador de cierre de ejercicios económicos' : 'Realizar nuevo cierre de ejercicio' }}
                        <a v-if="exist_accounting" href="javascript:void(0)" title="haz click para ver la ayuda guiada de este elemento"
                           data-toggle="tooltip" class="btn-help" @click="initUIGuide(helpFile)">
                            <i class="ion ion-ios-help-outline cursor-pointer"></i>
                        </a>
                    </h6>
                    <div class="card-btns">
                        <div class="d-inline-flex">
                            <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
                               title="Ir atrás" data-toggle="tooltip">
                                <i class="fa fa-reply"></i>
                            </a>
                            <close-fiscal-years-create></close-fiscal-years-create>
                            <a href="javascript:void(0)" class="card-minimize btn btn-card-action btn-round"
                               title="Minimizar" data-toggle="tooltip">
                                <i class="now-ui-icons arrows-1_minimal-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div v-if="exist_accounting" class="card-body">
                    <form-errors :listErrors="errors"></form-errors>
                    <div class="row">
                        <div class="col-md-2">
                            <b>Filtros</b>
                        </div>
                        <!-- Organización -->
                            <div class="col-md-4" id="helpInstitution">
                                <div class=" form-group is-required">
                                    <label>Organización:</label>
                                    <select2 :options="institutions" v-model="record.institution_id"></select2>
                                </div>
                            </div>
                        <!-- ./Organización -->
                        <!-- Año fiscal -->
                            <div class="col-md-4" id ="helpFiscalYear">
                                <div class=" form-group is-required">
                                    <label>Año fiscal:</label>
                                    <select2 :options="fiscal_years" v-model="record.fiscal_year"></select2>
                                </div>
                            </div>
                        <!-- ./Año fiscal -->
                    </div>
                </div>
                <div v-if="exist_accounting" class="card-footer text-right" id ="helpSearchButton">
                    <button class="btn btn-info btn-sm" title="Buscar cierres de ejercicio" data-toggle="tooltip"
                            @click="searchRecords()" v-has-tooltip>
                        <i class="fa fa-search"></i>
                    </button>
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
                institution_id: '',
                fiscal_year: '',
            },
            exist_accounting: false,
            institutions: [],
            fiscal_years: [],
            records: [],
            errors: []
        }
    },
    mounted() {
        const vm = this;
        vm.getInstitutions();
        vm.getClosedFiscalYears();
        vm.moduleExistAccounting();
    },
    methods: {
        /**
         * Listado de años fiscales         *
         */
        async getClosedFiscalYears() {
            const vm = this;
            const url = vm.setUrl('fiscal-years/closed/list');
            await axios.get(url).then(response => {
                vm.fiscal_years = response.data.records;
            }).catch(error => {
                console.error(error);
            });
        },

        /**
         * Método que evalúa si el módulo de contabilidad está instalado en el sistema
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        async moduleExistAccounting() {
            const vm = this;

            await axios.get(`${window.app_url}/modules/details/accounting`, {}).then(response => {
                if (response.data.result) {
                    vm.exist_accounting = response.data.result;
                }
            })
        },

        /**
         * Método que busca los ejercicios económicos ya cerrados realizados en el sistema
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         */
        async searchRecords() {
            const vm = this;
            let url = `${window.app_url}/close-fiscal-year/registers/search`;

            await axios.post(url + '/', vm.record).then(response => {
                if (response.data.records) {
                    vm.$parent.$refs.closeFiscalYearsList.records = response.data.records;
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
    }
};
</script>
