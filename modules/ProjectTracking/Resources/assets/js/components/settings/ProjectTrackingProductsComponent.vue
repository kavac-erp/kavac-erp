<template>
    <div
        class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center"
    >
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)"
            title="Productos"
            data-toggle="tooltip"
            @click="
                addRecord(
                    'add_products-config',
                    'projecttracking/products-config',
                    $event
                )
            "
        >
            <i class="fa fa-tag ico-3x"></i>
            <span>Productos</span>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_products-config"
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
                            <i class="icofont icofont-ui-copy inline-block"></i>
                            Registro de Producto
                        </h6>
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
                            <div class="col-md-6">
                                <div class="form-group" name="category">
                                    <label>Proyecto Asociado:</label>
                                    <select2
                                        :options="projects_list"
                                        id="project"
                                        data-toggle="tooltip"
                                        @input="showProductTypes"
                                        title="Seleccione el Proyecto asociado (requerido)"
                                        v-model="record.project_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" name="category">
                                    <label>Subproyecto Asociado:</label>
                                    <select2
                                        :options="subprojects_list"
                                        id="subproject"
                                        data-toggle="tooltip"
                                        @input="showProductTypes"
                                        title="Seleccione el Subproyecto Asociado (requerido)"
                                        v-model="record.subproject_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        type="text"
                                        id="name"
                                        placeholder="Nombre"
                                        data-toggle="tooltip"
                                        title="Ingrese el nombre del Producto (requerido)"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <label for="description"
                                        >Descripción:</label
                                    >
                                    <input
                                        type="text"
                                        id="description"
                                        placeholder="Descripción"
                                        class="form-control input-sm"
                                        data-toggle="tooltip"
                                        title="Ingrese la descripción del Producto"
                                        v-model="record.description"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="form-group is-required"
                                    name="category"
                                >
                                    <label>Dependencia:</label>
                                    <select2
                                        :options="dependencies_list"
                                        id="dependencyy"
                                        data-toggle="tooltip"
                                        title="Seleccione la dependencia del Producto (requerido)"
                                        v-model="record.dependency_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="form-group is-required"
                                    name="category"
                                >
                                    <label>Responsable del Producto:</label>
                                    <select2
                                        :options="payroll_staffs"
                                        id="responsablee"
                                        data-toggle="tooltip"
                                        title="Seleccione la persona responsable del Producto (requerido)"
                                        v-model="record.responsable_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="form-group is-required"
                                    name="category"
                                >
                                    <label>Tipos de Producto:</label>
                                    <v-multiselect
                                        :options="type_products_list"
                                        track_by="text"
                                        :hide_selected="false"
                                        id="product_typee"
                                        data-toggle="tooltip"
                                        title="Indique los tipos de productos"
                                        v-model="record.product_types"
                                    >
                                    </v-multiselect>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="start_date"
                                        >Fecha de inicio:</label
                                    >
                                    <input
                                        type="date"
                                        id="start_date"
                                        placeholder="Fecha inicial"
                                        class="form-control input-sm"
                                        data-toggle="tooltip"
                                        title="Indique la fecha inicial"
                                        v-model="record.start_date"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="end_date"
                                        >Fecha de culminación:</label
                                    >
                                    <input
                                        type="date"
                                        id="end_date"
                                        placeholder="Fecha final"
                                        class="form-control input-sm no-restrict"
                                        data-toggle="tooltip"
                                        title="Indique la fecha de culminación"
                                        v-model="record.end_date"
                                    />
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
                                            'projecttracking/products-config'
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
                                    slot="description"
                                    slot-scope="props"
                                    class="text-justify"
                                >
                                    <div
                                        class="mt-3"
                                        v-html="props.row.description"
                                    ></div>
                                </div>
                                <div slot="responsable_name" slot-scope="props">
                                    {{
                                        props.row.responsable.first_name
                                            ? props.row.responsable.first_name
                                            : props.row.responsable.name
                                    }}
                                    {{ props.row.responsable.last_name }}
                                </div>
                                <div slot="id" slot-scope="props">
                                    <div class="d-inline-flex">
                                        <project-tracking-product-info
                                            :modal_id="props.row.id"
                                            :url="
                                                'projecttracking/get-product-info/' +
                                                props.row.id
                                            "
                                        >
                                        </project-tracking-product-info>
                                        <button
                                            @click="
                                                initUpdate(
                                                    props.row.id,
                                                    'projecttracking/products-config'
                                                )
                                            "
                                            class="btn btn-warning btn-xs btn-icon btn-action"
                                            v-has-tooltip
                                            title="Modificar registro"
                                            aria-label="Modificar registro"
                                            data-toggle="tooltip"
                                            type="button"
                                        >
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button
                                            @click="
                                                deleteRecord(
                                                    props.row.id,
                                                    'projecttracking/products-config'
                                                )
                                            "
                                            class="btn btn-danger btn-xs btn-icon btn-action"
                                            v-has-tooltip
                                            title="Eliminar registro"
                                            aria-label="Eliminar registro"
                                            data-toggle="tooltip"
                                            type="button"
                                        >
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </div>
                                </div>
                            </v-client-table>
                        </div>
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
                name: "",
                project_id: "",
                subproject_id: "",
                description: "",
                code: "",
                dependency_id: "",
                type_product_id: 1,
                responsable_id: "",
                start_date: "",
                end_date: "",
                product_types: "",
            },
            payroll_staffs: [],
            dependencies_list: [],
            projects_list: [],
            subprojects_list: [],
            type_products_list: [],
            errors: [],
            records: [],
            columns: ["code", "name", "responsable_name", "end_date", "id"],
            payroll: "",
        };
    },
    props: {},
    methods: {
        /**
         * Método que obtiene los datos asociados al id del producto seleccionado
         *
         * @author  Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        async initUpdate(product_id, url) {
            const vm = this;
            try {
                url = vm.setUrl(`${url}/${product_id}`);
                const response = await axios.get(url);
                vm.record = response.data.records;
                vm.record.product_types = response.data.selected_product_types;
            } catch (error) {
                console.error(error);
            }
        },
        /**
         * Método que asigna los tipos de productos
         * Si se selecciona un proyecto asigna los tipos de productos asociado a ese proyecto
         * Si se selecciona tanto un subproyecto o un proyecto asigna los tipos de productos asociado a ese subproyecto
         * Si no se selecciona ni proyecto ni subproyecto se asigna la lista de todos los tipos de productos en registros comunes
         *
         * @author  Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        async showProductTypes() {
            const vm = this;
            let response;
            let url = "";
            try {
                if (
                    vm.record.subproject_id ||
                    (vm.record.subproject_id && vm.record.project_id)
                ) {
                    vm.type_products_list = [];
                    url = vm.setUrl(
                        `${window.app_url}/projecttracking/subprojects/${vm.record.subproject_id}`
                    );
                    response = await axios.get(url);
                    vm.type_products_list =
                        response.data.selected_product_types;
                } else if (vm.record.project_id) {
                    vm.type_products_list = [];
                    url = vm.setUrl(
                        `${window.app_url}/projecttracking/projects/${vm.record.project_id}`
                    );
                    response = await axios.get(url);
                    vm.type_products_list =
                        response.data.selected_product_types;
                } else {
                    vm.type_products_list = [];
                    vm.getProductTypes();
                }
            } catch (error) {
                console.error(error);
            }
        },
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Oscar González <xmaestroyixx@gmail.com>
         */
        reset() {
            this.record = {
                id: "",
                name: "",
                project_id: "",
                subproject_id: "",
                description: "",
                code: "",
                dependency_id: "",
                type_product_id: 1,
                responsable_id: "",
                start_date: "",
                end_date: "",
                product_types: "",
            };
        },
        getPersonal() {
            const vm = this;
            axios
                .get(`${window.app_url}/projecttracking/get-personal`)
                .then((response) => {
                    vm.payroll_staffs = response.data;
                });
        },
        getProjects() {
            const vm2 = this;
            axios
                .get(`${window.app_url}/projecttracking/get-projects`)
                .then((response) => {
                    vm2.projects_list = response.data;
                });
        },
        getSubprojects() {
            const vm3 = this;
            axios
                .get(`${window.app_url}/projecttracking/get-subprojects`)
                .then((response) => {
                    vm3.subprojects_list = response.data;
                });
        },
        getProductTypes() {
            const vm4 = this;
            axios
                .get(`${window.app_url}/projecttracking/get-product-types`)
                .then((response) => {
                    vm4.type_products_list = response.data;
                });
        },
        getDependencies() {
            const vm5 = this;
            axios
                .get(`${window.app_url}/projecttracking/get-dependencies`)
                .then((response) => {
                    vm5.dependencies_list = response.data;
                });
        },
        /**
         * Inicializa los registros base del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param {string}  url       Ruta que obtiene los datos a ser mostrado en listados
         * @param {string}  modal_id  Identificador del modal a mostrar con la información solicitada
         */
        async initRecords(url, modal_id) {
            this.errors = [];
            if (typeof this.reset === "function") {
                this.reset();
            }
            const vm = this;
            url = this.setUrl(url);

            await axios
                .get(url)
                .then((response) => {
                    if (typeof response.data.records !== "undefined") {
                        vm.records = response.data.records;
                        vm.payroll = response.data.payroll;
                    }
                    if (modal_id) {
                        $(`#${modal_id}`).modal("show");
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

            if (vm.payroll) {
                await vm.getPayrollStaffs();
            } else {
                await vm.getPersonal();
            }
        },
    },
    created() {
        this.table_options.headings = {
            code: "Código",
            name: "Nombre",
            responsable_name: "Responsable del Producto",
            end_date: "Fecha de Culminación",
            id: "Acción",
        };
        this.table_options.sortable = [
            "code",
            "name",
            "responsable_name",
            "end_date",
        ];
        this.table_options.filterable = [
            "code",
            "name",
            "responsable_name",
            "end_date",
        ];
        this.table_options.columnsClasses = {
            code: "col-md-2",
            name: "col-md-3",
            responsable_id: "col-md-3",
            end_date: "col-md-3",
            id: "col-md-1",
        };
    },
    mounted() {
        const vm = this;
        $("#add_projecttracking_products-config").on(
            "show.bs.modal",
            function () {
                vm.reset();
            }
        );
        vm.getPersonal();
        vm.getProjects();
        vm.getSubprojects();
        vm.getProductTypes();
        vm.getDependencies();
    },
};
</script>
