<template>
    <div>
        <button
            @click="customActions(`cancel_bank_movement${id}`, $event)"
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
            :id="`cancel_bank_movement${id}`"
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
                            @click="reset"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-ui-block ico-x2"></i>
                            ANULACIÓN DEL MOVIMIENTO BANCARIO {{ code }}
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
                                        Código del movimiento
                                    </label>
                                    <input
                                        type="tetx"
                                        class="form-control"
                                        v-model="code"
                                        readonly
                                    >
                                </div>
                            </div>
                            <div class="col-md-4" id="helpCanceledAt">
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
                            <div v-if="concept" class="col-md-5" id="helpConcept">
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea
                                            rows="2"  
                                            class="form-control" 
                                            tabindex="14"
                                            v-model="concept"
                                            readonly
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-9 center" id="helpDescription">
                                <div class="form-group is-required">
                                    <label>Descripción del motivo de la anulación</label>
                                    <textarea
                                            rows="4"  
                                            class="form-control" 
                                            tabindex="14"
                                            v-model="record.description"
                                            title="Indique el motivo de la anulación de este movimiento bancario"
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
                                    @click="getMessage"
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
            concept: {
                type: String,
                required: false,
            },
            is_payment_executed: {
                type: Boolean,
                required: false,
            },
            cancelBankMovementPermission: {
                type: Boolean,
                required: false,
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
                description : "",
                canceled_at: '',
                is_payment_executed: false
            },
            errors : [],
            url : 'finance/movements/cancel-movements',
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
                description : "",
                canceled_at: '',
                is_payment_executed: false
            };
            vm.errores = [];
        },

        /**
         * Método que obtiene el mensaje de alerta a tomar en cuenta antes de seguir con el proceso de anulación
         * 
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         *
         */
        getMessage() {
            const vm = this;
            bootbox.confirm({
                title: "¿Está seguro?",
                message: "Una vez anulado este Movimiento bancario,"
                    +" el estatus del registro cambiará a 'Anulado'",
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
                            vm.showMessage('custom', 'Cancelado', 'danger', 'screen-error', 'Proceso de anulación no completado');
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }
            });
        },

        /**
         * Ejecuta acciones personalizadas basadas en el ID de modal y el evento dado.
         *
         * @param {string} modal_id - El ID del modal.
         * @param {Event} $event - El objeto de evento.
         */
        customActions (modal_id, $event) {
            const vm = this;

            vm.cancelBankMovementPermission
            ? (vm.is_payment_executed 
                ? vm.showMessage(
                    'custom',
                    'Acceso Denegado',
                    'danger', 'screen-error',
                    'Esta funcionalidad no puede ser ejecutada'
                ) : vm.showModal(modal_id, $event)
            ): vm.showMessage(
                'custom',
                'Acceso Denegado',
                'danger', 'screen-error',
                'No posee los permisos necesarios para ejecutar esta funcionalidad'
            );
        },

        /**
         * Método que permite levantar el modal
         * @param {*} modal_id 
         * @param {*} event 
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
            vm.record.is_payment_executed = vm.is_payment_executed;
            let url = vm.setUrl(vm.url);
            
            vm.loading = true;
            
            await axios.post(url, vm.record).then(response => {
                if (response.status == 200){
                    vm.reset();
                    setTimeout(() => {
                        location.reload();
                        vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Registro Anulado');
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
};
</script>
