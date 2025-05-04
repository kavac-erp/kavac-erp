<template>
    <div>
        <v-server-table
            :url="route_list"
            :columns="columns"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button
                        @click.prevent="
                            setDetails('AssetInfo', props.row.id, 'AssetInfo')
                        "
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button"
                    >
                        <i class="fa fa-eye"></i>
                    </button>

                    <button
                        v-if="isAssigned(props.row)"
                        @click="assignAsset(props.row.id)"
                        class="btn btn-primary btn-xs btn-icon btn-action"
                        title="Asignar Bien"
                        data-toggle="tooltip"
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-filter"></i>
                    </button>
                    <button
                        v-else
                        class="btn btn-primary btn-xs btn-icon btn-action"
                        title="Asignar Bien"
                        data-toggle="tooltip"
                        disabled
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-filter"></i>
                    </button>

                    <button
                        v-if="isDisincorporable(props.row)"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Desincorporar Bien"
                        data-toggle="tooltip"
                        disabled
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-chain"></i>
                    </button>
                    <button
                        v-else
                        @click="disassignAsset(props.row.id)"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Desincorporar Bien"
                        data-toggle="tooltip"
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-chain"></i>
                    </button>

                    <button
                        v-if="isEditable(props.row)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        disabled
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        v-else
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-edit"></i>
                    </button>

                    <!-- <button v-if="isRemovable(props.row)"
          class="btn btn-danger btn-xs btn-icon btn-action"
          title="Eliminar registro"
          data-toggle="tooltip"
          disabled
          type="button"
          v-has-tooltip
        >
          <i class="fa fa-trash-o"></i>
        </button> -->
                    <button
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro"
                        data-toggle="tooltip"
                        type="button"
                        v-has-tooltip
                        disabled
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-server-table>
        <asset-info ref="AssetInfo"> </asset-info>
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            supplier: [],
            columns: [
                "asset_institutional_code.name",
                "code_sigecof.name",
                "asset_specific_category.name",
                "asset_condition.name",
                "asset_status.name",
                "id",
            ],
        };
    },
    created() {
        this.table_options.headings = {
            "code_sigecof.name": "Código SIGECOF",
            "asset_specific_category.name": "Categoría específica",
            "asset_condition.name": "Condición física",
            "asset_status.name": "Estatus de uso",
            "asset_institutional_code.name": "Código interno",
            id: "Acción",
        };
        this.table_options.sortable = [
            "code_sigecof.name",
            "asset_specific_category.name",
            "asset_condition.name",
            "asset_status.name",
            "asset_institutional_code.name",
        ];
        this.table_options.filterable = [
            "code_sigecof.name",
            "asset_specific_category.name",
            "asset_condition.name",
            "asset_status.name",
            "asset_institutional_code.name",
        ];
        this.table_options.orderBy = { column: "id" };
    },
    mounted() {
        //this.readRecords(this.route_list);
    },
    methods: {
        /**
         * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
         *
         * @method    setDetails
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param     string   ref       Identificador del componente
         * @param     integer  id        Identificador del registro seleccionado
         * @param     object  var_list  Objeto con las variables y valores a asignar en las variables del componente
         */
        setDetails(ref, id, modal, var_list = null) {
            const vm = this;
            if (var_list) {
                for (var i in var_list) {
                    vm.$refs[ref][i] = var_list[i];
                }
            } else {
                vm.$refs[ref].record = vm.$refs.tableResults.data.filter(
                    (r) => {
                        return r.id === id;
                    }
                )[0];
            }
            vm.$refs[ref].id = id;
            document.getElementById("info_general").click();

            $(`#${modal}`).modal("show");
        },

        /**
         * Función que verifica si un bien está en proceso de solicitud,
         * entregado o rechazado
         *
         * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
         */
        isReq(value) {
            if (value === null) {
                return true;
            } else if (typeof value.asset_request !== "undefined") {
                if (
                    value.asset_request.state === "Entregados" ||
                    value.asset_request.state === "Rechazado"
                ) {
                    return true;
                } else {
                    return false;
                }
            }
        },

        /**
         * Verifica si un bien está asignado o ha sido entregado.
         *
         * @param {Object} row - La fila del bien a verificar.
         * @returns {boolean} - Devuelve true si el bien está asignado o entregado, false en caso contrario.
         */
        isAssigned(row) {
            // Verifica si el estado del bien es "asignado" (id 10), la condición es "bueno" (id 1) y el tipo es "mueble" (id 1).
            // Si se cumplen todas las condiciones, devuelve false (no asignado ni entregado).
            // De lo contrario, devuelve true (asignado o entregado).

            return (
                row.asset_status.id == 10 &&
                row.asset_condition.id == 1 &&
                row.asset_type.id == 1
            );
        },

        /**
         * Verifica si un bien puede ser desincorporado
         *
         * @param {Object} row - La fila del bien a verificar.
         * @returns {boolean} - Devuelve true si el bien puede ser desincorporado, false en caso contrario
         */
        isDisincorporable(row) {
            const assetStatus = row.asset_status.id;
            const disincorporated =
                row.asset_disincorporation_asset == null &&
                [1, 3, 6, 11].indexOf(assetStatus) === -1;

            return !disincorporated;
        },
        /**
         * Verifica si un bien puede ser editado
         *
         * @param {Object} row - La fila del bien a verificar.
         * @returns {boolean} - Devuelve true si el bien puede ser editad, false en caso contrario
         */
        isEditable(row) {
            const assetStatus = row.asset_status.id;
            const assetCondition = row.asset_condition.id;
            const editable =
                row.asset_disincorporation_asset == null &&
                [1, 6, 11].indexOf(assetStatus) === -1 &&
                [4, 7].indexOf(assetCondition) === -1;

            return !editable;
        },

        /**
         * Verifica si un bien puede ser eliminado
         *
         * @param {Object} row - La fila del bien a verificar.
         * @returns {boolean} - Devuelve true si el bien puede ser editad, false en caso contrario
         */
        isRemovable(row) {
            const remove =
                row.asset_disincorporation_asset == null &&
                row.asset_asignation_asset == null &&
                row.asset_request_asset == null;

            return !remove;
        },
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {},

        /**
         * Redirige al formulario de asignación de bienes institucionales
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param [Integer] $id Identificador único del bien
         */
        assignAsset(id) {
            location.href = `${window.app_url}/asset/asignations/asset/${id}`;
        },

        /**
         * Redirige al formulario de desincorporación de bienes institucionales
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param [Integer] $id Identificador único del bien
         */
        disassignAsset(id) {
            location.href = `${window.app_url}/asset/disincorporations/asset/${id}`;
        },
        /**
         * Reescribe el método initRecords para cambiar su comportamiento por defecto
         * Inicializa los registros base del formulario
         *
         * @author Henry Paredes <hparedes@cenditel.gob.ve>
         *
         * @param {string} url      Ruta que obtiene los datos a ser mostrado en listados
         * @param {string} modal_id Identificador del modal a mostrar con la información solicitada
         */
        initRecords(url, modal_id) {
            this.errors = [];
            this.reset();
            const vm = this;
            url = vm.setUrl(url);

            axios
                .get(url)
                .then((response) => {
                    if (typeof response.data.records !== "undefined") {
                        vm.records = response.data.records;
                        vm.total = response.data.total;
                        vm.lastPage = response.data.lastPage;
                        vm.$refs.tableMax.setLimit(vm.perPage);
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

        /**
         * Reescribe el método deleteRecord para funcionar con v-client-table
         * eliminando el metodo refresh();
         * Método para la eliminación de registros
         *
         * @author Manuel Zambrano <manzambrano@cenditel.gob.ve>
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
                callback: async function (result) {
                    if (result) {
                        vm.loading = true;
                        /** @type {object} Objeto con los datos del registro a eliminar */
                        let recordDelete = JSON.parse(
                            JSON.stringify(
                                vm.records.filter((rec) => {
                                    return rec.id === id;
                                })[0]
                            )
                        );

                        await axios
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

                                vm.showMessage("destroy");
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
                                }
                                vm.logs(
                                    "mixins.js",
                                    498,
                                    error,
                                    "deleteRecord"
                                );
                            });
                        vm.loading = false;
                    }
                },
            });
        },
    },
};
</script>
