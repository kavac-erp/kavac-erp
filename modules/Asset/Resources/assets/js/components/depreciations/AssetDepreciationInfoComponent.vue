<template>
    <div>
        <a
            class="btn btn-info btn-xs btn-icon btn-action"
            href="#"
            title="Ver información del registro"
            data-toggle="tooltip"
            @click="addRecord('view_depreciation' + id, route_list, $event)"
        >
            <i class="fa fa-eye"></i>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'view_depreciation' + id"
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
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-read-book ico-2x"></i>
                            Detalles de la depreciación
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
                                    :href="'#general' + id"
                                    id="info_general"
                                    role="tab"
                                >
                                    <i class="ion-android-person"></i>
                                    Información General
                                </a>
                            </li>

                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    data-toggle="tab"
                                    :href="'#equipment' + id"
                                    role="tab"
                                >
                                    <i class="ion-arrow-swap"></i> Bienes
                                    depreciados
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div
                                class="tab-pane active"
                                :id="'general' + id"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Código:</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span class="col-md-12">
                                                    {{ record.code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Año fiscal:</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span class="col-md-12">
                                                    {{ record.year }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Monto:</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span class="col-md-12">
                                                    {{
                                                        record.amount
                                                            ? currencyFormat(
                                                                  record.amount,
                                                                  2
                                                              )
                                                            : ""
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>Estatus:</strong>
                                            <div
                                                class="row"
                                                style="margin: 1px 0"
                                            >
                                                <span class="col-md-12">
                                                    {{
                                                        record.document_status
                                                            ? record
                                                                  .document_status
                                                                  .name
                                                            : ""
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tab-pane"
                                :id="'equipment' + id"
                                role="tabpanel"
                            >
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <v-server-table
                                            :columns="columns"
                                            :url="route_list"
                                            :options="table_options"
                                        >
                                            <div
                                                slot="asset_institutional_code.name"
                                                slot-scope="props"
                                                class="text-center"
                                            >
                                                <span>
                                                    {{
                                                        props.row.asset
                                                            .asset_details.code
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                slot="asset_specific_category.name"
                                                slot-scope="props"
                                                class="text-center"
                                            >
                                                <span>
                                                    {{
                                                        props.row.asset
                                                            .asset_specific_category_id
                                                            ? props.row.asset
                                                                  .asset_specific_category
                                                                  .name
                                                            : ""
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                slot="acquisition_date.name"
                                                slot-scope="props"
                                                class="text-center"
                                            >
                                                <span>
                                                    {{
                                                        props.row.asset
                                                            .acquisition_date
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                slot="depreciation_value"
                                                slot-scope="props"
                                                class="text-center"
                                            >
                                                <span>
                                                    {{
                                                        props.row.amount
                                                            ? currencyFormat(
                                                                  props.row
                                                                      .amount
                                                              )
                                                            : ""
                                                    }}
                                                </span>
                                            </div>
                                        </v-server-table>
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
            record: {},
            errors: [],
            columns: [
                "asset_institutional_code.name",
                "asset_specific_category.name",
                "acquisition_date.name",
                "depreciation_value",
            ],
        };
    },
    props: {
        id: {
            type: Number,
            required: true,
        },
    },
    created() {
        this.table_options.headings = {
            "asset_institutional_code.name": "Código",
            "asset_specific_category.name": "Categoría Específica",
            "acquisition_date.name": "Fecha de adquisición",
            depreciation_value: "Valor de depreciacion",
        };
        this.table_options.sortable = [
            "asset_institutional_code.name",
            "asset_specific_category.name",
            "acquisition_date.name",
            "depreciation_value",
        ];
        this.table_options.filterable = [
            "asset_institutional_code.name",
            "asset_specific_category.name",
            "acquisition_date.name",
            "depreciation_value",
        ];
        this.table_options.orderBy = { column: "id" };
    },

    mounted() {
        //
    },

    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {},

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

            url = vm.setUrl(url);

            axios
                .get(url)
                .then((response) => {
                    if (typeof response.data.record !== "undefined") {
                        this.record = response.data.record;
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
    },
};
</script>
