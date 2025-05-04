<template>
    <section id="PayrollReportTimeSheetsForm">
        <div class="card-body">
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
                <div class="col-md-12">
                    <strong>Filtros</strong>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Desde:</label>
                        <input
                            id="from_date"
                            type="date"
                            name="from_date"
                            class="form-control input-sm"
                            v-model="record.from_date"
                        />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Hasta:</label>
                        <input
                            id="to_date"
                            type="date"
                            name="to_date"
                            class="form-control input-sm"
                            v-model="record.to_date"
                        />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="z-index: unset">
                        <label>Código grupo de supervisados</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_supervised_groups"
                            v-model="record.payroll_supervised_groups"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="z-index: unset;">
                        <label>Trabajador:</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_staffs"
                            v-model="record.payroll_staffs"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Parámetro de hoja de tiempo</label>
                        <v-multiselect
                            track_by="text"
                            :hide_selected="false"
                            :options="payroll_time_parameters"
                            :group_values="'group'"
                            :group_label="'label'"
                            :group_select="true"
                            v-model="record.payroll_time_parameters"
                        >
                        </v-multiselect>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group" style="z-index: unset">
                        <label>Estatus</label>
                        <v-multiselect
                            track_by="text"
                            :options="document_status"
                            v-model="record.document_status"
                        >
                        </v-multiselect>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button @click.prevent="createReport()"
                class="btn btn-primary btn-sm" data-toggle="tooltip" title="Generar Reporte"
                type="button">
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
                record: {
                    id: '',
                    from_date: '',
				    to_date: '',
                    payroll_staffs: [],
                    document_status: [],
                    payroll_time_parameters: [],
                    payroll_supervised_groups: [],

                },

                records: [],
                errors: [],
                columns: ['payroll_concept_type_id', 'payroll_payment_type_id', 'payroll_concept_id'],
                payroll_staffs: [],
                document_status: [],
                payroll_time_parameters: [],
                payroll_supervised_groups: [],

            }
        },
        methods: {
            reset() {
                this.record = {
                    id: '',
                    from_date: '',
				    to_date: '',
                    payroll_staffs: [],
                    document_status: [],
                    payroll_time_parameters: [],
                    payroll_supervised_groups: [],
                };
            },

            async getPayrollTimeParameters() {
                const vm = this;

                vm.payroll_time_parameters = [];
                await axios.get(`${window.app_url}/payroll/get-time-parameters?setting=true`).then(response => {
                    vm.payroll_time_parameters = Object.values(response.data);
                });
            },

            createReport() {
                const vm = this;

                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }

                axios.post(`${window.app_url}/payroll/reports/time-sheets/create`, fields).then(response => {
                    if (response.data.result == false)
                        location.href = response.data.redirect;
                    else if (typeof(response.data.redirect) !== "undefined") {
                        window.open(response.data.redirect, '_blank');
                    }
                    else {
                        vm.reset();
                    }
                    vm.loading = false;
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                    vm.loading = false;
                });

            },

            async addAllToOptions() {
                const vm = this;
                vm.payroll_supervised_groups = vm.payroll_supervised_groups.filter(el => el.id != '');
                vm.payroll_supervised_groups.push({'id':'todos', 'text':'Todos'});

                vm.payroll_staffs = vm.payroll_staffs.filter(el => el.id != '');
                vm.payroll_staffs.push({'id':'todos', 'text':'Todos'});

                vm.payroll_time_parameters = vm.payroll_time_parameters.filter(el => el.id != '');
                vm.payroll_time_parameters.push({'id':'todos', 'text':'Todos'});
            },

            documentStatus() {
                const vm = this;
                vm.document_status = [
                    {'id':'0', 'text':'Seleccione...'},
                    {'id':'2', 'text':'Elaborado(a)'},
                    {'id':'4', 'text':'Aprobado(a)'},
                    {'id':'6', 'text':'Cerrado(a)'},
                    {'id':'todos', 'text':'Todos'},
                ];
            },
        },
        async mounted() {
            const vm = this;

            await vm.getPayrollSupervisedGroups();
            await vm.getPayrollStaffs();
            await vm.documentStatus();
            await vm.addAllToOptions();
            await vm.getPayrollTimeParameters();
        },
    };
</script>
