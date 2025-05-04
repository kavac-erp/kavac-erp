<template>
    <div
        class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center"
    >
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)"
            title="Tipos de Proyecto"
            data-toggle="tooltip"
            @click="
                addRecord(
                    'add_activity_status',
                    'projecttracking/activity-status',
                    $event
                )
            "
        >
            <i class="icofont icofont-copy-black ico-3x"></i>
            <span>Estatus de Actividades</span>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_activity_status"
        >
            <div class="modal-dialog vue-crud" role="document">
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
                            <i
                                class="icofont icofont-copy-black inline-block ico-3x"
                            ></i>
                            Estatus de Actividades
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        type="text"
                                        placeholder="Nombre"
                                        data-toggle="tooltip"
                                        title="Indique el nombre del estatus de la actividad"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                        v-is-text
                                        required
                                    />
                                    <input type="hidden" v-model="record.id" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div>
                                    <label for="description"
                                        >Descripción:</label
                                    >
                                    <input
                                        type="text"
                                        id="description"
                                        placeholder="Descripción"
                                        class="form-control input-sm"
                                        v-model="record.description"
                                        data-toggle="tooltip"
                                        title="Indique la descripción del estatus de la actividad"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div>
                                    <label for="description">Color:</label>
                                    <div class="row">
                                        <input
                                            type="color"
                                            id="color"
                                            placeholder="Color"
                                            class="form-control input-sm"
                                            v-model="record.color"
                                            data-toggle="tooltip"
                                            title="Indique el color del estatus de la actividad"
                                            style="padding: 5px 12px !important"
                                        />
                                        <span
                                            class="text-danger"
                                            style="
                                                position: relative;
                                                right: 10px;
                                            "
                                            >*</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters"
                                data-dismiss="modal"
                            >
                                Cerrar
                            </button>
                            <button
                                type="button"
                                class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="
                                    createRecord(
                                        'projecttracking/activity-status'
                                    )
                                "
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table text-center">
                        <v-client-table
                            :columns="columns"
                            :data="records"
                            :options="table_options"
                        >
                            <div
                                slot="name"
                                slot-scope="props"
                                class="text-center"
                            >
                                <div class="mt-3" v-html="props.row.name"></div>
                            </div>
                            <div
                                slot="description"
                                slot-scope="props"
                                class="text-center"
                            >
                                <div
                                    class="mt-3"
                                    v-html="props.row.description"
                                ></div>
                            </div>
                            <div
                                slot="color"
                                slot-scope="props"
                                class="text-center"
                            >
                                <input
                                    type="color"
                                    id="color"
                                    class="form-control input-sm"
                                    v-model="props.row.color"
                                    data-toggle="tooltip"
                                    style="
                                        padding: 5px 12px !important;
                                        position: relative;
                                        margin: auto !important;
                                    "
                                    disabled
                                />
                            </div>
                            <div slot="id" slot-scope="props">
                                <button
                                    @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    v-has-tooltip
                                    title="Modificar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="
                                        deleteRecord(
                                            props.row.id,
                                            'projecttracking/activity-status'
                                        )
                                    "
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    v-has-tooltip
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
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
                color: "",
                name: "",
                description: "",
            },
            errors: [],
            records: [],
            accounting_accounts: [],
            columns: ["name", "description", "color", "id"],
        };
    },
    props: {},
    methods: {
        reset() {
            this.record = {
                id: "",
                color: "",
                name: "",
                description: "",
            };
        },
    },
    created() {
        this.table_options.headings = {
            name: "Nombre",
            description: "Descripción",
            color: "Color",
            id: "Acción",
        };
        this.table_options.sortable = ["name", "description", "color"];
        this.table_options.filterable = ["name", "description"];
        this.table_options.columnsClasses = {
            name: "col-md-2",
            description: "col-md-6",
            color: "col-md-2",
            id: "col-md-2",
        };
    },
};
</script>
