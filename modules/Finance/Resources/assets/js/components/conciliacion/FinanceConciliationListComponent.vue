<template>
    <v-client-table :columns="columns" :data="records" :options="table_options">
        <div slot="code" slot-scope="props" class="text-center">
            <span>{{ props.row.code }}</span>
        </div>
        <div slot="bank" slot-scope="props" class="text-left">
            <span>{{ props.row.finance_bank_account.bank.name }}</span>
        </div>
        <div slot="system_balance" slot-scope="props" class="text-center">
            <span>{{ props.row.system_balance ?? 0 }} {{ props.row.currency.symbol }}</span>
        </div>
        <div slot="bank_balance" slot-scope="props" class="text-center">
            <span>{{ props.row.bank_balance }} {{ props.row.currency.symbol }}</span>
        </div>
        <div slot="document_status" slot-scope="props">
            <span class="text-success" v-if="props.row.document_status.action === 'AP'">
                {{ props.row.document_status.name }}
            </span>
            <span class="text-warning" title="Este registro puede ser aprobado desde asientos contables"
                v-else-if="props.row.document_status.action === 'PR'">
                Pendiente
            </span>
            <span class="text-danger" v-else-if="props.row.document_status.action === 'AN'">
                {{ props.row.document_status.name }}
            </span>
        </div>
        <div slot="id" slot-scope="props" class="text-center">
            <div class="d-inline-flex">
                <button class="btn btn-success btn-xs btn-icon btn-action" title="Aprobar Registro" data-toggle="tooltip"
                    :disabled="props.row.document_status.action === 'AP'" v-has-tooltip
                    @click="approve(props.index)">
                    <i class="fa fa-check"></i>
                </button>
                <finance-conciliacion-show-modal
                    modal_name="show_finance_conciliation_"
                    :id="props.row.id"
                    :route_show="
                        app_url + '/finance/conciliation/' + props.row.id
                    "
                />
                <button
                    @click="editForm(props.row.id)"
                    class="btn btn-warning btn-xs btn-icon btn-action"
                    title="Modificar registro"
                    data-toggle="tooltip"
                    v-has-tooltip
                    v-if="props.row.document_status.action === 'PR'"
                >
                    <i class="fa fa-edit"></i>
                </button>
                <a
                    class="btn btn-primary btn-xs btn-icon"
                    data-toggle="tooltip"
                    title="Imprimir Registro"
                    v-has-tooltip
                    :href="`${urlPdf}/${props.row.id}`"
                    target="_blank"
                >
                    <i class="fa fa-print"></i>
                </a>
                <button @click="deleteRecord(props.row.id, '/finance/conciliation')"
                    :disabled="props.row.document_status.action === 'AP'"
                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro" data-toggle="tooltip"
                    type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </div>
    </v-client-table>
</template>

<script>
export default {
    data() {
        return {
            urlPdf: `${window.app_url}/finance/conciliation/pdf`,
            columns: ['code', 'bank', 'system_balance', 'bank_balance', 'start_date', 'end_date', 'document_status', 'id'],
            records: [],
            record: {
                account_id: '',
                month: '',
                year: '',
                file: '',
                coincidences: false,
            },
            accounts: [],
            errors: [],
            movements: [],
            months: [
                { "id": "", "text": "Seleccione..." },
                { "id": 1, "text": "Enero" },
                { "id": 2, "text": "Febrero" },
                { "id": 3, "text": "Marzo" },
                { "id": 4, "text": "Abril" },
                { "id": 5, "text": "Mayo" },
                { "id": 6, "text": "Junio" },
                { "id": 7, "text": "Julio" },
                { "id": 8, "text": "Agosto" },
                { "id": 9, "text": "Septiembre" },
                { "id": 10, "text": "Octubre" },
                { "id": 11, "text": "Noviembre" },
                { "id": 12, "text": "Diciembre" },
            ],
            years: [
                { "id": "", "text": "Seleccione..." },
            ],
            tableResults: false
        }
    },
    methods: {
        /**
         * Método que limpia todos los datos del formulario.
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            this.resetErrors();
        },

        resetErrors() {
            this.errors = [];
        },

        /**
         * Método que muestra la tabla de resultados.
         * 
         * @author  José Briceño <josejorgebriceno9@gmail.com>
         */
        async showTableResults() {
            const vm = this;

            try {
                let response = await axios.get(`${vm.app_url}/finance/conciliation/vueList`);
                console.log(response.data.records);
                vm.records = response.data.records;
            } catch (error) {
                console.log(error.response.data.errors);
                vm.errors = [];

                if (typeof (error.response) != "undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
            }
        },
        /**
         * Se aprueba conciliacion bancaria
         *
         * @author Juan Rosas <juan.rosasr01@gmail.com>
         */
        approve(index) {
            var url = `${window.app_url}/finance/conciliation/approve`;
            var records = this.records;
            var confirmated = false;
            index = index - 1;
            const vm = this;

            if (vm.records[index].document_status.action != 'PR') {
                return;
            }

            bootbox.confirm({
                title: '¿Aprobar Conciliación?',
                message: '¿Esta seguro de aprobar esta conciliación?',
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                        className: 'btn btn-default btn-sm btn-round'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                        className: 'btn btn-primary btn-sm btn-round'
                    }
                },
                callback: function (result) {
                    if (result) {
                        confirmated = true;
                        vm.loading = true;

                        axios.post(url + '/' + records[index].id).then(response => {
                            if (typeof (response.data.error) !== 'undefined') {
                                /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                vm.showMessage('custom', 'Alerta!', 'danger', 'screen-error', response.data.message);
                                return false;
                            }

                            vm.showMessage('update');
                            vm.reload = true;

                            if (typeof (response.data.redirect) !== "undefined") {
                                location.href = response.data.redirect;
                            }

                            vm.loading = false;
                        }).catch(error => {
                            if (typeof (error.response) != "undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                                if (error.response.status == 500) {
                                    const messages = error.response.data.message;
                                    vm.showMessage(
                                        messages.type, messages.title, messages.class, messages.icon, messages.text
                                    );
                                }
                            }
                            vm.loading = false;
                            console.log(error);
                        });
                    }
                }
            });

            if (confirmated) {
                vm.records = records;
                vm.reload = true;
            }
        },
    },
    created() {
        this.table_options.headings = {
            'code': 'CÓDIGO',
            'bank': 'BANCO',
            'system_balance': 'SALDO EN SISTEMA',
            'bank_balance': 'SALDO EN BANCO',
            'start_date': 'INICIO DE PERIODO', 
            'end_date': 'FIN DE PERIODO',
            'document_status': 'ESTATUS',
            'id': 'ACCIÓN'
        };
        this.table_options.filterable = ['code', 'system_balance', 'bank_balance', 'start_date','end_date'];
        this.table_options.columnsClasses = {
            'code': 'text-center col-xs-1',
            'start_date': 'text-center col-xs-1',
            'end_date': 'text-center col-xs-1',
            'document_status': 'text-center col-xs-1',
            'id': 'text-center col-xs-2'
        };
    },
    async mounted() {
        await this.showTableResults();
    },
};
</script>
