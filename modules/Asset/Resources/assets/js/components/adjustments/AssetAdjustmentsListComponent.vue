<template>
    <div>
        <v-client-table
            :columns="columns"
            :data="records"
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
                        v-if="isDisincorporable(props.row)"
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        type="button"
                        v-has-tooltip
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button v-else
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        type="button"
                        disabled
                        v-has-tooltip
                        >
                        <i class="fa fa-edit"></i>
                        </button>
                </div>
            </div>
        </v-client-table>
        <asset-info ref="AssetInfo"></asset-info>
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
        this.readRecords(this.route_list);
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
         * Método que obtiene los registros a mostrar
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {string} url Ruta que obtiene todos los registros solicitados
         */
        async readRecords(url) {
            const vm = this;
            vm.loading = true;
            url = this.setUrl(url);

            await axios
                .get(url)
                .then((response) => {
                    if (typeof response.data.data !== "undefined") {
                        vm.records = response.data.data;
                    }
                })
                .catch((error) => {
                    vm.logs("mixins.js", 285, error, "readRecords");
                });
            vm.loading = false;
        },

        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {
            //
        },

        /**
         * Verifica si un bien puede ser desincorporado
         *
         * @param {Object} row - La fila del bien a verificar.
         * @returns {boolean} - Devuelve true si el bien puede ser desincorporado, false en caso contrario
         */
        isDisincorporable(row) {
            const assetStatus = row.asset_status.name;
            if(assetStatus == "Desincorporado"){
                return false;
            }else{
                return true;
            }
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
    },
};
</script>
