<template>
    <div>
        <a
            class="btn btn-info btn-xs btn-icon btn-action"
            href="#"
            title="Ver información de la Solicitud"
            data-toggle="tooltip"
            @click="addRecord('view_request', route_list, $event)"
        >
            <i class="fa fa-eye"></i>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="view_request"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">x</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Información de la Solicitud Registrada
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div
                            class="alert alert-danger"
                            v-if="errors.length > 0"
                        >
                            <ul>
                                <li v-for="error in errors" :key="error">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <ul
                            class="nav nav-tabs custom-tabs justify-content-center"
                            role="tablist"
                        >
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    data-toggle="tab"
                                    href="#general"
                                    id="info_general"
                                    role="tab"
                                >
                                    <i class="ion-android-person"></i>
                                    Información general
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    href="#contact"
                                    role="tab"
                                >
                                    <i class="ion-android-person"></i>
                                    Información de contacto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    href="#equipment"
                                    role="tab"
                                    @click="loadEquipment()"
                                >
                                    <i class="ion-arrow-swap"></i> Equipos
                                    solicitados
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div
                                class="tab-pane active"
                                id="general"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong
                                                >Fecha de la solicitud</strong
                                            >
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="date_init"
                                                >
                                                </span>
                                            </div>
                                            <input type="hidden" id="id" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong
                                                >Motivo de la solicitud</strong
                                            >
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="motive"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Tipo de solicitud</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="type"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong
                                                >Fecha de entrega de los
                                                bienes</strong
                                            >
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="delivery_date"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong
                                                >Ubicación de los bienes</strong
                                            >
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="ubication"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="contact" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Nombre</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="agent_name"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Teléfono</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="agent_telf"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Correo</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span
                                                    class="col-md-12"
                                                    id="agent_email"
                                                >
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane"
                                id="equipment"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <v-client-table
                                            :columns="columns"
                                            :data="records"
                                            :options="table_options"
                                        >
                                            <div
                                                class="text-center"
                                                slot="asset.asset_details.code"
                                                slot-scope="props"
                                            >
                                                <span>{{
                                                    props.row.asset
                                                        .asset_details !== null
                                                        ? props.row.asset
                                                              .asset_details
                                                              .code
                                                        : "No definido"
                                                }}</span>
                                            </div>
                                            <div
                                                class="text-center"
                                                slot="asset.asset_specific_category.name"
                                                slot-scope="props"
                                            >
                                                <span>{{
                                                    props.row.asset
                                                        .asset_specific_category
                                                        ? prepareText(
                                                              props.row.asset
                                                                  .asset_specific_category
                                                                  .name
                                                          )
                                                        : "No definido"
                                                }}</span>
                                            </div>
                                            <div
                                                class="text-center"
                                                slot="asset.asset_details.serial"
                                                slot-scope="props"
                                            >
                                                <span>{{
                                                    props.row.asset
                                                        .asset_details !== null
                                                        ? props.row.asset
                                                              .asset_details
                                                              .serial
                                                        : "No definido"
                                                }}</span>
                                            </div>
                                            <div
                                                class="text-center"
                                                slot="asset.asset_details.brand"
                                                slot-scope="props"
                                            >
                                                <span>{{
                                                    props.row.asset
                                                        .asset_details !== null
                                                        ? props.row.asset
                                                              .asset_details
                                                              .brand
                                                        : "No definido"
                                                }}</span>
                                            </div>
                                            <div
                                                class="text-center"
                                                slot="asset.asset_details.model"
                                                slot-scope="props"
                                            >
                                                <span>{{
                                                    props.row.asset
                                                        .asset_details !== null
                                                        ? prepareText(
                                                              props.row.asset
                                                                  .asset_details
                                                                  .model
                                                          )
                                                        : "No definido"
                                                }}</span>
                                            </div>
                                        </v-client-table>
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
                        >
                            Cerrar
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
            records: [],
            errors: [],
            columns: [
                "asset.asset_details.code",
                "asset.asset_specific_category.name",
                "asset.asset_details.serial",
                "asset.asset_details.brand",
                "asset.asset_details.model",
            ],

            types: [
                { id: "", text: "Seleccione..." },
                { id: 1, text: "Prestamo de Equipos (Uso Interno)" },
                { id: 2, text: "Prestamo de Equipos (Uso Externo)" },
                { id: 3, text: "Prestamo de Equipos para Agentes Externos" },
            ],
        };
    },
    created() {
        this.table_options.headings = {
            "asset.asset_details.code": "Código interno",
            "asset.asset_specific_category.name": "Categoria especifica",
            "asset.asset_details.serial": "Serial",
            "asset.asset_details.brand": "Marca",
            "asset.asset_details.model": " Modelo",
        };
        this.table_options.sortable = [
            "asset.asset_details.code",
            "asset.asset_specific_category.name",
            "asset.asset_details.serial",
            "asset.asset_details.brand",
            "asset.asset_details.model",
        ];
        this.table_options.filterable = [
            "asset.asset_details.code",
            "asset.asset_specific_category.name",
            "asset.asset_details.serial",
            "asset.asset_details.brand",
            "asset.asset_details.model",
        ];
    },
    methods: {
        /**
         * Método que reinicia los datos que se muestran en el modal
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {
            this.records = [];
        },

        /**
         * Método que elimina las etiquetas HTML dentro de un String
         *
         *@returns String
         */
        prepareText(text) {
            return text.replace("<p>", "").replace("</p>", "");
        },

        /**
         * Inicializa los registros base del formulario
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         */
        initRecords(url, modal_id) {
            this.errors = [];
            this.reset();

            const vm = this;
            var fields = {};
            document.getElementById("info_general").click();
            url = vm.setUrl(url);
            axios
                .get(url)
                .then((response) => {
                    if (typeof response.data.records !== "undefined") {
                        fields = response.data.records;
                        $(".modal-body #id").val(fields.id);

                        document.getElementById("date_init").innerText =
                            fields.created_at
                                ? vm.format_date(fields.created_at)
                                : "N/A";
                        document.getElementById("motive").innerHTML =
                            fields.motive ? fields.motive : "N/A";
                        document.getElementById("type").innerText = fields.type
                            ? vm.types[fields.type].text
                            : "N/A";
                        document.getElementById("delivery_date").innerText =
                            fields.delivery_date
                                ? vm.format_date(fields.delivery_date)
                                : "N/A";
                        document.getElementById("ubication").innerText =
                            fields.ubication ? fields.ubication : "N/A";
                        document.getElementById("agent_name").innerText =
                            fields.agent_name ? fields.agent_name : "N/A";
                        document.getElementById("agent_telf").innerText =
                            fields.agent_telf ? fields.agent_telf : "N/A";
                        document.getElementById("agent_email").innerText =
                            fields.agent_email ? fields.agent_email : "N/A";
                    }
                    if ($("#" + modal_id).length) {
                        $("#" + modal_id).modal("show");
                    }
                })
                .catch((error) => {
                    if (typeof error.response !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                "custom",
                                "Acceso Denegado",
                                "danger",
                                "screen-error",
                                error.response.data.message
                            );
                        } else {
                            vm.logs(
                                "resources/js/all.js",
                                343,
                                error,
                                "initRecords"
                            );
                        }
                    }
                });
        },
        loadEquipment() {
            this.reset();
            var index = $(".modal-body #id").val();
            axios
                .get(`${window.app_url}/asset/requests/vue-info/${index}`)
                .then((response) => {
                    this.records = response.data.records.asset_request_assets;
                });
        },
    },
};
</script>
