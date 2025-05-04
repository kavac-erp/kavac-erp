<template>
    <div>
        <button
            @click="
                reversePermission
                    ? getMessageOrModal(
                          recordsAccounting,
                          `accounting_entry_reverse_${id}`,
                          $event
                      )
                    : showMessage(
                          'custom',
                          'Acceso Denegado',
                          'danger',
                          'screen-error',
                          'No posee los permisos necesarios para ejecutar esta funcionalidad'
                      )
            "
            class="btn btn-secondary btn-xs btn-icon btn-action"
            title="Generar Reverso de asiento"
            data-toggle="tooltip"
            type="button"
        >
            <i class="fa fa-exchange"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="`accounting_entry_reverse_${id}`"
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
                            <i
                                class="icofont icofont-exchange success ico-x2"
                            ></i>
                            REVERSO DEL ASIENTO CONTABLE CON REFERENCIA
                            {{ recordsAccounting.reference }}
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body">
                        <div
                            class="alert alert-danger"
                            v-if="errors.length > 0"
                        >
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>¡Atención!</strong> Debe verificar los
                            siguientes errores antes de continuar:
                            <button
                                type="button"
                                class="close"
                                data-dismiss="alert"
                                aria-label="Close"
                                @click.prevent="errors = []"
                            >
                                <span aria-hidden="true">
                                    <i
                                        class="now-ui-icons ui-1_simple-remove"
                                    ></i>
                                </span>
                            </button>
                            <ul>
                                <li
                                    v-for="(error, index) in errors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-md-3" id="helpCode">
                                <div class="form-group">
                                    <label class="control-label">
                                        Referencia del asiento contable
                                    </label>
                                    <input
                                        type="tetx"
                                        class="form-control"
                                        v-model="recordsAccounting.reference"
                                        readonly
                                    />
                                </div>
                            </div>
                            <div class="col-md-3" id="helpreversedAt">
                                <div class="form-group">
                                    <label class="control-label">
                                        Fecha de reverso {{ fiscal_year }}
                                    </label>
                                    <input
                                        type="date"
                                        class="form-control is-required"
                                        v-model="record.reversed_at"
                                        :min="
                                            new Date(
                                                fiscal_year
                                                    ? fiscal_year + '-01-01'
                                                    : '2000-01-01'
                                            )
                                                .toISOString()
                                                .split('T')[0]
                                        "
                                        :max="
                                            new Date()
                                                .toISOString()
                                                .split('T')[0]
                                        "
                                    />
                                </div>
                            </div>
                            <div
                                v-if="recordsAccounting.concept"
                                class="col-md-6"
                                id="helpDescription"
                            >
                                <div class="form-group">
                                    <label>Descripción ó concepto:</label>
                                    <textarea
                                        rows="2"
                                        class="form-control"
                                        tabindex="14"
                                        v-model="recordsAccounting.concept"
                                        readonly
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 center" id="helpObservations">
                                <div class="form-group is-required">
                                    <label
                                        >Descripción del motivo del
                                        reverso:</label
                                    >
                                    <textarea
                                        rows="4"
                                        class="form-control"
                                        tabindex="14"
                                        v-model="record.observations"
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
                            <button
                                type="button"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                                @click="reverse()"
                            >
                                Aceptar
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
        recordsAccounting: {
            type: Array,
            required: true,
        },
        reversePermission: {
            type: Boolean,
            required: true,
        },
        fiscal_year: {
            type: String,
            required: false,
        },
    },
    data() {
        return {
            record: {
                entryId: "",
                reversed_at: "",
                observations: "",
            },
            errors: [],
            url: "accounting/entries/reverse",
        };
    },
    created() {},
    async mounted() {},
    methods: {
        /**
         * Método que borra todos los datos del formulario
         */
        reset() {
            const vm = this;
            (vm.record = {
                entryId: "",
                reversed_at: "",
                observations: "",
            }),
                (vm.errores = []);
        },

        /**
         * Método que obtiene el mensaje de alerta a tomar en cuenta antes de seguir con el proceso de revesro
         *
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         *
         *  @param {type: Integer} id  entero que representa el id del registro
         */
        reverse() {
            const vm = this;
            const url = vm.setUrl(vm.url);
            vm.record.entryId = vm.id;

            bootbox.confirm({
                title: "Crear reverso",
                message:
                    "¿Está seguro de realizar el reverso de este asiento contable? Una vez realizado este proceso deberá realizar nuevamente el asiento contable",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                        className: "btn btn-default btn-sm btn-round",
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                        className: "btn btn-primary btn-sm btn-round",
                    },
                },
                callback: function (result) {
                    if (result) {
                        vm.loading = true;
                        axios
                            .post(url, vm.record)
                            .then((response) => {
                                if (response.status == 200) {
                                    location.reload();
                                    vm.showMessage(
                                        "custom",
                                        "¡Éxito!",
                                        "success",
                                        "screen-ok",
                                        "Reverso de asiento contable creado."
                                    );
                                }
                            })
                            .catch((error) => {
                                if (typeof error.response != "undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            "custom",
                                            "Acceso Denegado",
                                            "danger",
                                            "screen-error",
                                            error.response.data.message
                                        );
                                    }
                                    if (error.response.status == 500) {
                                        const messages =
                                            error.response.data.message;
                                        vm.showMessage(
                                            messages.type,
                                            messages.title,
                                            messages.class,
                                            messages.icon,
                                            messages.text
                                        );
                                    }
                                }
                                vm.errors = [];
                                for (let index in error.response.data.errors) {
                                    if (error.response.data.errors[index]) {
                                        vm.errors.push(
                                            error.response.data.errors[index][0]
                                        );
                                    }
                                }
                            });
                        vm.loading = false;
                    }
                },
            });
        },

        getMessageOrModal(record, modal_id, $event) {
            const vm = this;
            if (record.reversed) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "Los asientos solo pueden ser revertidos un vez."
                );

                return;
            } else if (record.reversed_id) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "No se permite generar un asiento reverso de un otro asiento reverso."
                );

                return;
            } else if (!record.approved) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "Solo se puede generar un reverso de un asiento contable aprobado."
                );

                return;
            } else if (record.pivot_entryable_count != 0) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "Solo se pueden generar reversos de asientos contables generados manualmente."
                );

                return;
            } else {
                vm.showModal(modal_id, $event);
            }
        },

        /**
         * Método que permite levantar el modal
         * @param {*} modal_id
         * @param {*} event
         */
        async showModal(modal_id, event) {
            const vm = this;
            event.preventDefault();
            vm.loading = true;
            if (modal_id) {
                $(`#${modal_id}`).modal("show");
            }
            vm.loading = false;
        },
    },
};
</script>
