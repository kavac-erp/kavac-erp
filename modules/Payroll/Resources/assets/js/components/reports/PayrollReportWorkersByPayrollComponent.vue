<template>
    <section id="PayrollReportWorkersByPayrollComponent">
        <div class="card-body">
            <!-- mensajes de error -->
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
            <!-- ./mensajes de error -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group is-required">
                        <strong>Tipo de nómina</strong>
                        <v-multiselect track_by="text" :options="payroll_payment_types"
                            v-model="record.payroll_payment_types">
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-12">
                    <strong class="mt-4">Periodo</strong>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: 100;">
                        <label>Desde:</label>
                        <input type="date" class="form-control input-sm" v-model="record.start_date"
                            @change="validateStartDate()" :max="record.end_date" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: 100;">
                        <label>Hasta:</label>
                        <input type="date" class="form-control input-sm" v-model="record.end_date"
                            :min="record.start_date" />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel-legend">
                        <div>
                            <strong>Leyenda</strong>
                        </div>
                        <div>
                            <span>Eje Y: cantidad de trabajadores</span>
                        </div>
                        <div>
                            <span>Eje X: fecha de corte del periodo</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center" v-for="(chartByType, index) in payroll_payment_types"
                v-show="showCharts" :key="chartByType.id">
                <div class="col-md-6 mt-4">
                    <payroll-graph-charts type="bar" :title="`Trabajadores en ${chartByType.text.split(' - ')[1]}`"
                        :ref="`chart_${index}`" :canvaId="`canva-id-${chartByType.id}`" />
                </div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button @click.prevent="filterRecords()" class="btn btn-primary btn-sm" data-toggle="tooltip"
                title="Generar Reporte" type="button">
                <span>Generar reporte</span>
                <i class="fa fa-file-pdf-o"></i>
            </button>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        institution_id: '',
    },
    data() {
        return {
            errors: [],
            record: {
                start_date: '',
                end_date: '',
                payroll_payment_types: []
            },
            showCharts: true,
            records: null,
            payroll_payment_types: [],
        }
    },
    methods: {
        reset() {
            this.record = {
                start_date: '',
                end_date: '',
                payroll_payment_types: [12],
            };
        },
        addAllToOptions() {
            const vm = this;
            vm.payroll_payment_types = vm.payroll_payment_types.filter(el => el.id != '');
            vm.payroll_payment_types.push({ 'id': 'todos', 'text': 'Todos' });
        },
        sortValuesForChart(data, labels) {
            let combinedArray = data.map(function (value, index) {
                return { value: value, date: labels[index] };
            });

            combinedArray.sort(function (a, b) {
                const dateA = new Date(a.date.split('/').reverse().join('/'));
                const dateB = new Date(b.date.split('/').reverse().join('/'));
                return dateA - dateB;
            });

            data = combinedArray.map(function (item) {
                return item.value;
            });

            labels = combinedArray.map(function (item) {
                return item.date;
            });

            return {
                data,
                labels
            }
        },
        filterRecords() {
            const vm = this;
            vm.showCharts = false;

            axios.post(`${window.app_url}/payroll/reports/workers-by-payroll/filter`, {
                start_date: vm.record.start_date,
                end_date: vm.record.end_date,
                payroll_payment_types: vm.record.payroll_payment_types,
            }).then(response => {
                vm.showCharts = true;
                let record = response.data.records

                Object.keys(response.data.records).forEach((key, index) => {
                    let { data, labels } = this.sortValuesForChart(
                        Object.values(record[key]),
                        Object.keys(record[key]).map((v) => moment(String(v)).format('DD/MM/YYYY'))
                    )

                    this.$refs["chart_" + index][0].updateChart(
                        data,
                        labels,
                    )
                });
            }).catch(error => {
                vm.errors = [];

                if (typeof (error.response) != "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }

            });
        },
        validateStartDate() {
            const vm = this;
            if (vm.record.end_date !== "" && vm.record.start_date > vm.record.end_date) {
                vm.record.start_date = vm.record.end_date
            }
        }
    },
    async mounted() {
        const vm = this;
        await vm.getPayrollPaymentTypes();
        await vm.addAllToOptions();

        vm.showCharts = false;
    },
}
</script>