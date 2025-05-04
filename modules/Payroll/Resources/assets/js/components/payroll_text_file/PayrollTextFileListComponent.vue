<template>
    <section id="payrollTextListComponent">
        <div class="card-body">
            <v-client-table :columns="columns" :data="records" :options="table_options" class="text-center">
                <div slot="created_at" slot-scope="props">
                    <span> {{ format_date(props.row.created_at) }}</span>
                </div>
                <div slot="payment_date" slot-scope="props">
                    <span> {{ format_date(props.row.payment_date) }}</span>
                </div>
                <div slot="id" slot-scope="props">
                    <button class="btn btn-warning btn-xs btn-icon btn-action" data-toggle="tooltip"
                        @click="editForm(props.row.id)" title="Modificar registro" data-placement="bottom" type="button">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.row.id, 'payroll/delete-text-file-record')"
                        class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro" data-toggle="tooltip"
                        type="button">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </v-client-table>
        </div>
    </section>
</template>
<script>

export default {
    data() {
        return {
            records: [],
            columns: ['created_at', 'file_name', 'file_number', 'payment_date', 'payroll_name', 'id'],
        };
    },

    async created() {
        const vm = this;
        try {
            await vm.getRecords();

            vm.table_options.headings = {
                id: 'Acción',
                created_at: 'Fecha de creación',
                file_name: 'Nombre del archivo',
                file_number: 'Número del archivo',
                payment_date: 'Fecha de pago',
                payroll_name: 'Nómina',
            };
            vm.table_options.sortable = ['file_name'];
            vm.table_options.filterable = ['file_name'];

        } catch (error) {
            console.log(error);
        }
    },

    async mounted() {

    },

    methods: {
        async editRecord(id) {
            try {
                await axios({
                    url: `${window.app_url}/payroll/edit-text-file-record/${id}`,
                    method: "get",
                });
            } catch (error) {
                console.log(error);
            }

        },
        async getRecords() {
            const vm = this;
            try {
                let response = await axios({
                    url: `${window.app_url}/payroll/get-text-file-records`,
                    method: "get",
                });
                vm.records = [...response.data.records];
            } catch (error) {
                console.log(error);
            }
        },
        /**
         * Método que envia la petición para generar el archivo de texto
         */
        async generateTextFile(record) {
            console.log(record);
            const vm = this;
            vm.errors = [];

            try {
                location.href = `${window.app_url}/payroll/generate-txt/${record.payment_type_id}/${record.bank_account_id}/${record.file_number}/${record.payment_date}/${record.file_name}/${record.payroll_id}`;

            } catch (error) {
                console.log(error);
            }
        }
    },
};
</script>