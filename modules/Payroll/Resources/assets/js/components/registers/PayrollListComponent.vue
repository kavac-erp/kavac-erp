<template>
    <section id="payrollListComponent">
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button @click="exportRegister(props.row.id)" class="btn btn-primary btn-xs btn-icon btn-action"
                        data-toggle="tooltip" title="Generar archivo .xlsx" data-placement="bottom" type="button">
                        <i class="fa fa-file-excel-o"></i>
                    </button>
                    <button class="btn btn-primary btn-xs btn-icon btn-action" data-toggle="tooltip"
                        @click="props.row.payroll_payment_period.payroll_payment_type.skip_moments
                            ? 'javascript:void(0)'
                            : generatePdfReport(props.row.id)" title="Generar reporte presupuestario del registro"
                        data-placement="bottom" type="button"
                        :disabled="props.row.payroll_payment_period.payroll_payment_type.skip_moments">
                        <i class="fa fa-file-pdf-o"></i>
                    </button>
                    <button
                        v-if="(lastYear && format_date(props.row.payroll_payment_period.start_date, 'YYYY') <= lastYear)"
                        class="btn btn-warning btn-xs btn-icon btn-action" disabled type="button">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button v-else :disabled="(props.row.payroll_payment_period) &&
                        (props.row.payroll_payment_period.payment_status == 'generated' ||
                            props.row.payroll_payment_period.payment_status == 'approved')"
                        @click="editForm(props.row.id)" class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip" title="Modificar registro" data-placement="bottom" type="button">
                        <i class="fa fa-edit"></i>
                    </button>
                    <div v-if="props.row.payroll_payment_period.payroll_concept_account">
                        <span>
                            <button class="btn btn-success btn-xs btn-icon btn-action"
                                title="Solicitar Disponibilidad Presupuestaria"
                                data-toggle="tooltip"
                                v-has-tooltip
                                @click="props.row.payroll_payment_period.payroll_payment_type.skip_moments
                                 ? 'javascript:void(0)'
                                 : payrolConceptAccounts(props.row)"
                                :disabled="props.row.payroll_payment_period.payroll_payment_type.skip_moments">
                                <i class="fa fa-commenting"></i>
                            </button>
                        </span>
                    </div>
                    <div v-else>
                        <span v-if="(lastYear && format_date(props.row.payroll_payment_period.start_date, 'YYYY') <= lastYear) ||
                            (props.row.status != 'Completado') || (props.row.payroll_payment_period) &&
                                (props.row.payroll_payment_period.payment_status == 'generated' ||
                                    props.row.payroll_payment_period.payment_status == 'approved') ||
                                        props.row.payroll_payment_period.payroll_payment_type.skip_moments "
                        >
                            <button class="btn btn-success btn-xs btn-icon btn-action"
                                title="Solicitar Disponibilidad Presupuestaria" data-toggle="tooltip" v-has-tooltip disabled>
                                <i class="fa fa-commenting"></i>
                            </button>
                        </span>
                        <span v-else>
                            <send-custom-messages v-if="budget_exist && moment_close_permission != true "
                                :has_availability_request_permission="has_availability_request_permission"
                                :employments="employments" :id="props.row.id" :module="'payroll'"
                                :status="props.row.payroll_payment_period.payment_status" />
                        </span>
                    </div>
                    <button
                        v-if="props.row.payroll_payment_period
                        && props.row.payroll_payment_period.payment_status == 'pending'
                        && props.row.payroll_payment_period.availability_status == 'AP'"
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar Registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        @click="approved(props.row.id)"
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <button
                        v-else
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar Registro"
                        data-toggle="tooltip"
                        disabled
                        v-has-tooltip
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <button v-if="props.row.payroll_payment_period
                        && props.row.payroll_payment_period.payment_status == 'approved'"
                        class="btn btn-default btn-xs btn-icon btn-action"
                        title="Cerrar registro"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        @click="closeRecord(props.row.id)"
                        type="button">
                        <i class="fa fa-unlock-alt"></i>
                    </button>
                    <button v-else
                        class="btn btn-default btn-xs btn-icon btn-action"
                        title="Cerrar registro"
                        disabled
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button">
                        <i class="fa fa-unlock-alt"></i>
                    </button>
                    <button v-if="props.row.payroll_payment_period.payroll_payment_type.receipt
                        && props.row.payroll_payment_period
                        && props.row.payroll_payment_period.payment_status == 'generated'"
                        @click="sendReceipts(props.row.id)" class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip" title="Enviar recibos" data-placement="bottom" type="button">
                        <i class="fa fa-send"></i>
                    </button>
                    <button v-else
                        disabled class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip" title="Enviar recibos" data-placement="bottom" type="button">
                        <i class="fa fa-send"></i>
                    </button>
                </div>
            </div>
            <div slot="created_at" slot-scope="props">
                {{ format_timestamp(props.row.created_at) }}
            </div>
            <div slot="payroll_payment_period" slot-scope="props" class="text-center">
                {{
                    props.row.payroll_payment_period
                    ? format_date(props.row.payroll_payment_period.start_date) + ' - ' +
                    format_date(props.row.payroll_payment_period.end_date)
                    : 'No definido'
                }}
            </div>
        </v-client-table>
    </section>
</template>
<script>
export default {
    props: {
        employments: {
            type: Array,
            default: function () {
                return [];
            }
        },
        budget_exist: {
            type: Number
        },
        moment_close_permission: {
            type: Boolean
        },
        has_availability_request_permission: {
            type: Boolean
        }
    },
    data() {
        return {
            record: {},
            records: [],
            lastYear: "",
            columns: [
                'code',
                'created_at',
                'name',
                'payroll_payment_period',
                'payroll_payment_period.payroll_payment_type.name',
                'id'
            ],
        }
    },
    async created() {
        const vm = this;
        vm.table_options.headings = {
            'code': 'Código',
            'created_at': 'Fecha de generación',
            'name': 'Nombre',
            'payroll_payment_period': 'Período de pago',
            'payroll_payment_period.payroll_payment_type.name': 'Tipo de nómina',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['code', 'created_at', 'name', 'payroll_payment_period'];
        vm.table_options.filterable = ['code', 'created_at', 'name', 'payroll_payment_period'];
    },
    async mounted() {
        const vm = this;
        await vm.initRecords(vm.route_list, '');
        await vm.queryLastFiscalYear();
    },
    methods: {
        async sendReceipts(payroll_id) {
            const vm = this;
            vm.loading = true;
            await axios.post(`${window.app_url}/payroll/send-payroll-payment-type-receipt/${payroll_id}`).then(response => {
                vm.showMessage(
                    'custom', 'Éxito', 'primary', 'screen-ok',
                    'Su solicitud esta en proceso, esto puede tardar unos ' +
                    'minutos. Se le notificara al terminar la operación',
                );
            })
            .catch(error => {
                // Handle the error, such as displaying an error message
                if (typeof error.response !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            "custom",
                            "Acceso Denegado",
                            "danger",
                            "screen-error",
                            "No dispone de permisos para acceder a esta funcionalidad."
                        );
                    } else {
                        vm.logs("modules/Payroll/Resources/assets/js/components/PayrollListComponent.vue", 143, error, "sendReceipts");
                    }
                }
            });
            vm.loading = false;
        },
        async generatePdfReport(id) {
            const vm = this;
            try {
                await axios.get(`${window.app_url}/payroll/get-budget-accounting-report/${id}`, { responseType: 'blob' })
                    .then(response => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'sample.pdf');
                        document.body.appendChild(link);
                        link.click();
                    })
                    .catch(error => {
                        // Handle the error, such as displaying an error message
                        if (typeof error.response !== "undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    "custom",
                                    "Acceso Denegado",
                                    "danger",
                                    "screen-error",
                                    "No dispone de permisos para acceder a esta funcionalidad."
                                );
                            } else {
                                vm.logs("resources/js/all.js", 343, error, "initRecords");
                            }
                        }
                    });
            } catch (error) {
                console.log(error);
            }
        },

        reset() {
            //
        },
        closeRecord(id) {
            const vm = this;
            bootbox.confirm({
                title: 'Cierre de nómina',
                message: '¿Está seguro de cerrar está nómina? Una vez cerrada, no se puede modificar este registro',
                callback: function (result) {
                    if (result) {
                        vm.loading = true;
                        axios.patch(`${window.app_url}/payroll/registers/close/${id}`, null)
                            .then(response => {
                                if (typeof (response.data.redirect) !== "undefined") {
                                    location.href = response.data.redirect;
                                }
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
                        vm.loading = false;
                    }
                }
            });
        },

        approved(id) {
            const vm = this;
            bootbox.confirm({
                title: 'Aprobar nómina',
                message: '¿Está seguro de aprobar está nómina? Una vez aprobada, no se puede modificar este registro',
                callback: function (result) {
                    if (result) {
                        vm.loading = true;
                        axios.put(`${window.app_url}/payroll/registers/approved/${id}`, null)
                            .then(response => {
                                if (typeof (response.data.redirect) !== "undefined") {
                                    location.href = response.data.redirect;
                                }
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
                        vm.loading = false;
                    }
                }
            });
        },

        exportRegister(id) {
            const vm = this;
            location.href = `${window.app_url}/payroll/registers/export/${id}`;
        },

        payrolConceptAccounts(item) {
            const vm = this;
            if (item.payroll_payment_period.payroll_concept_account == true) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "No se puede solicitar la disponibilidad presupuestaria " +
                        "debido a que los siguientes conceptos no tienen partidas presupuestarias asociadas: "
                            + item.payroll_payment_period.payroll_concepts.map(concept => concept.name) + "."
                );

                return;
            }
        },
    }
};
</script>
