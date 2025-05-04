<template>
    <div
        class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center"
    >
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)"
            title="Estatus de Entrega"
            data-toggle="tooltip"
            @click="
                addRecord(
                    'add_delivery_status',
                    'projecttracking/delivery-status',
                    $event
                )
            "
        >
            <i class="icofont icofont-ui-folder ico-3x"></i>
            <span>Estatus de Entrega</span>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_delivery_status"
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
                                class="icofont icofont-ui-folder inline-block ico-3x"
                            ></i>
                            Estatus de Entrega
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label>Estatus:</label>
                                    <select2
                                        :options="status_list"
                                        data-toggle="tooltip"
                                        title="Seleccione un estatus"
                                        v-model="record.status"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        type="text"
                                        placeholder="Nombre"
                                        data-toggle="tooltip"
                                        title="Indique el nombre del estatus de la entrega"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                        v-is-text
                                        required
                                    />
                                    <input type="hidden" v-model="record.id" />
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                        title="Indique la descripción del estatus de la entrega"
                                    />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div>
                                    <label for="color">Color:</label>
                                    <div class="row">
                                        <input
                                            type="color"
                                            id="color"
                                            placeholder="Color"
                                            class="form-control input-sm"
                                            style="padding: 5px 12px !important"
                                            v-model="record.color"
                                            data-toggle="tooltip"
                                            title="Indique el color del estatus de la entrega"
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
                                        'projecttracking/delivery-status'
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
                            <div slot="name" slot-scope="props">
                                <span
                                    class="badge badge-secondary"
                                    style="w-auto"
                                    v-bind:style="{
                                        color: props.row.color,
                                    }"
                                >
                                    <div v-html="props.row.name"></div>
                                </span>
                            </div>
                            <div
                                slot="status"
                                slot-scope="props"
                                class="text-center"
                            >
                                <div
                                    class="mt-3"
                                    v-html="idToStatus(props.row.status)"
                                ></div>
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
                                            'projecttracking/delivery-status'
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
                status: "",
            },
            errors: [],
            records: [],
            columns: ["name", "status", "description", "id"],
            status_list: [
                {
                    id: "",
                    text: "Seleccione...",
                },
                {
                    id: 1,
                    text: "Entrega antes del tiempo",
                },
                {
                    id: 2,
                    text: "Entrega a tiempo",
                },
                {
                    id: 3,
                    text: "Entrega tardía",
                },
                {
                    id: 4,
                    text: "No entregada",
                },
            ],
        };
    },
    methods: {
        reset() {
            this.record = {
                id: "",
                color: "",
                name: "",
                description: "",
                status: "",
            };
            this.errors = [];
        },
        idToStatus(id) {
            const vm = this;
            const foundDeliveryStatus = vm.status_list.find(
                (deliveryStatus) => deliveryStatus.id == id
            );
            return foundDeliveryStatus.text;
        },
    },
    created() {
        this.table_options.headings = {
            color: "Color",
            name: "Nombre",
            description: "Descripción",
            status: "Estatus",
            id: "Acción",
        };
        this.table_options.sortable = ["color", "name", "status"];
        this.table_options.filterable = ["color", "name", "status"];
        this.table_options.columnsClasses = {
            name: "col-md-2",
            description: "col-md-4",
            id: "col-md-2",
            color: "col-md-2",
            status: "col-md-2",
        };
    },
};
</script>
