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
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Nombre del concepto</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_concepts"
                            v-model="record.payroll_concepts"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Tipo de concepto</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_concept_types"
                            v-model="record.payroll_concept_types"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required" style="z-index: unset">
                        <label>Tipo de nómina</label>
                        <v-multiselect
                            track_by="text"
                            :options="payroll_payment_types"
                            v-model="record.payroll_payment_types"
                        >
                        </v-multiselect>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button @click.prevent="exportReport()"
                class="btn btn-primary btn-sm" data-toggle="tooltip" title="Generar archivo .xlsx"
                type="button">
                <span>Generar archivo .xlsx</span>
                <i class="fa fa-file-pdf-o"></i>
            </button>
            <button @click.prevent="createReport('concepts')"
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
                    payroll_concept_types: [],
                    payroll_payment_types: [],
                    payroll_concepts: [],

                },

                records: [],
                errors: [],
                columns: ['payroll_concept_type_id', 'payroll_payment_type_id', 'payroll_concept_id'],
                payroll_concept_types: [],
                payroll_payment_types: [],
                payroll_concepts: [],

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
                fields['current'] = 'concepts';
                axios.post(`${window.app_url}/payroll/reports/${current}/create`, fields).then(response => {
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

            async exportReport() {
                const vm = this;
                let postData = {
                    payroll_concepts: vm.record.payroll_concepts,
                    payroll_concept_types: vm.record.payroll_concept_types,
                    payroll_payment_types: vm.record.payroll_payment_types
                };

                await axios.get(`${window.app_url}/payroll/report-concepts/export`, {params: postData}).then(response => {
                    window.location.reload();
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
                vm.payroll_concept_types = vm.payroll_concept_types.filter(el => el.id != '');
                vm.payroll_concept_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_payment_types = vm.payroll_payment_types.filter(el => el.id != '');
                vm.payroll_payment_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_concepts = vm.payroll_concepts.filter(el => el.id != '');
                vm.payroll_concepts.push({'id':'todos', 'text':'Todos'});

            },
        },
        async mounted() {
            const vm = this;
            await vm.getPayrollConceptTypes();
            await vm.getPayrollPaymentTypes();
            await vm.getPayrollConcepts();
            await vm.addAllToOptions();
        },
    };
</script>
