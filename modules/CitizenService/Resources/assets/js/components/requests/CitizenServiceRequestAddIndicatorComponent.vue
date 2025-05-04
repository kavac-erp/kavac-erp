<template>
    <div>
        <a
            class="btn btn-primary btn-xs btn-icon ion ion-stats-bars"
            href="#"
            title="Agregar indicadores"
            data-toggle="tooltip"
            @click="initPending('view_add_indicator' + requestid, $event)"
            :disabled="requeststate != 'Aceptado'"
        >
            <i class="fa fa-check"></i>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'view_add_indicator' + requestid"
        >
            <div class="modal-dialog modal-xs" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>Agregar indicadores</h6>
                    </div>
                    <div class="modal-body">
                        <div
                            class="alert alert-danger"
                            v-if="errors.length > 0"
                        >
                            <div class="container">
                                <div class="alert-icon">
                                    <i
                                        class="now-ui-icons objects_support-17"
                                    ></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los
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
                        </div>
                        <div class="row">
                            <h6 class="card-title">
                                Agregar indicadores
                                <i
                                    class="fa fa-plus-circle cursor-pointer"
                                    @click="addIndicators()"
                                >
                                </i>
                            </h6>
                        </div>
                        <br />

                        <div
                            v-for="(indicator, index) in record.indicators"
                            :key="index"
                        >
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group is-required">
                                        <label for="indicator_id"
                                            >Indicador:</label
                                        >
                                        <select2
                                            :options="
                                                citizen_service_indicators
                                            "
                                            placeholder="Indicador"
                                            class="form-control input-sm"
                                            v-model="indicator.indicator_id"
                                            data-toggle="tooltip"
                                            title="Seleccione el indicador"
                                        >
                                        </select2>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group is-required">
                                        <label for="name">Nombre:</label>
                                        <input
                                            type="text"
                                            id="name"
                                            placeholder="Nombre"
                                            v-input-mask
                                            data-inputmask-regex="[a-zA-ZÁ-ÿ0-9\s]*"
                                            class="form-control input-sm"
                                            v-model="indicator.name"
                                            data-toggle="tooltip"
                                            title="Indique el nombre del indicador"
                                        />
                                        <input
                                            type="hidden"
                                            name="id"
                                            id="id"
                                            v-model="record.id"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-default btn-sm btn-round btn-modal-close"
                            data-dismiss="modal"
                            title="Presione para cerrar la ventana"
                            data-toggle="tooltip"
                            v-has-tooltip
                            @click="reset()"
                        >
                            Cerrar
                        </button>
                        <button
                            type="button"
                            @click="
                                updateRecord(
                                    '/citizenservice/requests/request-add-indicator/'
                                )
                            "
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                            title="Presione para guardar el registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            Guardar
                        </button>
                    </div>
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
                id: "",
                indicators: [],
            },
            citizen_service_indicators: [],
            errors: [],
        };
    },
    props: {
        requestid: Number,
        requeststate: String,
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            this.record = {
                id: "",
                indicators: [],
            };
        },
        initPending(modal_id, event) {
            if (this.requeststate == "Aceptado") {
                $(".modal-body #id").val(this.requestid);
                if ($("#" + modal_id).length) {
                    $("#" + modal_id).modal("show");
                }
                event.preventDefault();
            }
        },
        updateRecord(url) {
            const vm = this;
            var id = vm.requestid;
            if (typeof url != "undefined") {
                url = vm.setUrl(url);
                axios
                    .put(url + id, vm.record)
                    .then((response) => {
                        if (typeof response.data.redirect !== "undefined")
                            location.href = response.data.redirect;
                    })
                    .catch((error) => {
                        vm.errors = [];
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
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        }
                    });
            }
        },
        addIndicators() {
            const vm = this;
            vm.record.indicators.push({
                name: "",
                indicator_id: "",
            });
        },
    },
    mounted() {
        this.record.indicators = [];
        this.getCitizenServiceIndicators();
    },
};
</script>
