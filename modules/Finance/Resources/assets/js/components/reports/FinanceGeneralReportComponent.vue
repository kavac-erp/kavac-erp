<template>
    <div class="form-horizontal">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
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
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                </ul>
            </div>
            <div class="row">
                <div class="col-3" id="reportType">
                    <div>
                        <label class="control-label is-required">Tipo de reporte</label>
                        <select2 :options="reportType" v-model="reportTypeId"></select2>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <label class="control-label"> Todos </label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="consolidated" :value="true"
                            v-model="all">
                        <label class="custom-control-label" for="consolidated"></label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div v-if="reportTypeId != 'movement'" class="col-3" id="receivers">
                    <div>
                        <label class="control-label">Proveerdor / Beneficiario</label>
                        <select2 :options="receivers" v-model="receiverId" :disabled="all"></select2>
                    </div>
                </div>
                <div class="col-3" id="documentStatus">
                    <div>
                        <label class="control-label">Estatus</label>
                        <select2 :options="documentStatusList" v-model="action" :disabled="all"></select2>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-3" id="initialDate">
                    <div class="is-required">
                        <label class="control-label">Fecha inicial</label>
                        <input type="date" class="form-control input-sm" v-model="dateIni">
                    </div>
                </div>
                <div class="col-3" id="finalDate">
                    <div class="is-required">
                        <label class="control-label">Fecha final</label>
                        <input type="date" class="form-control input-sm" v-model="dateEnd" :min="dateIni ? dateIni : ''"
                            :disabled="dateIni ? false : true">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" @click="generateReport()" data-toggle="tooltip" v-has-tooltip
                title="Generar Reporte" id="helpDailyBookGenerateReport">
                <span>Generar reporte</span>
                <i class="fa fa-print"></i>
            </button>
        </div>
    </div>
</template>
<script>

export default {
    data() {
        return {
            dateIni: '',
            dateEnd: '',
            currencies: [],
            currency_id: '',
            receivers: [],
            receiverId: '',
            documentStatusList: [],
            action: '',
            all: false,
            errors: [],
            reportType: [
                {id: '', text: 'Seleccione...'},
                {id: 'order', text: 'Órdenes de pago', disabled: false},
                {id: 'execute', text: 'Emisiones de pago'},
                {id: 'movement', text: 'Movimientos bancarios', disabled: false},
            ],
            reportTypeId: null,
            routes: {
                order: '/finance/payment-reports/pay-order/pdf',
                execute: '/finance/payment-reports/payment-execute/pdf',
                movement: '/finance/payment-reports/banking-movements/pdf',
            }
        };
    },
    created() {
        const vm = this;
        vm.getReceivers();
        vm.getDocumentStatusList();
    },
    methods: {
        async getReceivers() {
            const vm = this;
            await axios.get('/finance/pay-orders/list/get-receivers').then(response => {
                vm.receivers = response.data.records;
            }).catch(error => {
                console.error(error);
            });
        },

        async getDocumentStatusList() {
            const vm = this;
            await axios.get('/finance/payment-reports/get-document-status-list').then(response => {
                vm.documentStatusList = response.data.records;
            }).catch(error => {
                console.error(error);
            });
        },
        async generateReport() {
            const vm = this;
            let postData = {
                receiverId: vm.receiverId,
                action: vm.action,
                dateIni: vm.dateIni,
                dateEnd: vm.dateEnd,
                reportTypeId: vm.reportTypeId
            };
            vm.all && (postData.all = true);

            if (vm.reportTypeId == null) {
                vm.errors = [];
                vm.errors.push('Seleccione un tipo de reporte');
                return;
            }

            await axios.post(vm.routes[vm.reportTypeId], postData,
                {
                    responseType: 'arraybuffer'
                }).then(response => {
                    if (response.status == 200) {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'file.pdf');
                        document.body.appendChild(link);
                        link.click();
                    }
                    
                }).catch(error => {
                    let { errors } = JSON.parse(
                    String.fromCharCode.apply(
                        null,
                        new Uint8Array(error.response.data)
                    )
                    );
                    vm.errors = [];

                    for (let index in errors) {
                        if (errors[index]) {
                            vm.errors.push(errors[index][0]);
                        }
                    }
                })
        }
    },
    watch: {
        all(newVal) {
            const vm = this;
            if (newVal) {
                vm.receiverId = '';
                vm.action = '';
                vm.dateIni = '';
                vm.dateEnd = '';
            }
        }
    }
}
</script>