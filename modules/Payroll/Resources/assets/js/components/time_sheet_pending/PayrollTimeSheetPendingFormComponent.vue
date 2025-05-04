<template>
    <section id="PayrollEmploymentForm">
        <!-- card-body -->
        <div class="card-body">
            <!-- mensajes de error -->
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="m-2">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-danger" v-if="peopleNotFound.length > 0">
                <div class="m-2">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>El listado de trabajadores que se importo no se correponde con el grupo de supervisados</strong> las siguientes personas no se han
                    encontrado al importar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="peopleNotFound = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="people in peopleNotFound" :key="people">
                            {{ people["Nombre"] }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-danger" v-if="peopleNotOneFound && peopleNotFound.length === 0">
                <div class="m-2">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>El listado de trabajadores que se importo no se correponde con el grupo de supervisados</strong>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="peopleNotOneFound = false"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                </div>
            </div>
            <!-- ./mensajes de error -->

            <div class="row">
                <div class="col-md-6">
                    <!-- Desde -->
                    <div class="form-group is-required">
                        <label for="from_date">Desde:</label>
                        <input
                            type="date"
                            id="from_date"
                            class="form-control no-restrict input-sm"
                            v-model="record.from_date"
                            data-toggle="tooltip"
                            title="Indique el periodo (requerido)"
                        />
                        <input
                            type="hidden"
                            name="id"
                            id="id"
                            v-model="record.id"
                        />
                    </div>
                    <!-- ./Desde -->
                </div>
                <div class="col-md-6">
                    <!-- Hasta -->
                    <div class="form-group is-required">
                        <label for="to_date">Hasta:</label>
                        <input
                            type="date"
                            id="to_date"
                            placeholder="Hasta"
                            class="form-control no-restrict input-sm"
                            v-model="record.to_date"
                            data-toggle="tooltip"
                            title="Indique el periodo (requerido)"
                        />
                    </div>
                    <!-- ./Hasta -->
                </div>
                <div class="col-md-6">
                    <!-- Código -->
                    <div class="form-group is-required">
                        <label>Código grupo de supervisados:</label>
                        <select2
                            :options="payroll_supervised_groups"
                            v-model="record.payroll_supervised_group_id"
                            @input="
                                getSupervisedGroupData();
                                setTimeSheetData();
                            "
                        >
                        </select2>
                    </div>
                    <!-- ./Código -->
                </div>
                <div class="col-md-6" v-if="record.payroll_supervised_group_id">
                    <!-- Supervisor -->
                    <div class="form-group">
                        <label for="supervisor">Supervisor:</label>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="Approver">
                                {{ record.supervisor }}
                            </span>
                        </div>
                    </div>
                    <!-- ./Supervisor -->
                </div>
                <div class="col-md-6" v-if="record.payroll_supervised_group_id">
                    <!-- Aprobador -->
                    <div class="form-group">
                        <label for="approver">Aprobador:</label>
                        <div class="row" style="margin: 1px 0">
                            <span class="col-md-12" id="Approver">
                                {{ record.approver }}
                            </span>
                        </div>
                    </div>
                    <!-- ./Aprobador -->
                </div>
                <div class="col-md-6">
                    <!-- Parámetros de la hoja de tiempo -->
                    <div class="form-group is-required">
                        <label>Parámetros de la hoja de tiempo:</label>
                        <select2
                            :options="payroll_time_sheet_parameters"
                            v-model="record.payroll_time_sheet_parameter_id"
                            @input="setTimeSheetColumns()"
                        >
                        </select2>
                    </div>
                    <!-- ./Parámetros de la hoja de tiempo -->
                </div>
            </div>
            <br />
            <br />
            <div class="row">
                <div
                    class="col-md-12"
                    v-if="record.payroll_time_sheet_parameter_id"
                >
                    <!-- Tabla con los datos para la hoja de tiempo -->
                    <div class="float-right">
                        <button
                            class="btn btn-sm btn-primary btn-custom"
                            data-toggle="tooltip"
                            type="button"
                            title="Importar registros"
                            @click="setFile()"
                        >
                            <i class="fa fa-upload"></i>
                        </button>
                        <button
                            class="btn btn-sm btn-primary btn-custom"
                            data-toggle="tooltip"
                            type="button"
                            title="Exportar registros"
                            @click="exportTimeSheetData()"
                        >
                            <i class="fa fa-download"></i>
                        </button>
                        <input
                            type="file"
                            id="import_data"
                            ref="fileInput"
                            class="nodisplay"
                            @change="importTimeSheetData()"
                        />
                    </div>
                </div>
            </div>
            <v-draggable-table
                ref="draggableTable"
                :custom_components="custom_components"
                :columns="draggableColumns"
                :data="draggableData"
            >
            </v-draggable-table>
            <!-- ./Tabla con los datos para la hoja de tiempo -->
            <payroll-time-sheet-pending-concepts
                ref="concepts"
            ></payroll-time-sheet-pending-concepts>
            <payroll-time-sheet-pending-observations
                ref="observations"
            ></payroll-time-sheet-pending-observations>
        </div>
        <!-- Final card-body -->

        <!-- card-footer -->
        <div class="card-footer text-right" id="helpParamButtons">
            <button
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                type="button"
                title="Borrar datos del formulario"
                @click="reset()"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createRecord('payroll/time-sheet-pending')"
                data-toggle="tooltip"
                title="Guardar registro"
                class="btn btn-success btn-icon btn-round"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
        <!-- Final card-footer -->
    </section>
</template>
<script>
export default {
    props: {
        payroll_time_sheet_pending_id: Number,
    },
    data() {
        return {
            record: {
                id: "",
                from_date: "",
                to_date: "",
                payroll_supervised_group_id: "",
                payroll_time_sheet_parameter_id: "",
                supervisor: "",
                approver: "",
                time_sheet_data: {},
                time_sheet_columns: {},
            },
            peopleNotOneFound:false,
            payroll_supervised_groups: [],
            payroll_time_sheet_parameters: [],
            payroll_staffs_reference: [],
            peopleNotFound: new Array(),
            errors: [],
            draggableColumns: [],
            draggableData: [],
            custom_components: [
                {
                    ref: "concepts",
                    modalId: "PayrollTimeSheetPendingConcepts",
                    column: "Conceptos",
                },
                {
                    ref: "observations",
                    modalId: "PayrollTimeSheetPendingObservations",
                    column: "Observación",
                },
            ],
        };
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        reset() {
            const vm = this;
            vm.record = {
                id: "",
                from_date: "",
                to_date: "",
                payroll_supervised_group_id: "",
                payroll_time_sheet_parameter_id: "",
                supervisor: "",
                approver: "",
                time_sheet_data: {},
                time_sheet_columns: {},
            };
        },

        /**
         * Método que carga los datos del supervisor y el aprobador
         * a partir del grupo de supervisados seleccionado
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        getSupervisedGroupData() {
            const vm = this;

            if (vm.record.payroll_supervised_group_id) {
                let group = vm.payroll_supervised_groups.find(function (
                    $group
                ) {
                    return (
                        vm.record.payroll_supervised_group_id == $group["id"]
                    );
                });

                vm.record.supervisor = group.supervisor.name;
                vm.record.approver = group.approver.name;
            } else {
                vm.record.supervisor = null;
                vm.record.approver = null;
            }
        },

        /**
         * Método que obitiene los parámetros de hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        async getPayrollTimeSheetParameters() {
            const vm = this;
            vm.payroll_time_sheet_parameters = [];
            await axios
                .get(`${window.app_url}/payroll/get-time-sheet-parameters`)
                .then((response) => {
                    vm.payroll_time_sheet_parameters = response.data;
                });
        },

        /**
         * Método que carga las columnas de la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setTimeSheetColumns() {
            const vm = this;

            if (vm.record.payroll_time_sheet_parameter_id) {
                if (!vm.record.id || vm.record.time_sheet_columns == null) {
                    let draggableColumns = [
                        {
                            name: "N°",
                            type: "text",
                            isDraggable: false,
                        },
                        {
                            name: "Ficha",
                            type: "text",
                            isDraggable: false,
                        },
                        {
                            name: "Nombre",
                            type: "text",
                            isDraggable: false,
                        },
                    ];

                    let params = vm.payroll_time_sheet_parameters.find(
                        function ($param) {
                            return (
                                vm.record.payroll_time_sheet_parameter_id ==
                                $param["id"]
                            );
                        }
                    );

                    Object.values(params.parameters).forEach(
                        (param, index, array) => {
                            let group = "";
                            let groupMax = "";

                            param.forEach((p) => {
                                if ("" === group) {
                                    group = p.group;
                                    groupMax = p.max;
                                }

                                draggableColumns.push({
                                    name: p.text,
                                    group: p.group,
                                    sign: p.sign,
                                    type: "input",
                                    isDraggable: true,
                                });
                            });

                            if (index != array.length - 1) {
                                draggableColumns.push({
                                    name: "Subtotal",
                                    type: "subtotal",
                                    group: group,
                                    max: groupMax,
                                    isDraggable: false,
                                });
                            } else {
                                draggableColumns.push(
                                    {
                                        name: "Subtotal",
                                        type: "subtotal",
                                        group: group,
                                        max: groupMax,
                                        isDraggable: false,
                                    },
                                    {
                                        name: "Total",
                                        type: "total",
                                        isDraggable: false,
                                    },
                                    {
                                        name: "Observación",
                                        field: "observation",
                                        type: "custom",
                                        isDraggable: false,
                                    },
                                    {
                                        name: "Conceptos",
                                        type: "custom",
                                        field: "payroll_payment_types",
                                        isDraggable: false,
                                    }
                                );
                            }
                        }
                    );

                    vm.draggableColumns = draggableColumns;
                } else {
                    let draggableColumns = [];

                    vm.record.time_sheet_columns.forEach((column, index) => {
                        if (column.name.includes("subtotal")) {
                            column.name = "Subtotal";
                        }

                        if (column.name == "total") {
                            column.name = "Total";
                        }

                        draggableColumns.push(column);
                    });
                    vm.draggableColumns = vm.record.time_sheet_columns;
                }
            } else {
                vm.draggableColumns = [];
            }

            if (vm.record.id) {
                Vue.set(
                    vm.$refs.draggableTable,
                    "inputValues",
                    vm.record.time_sheet_data
                );
            }
        },

        /**
         * Método que carga los datos de la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        setTimeSheetData() {
            const vm = this;
            let draggableData = [];
            let payroll_staffs = [];

            if (
                vm.$refs.draggableTable &&
                typeof vm.$refs.draggableTable != "undefined"
            ) {
                vm.$refs.draggableTable.inputValues = {};
            }

            if (vm.record.payroll_supervised_group_id) {
                let group = vm.payroll_supervised_groups.find(function (
                    $group
                ) {
                    return (
                        vm.record.payroll_supervised_group_id == $group["id"]
                    );
                });
                let index = 1;

                group.payroll_staffs.forEach((staff, indexof) => {
                    draggableData.push({
                        "N°": index++,
                        Ficha: staff.worksheet_code,
                        Nombre: staff.name,
                        staff_id: staff.id,
                        id_number: staff.id_number,
                    });
                    payroll_staffs.push({
                        "N°": index,
                        index: indexof,
                        Nombre: staff.name,
                        id_number: staff.id_number,
                        staff_id: staff.id,
                    });
                });
            }

            vm.draggableData = draggableData;
            vm.payroll_staffs_reference = payroll_staffs;
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

                if (
                    vm.$refs.draggableTable &&
                    typeof vm.$refs.draggableTable != "undefined"
                ) {
                    let columns = [];

                    vm.$refs.draggableTable.columns.forEach((column, index) => {
                        let updatedColumn = { ...column };

                        if (updatedColumn.name.includes("Subtotal")) {
                            updatedColumn.name =
                                "subtotal" + " - " + updatedColumn.group;
                        }

                        if (updatedColumn.name == "Total") {
                            updatedColumn.name = "total";
                        }

                        if (
                            !updatedColumn.name.includes("Conceptos") &&
                            !updatedColumn.name.includes("Observación")
                        ) {
                            columns.push({
                                position: index,
                                name: updatedColumn.name,
                                group: updatedColumn.group,
                                type: updatedColumn.type,
                                isDraggable: updatedColumn.isDraggable,
                                max: updatedColumn.max,
                            });
                        } else if (updatedColumn.name.includes("Conceptos")) {
                            columns.push({
                                position: index,
                                name: updatedColumn.name,
                                group: updatedColumn.group,
                                type: updatedColumn.type,
                                field: "payroll_payment_types",
                                isDraggable: updatedColumn.isDraggable,
                                max: updatedColumn.max,
                            });
                        } else if (updatedColumn.name.includes("Observación")) {
                            columns.push({
                                position: index,
                                name: updatedColumn.name,
                                group: updatedColumn.group,
                                type: updatedColumn.type,
                                field: "observation",
                                isDraggable: updatedColumn.isDraggable,
                                max: updatedColumn.max,
                            });
                        }
                    });

                    vm.record.time_sheet_data =
                        vm.$refs.draggableTable.inputValues;
                    vm.record.time_sheet_columns = columns;
                } else {
                    vm.record.time_sheet_data = [];
                    vm.record.time_sheet_columns = [];
                }

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

            let columns = [];

            vm.$refs.draggableTable.columns.forEach((column, index) => {
                let updatedColumn = { ...column };

                if (updatedColumn.name.includes("Subtotal")) {
                    updatedColumn.name =
                        "subtotal" + " - " + updatedColumn.group;
                }

                if (updatedColumn.name == "Total") {
                    updatedColumn.name = "total";
                }

                if (
                    !updatedColumn.name.includes("Conceptos") &&
                    !updatedColumn.name.includes("Observación")
                ) {
                    columns.push({
                        position: index,
                        name: updatedColumn.name,
                        group: updatedColumn.group,
                        type: updatedColumn.type,
                        isDraggable: updatedColumn.isDraggable,
                        max: updatedColumn.max,
                    });
                } else if (updatedColumn.name.includes("Conceptos")) {
                    columns.push({
                        position: index,
                        name: updatedColumn.name,
                        group: updatedColumn.group,
                        type: updatedColumn.type,
                        field: "payroll_payment_types",
                        isDraggable: updatedColumn.isDraggable,
                        max: updatedColumn.max,
                    });
                } else if (updatedColumn.name.includes("Observación")) {
                    columns.push({
                        position: index,
                        name: updatedColumn.name,
                        group: updatedColumn.group,
                        type: updatedColumn.type,
                        field: "observation",
                        isDraggable: updatedColumn.isDraggable,
                        max: updatedColumn.max,
                    });
                }
            });

            vm.record.time_sheet_columns = columns;
            vm.record.time_sheet_data = vm.$refs.draggableTable.inputValues;

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

        /**
         * Método que carga el formulario con los datos a modificar
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         * @param  {integer} index Identificador del registro a ser modificado
         */
        async loadForm(id) {
            let vm = this;
            vm.errors = [];
            vm.getPayrollSupervisedGroups(id, "pending");
            let recordEdit = await axios
                .get(
                    `${window.app_url}/payroll/time-sheet-pending/vue-info/${id}`
                )
                .then((response) => {
                    return response.data.record;
                });

            vm.record = recordEdit;
        },

        /**
         * Metodo que permite exportar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        exportTimeSheetData() {
            const vm = this;
            const originalColumns = vm.$refs.draggableTable.columns;
            let columns = originalColumns.slice(0, 1);
            columns.splice(1, 0, {
                name: "Cedula",
                type: "text",
                isDraggable: true,
            });
            for (let i = 1; i < originalColumns.length; i++) {
                columns.push(originalColumns[i]);
            }
            let data = {
                columns: columns,
                data: vm.$refs.draggableTable.data,
                inputValues: vm.$refs.draggableTable.inputValues,
            };

            axios
                .post(
                    `${window.app_url}/payroll/time-sheet-pending/export`,
                    { params: data },
                    { responseType: "blob" }
                )
                .then((response) => {
                    const url = window.URL.createObjectURL(
                        new Blob([response.data])
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", "payroll-time-sheet.xlsx");
                    document.body.appendChild(link);
                    link.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        /**
         * Metodo que permite importar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        importTimeSheetData() {
            const vm = this;
            const inputFile = document.getElementById("import_data");
            const file = inputFile.files[0];
            vm.peopleNotFound = new Array();
            const formData = new FormData();
            formData.append("file", file);

            axios
                .post(
                    `${window.app_url}/payroll/time-sheet-pending/import`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                )
                .then((response) => {
                    let formattedColumns = [];
                    const avalibilityItems = vm.$refs.draggableTable.data;
                    let columns = vm.$refs.draggableTable.columns;
                    let people = new Array();
                    columns.forEach((column) => {
                        let columnName = column.name.toLowerCase();
                        columnName = columnName
                            .replace(/ - /g, "_")
                            .replace(/ /g, "_");

                        if (columnName === "n°") {
                            columnName = "n";
                        }

                        vm.$refs.draggableTable.data.forEach((d) => {
                            formattedColumns.push({
                                staff_id: d.staff_id,
                                column_name: columnName,
                                id_number: d.id_number,
                                name: column.name + "-" + d.staff_id,
                                n: d["N°"],
                            });
                        });
                    });

                    for (let i = 0; i < formattedColumns.length; i++) {
                        let col_name = formattedColumns[i].name;
                        let n = formattedColumns[i].n;
                        let id_number = formattedColumns[i].id_number;
                        let column_name = formattedColumns[i].column_name;

                        let valor = response.data.find(
                            (item) =>
                                item.cedula == id_number &&
                                item[column_name] !== null
                        );

                        if (
                            valor &&
                            !col_name.includes("N°") &&
                            !col_name.includes("Nombre") &&
                            !col_name.includes("total") &&
                            !col_name.includes("Total")
                        ) {
                            function removeValue(value) {
                                // Si el valor en el índice actual del array coincide con el valor especificado (2)
                                if (value.id_number === id_number) {
                                    return true; // Indica que se debe eliminar
                                }
                                return false; // Indica que se debe incluir
                            }
                            vm.peopleNotOneFound = false;
                            // Recorrer el array original y crear uno nuevo filtrado
                            avalibilityItems.forEach(function (value, index) {
                                if (
                                    vm.peopleNotFound.length == 0 &&
                                    !removeValue(value)
                                ) {
                                    people[people.length] = {
                                        Ficha: value.Ficha,
                                        Nombre: value.Nombre,
                                        "N°": value["N°"],
                                        id_number: value.id_number,
                                        staff_id: value.staff_id,
                                    };
                                } else {
                                    if (
                                        removeValue(value) &&
                                        vm.peopleNotFound.some(
                                            (element) =>
                                                element.id_number === id_number
                                        )
                                    ) {
                                        vm.peopleNotFound.splice(
                                            people.findIndex(
                                                (element) =>
                                                    element.id_number ===
                                                    id_number
                                            ),
                                            1
                                        );
                                    }
                                }
                            });
                            if (vm.peopleNotFound.length == 0) {
                                vm.peopleNotFound = people;
                            }
                            Vue.set(
                                vm.$refs.draggableTable.inputValues,
                                col_name,
                                parseInt(valor[column_name])
                            );
                        }else{
                            vm.peopleNotOneFound = true;
                        }
                    }
                    vm.showMessage(
                        "custom",
                        "!Éxito!",
                        "success",
                        "screen-ok",
                        "Se ha realizado el proceso de importación exitosamente"
                    );
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        /**
         * Activa el método para importar la hoja de tiempo
         *
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        setFile() {
            this.$refs.fileInput.click();
        },
    },

    created() {
        const vm = this;
        vm.getPayrollTimeSheetParameters();

        if (vm.payroll_time_sheet_pending_id) {
            vm.loadForm(vm.payroll_time_sheet_pending_id);
        } else {
            vm.getPayrollSupervisedGroups();
        }
    },
};
</script>
