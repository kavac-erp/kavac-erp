<template>
    <div class="col-xs-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)" title="Fuentes de financiamiento"
            data-toggle="tooltip"
            @click="addRecord('add_financement-sources', '/budget/financement-sources', $event)"
        >
            <i class="icofont icofont-files ico-3x"></i>
            <span>Tipos de financiamiento</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog"
            id="add_financement-sources">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-bank-alt inline-block"></i>
                            Tipos de financiamiento
                        </h6>
                    </div>

                    <!-- Modal-body -->
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="alert-icon">
                                <i class="now-ui-icons objects_support-17"></i>
                            </div>
                            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                            <button type="button" class="close" data-dismiss="alert"
                                aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="now-ui-icons ui-1_simple-remove"></i>
                                </span>
                            </button>
                            <ul>
                                <li v-for="(error, index) in errors"
                                    :key="index">{{ error }}
                                </li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Fuente de financiamiento:</label>
                                    <select2
                                        class="form-control"
                                        :options="financementTypes"
                                        v-model="record.budget_financement_type_id"
                                        data-toggle="tooltip"
                                        title="Indique la fuente de financiamiento"
                                    >
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group is-required">
                                    <label>Tipo de financiamiento:</label>
                                    <input type="text" placeholder="Nombre del tipo de financiamiento"
                                        tabindex="1" data-toggle="tooltip"
                                        title="Indique el nombre del tipo de financiamiento"
                                        class="form-control input-sm" v-model="record.name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Final modal-body -->

                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button"
                                class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('budget/financement-sources')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <!-- Final modal-footer -->

                    <!-- Tabla de registros -->
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <a slot="budget_financement_type_id" slot-scope="props" target="_blank"
                                v-if="props.row.budget_financement_type_id">
                                <span v-for="financementType in financementTypes" :key="financementType.id">
                                    <span v-if="props.row.budget_financement_type_id==financementType.id">
                                        {{ financementType.text }}
                                    </span>
                                </span>
                            </a>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                                    title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, '/budget/financement-sources')"
                                    class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                                    title="Eliminar registro" data-toggle="tooltip"
                                    type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                    <!-- Final tabla de registros -->
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
                    budget_financement_type_id: '',
                    name: ''
                },
                columns: [
                    'budget_financement_type_id',
                    'name',
                    'id'
                ],
                errors: [],
                records: [],
                financementTypes: []
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
                    budget_financement_type_id: '',
                    name: ''
                };
            },
        },
        created() {
            this.getFinancementTypes();
            this.table_options.headings = {
                'budget_financement_type_id': 'Fuente de financiamiento',
                'name': 'Tipo de financiamiento',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'budget_financement_type_id',
                'name',
            ];
            this.table_options.filterable = [
                'budget_financement_type_id',
                'name',
            ];
        },
    };
</script>
