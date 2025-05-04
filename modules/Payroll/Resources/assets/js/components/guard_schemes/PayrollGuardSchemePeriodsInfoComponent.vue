<template>
    <div
        id="PayrollGuardSchemePeriodsInfo"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollGuardSchemePeriodsInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width:80%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Periodos del Esquema de Guardias
                    </h6>
                </div>
                <div class="modal-body">
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
                    <div class="row">
                        <!-- período a planificar -->
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Desde:</label>
                                <input type="date" id="from_date" placeholder="Desde"
                                        data-toggle="tooltip" title="Indique la fecha inicial del período a revisar"
                                        :min="minFromDatePeriod"
                                        :max="(period.to_date == '') ? record.to_date: period.to_date"
                                        class="form-control input-sm no-restrict" v-model="period.from_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label>Hasta:</label>
                                <input type="date" id="to_date" placeholder="Hasta"
                                        data-toggle="tooltip" title="Indique la fecha final período a revisar"
                                        :min="period.from_date"
                                        :max="record.to_date"
                                        :disabled="(period.from_date == '')"
                                        class="form-control input-sm no-restrict" v-model="period.to_date">
                            </div>
                        </div>
                        <!-- ./período a planificar -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
                                @click="clearFilters" data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" 
                                @click="reset()">
                            Cancelar
                        </button>
                        <button type="button" @click="createRecord('payroll/guard-schemes/periods')" 
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                            Guardar
                        </button>
                    </div>
                </div>

                <div class="modal-body modal-table">
                    <v-client-table ref="tableConfirmResults"
                        :columns="columns"
                        :data="record.payroll_guard_scheme_periods"
                        :options="table_options"
                        v-if="record.id">
                        <div slot="from_date" slot-scope="props" class="text-center">
                            <span>{{ format_date(props.row.from_date) }}</span>
                        </div>
                        <div slot="to_date" slot-scope="props" class="text-center">
                            <span>{{ format_date(props.row.to_date) }}</span>
                        </div>
                        <div slot="status" slot-scope="props" class="text-center">
                            <span v-if="props.row.document_status.action == 'EL'" class="text-warning">
                                {{ props.row.document_status.name }}
                            </span>
                            <span v-if="props.row.document_status.action == 'RE'" class="text-danger">
                                {{ props.row.document_status.name }}
                            </span>
                            <span v-else-if="props.row.document_status.action == 'PR'" class="text-info">
                                {{ props.row.document_status.name }}
                            </span>
                            <span v-else-if="props.row.document_status.action == 'AP'" class="text-success">
                                {{ props.row.document_status.name }}
                            </span>
                            <span v-else-if="props.row.document_status.action == 'CE'" :style="{color: props.row.document_status.color}">
                                {{ props.row.document_status.name }}
                            </span>
                        </div>
                        <div slot="id" slot-scope="props" class="text-center">
                            <button
                                v-if="request_review_permission"
                                class="btn btn-info btn-xs btn-icon btn-action"
                                title="Solicitar revision"
                                data-toggle="tooltip"
                                @click.prevent="'RE' != props.row.document_status.action
                                    ? 'javascript:void(0)' : requestReview(props.row.id)"
                                :disabled="'RE' != props.row.document_status.action"
                                type="button">
                                <i class="fa fa-search"></i>
                            </button>
                            <button
                                class="btn btn-warning btn-xs btn-icon btn-action"
                                title="Editar registro"
                                data-toggle="tooltip"
                                @click.prevent="['AP', 'CE'].includes(props.row.document_status.action)
                                    ? 'javascript:void(0)'
                                    : initUpdate(props.row.id, $event)"
                                :disabled="['AP', 'CE'].includes(props.row.document_status.action)"
                                type="button">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button
                                v-if="confirm_permission"
                                class="btn btn-default btn-xs btn-icon btn-action"
                                title="Confirmar registro"
                                data-toggle="tooltip"
                                @click.prevent="'AP' != props.row.document_status.action
                                    ? 'javascript:void(0)' : setDetails('GuardSchemePeriodConfirm', props.row.id ,'PayrollGuardSchemePeriodConfirm')"
                                :disabled="'AP' != props.row.document_status.action"
                                type="button">
                                <i class="fa fa-check"></i>
                            </button>
                        </div>
                    </v-client-table>
                    <payroll-guard-scheme-period-confirm ref="GuardSchemePeriodConfirm"></payroll-guard-scheme-period-confirm>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    id:                           '',
                    institution_id:               '',
                    from_date:                    '',
                    to_date:                      '',
                    payroll_supervised_group_id:  '',
                    payroll_supervised_group:     null,
                    data_source:                  {},
                    payroll_guard_scheme_periods: [],
                },
                confirm_value: 'total',
                period: {
                    from_date: '',
                    to_date: '',
                },
                errors: [],
                columns: [
                    'from_date',
                    'to_date',
                    'status',
                    'observations',
                    'id',
                ],
            }
        },
        props: {
            confirm_permission: String,
            request_review_permission: String,
        },
        methods: {
            reset() {
                const vm = this;
                vm.period = {
                    from_date: '',
                    to_date: '',
                };
                vm.confirm_value = 'total';
            },
            async createRecord(url, list = true, reset = true) {
                const vm = this;
                url = vm.setUrl(url);

                if (vm.period.id) {
                    vm.updateRecord(url);
                } else {
                    vm.loading = true;
                    var fields = {
                        from_date: vm.period.from_date,
                        to_date: vm.period.to_date,
                        payroll_guard_scheme_id: vm.record.id
                    };

                    await axios.post(url, fields).then(response => {
                        if ('undefined' !== typeof(response.data.record)) {
                            vm.record.payroll_guard_scheme_periods.push(response.data.record);
                            if (null == vm.record.document_status.pending_period) {
                                vm.record.document_status.pending_period = response.data.record;
                            }
                            vm.record.document_status.last_period = response.data.record;

                        }
                        vm.errors = [];
                        if (reset) {
                            vm.reset();
                        }
                        if (list) {
                            vm.$parent.readRecords(vm.$parent.route_list);
                        }

                        vm.showMessage('store');
                    }).catch(error => {
                        vm.errors = [];

                        if (typeof(error.response) !="undefined") {
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
            },
            async updateRecord(url, list = true, reset = true) {
                const vm = this;
                vm.loading = true;
                var fields = {};
                url = vm.setUrl(url);

                var fields = {
                    from_date: vm.period.from_date,
                    to_date: vm.period.to_date,
                    payroll_guard_scheme_id: vm.record.id
                };
                await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.period.id}`, fields).then(response => {
                    if ('undefined' !== typeof(response.data.record)) {
                        vm.$nextTick(() => {
                            let index = vm.record.payroll_guard_scheme_periods.findIndex((element) => element.id == response.data.record.id);
                            vm.removeRow(index, vm.record.payroll_guard_scheme_periods);
                            vm.record.payroll_guard_scheme_periods.push(response.data.record);
                            vm.record.payroll_guard_scheme_periods.sort((a, b) => new Date(a.to_date) - new Date(b.to_date));
                        });
                        if (
                            vm.record.document_status.last_period &&
                            vm.record.document_status.last_period.id &&
                            response.data.record.id == vm.record.document_status.last_period.id
                        ) {
                            vm.record.document_status.last_period = response.data.record;
                        }
                    }
                    vm.errors = [];
                    if (reset) {
                        vm.reset();
                    }
                    if (list) {
                        vm.$parent.readRecords(vm.$parent.route_list);
                    }

                    vm.showMessage('update');

                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
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
            },
            async initUpdate(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit = JSON.parse(JSON.stringify(vm.$refs.tableConfirmResults.data.filter(r => {
                    return r.id === id;
                })[0]));
                vm.period = recordEdit;

                event.preventDefault();
            },
            /**
             * Método que establece los datos del registro seleccionado para el
             * cual se desea mostrar detalles.
             *
             * @method    setDetails
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             * @param     string   ref       Identificador del componente
             * @param     integer  id        Identificador del registro seleccionado
             * @param     object  var_list  Objeto con las variables y valores a
             * asignar en las variables del componente
             */
            setDetails(ref, id, modal ,var_list = null) {
                const vm = this;
                if (var_list) {
                    for(var i in var_list){
                        vm.$refs[ref][i] = var_list[i];
                    }
                } else {
                    vm.$refs[ref].record = JSON.parse(JSON.stringify(vm.$refs.tableConfirmResults.data.filter(r => {
                        return r.id === id;
                    })[0]));
                }
                vm.$refs[ref].id = id;

                $(`#${modal}`).modal('show');
            },
            async requestReview(id) {
                const vm = this;
                const url = vm.setUrl(`payroll/guard-schemes/periods/request-review/${id}`);

                vm.loading = true;
                await axios.put(url).then(response => {
                    if (response.status == 200){
                        if ('undefined' !== typeof(response.data.record)) {
                            vm.$nextTick(() => {
                                let index = vm.record.payroll_guard_scheme_periods.findIndex((element) => element.id == response.data.record.id);
                                vm.removeRow(index, vm.record.payroll_guard_scheme_periods);
                                vm.record.payroll_guard_scheme_periods.push(response.data.record);
                                vm.record.payroll_guard_scheme_periods.sort((a, b) => new Date(a.to_date) - new Date(b.to_date));
                            });
                        }
                        vm.showMessage('update');
                        vm.$parent.readRecords(vm.$parent.route_list);
                    }
                }).catch(error => {
                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                    }
                });
                vm.loading = false;
            },
        },
        created() {
            const vm = this;
                vm.table_options.headings = {
                'from_date':    'Fecha de inicio',
                'to_date':      'Fecha de fin',
                'observations': 'Observaciones',
                'status':       'Estatus',
                'id':           'Acción'
            };
            vm.table_options.sortable   = ['from_date', 'to_date', 'observations', 'status'];
            vm.table_options.filterable = ['from_date', 'to_date', 'observations', 'status'];
        },
        computed: {
            minFromDatePeriod() {
                const vm = this;
                let date = '';
                if (vm.record.payroll_guard_scheme_periods && vm.record.document_status?.last_period?.to_date) {
                    return vm.add_period(vm.record.document_status.last_period.to_date, 1, 'days', 'YYYY-MM-DD');
                } else if (vm.record.from_date) {
                    return vm.record.from_date;
                } else {
                    return '';
                }
            }
        }
    }
</script>
