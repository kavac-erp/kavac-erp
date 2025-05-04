<template>
    <section id="payrollSupervisedGroupsFormComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href=""
           title="Registros de grupos de supervisados" data-toggle="tooltip"
           @click="addRecord('add_payroll_supervised_group', 'payroll/supervised-groups', $event)">
           <i class="icofont icofont icofont-ui-user-group ico-3x"></i>
           <span>Grupos de<br>Supervisados</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_payroll_supervised_group">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont icofont-ui-user-group ico-3x"></i>
                            Grupos de supervisados
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- mensajes de error -->
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
                        <!-- ./mensajes de error -->
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Código -->
                                <div class="form-group is-required">
                                    <label for="code">Código:</label>
                                    <input type="text" id="code" placeholder="Código"
                                           class="form-control input-sm" v-model="record.code" data-toggle="tooltip"
                                           title="Indique el Código del grupo de supervisados (requerido)">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                                <!-- ./Código -->
                            </div>
                            <div class="col-md-6">
                                <!-- Supervisor -->
                                <div class="form-group is-required">
                                    <label>Supervisor:</label>
                                    <select2 :options="payroll_staffs"
                                        v-model="record.supervisor_id">
                                    </select2>
                                </div>
                                <!-- ./Supervisor -->
                            </div>
                            <div class="col-md-6">
                                <!-- Aprobador -->
                                <div class="form-group is-required">
                                    <label>Aprobador:</label>
                                    <select2 :options="payroll_staffs"
                                        v-model="record.approver_id">
                                    </select2>
                                </div>
                                <!-- ./Aprobador -->
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Supervisados</label>
                                    <v-multiselect
                                        data-toggle="tooltip"
                                        title="Indique los trabajadores dentro del grupo de supervisados"
                                        track_by="text"
                                        :hide_selected="false"
                                        :group_values="'group'"
                                        :group_label="'label'"
                                        :group_select="group_select"
                                        :close_on_select="group_select"
                                        :options="payroll_staffs_grouped"
                                        :limit="3"
                                        v-model="record.supervised"
                                    >
                                    </v-multiselect>
                                </div>
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
							<button type="button" @click="createRecord('payroll/supervised-groups')" 
									class="btn btn-primary btn-sm btn-round btn-modal-save">
								Guardar
							</button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="supervisor" slot-scope="props">
                                <span>{{ props.row.supervisor.first_name + ' ' + props.row.supervisor.last_name }}</span>
                            </div>
                            <div slot="approver" slot-scope="props">
                                <span>{{ props.row.approver.first_name + ' ' + props.row.approver.last_name }}</span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                        class="btn btn-warning btn-xs btn-icon btn-action"
                                        title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'payroll/supervised-groups')"
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
                    code: '',
                    supervisor_id: '',
                    approver_id: '',
                    supervised: []
                },
                group_select: true,
                errors:  [],
                records: [],
                payroll_staffs: [],
                payroll_staffs_grouped: [],
                columns: ['code', 'supervisor', 'approver', 'id'],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
                const vm  = this;
                vm.record = {
                    id:          '',
                    code:        '',
                    supervisor_id: '',
                    approver_id: '',
                    supervised: []
                };

                vm.getPayrollStaffsGrouped();
                vm.group_select = true;
            },

            /**
             * Obtiene los datos de los trabajadores registrados agrupados por departamento
             *
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             * 
             */
            async getPayrollStaffsGrouped(ids = []) {
                this.payroll_staffs_grouped = [];
                await axios.get(`${window.app_url}/payroll/get-grouped-staff/${[ids]}`).then(response => {
                    this.payroll_staffs_grouped = Object.values(response.data);
                });
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
                }
                else {
                    vm.loading = true;
                    var fields = {};

                    for (var index in vm.record) {
                        fields[index] = vm.record[index];
                    }
                    await axios.post(url, fields).then(response => {
                        if (typeof(response.data.redirect) !== "undefined") {
                            location.href = response.data.redirect;
                        }
                        else {
                            vm.errors = [];
                            if (reset) {
                                vm.reset();
                            }
                            if (list) {
                                vm.readRecords(url);
                            }

                            vm.getPayrollStaffsGrouped();
                            vm.group_select = true;
                            vm.showMessage('store');
                        }
                    }).catch(error => {
                        vm.errors = [];

                        if (typeof(error.response) !="undefined") {
                            if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(error.response.data.errors[index][0]);
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

                for (var index in vm.record) {
                    fields[index] = vm.record[index];
                }
                await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.getPayrollStaffsGrouped();
                        vm.group_select = true;
                        vm.showMessage('update');
                    }

                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                                vm.showMessage(
                                    'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                );
                            }
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
                vm.loading = false;
            },

            /**
             * Método para la eliminación de registros
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} id    ID del Elemento seleccionado para su eliminación
             * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
             */
            deleteRecord(id, url) {
                const vm = this;
                /** @type {string} URL que atiende la petición de eliminación del registro */
                var url = vm.setUrl((url)?url:vm.route_delete);

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
                    callback: async function (result) {
                        if (result) {
                            vm.loading = true;
                            /** @type {object} Objeto con los datos del registro a eliminar */
                            let recordDelete = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                return rec.id === id;
                            })[0]));

                            await axios.delete(`${url}${url.endsWith('/')?'':'/'}${recordDelete.id}`).then(response => {
                                if (typeof(response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                                vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                    return rec.id !== id;
                                })));
                                if (typeof(vm.$refs.tableResults) !== "undefined") {
                                    vm.$refs.tableResults.refresh();
                                }

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
                            vm.getPayrollStaffsGrouped();
                            vm.loading = false;
                        }
                    }
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
            async initUpdate(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;
                event.preventDefault();

                let ids = [];

                for (const value of recordEdit.payroll_supervised_group_staff) {
                    ids.push(value.payroll_staff_id);
                }

                vm.getPayrollStaffsGrouped(ids).then(() => {
                    vm.group_select = false;

                    for (const value of recordEdit.payroll_supervised_group_staff) {
                        vm.record.supervised.push({
                            group: value.payroll_staff?.payroll_employment?.department?.name,
                            id: value.payroll_staff_id,
                            text: value.payroll_staff.id_number + ' - ' +
                                value.payroll_staff.first_name + ' ' +
                                value.payroll_staff.last_name
                        })
                    }
                });
            },
        },
        created() {
            const vm = this;
            vm.table_options.headings = {
                'code': 'Código',
                'supervisor': 'Supervisor',
                'approver': 'Aprobador',
                'id': 'Acción'
            };
            vm.table_options.sortable       = ['code', 'supervisor', 'approver'];
            vm.table_options.filterable     = ['code', 'supervisor', 'approver'];
            vm.table_options.columnsClasses = {
                'code': 'col-xs-3',
                'supervisor': 'col-xs-3',
                'approver': 'col-xs-3',
                'id': 'col-xs-3'
            };
        },
        mounted() {
            const vm = this;

            $("#add_payroll_supervised_group").on('show.bs.modal', function() {
                vm.getPayrollStaffs('all-active');
                vm.getPayrollStaffsGrouped();
            });
        },
    };
</script>
