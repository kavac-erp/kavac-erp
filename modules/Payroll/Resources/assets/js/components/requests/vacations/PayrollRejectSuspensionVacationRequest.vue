<template>
    <div class="modal fade text-left" tabindex="-1" role="dialog" id="PayrollRejectSuspensionVacationRequest">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        ¿Seguro que desea rechazar esta solicitud?
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
                                <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p>
                                Una vez rechazada la operación no se podrán realizar cambios en la misma.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal"
                        title="Presione para cerrar la ventana" data-toggle="tooltip" v-has-tooltip>
                        Cerrar
                    </button>
                    <button @click="updateRecord('/payroll/suspension-vacation-requests/rejected')" type="button"
                        class="btn btn-primary btn-sm btn-round btn-modal-save"
                        title="Presione para guardar el registro" data-toggle="tooltip" v-has-tooltip>
                        Rechazar
                    </button>
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
                date_request: '',
                enjoyed_days: '',
                suspension_reason: '',
                status: '',
            },
            action: '',
            errors: [],
        }
    },
    methods: {
        async updateRecord(url) {
            const vm = this;
            vm.errors = [];
            try {
                vm.record.status = vm.action === 'approved' ? 'approved' : 'rejected';
                url = vm.setUrl(`${url}/${vm.record.id}/${true}`);
                const response = await axios.put(url, vm.record);
                if (typeof (response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                }
            } catch (error) {
                if (typeof (error.response) != "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                        );
                    }
                    for (let index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
            }
        },
    },
}
</script>
