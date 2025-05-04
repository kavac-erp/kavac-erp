<template>
    <div>
        <button
            @click="cancelPaymentExecutedPermission
            ? showModal('cancel_payment_execute', id, $event)
            : showMessage(
                'custom',
                'Acceso Denegado',
                'danger', 'screen-error',
                'No posee los permisos necesarios para ejecutar esta funcionalidad'
            )"
            :disabled="!cancelPaymentExecutedPermission"
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
            :id="'cancel_payment_execute' + id"
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
                            ANULACIÓN DE LA EMISIÓN DE PAGO {{ code }}
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
                                        Código de la emisión de pago
                                    </label>
                                    <input
                                        type="tetx"
                                        class="form-control"
                                        v-model="code"
                                        readonly
                                    >
                                </div>
                            </div>
                            <div class="col-md-4" id="paymentExecuteOption">
                                <div class="form-group is-required">
                                    <label class="control-label">¿Anulación?</label>
                                    <select2
                                        :options="cancelPaymentExecuteOptions"
                                        v-model="record.cancel_payment_execute_option_id"
                                    >
                                    <option
                                        v-for="option in cancelPaymentExecuteOptions"
                                        :key="option.id"
                                        :value="option.id"
                                        :disabled="(isPartial && option.id === 1) || (is_payroll && option.id === 2)"
                                    >
                                         {{ option.text }}
                                    </option>
                                    </select2>
                                </div>
                            </div>
                            <div v-if="observations" class="col-md-5" id="helpObservations">
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
                            <div class="col-md-9" id="helpDescription">
                                <div class="form-group is-required">
                                    <label>Descripción del motivo de la anulación</label>
                                    <textarea
                                        rows="4"  
                                        class="form-control" 
                                        tabindex="14"
                                        v-model="record.description"
                                        title="
                                            Indique el motivo de la anulación de esta emisión de pago
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
                                @click="reset"
                            >
                                Cerrar
                            </button>
							<button type="button" 
                                    class="btn btn-primary btn-sm btn-round btn-modal-save"
                                    @click="sendCancellation"
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
            is_payroll: {
                type: Boolean,
                required: false,
            },
            isPartial: {
                type: Boolean,
                required: false,
            },
            cancelPaymentExecutedPermission: {
                type: Boolean,
                required: true,
            },
            fiscal_year: {
                type: String,
                required: false,
            }
        },
    data() {
        return {
            record :{
                id : '',
                cancel_payment_execute_option_id : '',
                description : "",
                canceled_at : '',
                is_payroll: false,
            },
            cancelPaymentExecuteOptions: [
                    {'id': '', 'text': 'Seleccione...', 'disabled': false},
                    {'id': 1, 'text': 'Sin Remisión', 'disabled': false},
                    {'id': 2, 'text': 'Con Remisión', 'disabled': false},
                ],
            message : [
                "¿Está seguro? Una vez anulada esta Emisión de pago,"
                    +" todo el proceso se anulará hasta el compromiso.",
                "¿Está seguro? Una vez anulada esta Emisión de pago,"
                    +" el estado del registro cambiaŕa a 'Anulado' y se podrá"
                    +" generar una nueva 'Emisión de pago'."
            ],
            errors : [],
            url : 'finance/payment-execute/cancel',
        };
    },
    created(){
    },
    mounted() {
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         */
        reset() {
            const vm = this;
            vm.record = {
                id : '',
                cancel_payment_execute_option_id : '',
                description : "",
                canceled_at : '',
                is_payroll: false,
            };
            vm.errores = [];
        },

        /**
         * Método que obtiene el mensaje de alerta a tomar en cuenta antes de seguir con el proceso de anulación
         * 
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         *
         *  @param {type: Integer} id  entero que representa el id de la opción que se escoge
         */
        getMessage(id, modal_id) {
            const vm = this;
            let title_ = vm.cancelPaymentExecuteOptions.filter(option => option.id == id)[0];
            bootbox.confirm({
                title: title_.text,
                message: vm.message[id-1],
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
                        vm.showMessage('custom', '¡Seleccionado con éxito!', 'success', 'screen-ok', 'Se continuará con el proceso de anulación');
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
         * @param {*} modal 
         * @param {*} id
         * @param {*} event 
         */
        async showModal(modal, id, event){
            event.preventDefault();
            this.loading = true;
            if (modal && id) {
                $(`#${modal}${id}`).modal('show');
            }
            this.loading = false;
        },

        async sendCancellation(){
            const vm = this;
            vm.record.id = vm.id;
            vm.record.is_payroll = vm.is_payroll;
            let url = vm.setUrl(vm.url);

            vm.loading = true;

            await axios.post(url, vm.record).then(response => {
                if (response.status == 200){
                    vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Registro Anulado');
                    vm.reset();
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                }
            }).catch(error => {
                if (typeof(error.response) !="undefined") {
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
                    if (error.response.data.errors[index]) {
                        vm.errors.push(error.response.data.errors[index][0]);
                    }
                }
            });
            vm.loading = false;
        },
    },

    watch : {
        'record.cancel_payment_execute_option_id' : function(value){
            value && this.getMessage(value, `cancel_payment_execute${this.id}`);
        }
    },
};
</script>
