<template>
    <div>
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
        >
            <div slot="name" slot-scope="props" class="text-left">
                {{ props.row.name }}
            </div>
            <div slot="created_at" slot-scope="props" class="text-center">
                {{ props.row.created_at }}
            </div>
            <div slot="interval" slot-scope="props" class="text-center">
                {{ props.row.interval }}
            </div>
            <div slot="range" slot-scope="props" class="text-center">
                <strong>{{ rangeOfReport(props.row.url) }} </strong>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <a
                    class="btn btn-primary btn-xs btn-icon"
                    data-toggle="tooltip"
                    v-has-tooltip
                    :href="getUrlReport(props.row.url, props.row.id)"
                    title="Generar Reporte"
                    target="_blank"
                >
                    <i class="fa fa-print" style="text-align: center"></i>
                </a>
            </div>
        </v-client-table>
    </div>
</template>
<script>
export default {
    data() {
        return {
            records: [],
            url: `${window.app_url}/accounting/report/`,
            columns: [
                "institution_name",
                "name",
                "created_at",
                "range",
                "interval",
                "id",
            ],
        };
    },
    created() {
        this.table_options.headings = {
            institution_name: "INSTITUCIÓN",
            created_at: "FECHA DE GENERACIÓN",
            interval: "TIEMPO TRANSCURRIDO",
            name: "TIPO DE REPORTE",
            range: "RANGO DEL REPORTE",
            id: "ACCIÓN",
        };
        this.table_options.sortable = ["created_at", "interval", "name"];
        this.table_options.filterable = [];
        this.table_options.columnsClasses = {
            institution_name: "col-xs-4",
            name: "col-xs-2",
            created_at: "col-xs-1",
            range: "col-xs-2",
            interval: "col-xs-2",
            id: "col-xs-1",
        };
    },
    mounted() {
        this.loadRecords();
    },
    methods: {
        /**
         * Obtiene los registros de asientos contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        loadRecords() {
            axios
                .post(`${window.app_url}/accounting/get_report_histories`)
                .then((response) => {
                    this.records = response.data.report_histories;
                });
        },

        getUrlReport(reportUrl, reportId) {
            return this.url + reportUrl.split("/")[0] + "/" + reportId;
        },

        /**
         * [rangeOfReport calcula el rango de las fechas que abarca el reporte]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {String} url [url del reporte con informacion del reporte]
         */
        rangeOfReport(url) {
            var aux = url.split("/");
            var auxDate = "";
            var endDate = null;
            var initDate = null;

            for (var i = 0; i < aux.length; i++) {
                if (aux[i].split("-").length > 1) {
                    if (!initDate) {
                        initDate = aux[i];
                    } else {
                        endDate = aux[i];
                    }
                }
            }

            /*
             * Se cambia el formato a dd/mm/YYYY
             */
            if (initDate.split("-")[0].length > 2) {
                for (var i = initDate.split("-").length - 1; i >= 0; i--) {
                    var f =
                        i != 0
                            ? initDate.split("-")[i] + "-"
                            : initDate.split("-")[i];
                    auxDate += f;
                }
                initDate = auxDate;
            }

            /*
             * Se cambia el formato a dd/mm/YYYY
             */
            if (endDate && endDate.split("-")[0].length > 2) {
                auxDate = "";
                for (var i = endDate.split("-").length - 1; i >= 0; i--) {
                    var f =
                        i != 0
                            ? endDate.split("-")[i] + "-"
                            : endDate.split("-")[i];
                    auxDate += f;
                }
                endDate = auxDate;
            }
            return endDate ? initDate + " al " + endDate : "al " + initDate;
        },
    },
};
</script>
