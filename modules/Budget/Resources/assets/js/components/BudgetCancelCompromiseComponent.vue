<template>
    <div>
        <button
            @click="cancelBudgetCompromisePermission
            ? showModal('cancel_compromise' + id, $event)
            : showMessage(
                'custom',
                'Acceso Denegado',
                'danger', 'screen-error',
                'No posee los permisos necesarios para ejecutar esta funcionalidad'
            )"

            class="btn btn-xs btn-dark btn-icon btn-action"
            title="Anular registro"
            data-toggle="tooltip"
            type="button"
        >
            <i class="ion ion-android-close"></i>
        </button>

        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'cancel_compromise' + id"
            data-backdrop="static"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button
                            type="reset"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            title="Presione para cerrar la ventana"
                            @click="reset"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-ui-block ico-x2"></i>
                            ANULACIÓN DEL COMPROMISO {{ code }}
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>¡Atención!</strong> Debe verificar los siguientes errores antes de continuar:
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
                            <div class="col-md-3" id="helpCode">
                                <div class="form-group">
                                    <label class="control-label">
                                        Código del Compromiso
                                    </label>
                                    <input
                                        type="tetx"
                                        class="form-control"
                                        v-model="code"
                                        readonly
                                    >
                                </div>
                            </div>
                            <div class="col-md-3" id="helpCAnceledAt">
                                <div class="form-group">
                                    <label class="control-label is-required">
                                        Fecha de anulación
                                    </label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        v-model="record.canceled_at"
                                        :min="new Date(fiscal_year
                                        ? fiscal_year + '-01-01'
                                        : '2000-01-01').toISOString().split('T')[0]"
                                        :max="new Date().toISOString().split('T')[0]"
                                    >
                                </div>
                            </div>
                            <div v-if="observations" class="col-md-6" id="helpObservations">
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea
                                        rows="2"
                                        class="form-control"
                                        tabindex="14"
                                        v-model="observations"
                                        readonly
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" id="helpDescription">
                                <div class="form-group is-required">
                                    <label>Descripción del motivo de la anulación</label>
                                    <textarea
                                        rows="3"
                                        class="form-control"
                                        tabindex="14"
                                        v-model="record.description"
                                        title="
                                            Indique el motivo de la anulación de este compromiso
                                        "
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Final modal-body -->
                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                data-dismiss="modal"
                                @click="reset, showMessage('custom', 'Cancelado', 'danger', 'screen-error', 'Proceso de anulación no completado')"
                            >
                                Cerrar
                            </button>
							<button type="button"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save"
                                    @click="getMessage(`cancel_compromise${id}`)"
                            >
								Guardar
							</button>
	                    </div>
                    </div>
                    <!-- Final modal-footer -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
            id: {
                type: Number,
                required: true,
            },
            code: {
                type: String,
                required: false,
            },
            observations: {
                type: String,
                required: false,
            },
            cancelBudgetCompromisePermission: {
                type: Boolean,
                required: false,
            },
            fiscal_year: {
                type: String,
                required: false,
            },
        },
    data() {
        return {
            record :{
                id : '',
                cancel_compromise_option_id : '',
                description : "",
                canceled_at: '',
            },
            errors : [],
            url : 'budget/compromises/cancel',
        };
    },
    created(){
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         */
        reset() {
            const vm = this;
            vm.record = {
                id : '',
                cancel_compromise_option_id : '',
                description : "",
                canceled_at: '',
            };
            vm.errores = [];
        },

        /**
         * Método que obtiene el mensaje de alerta a tomar en cuenta antes de seguir con el proceso de anulación
         *
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         */
        getMessage(modal_id) {
            const vm = this;
            bootbox.confirm({
                title: "¿Está seguro?",
                message: "Una vez anulado este Compromiso,"
                    +" el estatus del registro cambiaŕa a 'Anulado'",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                        className: 'btn btn-default btn-sm btn-round'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Continuar',
                        className: 'btn btn-primary btn-sm btn-round'

                    }
                },
                callback: function(result) {
                    try {
                        if (result) {
                            vm.sendCancellation();
                        }
                        else {
                            if (modal_id) {
                                $(`#${modal_id}`).modal('hide');
                            }
                            vm.showMessage('custom', 'Cancelado', 'danger', 'screen-error', 'Proceso de anulación no completado');
                            vm.reset();
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }
            });
        },

        /**
         * Método que permite levantar el modal
         * @param {string} modal_id
         * @param {object} event
         */
        async showModal(modal_id, event){
            event.preventDefault();
            this.loading = true;
            if (modal_id) {
                $(`#${modal_id}`).modal('show');
            }
            this.loading = false;
        },

        async sendCancellation(){
            const vm = this;
            vm.record.id = vm.id;
            let url = vm.setUrl(vm.url);

            vm.loading = true;

            await axios.post(url, vm.record).then(response => {
                if (response.status == 200){
                    vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Registro Anulado');
                    vm.reset();
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else if(response.status == 422){
                    vm.errors.push(response.data.errors[0]);
                }
            }).catch(error => {
                if (typeof(error.response) != "undefined") {
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
                vm.errors = [];
                for (let index in error.response.data.errors) {
                    console.log(error.response.data.errors[index]);
                    if (error.response.data.errors[index]) {
                        vm.errors.push(error.response.data.errors[index][0]);
                    }
                }
            });
            vm.loading = false;
        },
    },
};
</script>
