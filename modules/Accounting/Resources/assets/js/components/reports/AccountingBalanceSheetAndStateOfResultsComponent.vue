<template>
    <div class="form-horizontal">
        <div class="card-body">
            <accounting-show-errors :ref="type_report" />
            <div class="row">
                <div class="col-3" :id="'help'+this.type_report+'InitDate'">
                    <div class="form-group">
                        <label class="control-label">Mes</label>
                        <select2 :options="months" v-model="month_init"></select2>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Año</label>
                        <select2 :options="years" v-model="year_init"></select2>
                    </div>
                </div>
                <div class="col-3" :id="'help'+this.type_report+'Level'">
                    <div class="form-group">
                        <label class="control-label">Nivel de consulta</label>
                        <select2 :options="levels" v-model="level"></select2>
                    </div>
                </div>
                <div class="col-3" :id="'help'+this.type_report+'Currency'">
                    <div class="form-group">
                        <label class="is-required control-label">Tipo de moneda</label>
                        <select2 :options="currencies" v-model="currency_id"></select2>
                    </div>
                </div>
                <!-- <div class="col-3" :id="'help'+this.type_report+'AllAccount'">
                    <div class="form-group">
                        <label class="text-center"><strong>Mostrar valores en cero</strong></label>
                        <div class="custom-control custom-switch mt-4" data-toggle="tooltip" 
                             title="Seleccionar para mostrar valores de cuentas en cero">
                          <input type="checkbox" class="custom-control-input" :id="type_report+'BalanceResultsZero'"  
                                 :name="type_report+'BalanceResultsZero'" v-model="zero_accounts">
                          <label class="custom-control-label" :for="type_report+'BalanceResultsZero'"></label>
                        </div>
                    </div>
                </div> -->
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary btn-sm" @click="OpenPdf(getUrlReport(), '_blank')" :id="'help'+this.type_report+'GenerateReport'">
                    Generar Reporte <i class="fa fa-print"></i>
                </button>
                <!-- <button class="btn btn-primary btn-sm" @click="OpenPdf(getUrlReportSign(), '_blank')" :id="'help'+this.type_report+'GenerateReportSign'">
                    Generar y firmar Reporte <i class="fa fa-print"></i>
                </button> -->
            </div>
        </div>
    </div>
</template>
<script>
export default {
  props: {
    type_report: {
      type: String,
      default: ''
    },
    year_old: {
      type: String,
      default: ''
    },
  },
  data() {
    return {
      level: 1,
      levels: [
        { id: 1, text: 'Nivel 1' },
        { id: 2, text: 'Nivel 2' },
        { id: 3, text: 'Nivel 3' },
        { id: 4, text: 'Nivel 4' },
        { id: 5, text: 'Nivel 5' },
        { id: 6, text: 'Nivel 6' },
        { id: 7, text: 'Nivel 7' },
      ],
      url: `${window.app_url}/accounting/report/`,
      urlSign: `${window.app_url}/accounting/report/`,
      currencies: [],
      currency_id: '',
      zero_accounts: false,
    };
  },
  created() {
    this.getCurrencies();
    this.CalculateOptionsYears(this.year_old);
    this.url += this.type_report + '/pdf/';
    this.urlSign += this.type_report + 'Sign/pdf/';
  },
  methods: {

    /**
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
    getUrlReport: function() {

      var errors = [];
      if (!this.currency_id) {
        errors.push('El tipo de moneda es obligatorio.');
      }

      if (errors.length > 0) {
        this.$refs[this.type_report].showAlertMessages(errors);
        return;
      }
      this.$refs[this.type_report].reset();

      var zero = this.zero_accounts ? 'true' : '';
      return (this.url + (this.year_init + '-' + this.month_init)) + '/' + this.level + '/' + this.currency_id + '/' + zero;
    },

    /**
         * Formatea la url para el reporte
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
         * @return {string} url para el reporte
         */
    getUrlReportSign: function() {

      var errors = [];
      if (!this.currency_id) {
        errors.push('El tipo de moneda es obligatorio.');
      }

      if (errors.length > 0) {
        this.$refs[this.type_report + 'Sign'].showAlertMessages(errors);
        return;
      }

      this.$refs[this.type_report].reset();

      var zero = this.zero_accounts ? 'true' : '';
      return (this.urlSign + (this.year_init + '-' + this.month_init)) + '/' + this.level + '/' + this.currency_id + '/' + zero;
    }
  }
};
</script>
