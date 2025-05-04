<template>
    <div class="card-body">
        <v-server-table
            :url="route_list"
            :columns="columns"
            :options="table_options"
            ref="tableAsignationResults"
        >
            <div slot="code" slot-scope="props" class="text-center">
                <span>
                    {{ props.row.code }}
                </span>
            </div>
            <div slot="payroll_staff" slot-scope="props" class="text-center">
                <span>
                    {{
                        props.row.payroll_staff
                            ? props.row.payroll_staff.first_name +
                              " " +
                              props.row.payroll_staff.last_name
                            : "N/A"
                    }}
                </span>
            </div>
            <div slot="location_place" slot-scope="props" class="text-center">
                <span>
                    {{
                        props.row.location_place
                            ? props.row.location_place.name
                            : "N/A"
                    }}
                </span>
            </div>
            <div slot="created" slot-scope="props" class="text-center">
                <span>
                    {{
                        props.row.created_at
                            ? format_date(props.row.created_at)
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
                    <asset-asignation-info
                        :index="props.row.id"
                        :route_list="
                            app_url +
                            '/asset/asignations/load-info/' +
                            props.row.id
                        "
                    >
                    </asset-asignation-info>
                    <asset-asignation-deliver-equipment
                        :index="props.row.id"
                        :route_list="
                            app_url +
                            '/asset/asignations/vue-info/' +
                            props.row.id
                        "
                        :state="props.row.state"
                    >
                    </asset-asignation-deliver-equipment>

                    <a
                        v-if="isEditable(props.row.state)"
                        @click="viewMessage()"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip"
                        href="#"
                        title="Modificar registro"
                        disabled
                    >
                        <i class="fa fa-edit"></i>
                    </a>

                    <button
                        v-else
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        type="button"
                    >
                        <i class="fa fa-edit"></i>
                    </button>

                    <!-- <asset-manage-record-component
							:key="props.row.id"
							:route_list="app_url + '/asset/asignations/vue-info/' + props.row.id"
							:data="props.row"
							:index="props.row.id"
							:action='"Asignación"'
							:state="props.row.state">
					</asset-manage-record-component> -->
                    <a
                        class="btn btn-primary btn-xs btn-icon"
                        :href="asset_assignation_pdf + props.row.id"
                        title="Imprimir acta"
                        data-toggle="tooltip"
                        v-has-tooltip
                        target="_blank"
                    >
                        <i class="fa fa-print" style="text-align: center"></i>
                    </a>

                    <!-- <a v-if="isRemovable(props.row.state)"
							@click="viewMessage()"
							class="btn btn-danger btn-xs btn-icon btn-action"
							data-toggle="tooltip"
							href="#"
							title="Eliminar registro"
							disabled
					>
						<i class="fa fa-trash-o"></i>
					</a> -->

                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        type="button"
                        disabled
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-server-table>
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            errors: [],
            asset_assignation_pdf: `${window.app_url}/asset/asignations/asignations-record-pdf/`,
            url: `${window.app_url}/asset/asignations/asignations-record-pdf/`,
            columns: [
                "code",
                "payroll_staff",
                "location_place",
                "created",
                "state",
                "id",
            ],
        };
    },

    created() {
        this.table_options.headings = {
            code: "Código",
            payroll_staff: "Trabajador",
            location_place: "Lugar de ubicación",
            created: "Fecha de asignación",
            state: "Estado de la asignación",
            id: "Acción",
        };
        this.table_options.sortable = [
            "code",
            "payroll_staff",
            "location_place",
            "created",
            "state",
        ];
        this.table_options.filterable = [
            "code",
            "payroll_staff",
            "location_place",
            "created",
            "state",
        ];
        this.table_options.orderBy = { column: "id" };
    },
    mounted() {
        //this.readRecords(this.route_list);
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {},

        isEditable(state) {
            const editable = state == "Asignado";
            return !editable;
        },

        isRemovable(state) {
            const removable = state == "Asignado";
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
                                vm.showMessage("destroy");
                                location.href = response.data.redirect;
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
         * Método para descargar el pdf del acta de una asignación de bienes
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
