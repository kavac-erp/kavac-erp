<template>
    <div>
        <button @click="generateRecord($event)" class="btn btn-success btn-xs btn-icon btn-action"
            title="Solicitar Disponibilidad Presupuestaria" data-toggle="tooltip" v-has-tooltip
            :disabled="status == 'generated'">
            <i class="fa fa-commenting"></i>
        </button>
        <div class="modal fade text-left" tabindex="-1" role="dialog" :id="'show_noty_' + id">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="reset" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            Enviar Notificación
                        </h6>
                    </div>
                    <!-- Fromulario -->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="is-required">
                                    <label class="control-label" for="usuario">
                                        Usuario
                                    </label>
                                    <select2 :options="employments" id="usuario" class="is-required"
                                        @input="getUserInfo(record.userId)" v-model="record.userId"></select2>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="form-group ">
                                    <label>Mensaje</label>
                                    <input type="text" class="form-control" v-model="record.message" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-primary" @click="sendNotify()">
                            Enviar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        "id",
        "employments",
        "module",
        "status",
        "has_availability_request_permission",
    ],
    data() {
        return {
            record: {
                userId: '',
                user: '',
                message: ''
            },
        }
    },
    methods: {
        async generateRecord($event) {
            const vm = this;
            if (vm.has_availability_request_permission) {
                await vm.addRecord('show_noty_' + vm.id, vm.route_show, $event)
            } else {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "No dispone de permisos para acceder a esta funcionalidad."
                );
            }

        },
        reset() {
            const vm = this;
            vm.record.userId = '',
                vm.record.user = '',
                vm.record.message = ''
        },
        async getUserInfo(id) {
            const vm = this;
            if (id == '') {
                return false;
            }
            await axios.get(`${window.app_url}/user-info/${id}`).then(response => {
                vm.record.userId = id;
                vm.record.user = response.data.user.name;
                vm.record.toEmail = response.data.user.email;

            }).catch(error => {
                console.error(error);
            });
        },
        async sendNotify() {
            const vm = this;
            vm.loading = true;
            if (vm.record.userId == '') {
                vm.showMessage(
                    'custom',
                    'Alerta!',
                    'warning',
                    'screen-error',
                    'Selecionar el Usuario a enviar el mensaje es nesesario'
                );
                vm.loading = false;
                return false;
            }
            if (vm.module == 'payroll') {
                var url = `${window.app_url}/payroll/registers/availability/${vm.id}`;
            } else {
                var url = `${window.app_url}/purchase/budgetary_availability/${vm.id}/edit`;
            }
            await axios.post(`${window.app_url}/purchase/send_notify`, {
                id: vm.id,
                user_id: vm.record.userId,
                module: vm.module,
                title: 'Solicitud de Disponibilidad Presupuestaria  ',
                details: vm.record.message
            }).then(response => {
                vm.showMessage(
                    'custom', 'Enviado', 'success', 'screen-ok', 'Notificación enviada'
                );
                vm.loading = false;
                if (vm.module != 'payroll') {
                    setTimeout(function () {
                        location.href = `${window.app_url}/purchase/requirements`;
                    }, 1500);
                } else {
                    location.reload();
                }
            }).catch(error => {
                console.error(error);
            });
            // seccion para enviar email
            if (vm.record.toEmail) {
                await axios.post(`${window.app_url}/messages/send`, {
                    id: vm.id,
                    toEmail: vm.record.toEmail,
                    subject: 'Solicitud de Disponibilidad Presupuestaria',
                    message: vm.record.message,
                }).then(response => {
                    vm.showMessage(
                        'custom',
                        'Enviado',
                        'success',
                        'screen-ok',
                        'Mensaje enviado'
                    );
                    $("#show_noty_" + vm.id).find('.close').click();
                }).catch(error => {
                    console.error(error);
                });
            }
            vm.reset();
            vm.loading = false;
        }
    },
    async created() {
        const vm = this;
        vm.record.message = "Solicitud Prosupuestaria Numero " + vm.id;
    },
};
</script>
