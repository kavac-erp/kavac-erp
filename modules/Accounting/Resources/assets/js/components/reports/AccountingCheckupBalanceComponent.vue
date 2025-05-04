<template>
    <div class="form-horizontal">
        <div class="card-body">
            <accounting-show-errors ref="errorsCheckUpBalance" />
            <div class="row">
                <div class="col-3" id="helpCheckupBalanceInitDate">
                    <label><strong>Desde:</strong></label>
                    <div class="form-group">
                        <label class="control-label">Mes</label>
                        <select2
                            :options="months"
                            v-model="month_init"
                        ></select2>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Año</label>
                        <select2 :options="years" v-model="year_init"></select2>
                    </div>
                </div>
                <div class="col-3" id="helpCheckupBalanceEndDate">
                    <label><strong>Hasta:</strong></label>
                    <div class="form-group">
                        <label class="control-label">Mes</label>
                        <select2
                            :options="months"
                            v-model="month_end"
                        ></select2>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Año</label>
                        <select2 :options="years" v-model="year_end"></select2>
                    </div>
                </div>
                <div class="col-3" id="helpCheckupBalanceCurrency">
                    <div class="form-group">
                        <label class="is-required control-label"
                            ><strong>Tipo de moneda</strong></label
                        >
                        <div class="mt-4">
                            <select2
                                :options="currencies"
                                v-model="currency_id"
                            ></select2>
                        </div>
                    </div>
                </div>
                <div class="col-3" id="helpCheckupBalanceAllAccount">
                    <div class="form-group">
                        <label class="text-center"
                            ><strong>Mostrar valores en cero</strong></label
                        >
                        <div
                            class="custom-control custom-switch mt-4"
                            data-toggle="tooltip"
                            title="Seleccionar para mostrar valores de cuentas en cero"
                        >
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="zero"
                                name="zero"
                            />
                            <label
                                class="custom-control-label"
                                for="zero"
                            ></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                v-has-tooltip
                title="Generar Reporte"
                v-on:click="OpenPdf(getUrlReport(), '_black')"
                id="helpCheckupBalanceGenerateReport"
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
                Exportar Reporte <i class="fa fa-file-excel-o"></i>
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
            url: `${window.app_url}/accounting/report/balanceCheckUp/pdf/`,
            urlSign: `${window.app_url}/accounting/report/balanceCheckUpSign/pdf/`,
            currencies: [],
            currency_id: "",
        };
    },
    created() {
        this.getCurrencies();
        this.CalculateOptionsYears(this.year_old);
    },
    methods: {
        /**
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReport: function () {
            var errors = [];
            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorsCheckUpBalance.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsCheckUpBalance.reset();

            var zero = $("#zero").prop("checked") ? "true" : "";

            var initDate =
                this.year_init > this.year_end
                    ? this.year_end + "-" + this.month_end
                    : this.year_init + "-" + this.month_init;
            var endDate =
                this.year_init > this.year_end
                    ? this.year_init + "-" + this.month_init
                    : this.year_end + "-" + this.month_end;

            var url =
                this.url +
                initDate +
                "/" +
                endDate +
                "/" +
                this.currency_id +
                "/" +
                zero;
            return url;
        },
        /**
         * Abre una nueva ventana en el navegador
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @author Fjescala
         * @param  {string} url para la nueva ventana
         * @param  {string} type tipo de ventana que se desea abrir
         * @return {boolean} Devuelve falso si no se ha indicado alguna información requerida
         */
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
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReportSign: function () {
            var errors = [];
            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorsCheckUpBalance.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsCheckUpBalance.reset();

            var zero = $("#zero").prop("checked") ? "true" : "";

            var initDate =
                this.year_init > this.year_end
                    ? this.year_end + "-" + this.month_end
                    : this.year_init + "-" + this.month_init;
            var endDate =
                this.year_init > this.year_end
                    ? this.year_init + "-" + this.month_init
                    : this.year_end + "-" + this.month_end;

            var url =
                this.urlSign +
                initDate +
                "/" +
                endDate +
                "/" +
                this.currency_id +
                "/" +
                zero;
            return url;
        },
    },
};
</script>
