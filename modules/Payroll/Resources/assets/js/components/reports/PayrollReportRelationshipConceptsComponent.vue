<template>
    <section id="PayrollReportRelationshipConceptsForm">
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
                    <div class="form-group" style="z-index: unset">
                        <label>Tipo de pago</label>
                        <v-multiselect
                            @input="errors = []"
                            track_by="text"
                            :options="payroll_payment_types"
                            v-model="record.payroll_payment_types"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="z-index: unset">
                        <label>Tipo de concepto</label>
                        <v-multiselect
                            @input="errors = []"
                            track_by="text"
                            :options="payroll_concept_types"
                            v-model="record.payroll_concept_types"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="z-index: unset">
                        <label>Concepto</label>
                        <v-multiselect
                            @input="errors = []"
                            track_by="text"
                            :options="payroll_concepts"
                            v-model="record.payroll_concepts"
                        >
                        </v-multiselect>
                    </div>
                </div>   
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group" style="z-index: unset">
                        <label>Trabajador</label>
                        <v-multiselect
                            @input="errors = []"
                            track_by="text"
                            :options="payroll_staffs"
                            v-model="record.payroll_staffs"
                        >
                        </v-multiselect>
                    </div>
                </div> 
                <div class="col-md-4">
					<div class="form-group">
						<label>Desde:</label>
						<div class="input-group input-sm">
			                <span class="input-group-addon">
			                    <i class="now-ui-icons ui-1_calendar-60"></i>
			                </span>
			                <input type="date" data-toggle="tooltip" title="Indique la fecha minima de busqueda"
									   class="form-control input-sm no-restrict" v-model="record.start_date" 
                                       @input="errors = []">
			            </div>
		            </div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>Hasta:</label>
						<div class="input-group input-sm">
			                <span class="input-group-addon">
			                    <i class="now-ui-icons ui-1_calendar-60"></i>
			                </span>
			                <input type="date" data-toggle="tooltip" title="Indique la fecha maxima de busqueda"
									   class="form-control input-sm no-restrict" v-model="record.end_date" 
                                       @input="errors = []">
			            </div>
		            </div>
				</div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button @click.prevent="createReport('relationship-concepts')"
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
                    payroll_staffs: [],
                    start_date: '',
				    end_date: '',

                },

                records: [],
                errors: [],
                columns: ['payroll_concept_type_id', 'payroll_payment_type_id', 'payroll_concept_id', 'payroll_staff_id', ],
                payroll_concept_types: [],
                payroll_payment_types: [],
                payroll_concepts: [],
                payroll_staffs: [],

            }
        },
        methods: {
            reset() {
                this.errors = [];
                this.record = {
                    id: '',
                    payroll_concept_types: [],
                    payroll_payment_types: [],
                    payroll_concepts: [],
                    payroll_staffs: [],
                    start_date: '',
				    end_date: '',
                };
            },

            createReport(current) {
                const vm = this;

                vm.loading = true;
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                fields['current'] = 'relationship-concepts';
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

            addAllToOptions() {
                const vm = this;
                vm.payroll_concept_types = vm.payroll_concept_types.filter(el => el.id != '');
                vm.payroll_concept_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_payment_types = vm.payroll_payment_types.filter(el => el.id != '');
                vm.payroll_payment_types.push({'id':'todos', 'text':'Todos'});

                vm.payroll_concepts = vm.payroll_concepts.filter(el => el.id != '');
                vm.payroll_concepts.push({'id':'todos', 'text':'Todos'});

                vm.payroll_staffs = vm.payroll_staffs.filter(el => el.id != '');
                vm.payroll_staffs.push({'id':'todos', 'text':'Todos'});

            },
        },
        async mounted() {
            const vm = this;
            await vm.getPayrollConceptTypes();
            await vm.getPayrollPaymentTypes();
            await vm.getPayrollConcepts();
            await vm.getPayrollStaffs();
            await vm.addAllToOptions();
        },
    };
</script>
