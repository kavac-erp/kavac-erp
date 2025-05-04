<template>
    <section id="PayrollReportConceptsForm">
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
            <h6>Parámetros</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Trabajador</label>
                        <v-multiselect track_by="text"
                            :options="payroll_staffs"
                            v-model="record.payroll_staffs">
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Parentesco</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_relationships"
                            v-model="record.payroll_relationships"
                        >
                        </v-multiselect>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button @click.prevent="createReport('family-burden')"
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
                    payroll_staffs: [],
                    payroll_relationships: [],
                },

                errors: [],
                payroll_relationships: [],
                payroll_staffs: [],

            }
        },
        methods: {
            reset() {
                this.record = {
                    id: '',
                    payroll_concept_types: [],
                    payroll_payment_types: [],
                    payroll_concepts: [],
                };
            },

            createReport(current) {
                const vm = this;

                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields['current'] = 'family-burden';
                axios.post(`${window.app_url}/payroll/reports/${current}/create`, fields).then(response => {
                    if (response.data.result == false)
                        location.href = response.data.redirect;
                    else if (typeof(response.data.redirect) !== "undefined") {
                        window.open(response.data.redirect, '_blank');
                    } else if (response.data.result == 'empty') {
                        vm.showMessage(
                            'custom', 'Alerta!', 'danger', 'screen-error',
                            'No existen registros para los parámetros consultados'
                        );
                    } else {
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

            addAllToOptions() {
                const vm = this;
                vm.payroll_staffs = vm.payroll_staffs.filter(el => el.id != '');
                vm.payroll_staffs.unshift({'id':'todos', 'text':'Todos'});

                vm.payroll_relationships = vm.payroll_relationships.filter(el => el.id != '');
                vm.payroll_relationships.unshift({'id':'todos', 'text':'Todos'});
            },
        },
        async mounted() {
            const vm = this;
            await vm.getPayrollStaffs();
            await vm.getPayrollRelationships();
            await vm.addAllToOptions();
        },
    };
</script>
