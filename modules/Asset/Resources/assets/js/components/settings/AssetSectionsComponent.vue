<template>
    <section id="assetSectionsComponent">
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="#"
            title="Registros de secciones de edificaciones"
            data-toggle="tooltip"
            v-has-tooltip
            @click="addRecord('add_section', 'asset/sections', $event)"
        >
            <i class="icofont icofont-shopping-cart ico-3x"></i>
            <span>Secciones</span>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_section"
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
                            <i class="icofont icofont-shopping-cart ico-2x"></i>
                            Nueva seccion
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="office">
                                        <input
                                            type="radio"
                                            id="office"
                                            name="options"
                                            value="office"
                                            v-model="selectedOption"
                                        />
                                        Sección
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="offices">
                                        <input
                                            type="radio"
                                            id="offices"
                                            name="options"
                                            value="offices"
                                            v-model="selectedOption"
                                        />
                                        Secciones
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row"></div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label for="input_buildings"
                                        >Edificación:</label
                                    >
                                    <select2
                                        :options="buildings"
                                        v-model="record.building_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label for="input_floor">Nivel:</label>
                                    <select2
                                        :options="floors"
                                        v-model="record.floor_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label for="section-name">Nombre:</label>
                                    <input
                                        id="section-name"
                                        type="text"
                                        placeholder="Nombre de la secciòn"
                                        data-toggle="tooltip"
                                        title="Indique el nombre de la secciòn de la edificacion(requerido)"
                                        autocomplete="false"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                    />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="section-description"
                                        >Descripción:</label
                                    >
                                    <input
                                        id="section-description"
                                        type="text"
                                        placeholder="Descripción de la secciòn de  la edificación"
                                        data-toggle="tooltip"
                                        title="Indique la descripción de la secciòn de la edificacion(opcional)"
                                        autocomplete="false"
                                        class="form-control input-sm"
                                        v-model="record.description"
                                    />
                                </div>
                            </div>

                            <div
                                v-if="selectedOption === 'offices'"
                                class="col-md-3"
                            >
                                <div class="form-group is-required">
                                    <label for="officeAmount"
                                        >Número de secciones</label
                                    >
                                    <input
                                        type="number"
                                        class="form-control input-sm"
                                        id="officeAmount"
                                        name="office_amount"
                                        min="2"
                                        v-model="record.office_amount"
                                    />
                                </div>
                            </div>
                            <div
                                v-if="selectedOption === 'offices'"
                                class="col-md-3"
                            >
                                <div class="form-group">
                                    <br />
                                    <button
                                        v-if="isEditMode"
                                        type="button"
                                        @click="updateOffice(record.id)"
                                        class="btn btn-info btn-sm btn-round btn-modal-save"
                                    >
                                        Guardar
                                    </button>
                                    <button
                                        v-if="!generatedOffices"
                                        type="button"
                                        @click="createOffices()"
                                        class="btn btn-info btn-sm btn-round btn-modal-save"
                                    >
                                        Ver secciones
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="modal-body modal-table"
                        v-if="
                            offices.length > 1 && selectedOption === 'offices'
                        "
                    >
                        <v-client-table
                            :columns="columns"
                            :data="record.section_registers"
                            :options="table_options"
                        >
                            <div slot="id" slot-scope="props">
                                <button
                                    @click="editOffice(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    v-has-tooltip
                                    title="Modificar registro"
                                    data-toggle="tooltip"
                                    disabled
                                    type="button"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    v-if="isEditMode"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    disabled
                                    type="button"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                                <button
                                    v-else
                                    @click="deleteOffice(props.row.id)"
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
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="closeModal"
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
                                @click="saveRecords('asset/sections')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table
                            :columns="columns"
                            :data="records"
                            :options="table_options"
                        >
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
                                            'asset/sections'
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
    </section>
</template>

<script>
export default {
    data() {
        return {
            record: {
                id: "",
                name: "",
                description: "",
                building_id: "",
                floor_id: "",
                office_amount: 1,
                section_registers: [],
            },
            currentSectionAmount: 0,
            selectedOption: "",
            generatedOffices: false,
            isEditMode: false,
            offices: [],
            buildings: [],
            floors: [],
            errors: [],
            records: [],
            columns: [
                "name",
                "description",
                "building.name",
                "floor.name",
                "id",
            ],
        };
    },
    watch: {
        "record.building_id"(building_id) {
            if (this.record.floor_id !== "") {
                this.record.floor_id = 1;
                return;
            }
            this.getBuildingFloors(building_id);
        },
        selectedOption() {
            if (this.selectedOption == "office") {
                this.record.office_amount = 1;
                return;
            }
            this.record.office_amount = 2;
        },
    },
    methods: {
        /**
         * Método que limpia los datos asociados a la opcion "offices"
         *
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        clearOfficesFields() {
            const vm = this;
            vm.record.office_amount = 2;
            vm.offices = [];
            vm.generatedOffices = false;
            document.getElementById("officeAmount").disabled = false;
        },
        /**
         * Método que guarda los datos del formulario y luego limpia los campos dependiendo de la opcion en la que se encuentre el modal
         *
         * @param {string} url El endpoint al que se enviaran los datos
         *
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        saveRecords(url) {
            this.createRecord(url);
            this.reset();
            this.generatedOffices = false;
        },
        /**
         * Método que limpia los datos del modal cuando se cierra
         *
         * @author  Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        closeModal() {
            const vm = this;
            vm.reset();
            vm.record.office_amount = 1;
            vm.selectedOption = "office";
            vm.clearFilters();
        },
        /**
         * Método que actualiza los datos de un registro específico de la tabla de previsualización de secciones
         *
         * @param {number} index El índice de la posición de los datos en el arreglo que se van a actualizar
         *
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        updateOffice(index) {
            if (!this.validate()) {
                return;
            }
            const vm = this;
            vm.record.section_registers[index].name = vm.record.name;
            vm.record.section_registers[index].description =
                vm.record.description;
            vm.isEditMode = false;
        },
        /**
         * Método que asigna los datos de una posición del arreglo de la tabla de previsualización de secciones a los campos del modal
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         * @param {number} index Posición del arreglo
         * @param {MouseEvent} event Evento que accionó la función
         */
        editOffice(index, event) {
            const vm = this;
            vm.record.id = vm.record.section_registers[index].id;
            vm.record.name = vm.record.section_registers[index].name;
            vm.record.description =
                vm.record.section_registers[index].description;
            vm.isEditMode = true;
            event.preventDefault;
        },
        /**
         * Elimina un elemento del arreglo de la tabla de previsualización
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         * @param {number} index Posición del arreglo de previsualizaciones]
         */
        deleteOffice(index) {
            const vm = this;
            if (vm.records.length > 0) {
                vm.record.offices.splice(index, 1);
                return;
            }
            vm.record.offices.splice(index, 1);
            vm.generatedOffices = false;
        },
        /**
         * Valida la informacion introducida en los campos del formulario
         * @returns {bool} True para indicar que fue validado correctamente, y false para indicar que existe algún error
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        validate() {
            const vm = this;
            vm.errors = [];

            if (vm.record.name === "" || vm.record.name === null) {
                vm.errors.push("El campo Nombre de la sección es obligatorio.");
            }
            if (
                vm.record.building_id === "" ||
                vm.record.building_id === null
            ) {
                vm.errors.push("El campo Edificación es obligatorio.");
            }
            if (vm.record.floor_id === "" || vm.record.floor_id === null) {
                vm.errors.push(
                    "El campo Nivel de la edificación es obligatorio."
                );
            }
            if (
                vm.record.office_amount === "" ||
                vm.record.office_amount === null
            ) {
                vm.errors.push("El campo número de secciones es obligatorio.");
            }

            if (vm.errors.length > 0) {
                $("html,body").animate(
                    {
                        scrollTop: $("#assetSectionsComponent").offset(),
                    },
                    1000
                );
                return false;
            }
            return true;
        },
        /**
         * Crea el arreglo de datos para mostrarlos en la tabla de previsualizacion
         * @author Ing. Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        async createOffices() {
            if (!this.validate()) {
                return;
            }

            await this.getSectionAmount(this.record.name);

            const vm = this;
            const building_text = vm.buildings.find(
                (building) => building.id == vm.record.building_id
            ).text;
            const floor_text = vm.floors.find(
                (floor) => floor.id == vm.record.floor_id
            ).text;

            for (let i = 0; i < vm.record.office_amount; i++) {
                vm.currentSectionAmount += 1;
                vm.offices.push({
                    id: i,
                    name: this.record.name + `-${vm.currentSectionAmount}`,
                    description: this.record.description,
                    building: { name: building_text },
                    floor: { name: floor_text },
                });
            }
            document.getElementById("officeAmount").disabled = true;
            vm.record.section_registers = vm.offices;
            vm.generatedOffices = true;
            return;
        },
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            const vm = this;
            vm.record = {
                id: "",
                name: "",
                description: "",
                building_id: "",
                floor_id: "",
                office_amount: 1,
            };
            if (vm.selectedOption === "offices") {
                vm.clearOfficesFields();
                return;
            }
            vm.record.office_amount = 1;
        },
    },
    created() {
        this.table_options.headings = {
            name: "Nombre",
            description: "Descripción",
            "building.name": "Edificación",
            "floor.name": "Nivel",
            id: "Acción",
        };
        this.table_options.sortable = ["name"];
        this.table_options.filterable = ["name"];
        this.table_options.columnsClasses = {
            name: "col-md-3",
            description: "col-md-3",
            "building.name": "col-md-2",
            "floor.name": "col-md-2",
            id: "col-md-2 text-center",
        };
    },
    mounted() {
        const vm = this;
        $("#add_section").on("show.bs.modal", function () {
            vm.getBuildings();
            vm.record.section_registers = [];
        });
    },
};
</script>
