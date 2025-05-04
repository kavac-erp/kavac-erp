<template>
    <div class="card-body col-md-12">
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
            ref="tableMax"
        >
            <div
                slot="asset_asignation.code"
                slot-scope="props"
                class="text-center"
            >
                <span>
                    {{ props.row.asset_asignation.code }}
                </span>
            </div>
            <div slot="observation" slot-scope="props" class="text-center">
                <span>
                    {{ props.row.observation ? props.row.observation : "N/A" }}
                </span>
            </div>
            <div
                slot="asset_asignation.payroll_staff"
                slot-scope="props"
                class="text-center"
            >
                <span>
                    {{
                        props.row.asset_asignation.payroll_staff
                            ? props.row.asset_asignation.payroll_staff
                                  .first_name +
                              " " +
                              props.row.asset_asignation.payroll_staff.last_name
                            : "N/A"
                    }}
                </span>
            </div>
            <div
                slot="asset_asignation.location_place"
                slot-scope="props"
                class="text-center"
            >
                <span>
                    {{
                        props.row.asset_asignation.section
                            ? props.row.asset_asignation.section.name
                            : "N/A"
                    }}
                </span>
            </div>
            <div slot="state" slot-scope="props" class="text-center">
                <span>
                    {{ props.row.state }}
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <a
                        v-if="isAcceptedRejected(props.row.state)"
                        @click="viewMessage()"
                        href="#"
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aceptar Entrega"
                        data-toggle="tooltip"
                        disabled
                    >
                        <i class="fa fa-check"></i>
                    </a>

                    <button
                        v-else
                        @click="acceptRequest(props.index)"
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aceptar Entrega"
                        data-toggle="tooltip"
                        type="button"
                    >
                        <i class="fa fa-check"></i>
                    </button>

                    <a
                        v-if="isAcceptedRejected(props.row.state)"
                        @click="viewMessage()"
                        href="#"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Rechazar Entrega"
                        data-toggle="tooltip"
                        disabled
                    >
                        <i class="fa fa-ban"></i>
                    </a>

                    <button
                        v-else
                        @click="rejectedRequest(props.index)"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Rechazar Entrega"
                        data-toggle="tooltip"
                        type="button"
                    >
                        <i class="fa fa-ban"></i>
                    </button>
                    <a
                        v-if="
                            props.row.state == 'Aprobado' &&
                            props.row.approved_by_id &&
                            props.row.received_by_id
                        "
                        class="btn btn-primary btn-xs btn-icon"
                        :href="asset_assignation_delivery_pdf + props.row.id"
                        title="Imprimir acta"
                        data-toggle="tooltip"
                        v-has-tooltip
                        target="_blank"
                    >
                        <i class="fa fa-print" style="text-align: center"></i>
                    </a>

                    <a
                        v-else
                        @click="viewMessage()"
                        class="btn btn-primary btn-xs btn-icon"
                        disabled
                        title="Imprimir acta"
                        data-toggle="tooltip"
                        v-has-tooltip
                        target="_blank"
                    >
                        <i class="fa fa-print" style="text-align: center"></i>
                    </a>
                </div>
            </div>
        </v-client-table>

        <div id="delivery" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click="reset()"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">
                            ¿Aprobar entrega de equipos?
                        </h4>
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
                                    <li v-for="error in errors" :key="error">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Observaciones generales</label>
                                    <textarea
                                        data-toggle="tooltip"
                                        class="form-control input-sm"
                                        title="Indique las observaciones presentadas en la solicitud"
                                        id="asignation_observation"
                                        v-model="record.observation"
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6" id="approved_by_id">
                                <div class="form-group is-required">
                                    <strong>Aprobado por:</strong>
                                    <select2
                                        :options="payroll_staffs"
                                        v-model="record.approved_by_id"
                                    ></select2>
                                </div>
                            </div>
                            <div class="col-md-6" id="received_by_id">
                                <div class="form-group is-required">
                                    <strong>Recibido por:</strong>
                                    <select2
                                        :options="payroll_staffs"
                                        v-model="record.received_by_id"
                                    ></select2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            data-bb-handler="cancel"
                            type="button"
                            class="btn btn-default"
                            data-dismiss="modal"
                            @click="reset()"
                        >
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                        <button
                            data-bb-handler="confirm"
                            type="button"
                            class="btn btn-primary"
                            @click="createRecord('asset/asignations/deliver')"
                        >
                            <i class="fa fa-check"></i> Confirmar
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
                observation: "",
                state: "",
                asset_asignation_id: "",
                approved_by_id: "",
                received_by_id: "",
            },
            payroll_staffs: [],
            asset_assignation_delivery_pdf: `${window.app_url}/asset/asignations/deliveries-record-pdf/`,
            url: `${window.app_url}/asset/asignations/deliveries-record-pdf/`,
            records: [],
            errors: [],
            columns: [
                "asset_asignation.code",
                "asset_asignation.payroll_staff",
                "asset_asignation.location_place",
                "state",
                "observation",
                "id",
            ],
        };
    },

    created() {
        this.readRecords(this.route_list);
        this.table_options.headings = {
            "asset_asignation.code": "Código",
            "asset_asignation.payroll_staff": "Trabajador",
            "asset_asignation.location_place": "Lugar de ubicación",
            state: "Estado de entrega",
            observation: "Observaciones",
            id: "Acción",
        };
        this.table_options.sortable = [
            "asset_asignation.code",
            "asset_asignation.payroll_staff",
            "asset_asignation.location_place",
            "state",
        ];
        this.table_options.filterable = [
            "asset_asignation.code",
            "asset_asignation.payroll_staff",
            "asset_asignation.location_place",
            "state",
        ];

        this.getPayrollStaffs();
    },

    methods: {
        reset() {
            this.record = {
                id: "",
                observation: "",
                state: "",
                asset_asignation_id: "",
                approved_by_id: "",
                received_by_id: "",
            };
        },

        acceptRequest(index) {
            const vm = this;
            let fields = vm.records[index - 1];
            vm.record.id = fields.id;
            vm.record.state = "Aprobado";
            vm.record.asset_asignation_id = fields.asset_asignation.id;

            $("#delivery").modal("show");
        },

        isAcceptedRejected(state) {
            const accepted_rejected = state == "Pendiente";
            return !accepted_rejected;
        },

        rejectedRequest(index) {
            const vm = this;
            let fields = vm.records[index - 1];
            vm.record.id = fields.id;
            vm.record.state = "Rechazado";
            vm.record.asset_asignation_id = fields.asset_asignation.id;
            let dialog = bootbox.confirm({
                title: "¿Rechazar entrega de equipos?",
                message:
                    "<div class='row'>" +
                    "<div class='col-md-12'>" +
                    "<div class='form-group'>" +
                    "<label>Observaciones generales</label>" +
                    "<textarea data-toggle='tooltip' class='form-control input-sm'" +
                    " title='Indique las observaciones presentadas en la solicitud'" +
                    " id='asignation_observation'>" +
                    "</textarea>" +
                    "</div>" +
                    "</div>" +
                    "</div>",
                size: "medium",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    },
                },
                callback: function (result) {
                    if (result) {
                        vm.record.observation = document.getElementById(
                            "asignation_observation"
                        ).value;
                        vm.createRecord("asset/asignations/deliver");
                    }
                },
            });
        },

        /**
         * Método que permite crear o actualizar un registro
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
         * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
         *                         El valor por defecto es verdadero.
         * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
         *                         El valor por defecto es verdadero.
         */
        async createRecord(url, list = true, reset = true) {
            const vm = this;
            url = vm.setUrl(url);

            if (vm.record.id) {
                vm.updateRecord(url);
            } else {
                vm.loading = true;
                var fields = {};

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                await axios
                    .post(url, fields)
                    .then((response) => {
                        if (typeof response.data.redirect !== "undefined") {
                            location.href = response.data.redirect;
                        } else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.readRecords(url);
                            }

                            vm.showMessage("store");
                        }
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

                vm.loading = false;
            }
        },

        /**
         * Método que permite actualizar información
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta de la acci´on que modificará los datos
         */
        async updateRecord(url) {
            const vm = this;
            vm.loading = true;
            var fields = {};
            url = vm.setUrl(url);

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            await axios
                .patch(
                    `${url}${url.endsWith("/") ? "" : "/"}${vm.record.id}`,
                    fields
                )
                .then((response) => {
                    if (typeof response.data.redirect !== "undefined") {
                        location.href = response.data.redirect;
                    } else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.showMessage("update");
                    }
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
            vm.loading = false;
        },

        isRemovable(state) {
            const removable = state == "Aprobado" || state == "Rechazado";
            return !removable;
        },
        /**
         * Método para la eliminación de registros
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} id    ID del Elemento seleccionado para su eliminación
         * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
         */
        deleteRecord(id, url) {
            const vm = this;
            /** @type {string} URL que atiende la petición de eliminación del registro */
            var url = vm.setUrl(url ? url : vm.route_delete);

            bootbox.confirm({
                title: "¿Eliminar registro?",
                message: "¿Está seguro de eliminar este registro?",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    },
                },
                callback: function (result) {
                    if (result) {
                        /** @type {object} Objeto con los datos del registro a eliminar */
                        let recordDelete = JSON.parse(
                            JSON.stringify(
                                vm.records.filter((rec) => {
                                    return rec.id === id;
                                })[0]
                            )
                        );

                        axios
                            .delete(
                                `${url}${url.endsWith("/") ? "" : "/"}${
                                    recordDelete.id
                                }`
                            )
                            .then((response) => {
                                if (
                                    typeof response.data.error !== "undefined"
                                ) {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage(
                                        "custom",
                                        "Alerta!",
                                        "warning",
                                        "screen-error",
                                        response.data.message
                                    );
                                    return false;
                                }
                                /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                                vm.records = JSON.parse(
                                    JSON.stringify(
                                        vm.records.filter((rec) => {
                                            return rec.id !== id;
                                        })
                                    )
                                );
                                if (
                                    typeof vm.$refs.tableResults !== "undefined"
                                ) {
                                    vm.$refs.tableResults.refresh();
                                }
                                location.href = response.data.redirect;
                                vm.showMessage("destroy");
                            })
                            .catch((error) => {
                                vm.logs(
                                    "mixins.js",
                                    498,
                                    error,
                                    "deleteRecord"
                                );
                            });
                    }
                },
            });
        },

        /**
         * Método para descargar el pdf del acta de una Entrega de bienes
         *
         * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
         *
         *
         * @param  {integer}  code   código único que identifica el el nombre del pdf
         */
        downloadRecord(code) {
            const vm = this;
            let url = vm.url + code;

            axios({
                url: url,
                method: "get",
                responseType: "blob",
            })
                .then((response) => {
                    let fileURL = window.URL.createObjectURL(
                        new Blob([response.data])
                    );

                    let fileLink = document.createElement("a");
                    fileLink.href = fileURL;
                    fileLink.setAttribute("download", "file");
                    document.body.appendChild(fileLink);
                    fileLink.click();
                    URL.revokeObjectURL(fileLink.href);
                })
                .catch((error) => {
                    vm.errors = [];

                    if (typeof error.response != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                    }
                });
        },

        viewMessage() {
            const vm = this;
            vm.showMessage(
                "custom",
                "Alerta",
                "danger",
                "screen-error",
                "La solicitud está en un tramite que no le permite acceder a esta funcionalidad"
            );
            return false;
        },
    },
};
</script>
