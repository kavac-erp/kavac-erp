<template>
    <section class="text-center" id="responsibilities_level">
        <a
            href=""
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            title="Registros de Nivel de Responsabilidades"
            data-toggle="tooltip"
            @click="
                addRecord(
                    'add_responsibilities_level',
                    'payroll/responsibilities',
                    $event
                )
            "
        >
            <i class="icofont icofont-business-man ico-3x"></i>
            <span>Nivel de Responsabilidad</span>
        </a>
        <!-- Modal -->
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_responsibilities_level"
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
                            <i class="icofont icofont-business-man ico-3x"></i>
                            Nivel de Responsabilidades
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>
                                    Cuidado!
                                </strong> Debe verificar los siguientes errores antes de continuar:
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
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Departamento / Coordinación</label>
                                    <div class="col-md-12">
                                        <div
                                            class="custom-control custom-switch"
                                            data-toggle="tooltip"
                                            title="
                                                Indique si desea registrar el responsable de
                                                un Departamento o una Coordinación
                                            "
                                        >
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="responsabilityType"
                                                v-model="record.type_responsibility"
                                                :value="true"
                                            >
                                            <label
                                                class="custom-control-label"
                                                for="responsabilityType"
                                            ></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div
                                v-if="!record.type_responsibility"
                                class="col-md-6"
                            >
                                <div class="form-group is-required">
                                    <label>Departamento:</label>
                                    <select2
                                        class="form-control"
                                        :options="departments"
                                        v-model="record.department_id"
                                        data-toggle="tooltip"
                                        title="Indique el departamento"
                                        :disabled="record.type_responsibility"
                                    ></select2>
                                </div>
                            </div>
                            <div
                                v-else
                                class="col-md-6"
                            >
                                <div class="form-group is-required">
                                    <label>Coordinación:</label>
                                    <select2
                                        :options="payroll_coordinations"
                                        v-model="record.payroll_coordination_id"
                                        :disabled="!record.type_responsibility"
                                    ></select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Responsable:</label>
                                    <select2
                                        :options="payroll_staffs"
                                        v-model="record.payroll_staff_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Cargo:</label>
                                    <select2
                                        :options="payroll_positions"
                                        v-model="record.payroll_position_id"
                                    >
                                    </select2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- modal-footer -->
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
                                @click="createRecord('payroll/responsibilities')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                    <!-- Final de modal-footer -->

                    <!-- modal-table -->
                    <div class="modal-body modal-table">
                        <v-client-table
                            :columns="columns"
                            :data="records"
                            :options="table_options"
                        >
                            <div
                                v-if="props.row.department_id"
                                slot="department_id"
                                slot-scope="props"
                            >
                                <span v-for="a in departments" :key="a.id">
                                    <span v-if="props.row.department_id==a.id">
                                        Departamento: {{ a.text }}
                                    </span>
                                </span>
                            </div>
                            <div
                                v-else
                                slot="department_id"
                                slot-scope="props"
                            >
                                <span v-for="x in payroll_coordinations" :key="x.id">
                                    <span v-if="props.row.payroll_coordination_id==x.id">
                                        Coordinación: {{ x.text }}
                                    </span>
                                </span>
                            </div>
                            <div slot="payroll_staff_id" slot-scope="props">
                                <span v-for="y in payroll_staffs" :key="y.id">
                                    <span v-if="props.row.payroll_staff_id==y.id">
                                        {{ y.text }}
                                    </span>
                                </span>
                            </div>
                            <div slot="payroll_position_id" slot-scope="props">
                                <span v-for="z in payroll_positions" :key="z.id">
                                    <span v-if="props.row.payroll_position_id==z.id">
                                        {{ z.text }}
                                    </span>
                                </span>
                            </div>
                            <div
                                slot="id"
                                slot-scope="props"
                                class="text-center"
                            >
                                <button
                                    @click="initUpdateData(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    title="Modificar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="deleteRecord(props.row.id, 'payroll/responsibilities')"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                    <!-- Final de modal-table -->
                </div>
            </div>
        </div>
        <!-- Final del Modal -->
    </section>
</template>

<script>
    export default {
        data() {
            return {
                errors: [],
                records: [],
                record: {
                    id: '',
                    department_id: '',
                    payroll_staff_id: '',
                    payroll_position_id: '',
                    payroll_coordination_id: '',
                    type_responsibility: false,
                },
                columns: [
                    'department_id',
                    'payroll_staff_id',
                    'payroll_position_id',
                    'id'
                ],
                departments: [],
                payroll_staffs: [],
                payroll_positions: [],
                payroll_coordinations: [],
            }
        },
        methods: {
            /**
             * Método que limpia todos los datos del formulario.
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    department_id: '',
                    payroll_staff_id: '',
                    payroll_position_id: '',
                    payroll_coordination_id: '',
                    type_responsibility: false
                };
            },

            /**
             * Obtiene los datos de los cargos registrados en la institucion
             * que sean cargos de responsabilidad.
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
             */
            getPayrollPositions() {
                const vm = this;
                vm.payroll_positions = [];

                axios.get(`${window.app_url}/payroll/positions`).then(response => {
                    // Filtrar los registros donde el campo 'responsible' sea true
                    const filteredPositions = response.data.records.filter(
                        item => item.responsible === true
                    );

                    // Mapear los datos para obtener el formato deseado
                    vm.payroll_positions = filteredPositions.map(item => ({
                        id: item.id,
                        text: item.name
                    }));

                    // Agregar el elemento "Seleccione..." al principio del select.
                    vm.payroll_positions.unshift({ id: '', text: 'Seleccione...' });
                });
            },

            /**
             * Método que carga el formulario con los datos a modificar
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} index Identificador del registro a ser modificado
             * @param {object} event   Objeto que gestiona los eventos
             */
            async initUpdateData(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;

                if (recordEdit && typeof recordEdit.type_responsibility !== 'undefined') {
                    vm.record.type_responsibility = recordEdit.type_responsibility;
                } else {
                    vm.record.type_responsibility = false;
                }

                let recordEdit2 = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit2;

                if (recordEdit2 && typeof recordEdit2.type_responsibility !== 'undefined') {
                    vm.record.type_responsibility = recordEdit2.type_responsibility;
                } else {
                    vm.record.type_responsibility = false;
                }

                event.preventDefault();
            },

            /**
             * Obtiene los datos de los trabajadores registrados
             *
             * @author William Páez <wpaez@cenditel.gob.ve>
             * @author Ing. Roldan Vargas <rvargas at cenditel.gob.ve>
             *
             * @param string filter establece una condición bajo la cual filtrar los resultados
             */
            async getPayrollStaffs() {
                this.payroll_staffs = [];
                await axios.get(`${window.app_url}/payroll/get-staffs/`).then(response => {
                    this.payroll_staffs = response.data;
                });
            },

            /**
             * Método que permite obtener el listado de Unidades y Dependencias.
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getDepartments() {
                const vm = this;
                await axios.get(`${vm.app_url}/payroll/get-departments`).then(response => {
                    vm.departments = response.data;
                }).catch(error => {
                    vm.logs('Budget/Resources/assets/js/_all.js', 90, error, 'departments');
                });
            },
        },
        async created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'department_id': 'Departamento / Coordinación',
                'payroll_staff_id': 'Responsable',
                'payroll_position_id': 'Cargo',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name'];
            this.table_options.filterable = ['name'];
            this.table_options.columnsClasses = {
                'department_id': 'col-md-3',
                'payroll_staff_id': 'col-md-3',
                'payroll_position_id': 'col-md-3',
                'id': 'col-md-2'
            };
            // Cargar la lista de empleados
            this.getPayrollStaffs();
            // Cargar la lista de todos los cargos,
            this.getPayrollPositions();
            // Cargar la lista de coordinaciones
            this.getPayrollCoordinations();
            // Obtener el listado de Unidades y Dependencias.
            this.getDepartments();
        },
        async mounted() {
            const vm = this;
            $("#add_responsibilities_level").on('show.bs.modal', function() {
                vm.reset();
                // Cargar la lista de empleados
                vm.getPayrollStaffs();
                // Cargar la lista de todos los cargos,
                vm.getPayrollPositions();
                // Cargar la lista de coordinaciones
                vm.getPayrollCoordinations();
                // Obtener el listado de Unidades y Dependencias.
                vm.getDepartments();
            });
        },
    };
</script>
