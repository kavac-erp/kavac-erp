<template>
    <section id="payrollPermissionPoliciesComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de políticas de perimisos" data-toggle="tooltip"
           @click="addRecord('add_payroll_permission_policies', 'payroll/permission-policies', $event)">
           <i class="icofont icofont-paper ico-3x"></i>
           <span>Políticas de Permisos</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_permission_policies">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-paper ico-3x"></i>
                            Políticas de Permisos
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
                                    <label for="name">Nombre:</label>
                                    <input type="text" id="name" placeholder="Nombre"
                                           v-is-text
                                           class="form-control input-sm" v-model="record.name" data-toggle="tooltip"
                                           title="Indique el nombre del permiso">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                            </div>
                            <!-- activa -->
                            <div class="col-md-6">
                               <div class="form-group">
                                  <label>¿Activo?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip" 
                                             title="¿La política de permiso se encuentra activa actualmente?">
                                            <input type="checkbox" class="custom-control-input" id="permissionPolicyActive" 
                                                    v-model="record.active" :value="true">
                                            <label class="custom-control-label" for="permissionPolicyActive"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ./activa -->
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="institutions">Organización</label>
                                    <select2 :options="institutions"
                                             v-model="record.institution_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="anticipation_day">Días de anticipación:</label>
                                    <input type="text" data-toggle="tooltip" id="anticipation_day"
                                           placeholder="Días de anticipación para solicitar el permiso"
                                           title="Indique el día de anticipación para solicitar el permiso"
                                           class="form-control input-sm"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': 0"
                                           v-model="record.anticipation_day">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12"> <h6> Tiempo solicitado para el permiso </h6> </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="time_min">Rango mínimo:</label>
                                    <input type="text" data-toggle="tooltip" id="time_min"
                                           placeholder="Tiempo mínimo para el permiso"
                                           title="Indique el tiempo mínimo permitido para el permiso"
                                           :max="(record.time_max == '') ? '' : record.max"
                                           class="form-control input-sm"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': 0"
                                           v-model="record.time_min">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="time_max">Rango máximo:</label>
                                    <input type="text" data-toggle="tooltip" id="time_max"
                                           placeholder="Tiempo máximo permitido para el permiso"
                                           title="Indique el tiempo máximo permitido para el permiso"
                                           :min="record.time_min" :disabled="(record.time_min == '')"
                                           class="form-control input-sm"
                                           v-input-mask data-inputmask="
                                                'alias': 'numeric',
                                                'allowMinus': 'false',
                                                'digits': 0"
                                           v-model="record.time_max">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="time_unit">Unidad de tiempo</label>
                                    <select2 :options="time_units"
                                             v-model="record.time_unit"></select2>
                                </div>
                            </div>
                            <!-- días hábiles -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>¿Son días hábiles?</label>
                                    <div class="col-12">
                                        <div class="custom-control custom-switch" data-toggle="tooltip"
                                             title="¿La fecha incluye días hábiles?">
                                            <input type="checkbox" class="custom-control-input"
                                                    id="vacationalPolicieBusinessDays"
                                                    v-model="record.business_days"
                                                    :value="true">
                                            <label class="custom-control-label" for="vacationalPolicieBusinessDays"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ./días hábiles -->
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
							<button type="button" @click="createRecord('payroll/permission-policies')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="day_range" slot-scope="props" class="text-center">
                                <div v-for="time_unit in time_units" :key="time_unit.id">
                                    <span v-if="props.row.time_unit == time_unit.id">
                                        {{ props.row.time_min + ' - ' + props.row.time_max + ' ' + time_unit.text }}
                                    </span>
                                </div>
                            </div>
                            <div slot="active" slot-scope="props" class="text-center">
                                <span v-if="props.row.active" class="text-success font-weight-bold">Si</span>
                                <span v-else class="text-danger font-weight-bold">No</span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" v-has-tooltip type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.index, 'payroll/permission-policies')"
                                        class="btn btn-danger btn-xs btn-icon btn-action"
                                        title="Eliminar registro" data-toggle="tooltip" v-has-tooltip
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
                    id:               '',
                    name:             '',
                    anticipation_day: '',
                    time_min:          '',
                    time_max:          '',
                    time_unit:         '',
                    active:           false,
                    business_days:    false,
                    institution_id:   ''
                },
                errors:       [],
                records:      [],
                institutions: [],
                time_units:   [
                    {"id": "",       "text": "Seleccione..."},
                    {"id": "hours",  "text": "hora(s)"},
                    {"id": "days",   "text": "día(s)"},
                    {"id": "weeks",  "text": "semana(s)"},
                    {"id": "months", "text": "mese(s)"},

                ],
                columns:      ['name', 'anticipation_day', 'day_range', 'active', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             *
             */
            reset() {
                this.record = {
                    id:               '',
                    name:             '',
                    anticipation_day: '',
                    time_min:          '',
                    time_max:          '',
                    time_unit:         '',
                    active:           false,
                    business_days:    false,
                    institution_id:   ''
                };
            },
            deleteRecord(index, url) {
                const vm = this;
                var url = (url)?url:this.route_delete;
                var records = vm.records;
                var confirmated = false;
                var index = index - 1;

                bootbox.confirm({
                    title: "¿Eliminar registro?",
                    message: "¿Está seguro de eliminar este registro?",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            confirmated = true;
                            url = vm.setUrl(url);
                            axios.delete(url + '/' + records[index].id).then(response => {
                                if (typeof(response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                records.splice(index, 1);
                                vm.showMessage('destroy');
                            }).catch(error => {
                                 if (typeof(error.response) !="undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        ); 
                                    }
                                }
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                        }
                    }
                });

                if (confirmated) {
                    this.records = records;
                    this.showMessage('destroy');
                }
            },
        },
        created() {
            this.table_options.headings = {
                'name':             'Nombre',
                'anticipation_day': 'Dias de anticipación',
                'day_range':        'Rango de tiempo solicitado',
                'active':           'Activo',
                'id':               'Acción'
            };
            this.table_options.sortable = ['name'];
            this.table_options.filterable = ['name'];
            this.table_options.columnsClasses = {
                'name':             'col-xs-2',
                'anticipation_day': 'col-xs-3',
                'day_range':        'col-xs-3',
                'active':           'col-xs-2',
                'id':               'col-xs-2'
            };
        },
        mounted() {
            const vm = this;
            $("#add_payroll_permission_policies").on('show.bs.modal', function() {
                vm.getInstitutions();
            });
        },
    };
</script>
