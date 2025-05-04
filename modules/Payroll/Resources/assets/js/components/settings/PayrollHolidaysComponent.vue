<template>
    <section class="text-center" id="payroll_holidays">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de días feriados" data-toggle="tooltip"
           @click="addRecord('add_payroll_holidays', 'payroll/holidays', $event)">
           <i class="icofont icofont-ui-calendar ico-3x"></i>
           <span>Días feriados</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_holidays">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-ui-calendar ico-3x"></i>
                            Días feriados
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                        @click.prevent="errors = []">
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
                                    <label for="date">Día feriado:</label>
                                    <input type="date" id="date" placeholder="Día feriado"
                                           class="form-control input-sm no-restrict" v-model="record.date" data-toggle="tooltip"
                                           title="Indique el día feriados">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="description">Descripción:</label>
                                    <input type="text" id="description" placeholder="Descripción"
                                           class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
                                           title="Indique la descripción">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Día permanente -->
                                <div class="form-group">
                                    <label for="permanent_day">¿Es día permanente?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip" 
                                                title="Indique si el día feriado es un día permanente">
                                            <input type="checkbox" class="custom-control-input" id="active" 
                                                    v-model="record.permanent_day" :value="true">
                                            <label class="custom-control-label" for="active"></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- ./Día permanente -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
                                    @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" 
                                    @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('payroll/holidays')" 
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="date" slot-scope="props">
                                <span>{{ format_date(props.row.date) }}</span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/holidays')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip"
                                        type="button">
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
                    id: '',
                    date: '',
                    description: '',
                    permanent_day: false
                },
                errors: [],
                records: [],
                columns: ['date', 'description', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    date: '',
                    description: '',
                    permanent_day: false,
                };
            },
        },
        created() {
            this.table_options.headings = {
                date: 'Día festivo',
                description: 'Descripción',
                id: 'Acción'
            };
            this.table_options.sortable = ['date'];
            this.table_options.filterable = ['date'];
            this.table_options.columnsClasses = {
                date: 'col-md-5',
                description: 'col-md-5',
                id: 'col-md-2'
            };
        },
        mounted () {
            const vm = this;
            $("#add_payroll_holidays").on('show.bs.modal', function() {
                vm.reset();
            });
        },
    };
</script>