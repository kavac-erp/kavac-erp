<template>
    <div class="form-horizontal">
        <div class="card-body">
            <accounting-show-errors ref="errorsAnalyticalMajor" />
            <div class="row">
                <div class="col-3" id="helpAnaliticalMajorInitDate">
                    <label class="control-label"
                        ><strong>Fecha Inicial</strong></label
                    >
                    <br />
                    <div class="is-required">
                        <label>Mes</label>
                        <select2
                            :options="months"
                            v-model="month_init"
                        ></select2>
                    </div>
                    <br />
                    <div class="is-required">
                        <label>Año</label>
                        <select2 :options="years" v-model="year_init"></select2>
                    </div>
                </div>
                <div class="col-3" id="helpAnaliticalMajorEndDate">
                    <label class="control-label"
                        ><strong>Fecha Final</strong></label
                    >
                    <br />
                    <div class="is-required">
                        <label>Mes</label>
                        <select2
                            :options="months"
                            v-model="month_end"
                        ></select2>
                    </div>
                    <br />
                    <div class="is-required">
                        <label>Año</label>
                        <select2 :options="years" v-model="year_end"></select2>
                    </div>
                </div>
                <div class="col-3" id="helpAnaliticalMajorRangeAccount">
                    <br />
                    <div class="col-12 is-required">
                        <label class="control-label"
                            ><strong>Cuenta inicial</strong></label
                        >
                        <div
                            title="Seleccione un registro de la lista"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            <select2
                                :options="OptionsAcc"
                                v-model="InitAcc"
                                :disabled="disabledSelect"
                            ></select2>
                        </div>
                    </div>
                    <br />
                    <div class="col-12 is-required">
                        <label class="control-label"
                            ><strong>Cuenta final</strong></label
                        >
                        <div
                            title="Seleccione un registro de la lista"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            <select2
                                :options="OptionsAcc"
                                v-model="EndAcc"
                                :disabled="disabledSelect"
                            ></select2>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <br />
                    <div
                        class="col-12 is-required"
                        id="helpAnaliticalMajorCurrency"
                    >
                        <label class="control-label">Tipo de moneda</label>
                        <select2
                            :options="currencies"
                            v-model="currency_id"
                        ></select2>
                    </div>
                    <div id="helpAnaliticalMajorAllAccount">
                        <label for="" class="control-label mt-4"
                            >Seleccionar todas</label
                        >
                        <div
                            class="custom-control custom-switch"
                            data-toggle="tooltip"
                            title="Seleccionar todas las cuentas de mayor analítico"
                        >
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="analyticalReportCheckAll"
                                @click="checkAll()"
                                v-model="check_sel_all"
                            />
                            <label
                                class="custom-control-label"
                                for="analyticalReportCheckAll"
                            ></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                title="Generar Reporte"
                data-toggle="tooltip"
                v-has-tooltip
                v-on:click="OpenPdf(getUrlReport(), '_blank')"
                id="helpAnaliticalMajorGenerateReport"
            >
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
            </button>
            <button
                class="btn btn-primary btn-sm"
                @click="OpenPdf(getUrlReport(), '_blank', true)"
                :id="'help' + this.type_report + 'GenerateReport'"
                data-toggle="tooltip"
                v-has-tooltip
                title="Exportar Reporte"
            >
                Exportar Reporte
                <i class="fa fa-file-excel-o"></i>
            </button>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        year_old: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            url: `${window.app_url}/accounting/report/analyticalMajor`,
            urlSign: `${window.app_url}/accounting/report/analyticalMajorSign`,
            InitAcc: 0,
            EndAcc: 0,
            dates: null,
            OptionsAcc: [{ id: 0, text: "Seleccione..." }],
            disabledSelect: false,
            currencies: [],
            currency_id: "",
            check_sel_all: false,
        };
    },
    created() {
        this.getCurrencies();
        this.CalculateOptionsYears(this.year_old);
    },
    mounted() {
        const vm = this;
        vm.getAccountingAccounts();
    },
    methods: {
        /**
         * Selecciona todo el rango de registros de cuantas
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        checkAll() {
            const vm = this;
            if (vm.check_sel_all) {
                if (vm.OptionsAcc.length > 1) {
                    vm.disabledSelect = true;
                    vm.InitAcc = vm.OptionsAcc[1].id;
                    vm.EndAcc = vm.OptionsAcc[vm.OptionsAcc.length - 1].id;
                }
            } else {
                vm.disabledSelect = false;
                vm.InitAcc = 0;
                vm.EndAcc = 0;
            }
        },

        /**
         * Obtiene las cuentas encontradas en el rango de fecha dado
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        getAccountingAccounts: function () {
            const vm = this;
            vm.dates = {
                initMonth: vm.month_init,
                initYear:
                    vm.year_init > vm.year_end ? vm.year_end : vm.year_init,
                endMonth: vm.month_end,
                endYear:
                    vm.year_init > vm.year_end ? vm.year_init : vm.year_end,
            };
            axios.post(vm.url + "/AccAccount", vm.dates).then((response) => {
                vm.OptionsAcc = response.data.records;
                vm.InitAcc = "";
                vm.EndAcc = "";
            });
        },

        /**
         * genera la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReport: function () {
            var errors = [];
            if (this.InitAcc <= 0) {
                errors.push("Debe seleccionar una cuenta de inicio.");
            }
            if (this.EndAcc <= 0) {
                errors.push("Debe seleccionar una cuenta de final.");
            }
            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorsAnalyticalMajor.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsAnalyticalMajor.reset();

            var url = this.url + "/pdf";
            var InitAcc =
                this.InitAcc > this.EndAcc ? this.EndAcc : this.InitAcc;
            var EndAcc =
                this.InitAcc > this.EndAcc ? this.InitAcc : this.EndAcc;

            var dates =
                "/" +
                this.dates.initYear +
                "-" +
                this.dates.initMonth +
                "/" +
                this.dates.endYear +
                "-" +
                this.dates.endMonth;

            url += dates;

            if (InitAcc != 0) {
                url += "/" + InitAcc;
            }

            if (EndAcc != 0) {
                url += "/" + EndAcc;
            }

            url += "/" + this.currency_id;
            return url;
        },

        OpenPdf: function (url, type, xml = false) {
            const vm = this;
            if (!url) {
                return;
            }

            url = vm.setUrl(url).replace("/pdf", "/pdfVue");
            vm.loading = true;

            axios.get(url).then((response) => {
                if (!response.data.result) {
                    vm.showMessage(
                        "custom",
                        "Error en conversión",
                        "danger",
                        "screen-error",
                        response.data.message
                    );
                } else {
                    url = url.split("/pdf")[0];
                    if (xml) {
                        url += "/sheet/" + response.data.id;
                    } else {
                        url += "/" + response.data.id;
                    }
                    window.open(url, type);
                }
                vm.loading = false;
            });
        },

        /**
         * genera la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReportSign: function () {
            var errors = [];
            if (this.InitAcc <= 0) {
                errors.push("Debe seleccionar una cuenta de inicio.");
            }
            if (this.EndAcc <= 0) {
                errors.push("Debe seleccionar una cuenta de final.");
            }
            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorsAnalyticalMajor.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsAnalyticalMajor.reset();

            var url = this.urlSign + "/pdf";
            var InitAcc =
                this.InitAcc > this.EndAcc ? this.EndAcc : this.InitAcc;
            var EndAcc =
                this.InitAcc > this.EndAcc ? this.InitAcc : this.EndAcc;

            var dates =
                "/" +
                this.dates.initYear +
                "-" +
                this.dates.initMonth +
                "/" +
                this.dates.endYear +
                "-" +
                this.dates.endMonth;

            url += dates;

            if (InitAcc != 0) {
                url += "/" + InitAcc;
            }

            if (EndAcc != 0) {
                url += "/" + EndAcc;
            }

            url += "/" + this.currency_id;
            return url;
        },
    },
    watch: {
        month_init: function () {
            this.getAccountingAccounts();
            this.check_sel_all = false;
        },
        year_init: function () {
            this.getAccountingAccounts();
            this.check_sel_all = false;
        },
        month_end: function () {
            this.getAccountingAccounts();
            this.check_sel_all = false;
        },
        year_end: function () {
            this.getAccountingAccounts();
            this.check_sel_all = false;
        },
        check_sel_all: function () {
            this.checkAll();
        },
    },
};
</script>
