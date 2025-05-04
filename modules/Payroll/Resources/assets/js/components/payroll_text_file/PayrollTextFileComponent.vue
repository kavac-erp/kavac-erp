<template>
    <section id="PayrollTextFileComponent" style="overflow: overlay">

        <!-- Archivo de nómina -->
        <div class="card-body">

            <!-- mensajes de error -->
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <!-- mensajes de error -->

            <div class="row">
                <div class="col-4 mt-4">
                    <label for="fileName" class="is-required">Nombre del archivo</label>
                    <input type="text" class="form-control" id="fileName" v-model="record.fileName">
                </div>
                <div class="col-4 mt-4">
                    <label for="fileName" class="is-required">Número de archivo</label>
                    <input type="text" class="form-control" id="fileName" v-model="record.fileNumber">
                </div>
                <div id="date" class="col-4 mt-4">
                    <label for="periodStartDate" class="is-required">Fecha de pago</label>
                    <div class="input-group input-sm">
                        <span class="input-group-addon">
                            <i class="now-ui-icons ui-1_calendar-60"></i>
                        </span>
                        <input type="date" class="form-control no-restrict" data-toggle="tooltip" title="Desde la fecha"
                            v-model="record.date" id="periodStartDate" placeholder="Fecha">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 mt-4">
                    <label for="payroll" class="is-required">Nómina</label>
                    <v-multiselect :options="closedPayrollList" track_by="text" :hide_selected="false"
                        v-model="record.payrollId" :group_values="'group'" :group_label="'label'" style="margin-top: -5px;">
                    </v-multiselect>
                </div>
            </div>
        </div>
        <!-- Archivo de nómina -->

        <div class="card-footer text-right">
            <button type="button" @click="createRecord('payroll/validate-txt-data')" data-toggle="tooltip"
                title="Generar archivo txt de nómina" class="btn btn-primary btn-sm">
                <span>Generar archivo txt de nómina</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
    </section>
</template>
<script>

export default {
    props: {
        payroll_text_file_id: {
            type: Number,
            required: false,
            default: ''
        }
    },

    data() {
        return {
            errors: [],
            paymentTypes: [],
            // bankAccounts: [],
            closedPayrollList: [],
            records: [],
            record: {
                id: '',
                fileName: '',
                date: '',
                fileNumber: '',
                payrollId: '',
            }
        };
    },

    async created() {
        const vm = this;
        try {
            let payments = await axios.get(`${window.app_url}/payroll/get-payroll-payment-types`);
            let payrollList = await axios.get(`${window.app_url}/payroll/get-payroll-list`);

            vm.paymentTypes = payments.data.payment_types;
            vm.closedPayrollList = payrollList.data.payroll_list;
            if (vm.payroll_text_file_id) {
                await vm.editRecord(vm.payroll_text_file_id);
            }

        } catch (error) {
            console.log(error);
        }
    },

    async mounted() {
        const vm = this;

    },

    methods: {
        async reset() {
            const vm = this;
            vm.record = {
                id: '',
                fileName: '',
                paymentTypeId: '',
                bankAccountId: '',
                date: '',
                fileNumber: '',
                payrollId: '',
            }
        },
        async editRecord(id) {
            const vm = this;
            try {
                let response = await axios({
                    url: `${window.app_url}/payroll/get-edit-text-file-record/${id}`,
                    method: "get",
                });
                vm.record.payrollId = response.data.record.payroll_id;
                vm.record.fileName = response.data.record.file_name;
                vm.record.fileNumber = response.data.record.file_number;
                vm.record.date = response.data.record.payment_date;
                vm.record.id = response.data.record.id;
            } catch (error) {
                console.log(error);
            }

        },
        async createRecord(url) {
            const vm = this;
            try {
                const validateResponse = await axios.post(`${window.app_url}/payroll/validate-txt-data`, vm.record);
                if (validateResponse.status == 200) {
                    const response = await axios.get(`${window.app_url}/payroll/generate-txt`, {
                        responseType: 'blob', params: {
                            id: this.record.id,
                            fileName: this.record.fileName,
                            paymentTypeId: this.record.paymentTypeId,
                            bankAccountId: this.record.bankAccountId,
                            date: this.record.date,
                            fileNumber: this.record.fileNumber,
                            payrollId: this.record.payrollId,
                        }
                    });
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');

                    link.setAttribute('href', url);
                    link.setAttribute('download', `${this.record.fileNumber}_${this.record.fileName}.txt`);
                    link.setAttribute('target', '_blank');
                    link.click();

                    window.URL.revokeObjectURL(url);
                }

            } catch (error) {
                vm.errors = [];
                for (var index in error.response.data.errors) {
                    if (error.response.data.errors[index]) {
                        vm.errors.push(error.response.data.errors[index][0]);
                    }
                }
                console.log(error.response.data.errors);
            }
        },
    },
};
</script>
