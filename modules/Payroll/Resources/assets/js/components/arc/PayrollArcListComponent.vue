<template>
    <section>
        <div class="card-body">
            <section id="payrollArcSearch">
                <div class="row">
                    <div class="col-md-6" id="helpFiscalYear">
                        <div class="form-group">
                            <label>Período fiscal:</label>
                            <select2
                                :options="fiscal_years"
                                v-model="record.fiscal_year"></select2>
                        </div>
                    </div>
                    <div class="col-md-6" id="helpStaff">
                        <div class="form-group">
                            <label>Trabajador:</label>
                            <v-multiselect track_by="text" :options="payroll_staffs"
                                :hide_selected="false"
                                :close_on_select="false"
                                v-model="record.payroll_staffs">
                            </v-multiselect>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class='btn btn-sm btn-info btn-custom float-right' data-toggle="tooltip"
                                @click="filterRecords()"
                                title="Buscar registros">
                                Buscar
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </section>
            <section id="payrollArcTable">
                <hr>
                <v-server-table
                    :columns="columns"
                    :url="'payroll/arc/registers'"
                    :options="table_options"
                    ref="tableResults">
                    <div slot="payroll_staff.worksheet_code" slot-scope="props" class="text-center">
                        {{ props.row.payroll_staff.worksheet_code ? props.row.payroll_staff.worksheet_code : 'No Registrado' }}
                    </div>
                    <div slot="arc" slot-scope="props" class="text-center">
                        {{ formatToCurrency(props.row.arc, props.row.symbol) }}
                    </div>
                    <div slot="remuneration_paid" slot-scope="props" class="text-center">
                        {{ formatToCurrency(props.row.remuneration_paid, props.row.symbol) }}
                    </div>
                    <div slot="id" slot-scope="props" class="text-center">
                        <button @click.prevent="
                            setDetails(
                                'PayrollArcInfo',
                                props.row.id,
                                'PayrollArcInfo'
                            )
                            " class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" title="Ver registro"
                            aria-label="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button
                            @click="downloadFile([props.row.id], props.row.year)" class="btn btn-primary btn-xs btn-icon btn-action"
                            data-toggle="tooltip" title="Descargar planilla del registro" data-placement="bottom" type="button">
                            <i class="fa fa-download"></i>
                        </button>
                        <button
                            @click="sendFile([props.row.id], props.row.year)" class="btn btn-warning btn-xs btn-icon btn-action"
                            data-toggle="tooltip" title="Enviar planilla del registro" data-placement="bottom" type="button">
                            <i class="fa fa-send"></i>
                        </button>
                    </div>
                </v-server-table>
                <payroll-arc-info ref="PayrollArcInfo">
                </payroll-arc-info>
            </section>
        </div>
        <div class="card-footer text-right" v-if="$refs.tableResults && $refs.tableResults.data.length > 0">
            <button @click.prevent="downloadFile(record.payroll_staffs.map(item => item.id), record.fiscal_year, true)"
                class="btn btn-primary btn-sm" data-toggle="tooltip" title="Descargar todos los registros"
                type="button">
                <i class="fa fa-download"></i>
                <span>Descargar</span>
            </button>
            <button @click.prevent="sendFile(record.payroll_staffs.map(item => item.id), record.fiscal_year)"
                class="btn btn-warning btn-sm" data-toggle="tooltip" title="Enviar todos los registros"
                type="button">
                <i class="fa fa-send"></i>
                <span>Enviar</span>
            </button>
        </div>
    </section>
</template>
<script>
export default {
    data() {
        return {
            record: {
                id: '',
                payroll_staffs: [],
                fiscal_year: ''
            },
            records: [],
            columns: ['payroll_staff.id_number','payroll_staff.worksheet_code', 'payroll_staff.name', 'arc', 'remuneration_paid', 'id'],
            fiscal_years: [],
            payroll_staffs: [],
        }
    },

    created() {
        const vm = this;
        vm.table_options.headings = {
            'payroll_staff.id_number': 'Cédula de Identidad',
            'payroll_staff.worksheet_code': 'Ficha',
            'payroll_staff.name': 'Trabajador',
            'arc': 'Retención Acumulada',
            'remuneration_paid': 'Total Remuneración Pagadas',
            'id': 'Acción'
        };
        vm.table_options.sortable = ['payroll_staff.id_number', 'payroll_staff.worksheet_code', 'payroll_staff.name', 'arc', 'remuneration_paid'];
        vm.table_options.filterable = ['payroll_staff.id_number', 'payroll_staff.worksheet_code', 'payroll_staff.name', 'arc', 'remuneration_paid'];
        vm.table_options.requestFunction = function(data) {
            let filters = {
                fiscal_year: vm.record.fiscal_year,
                payroll_staffs: vm.record.payroll_staffs ? vm.record.payroll_staffs.map(item => item.id) : [],
                query: data.query,
                limit: data.limit,
                ascending: data.ascending,
                page: data.page,
                orderBy: data.orderBy
            };
            return axios.get(vm.setUrl('payroll/arc/registers'), {
                params: filters
            }).catch(error => {
                if (typeof(error.response.data.message) !== "undefined") {
                    vm.showMessage(
                    'custom', 'Error', 'danger', 'screen-error', error.response.data.message
                    );
                } else {
                    console.log(error);
                }
            });
        };
    },
    async mounted() {
        const vm = this;
        vm.getFiscalYears();
        await vm.getPayrollStaffs();
        await vm.addAllToOptions();
    },

    methods: {
        /**
         * Listado de años fiscales
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
         async getFiscalYears() {
            const vm = this;
            const url = vm.setUrl('fiscal-years/list');
            await axios.get(url).then(response => {
                vm.fiscal_years = response.data.records;
            }).catch(error => {
                console.error(error);
            });
        },
        addAllToOptions() {
            const vm = this;
            vm.payroll_staffs = vm.payroll_staffs.filter(el => el.id != '');
            vm.payroll_staffs.unshift({ 'id': 'todos', 'text': 'Todos' });

            vm.fiscal_years = vm.fiscal_years.filter(el => el.id != '');
            vm.fiscal_years.unshift({ 'id': '', 'text': 'Seleccione...' });
        },
        async filterRecords() {
            const vm = this;
            vm.loading = true;
            await this.$refs.tableResults.refresh();
            vm.loading = false;
        },
        setDetails(ref, id, modal, var_list = null) {
            const vm = this;
            if (var_list) {
                for (var i in var_list) {
                    vm.$refs[ref][i] = var_list[i];
                }
            } else {
                vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                    return r.id === id;
                })[0];
            }
            $(`#${modal}`).modal('show');
        },
        downloadFile(payroll_staffs, fiscal_year, withZip = false) {
            const vm = this;
            if (withZip) {
                vm.loading = true;
                axios.get(`${window.app_url}/payroll/arc/export`, {
                    params: {
                        fiscal_year: fiscal_year,
                        payroll_staffs: payroll_staffs,
                        with_zip: true
                    }
                }).then(response => {
                    vm.showMessage(
                        'custom', 'Éxito', 'primary', 'screen-ok',
                        'Su solicitud esta en proceso, esto puede tardar unos ' +
                        'minutos. Se le notificara al terminar la operación',
                    );
                })
                .catch(error => {
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
                            vm.logs("modules/Payroll/Resources/assets/js/components/PayrollArcListComponent.vue", 143, error, "sendFile");
                        }
                    }
                });
                vm.loading = false;
                return;
            }

            let payrollStaffs = JSON.stringify(payroll_staffs);
            location.href = `${window.app_url}/payroll/arc/export?fiscal_year=${fiscal_year}&payroll_staffs=${payrollStaffs}`;
        },
        async sendFile(payroll_staffs, fiscal_year) {
            const vm = this;
            vm.loading = true;
            await axios.post(`${window.app_url}/payroll/arc/send`, {
                fiscal_year: fiscal_year,
                payroll_staffs: payroll_staffs
            }).then(response => {
                vm.showMessage(
                    'custom', 'Éxito', 'primary', 'screen-ok',
                    'Su solicitud esta en proceso, esto puede tardar unos ' +
                    'minutos. Se le notificara al terminar la operación',
                );
            })
            .catch(error => {
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
                        vm.logs("modules/Payroll/Resources/assets/js/components/PayrollArcListComponent.vue", 143, error, "sendFile");
                    }
                }
            });
            vm.loading = false;
        }
    }
};
</script>
