<template>
    <div class="form-horizontal">
        <div class="card-body">
            <accounting-show-errors ref="errorsDialyBook" />
            <div class="row">
                <div class="col-3" id="helpDailyBookInitDate">
                    <div class="is-required">
                        <label class="control-label">Fecha inicial</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="dateIni"
                        />
                    </div>
                </div>
                <div class="col-3" id="helpDailyBookEndDate">
                    <div class="is-required">
                        <label class="control-label">Fecha final</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="dateEnd"
                            :min="dateIni ? dateIni : ''"
                            :disabled="dateIni ? false : true"
                        />
                    </div>
                </div>
                <div class="col-3" id="helpDailyBookCurrency">
                    <div class="is-required">
                        <label class="control-label">Tipo de moneda</label>
                        <select2
                            :options="currencies"
                            v-model="currency_id"
                        ></select2>
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
                v-on:click="OpenPdf(getUrlReport(), '_blank')"
                id="helpDailyBookGenerateReport"
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
    data() {
        return {
            url: `${window.app_url}/accounting/report/dailyBook/pdf/`,
            urlSign: `${window.app_url}/accounting/report/dailyBookSign/pdf/`,
            dateIni: "",
            dateEnd: "",
            currencies: [],
            currency_id: "",
        };
    },
    created() {
        const vm = this;
        vm.getCurrencies();
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
            if (!this.dateIni) {
                errors.push("La fecha inicial es obligatorio.");
            }
            if (!this.dateEnd) {
                errors.push("La fecha final es obligatorio.");
            }
            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorsDialyBook.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsDialyBook.reset();

            var dateIni = this.dateIni;
            var dateEnd = this.dateEnd;
            var info =
                this.dateIni <= this.dateEnd
                    ? dateIni + "/" + dateEnd
                    : dateEnd + "/" + dateIni;
            var url = this.url + info + "/" + this.currency_id;
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
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReportSign: function () {
            var errors = [];
            if (!this.dateIni) {
                errors.push("La fecha inicial es obligatorio.");
            }
            if (!this.dateEnd) {
                errors.push("La fecha final es obligatorio.");
            }
            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorsDialyBook.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsDialyBook.reset();

            var dateIni = this.dateIni;
            var dateEnd = this.dateEnd;
            var info =
                this.dateIni <= this.dateEnd
                    ? dateIni + "/" + dateEnd
                    : dateEnd + "/" + dateIni;
            var url = this.urlSign + info + "/" + this.currency_id;
            return url;
        },
    },
};
</script>
