<template>
    <section class="text-center" id="payroll_coordination">
        <a
            href=""
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            title="Registros de Coordinaciones"
            data-toggle="tooltip"
            @click="
                addRecord(
                    'add_payroll_coordination',
                    'payroll/coordinations',
                    $event
                )
            "
        >
            <i class="icofont icofont-company ico-3x"></i>
            <span>Coordinaciones</span>
        </a>
        <!-- Modal -->
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            id="add_payroll_coordination"
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
                            <i class="icofont icofont-company ico-3x"></i>
                            Coordinación
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
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input
                                        type="text"
                                        id="name"
                                        placeholder="Nombre"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                        data-toggle="tooltip"
                                        title="Indique el nombre de la coordinación (requerido)"
                                        oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜ\s]/g, '');"
                                    >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label
                                        for="description">Descripción:</label>
                                    <input
                                        type="text"
                                        id="description"
                                        placeholder="Descripción"
                                        class="form-control input-sm"
                                        v-model="record.description"
                                        data-toggle="tooltip"
                                        title="Indique la descripción de la coordinación"
                                    >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Departamento de adscripción:</label>
                                    <select2
                                        class="form-control"
                                        :options="departments"
                                        v-model="record.department_id"
                                        data-toggle="tooltip"
                                        title="Indique el departamento de adscripción"
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
                                @click="createRecord('payroll/coordinations')"
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
                            <div slot="department_id" slot-scope="props">
                                <span v-for="x in departments" :key="x.id">
                                    <span v-if="props.row.department_id==x.id">
                                        {{ x.text }}
                                    </span>
                                </span>
                            </div>
                            <div
                                slot="id"
                                slot-scope="props"
                                class="text-center"
                            >
                                <button
                                    @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    title="Modificar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="deleteRecord(props.row.id, 'payroll/coordinations')"
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
                    name: '',
                    description: '',
                    department_id: '',
                },
                columns: [
                    'name',
                    'description',
                    'department_id',
                    'id'
                ],
                departments: []
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
                    name: '',
                    description: '',
                    department_id: ''
                };
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
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'description': 'Descripción',
                'department_id': 'Departamento de adscripción',
                'id': 'Acción'
            };
            this.table_options.sortable = ['name'];
            this.table_options.filterable = ['name'];
            this.table_options.columnsClasses = {
                'name': 'col-md-3',
                'description': 'col-md-4',
                'department_id': 'col-md-3',
                'id': 'col-md-2'
            };
        },
        mounted() {
            const vm = this;

            $("#add_payroll_coordination").on('show.bs.modal', function() {
                vm.reset();
            });

            // Obtener el listado de Unidades y Dependencias.
            vm.getDepartments();
        },
    };
</script>
