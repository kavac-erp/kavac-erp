<template>
    <div class="form-horizontal">
        <div class="card-body">
            <accounting-show-errors ref="errorsPatrimonialMovement" />
            <div class="row">
                <div class="col-3" id="helpPatrimonialMovementInitDate">
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
                <div class="col-3" id="helpPatrimonialMovementEndDate">
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
                <div class="col-3" id="helpPatrimonialMovementCurrency">
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
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                class="btn btn-primary btn-sm"
                data-toggle="tooltip"
                v-has-tooltip
                title="Generar Reporte"
                v-on:click="OpenPdf(getUrlReport(), '_blank')"
                id="helpPatrimonialMovementGenerateReport"
            >
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
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
            url: `${window.app_url}/accounting/report/PatrimonialMovement/pdf/`,
            urlSign: `${window.app_url}/accounting/report/PatrimonialMovementSign/pdf/`,
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
                this.$refs.errorsPatrimonialMovement.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsPatrimonialMovement.reset();

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
                this.$refs.errorsPatrimonialMovement.showAlertMessages(errors);
                return;
            }
            this.$refs.errorsPatrimonialMovement.reset();

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
