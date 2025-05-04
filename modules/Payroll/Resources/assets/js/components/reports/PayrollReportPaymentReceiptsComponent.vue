<template>
    <section id="PayrollReportPaymentReceiptsForm">
        <div class="card-body" style="min-height: 400px;">
            <form-errors :listErrors="errors"></form-errors>

            <div class="row">
                <div class="col-12">
                    <strong>Filtros</strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Tipo de nómina</label>
                        <select2
                            :options="payroll_payment_types"
                            v-model="record.payroll_payment_type"
                            @input="setPeriods()"
                        />
                    </div>
                    <div class="form-group">
                        <label>Trabajador:</label>
                        <v-multiselect
                            track_by="text" :options="payroll_staffs" v-model="record.payroll_staffs"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Período <span class="ml-2" style="color:#FF3636;font-size:14px;">*</span></label>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="col-10">Períodos</th>
                                            <th class="col-2">Seleccione</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="periods.length > 0">
                                        <tr v-for="period in periods" :key="period.id">
                                            <td>
                                                <span>{{ period.start_day }} {{ format_date(period.start_date) }}</span>
                                                <span> - </span>
                                                <span>{{ period.end_day }} {{ format_date(period.end_date) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    :id="'period_' + period.id"
                                                    :name="'period_' + period.id"
                                                    :value="period.id"
                                                    @change="setConsultPeriod(period.id)"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="2" class="text-center">Sin Períodos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                @click.prevent="createReport()" class="btn btn-primary btn-sm" data-toggle="tooltip"
                title="Generar Reporte" type="button"
            >
                <span>Generar reporte</span>
                <i class="fa fa-file-pdf-o"></i>
            </button>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            errors: [],
            record: {
                period: '',
                payroll_payment_type: '',
                payroll_staffs: [],
            },
            payroll_staffs: [],
            payroll_payment_types: [],
            periods: [],
        };
    },
    props: {
        roles: {
            type: Array,
            required: true,
        },
        permissions: {
            type: Array,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        }
    },
    methods: {
        reset() {
            this.record = {
                period: '',
                payroll_payment_type: '',
                payroll_staffs: [],
            };
        },
        async setPeriods() {
            const vm = this;
            vm.periods = [];
            vm.record.period = '';
            if (!vm.record.payroll_payment_type) {
                return;
            }
            vm.loading = true;
            await axios.post(
                `${window.app_url}/payroll/get-payment-periods-by-status`,
                {
                    payment_type: vm.record.payroll_payment_type,
                    status: 'generated'
                }
            ).then(response => {
                vm.periods = response.data.records;
                console.log(vm.periods);
            }).catch(error => {
                console.error(error);
            });
            vm.loading = false;
        },
        setConsultPeriod(id) {
            $('input[type="checkbox"]').each(function() {
                const inputId = $(this).attr('id').replace('period_', '');
                if (inputId != id && $(this).is(':checked')) {
                    $(this).prop('checked', false);
                }
            });
            this.record.period = $('#period_' + id).is(':checked') ? id : '';
        },
        async createReport(current) {
            const vm = this;
            vm.loading = true;
            vm.errors = [];
            if (!vm.record.payroll_payment_type) {
                vm.errors.push('El tipo de nómina es obligatorio.');
            }
            if (!vm.record.period) {
                vm.errors.push('El período es obligatorio.');
            }
            if (vm.errors.length > 0) {
                vm.loading = false;
                return;
            }

            await axios.post(
                `${window.app_url}/payroll/reports/payment-receipts/create`,
                vm.record
            ).then(response => {
                if (!response.data.result) {
                    vm.errors = [
                        response.data.message
                    ];
                    vm.loading = false;
                    return;
                }
                vm.showMessage('success', response.data.message);
                //vm.reset();
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
            vm.loading = false;

        },
    },
    async mounted() {
        const vm = this;
        vm.loading = true;

        // Obtiene el listado de empleados
        let type = (
            vm.roles.filter(role => role.slug === 'admin' || role.slug === 'payroll').length === 0 &&
            vm.user?.employee_id
        ) ? vm.user.employee_id : '';
        await vm.getPayrollStaffs(type);

        vm.payroll_staffs = await vm.payroll_staffs.filter(el => el.id != '');

        if (type) {
            vm.payroll_staffs = await vm.payroll_staffs.filter(el => el.employee_id == type);
            if (vm.payroll_staffs.length === 1) {
                vm.record.payroll_staffs = await vm.payroll_staffs;
            }
        }

        // Obtiene el listado de tipos de nómina que tengan activa la opción de generar recibos de pago
        await vm.getPayrollPaymentTypes();
        vm.payroll_payment_types = await vm.payroll_payment_types.filter(el => el.id != '' && el.receipt);
        await vm.payroll_payment_types.unshift({'id':'', 'text':'Seleccione...'});

        vm.loading = false;
    },
};
</script>
