<template>
    <div class="col-xs-2 text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)" title="Tipos de financiamiento"
            data-toggle="tooltip"
            @click="addRecord('add_financement-types', '/budget/financement-types', $event)"
        >
            <i class="icofont icofont-deal ico-3x "></i>
            <span>Fuentes de financiamiento</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog"
            id="add_financement-types">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-bank-alt inline-block"></i>
                            Fuentes de financiamiento
                        </h6>
                    </div>
                    <!-- modal-body -->
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
                                    <label>Nombre:</label>
                                    <input type="text" placeholder="Nombre de la fuente de financiamiento"
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
                            <button type="button" @click="createRecord('budget/financement-types')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <!-- Final modal-footer -->

                    <!-- Tabla de registros -->
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                                    title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, '/budget/financement-types')"
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
                    name: '',
                },
                errors: [],
                records: [],
                columns: [
                    'name',
                    'id'
                ],
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
                };
            },
        },
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'name',
            ];
            this.table_options.filterable = [
                'name',
            ];
        },
        mounted() {
        },
    };
</script>
