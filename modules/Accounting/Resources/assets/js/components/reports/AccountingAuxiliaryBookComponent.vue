<template>
    <div class="form-horizontal">
        <div class="card-body">
            <accounting-show-errors ref="errorAuxiliaryBook" />
            <div class="row">
                <div class="col-3" id="helpAuxiliaryBookDate">
                    <label><strong>Fecha:</strong></label>
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
                <div class="col-3" id="helpAuxiliaryBookAccount">
                    <div class="col-12 is-required">
                        <label class="control-label"
                            ><strong>Cuentas Patrimoniales</strong></label
                        >
                        <br /><br />
                        <select2
                            :options="records"
                            v-model="account_id"
                            :disabled="allAccounts"
                        ></select2>
                    </div>
                </div>
                <div class="col-3" id="helpAuxiliaryBookCurrency">
                    <div class="col-12 is-required">
                        <label class="control-label">Tipo de moneda</label>
                        <br /><br />
                        <div
                            title="Seleccione un registro de la lista"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
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
                @click="OpenPdf(getUrlReport(), '_blank')"
                id="helpAuxiliaryBookGenerateReport"
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
        records: {
            type: Array,
            default() {
                return [];
            },
        },
        year_old: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            account_id: 0,
            url: `${window.app_url}/accounting/report/auxiliaryBook/pdf/`,
            urlSign: `${window.app_url}/accounting/report/auxiliaryBookSign/pdf/`,
            currencies: [],
            currency_id: "",
            allAccounts: false,
            check_sel_all: false,
        };
    },
    created() {
        this.getCurrencies();
        this.CalculateOptionsYears(this.year_old);
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
                vm.account_id = 0;
                vm.allAccounts = true;
            } else {
                vm.account_id = 0;
                vm.allAccounts = false;
            }
        },

        /**
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReport: function () {
            var errors = [];
            if (!this.allAccounts && this.account_id <= 0) {
                errors.push("Debe seleccionar una cuenta.");
            }

            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorAuxiliaryBook.showAlertMessages(errors);
                return;
            }

            this.$refs.errorAuxiliaryBook.reset();

            var acc = this.allAccounts ? "" : this.account_id;

            return (
                this.url +
                (this.year_init + "-" + this.month_init) +
                "/" +
                this.currency_id +
                "/" +
                acc
            );
        },

        /**
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
        getUrlReportSign: function () {
            var errors = [];
            if (!this.allAccounts && this.account_id <= 0) {
                errors.push("Debe seleccionar una cuenta.");
            }

            if (!this.currency_id) {
                errors.push("El tipo de moneda es obligatorio.");
            }

            if (errors.length > 0) {
                this.$refs.errorAuxiliaryBook.showAlertMessages(errors);
                return;
            }

            this.$refs.errorAuxiliaryBook.reset();
            var acc = this.account_id == 0 && this.allAccounts ? "" : "0";
            return (
                this.urlSign +
                (this.year_init + "-" + this.month_init) +
                "/" +
                this.currency_id +
                "/" +
                acc
            );
        },
    },
    watch: {
        check_sel_all: function () {
            this.checkAll();
        },
    },
};
</script>
