<template>
    <section class="text-center" id="payroll_position">
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href=""
            title="Registros de cargos"
            data-toggle="tooltip"
            @click="
                addRecord('add_payroll_position', 'payroll/positions', $event)"
        >
            <i class="icofont icofont-briefcase-alt-1 ico-3x"></i>
            <span>Cargos</span>
        </a>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_payroll_position"
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
                            <i class="icofont icofont-briefcase-alt-1 ico-3x"></i>
                            Cargo
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong>
                                    Debe verificar los siguientes errores antes de continuar:
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
                                    <li
                                        v-for="error in errors"
                                        :key="error"
                                    >
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input
                                        type="text"
                                        id="name"
                                        placeholder="Nombre"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                        data-toggle="tooltip"
                                        title="
                                            Indique el nombre del cargo (requerido)
                                        "
                                    >
                                    <input
                                        type="hidden"
                                        name="id"
                                        id="id"
                                        v-model="record.id"
                                    >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Descripción:</label>
                                    <input
                                        type="text"
                                        id="description"
                                        placeholder="Descripción"
                                        class="form-control input-sm"
                                        v-model="record.description"
                                        data-toggle="tooltip"
                                        title="Indique la descripción del cargo"
                                    >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Responsabilidad</label>
                                    <div
                                        class="custom-control custom-switch"
                                        data-toggle="tooltip"
                                        title="
                                            Indique si es cargo de responsabilidad
                                        "
                                    >
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="responsible"
                                            name="responsible"
                                            v-model="record.responsible"
                                            :value="true"
                                        >
                                        <label
                                            class="custom-control-label"
                                            for="responsible"
                                        ></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="number_positions_assigned">
                                        Cantidad de cargos por estructura:
                                    </label>
                                    <input
                                        type="text"
                                        id="number_positions_assigned"
                                        placeholder="Cantidad de cargos por estructura"
                                        class="form-control input-sm"
                                        v-model="record.number_positions_assigned"
                                        data-toggle="tooltip"
                                        title="Indique la cantidad de cargos asignados"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                                        :disabled="record.responsible"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="
                                    btn btn-default btn-sm btn-round btn-modal-close
                                "
                                @click="clearFilters"
                                data-dismiss="modal"
                            >
                                Cerrar
                            </button>
                            <button
                                type="button"
                                class="
                                    btn btn-warning btn-sm btn-round btn-modal btn-modal-clear
                                "
                                @click="reset()"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="createRecord('payroll/positions')"
                                class="
                                    btn btn-primary btn-sm btn-round btn-modal-save
                                "
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
                            <div slot="number_positions_assigned" slot-scope="props">
                                <span v-if="props.row.number_positions_assigned">
                                    {{ props.row.number_positions_assigned }}
                                </span>
                                <span v-else>
                                    Sin asignar
                                </span>
                            </div>
                            <div
                                slot="number_of_positions_held" 
                                slot-scope="props">
                                <span>
                                    {{ props.row.responsible ? props.row.payroll_responsibility_count : props.row.payroll_employments_count }}
                                </span>

                            </div>
                            <div
                                v-if="!props.row.responsible"
                                slot="number_positions_available"
                                slot-scope="props"
                            >
                                {{
                                    (props.row.number_positions_assigned - props.row.payroll_employments_count)
                                }}
                            </div>
                            <div
                                v-else
                                slot="number_positions_available"
                                slot-scope="props"
                            >
                                {{
                                    props.row.payroll_responsibility_count == 0 ? 1 : props.row.payroll_responsibility_count - 1
                                }}
                            </div>
                            <div
                                slot="responsible"
                                slot-scope="props"
                            >
                                {{
                                    props.row.responsible ?
                                    'Sí' : 'No'

                                }}
                            </div>
                            <div
                                slot="id"
                                slot-scope="props"
                                class="text-center"
                            >
                                <button
                                    @click="initUpdate(props.row.id, $event)"
                                    class="
                                        btn btn-warning btn-xs btn-icon btn-action
                                    "
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
                                            'payroll/positions'
                                        )
                                    "
                                    class="
                                        btn btn-danger btn-xs btn-icon btn-action
                                    "
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                    <div class="card-footer text-right">
                        <p> {{ totalPayrollPositions }} Cargos por estructura | {{ totalEmploymentCount }} Cargos ocupados | {{ totalAvailablePositions }} Cargos disponibles</p>
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
                    id: '',
                    name: '',
                    description: '',
                    number_positions_assigned: '',
                    responsible: ''
                },
                totalPayrollPositions: 0,
                totalEmploymentCount: 0,
                totalAvailablePositions: 0,
                count: [],
                query: [],
                errors: [],
                records: [],
                totalPayrollPositions: 0,
                totalEmploymentCount: 0,
                totalAvailablePositions: 0,
                columns: [
                    'name',
                    'description',
                    'number_positions_assigned',
                    'number_of_positions_held',
                    'number_positions_available',
                    'responsible',
                    'id'
                ],
            }
        },
        methods: {
            /**
             * Método que devuelve el conteo de empleados asociados
             * a un cargo específico y que estén true en la tabla intermedia
             * de cargos y empleados.
             *
             * @method loadPositionsData
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
            */
            loadPositionsData(id) {
                const matchingRecord = this.count.find(
                    record => record.payroll_position_id === id
                );

                if (matchingRecord) {
                    return matchingRecord.employment_count;
                } else {
                    return 0;
                }
            },

            /**
             * Método que devuelve los registros de las responsabilidades.
             *
             * @method getResponsibilities
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
            */
            async getResponsibilities() {
                await axios.get(`${window.app_url}/payroll/responsibilities`)
                .then((response) => {
                    this.query = response.data.records;
                })
                .catch((error) => {
                    console.log('error')
                })
            },

            /**
             * Método que devuelve si algún id de cargo coincide con el campo
             * payroll_position_id dentro de la tabla de responsabilidades.
             *
             * @method queryResponsibility
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
            */
            queryResponsibility(id) {
                // ID del cargo a buscar
                const idBuscado = id;

                // Verificar si hay una coincidencia con el ID en payroll_position_id
                const existeCoincidencia = this.query.some(record => record.payroll_position_id === idBuscado);

                // Retornar coincidencia para mostrar en la tabla
                if (existeCoincidencia) {
                    return 0;
                } else {
                    return 1;
                }
            },

            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve>
             */
            reset() {
                this.record = {
                    id: '',
                    name: '',
                    description: '',
                    number_positions_assigned: '',
                    responsible: false,
                };
            },

            /**
             * Obtener los registros de la tabla intermedia de cargos y empleados.
             *
             * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
            */
            async getPayrollEmploymentsPositions() {
                await axios.get(`${window.app_url}/payroll/get-payroll-employments-positions-count`)
                .then((response) => {
                    this.count = response.data.records;
                    this.totalPayrollPositions = response.data.totalPayrollPositions;
                    this.totalEmploymentCount = response.data.totalEmploymentCount;
                    this.totalAvailablePositions = response.data.totalAvailablePositions;
                })
                .catch((error) => {
                    console.log('error')
                })
            },
        },
        created() {
            this.record.active = false;
            this.table_options.headings = {
                'name': 'Nombre',
                'description': 'Descripción',
                'number_positions_assigned': 'Cantidad de cargos por estructura',
                'number_of_positions_held': 'Cantidad de cargos ocupados',
                'number_positions_available': 'Cantidad de cargos disponibles',
                'responsible': 'Cargo de responsabilidad',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name'];
            this.table_options.filterable = ['name'];
            this.table_options.columnsClasses = {
                'name': 'col-md-3',
                'description': 'col-md-2',
                'number_positions_assigned': 'col-md-1 text-center',
                'number_of_positions_held': 'col-md-1 text-center',
                'number_positions_available': 'col-md-1 text-center',
                'responsible': 'col-md-2 text-center',
                'id': 'col-md-2'
            };
        },
        async mounted () {
            const vm = this;
            $("#add_payroll_position").on('show.bs.modal', async function() {
                vm.reset();
                await vm.getPayrollEmploymentsPositions();
                await vm.getResponsibilities();
            });
        },
    };
</script>
