<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('FinancialInfo', props.row.id, 'PayrollFinancialInfo')"
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                    title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button @click="editForm(props.row.id)" v-if="!props.row.assigned"
                    class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                    title="Modificar registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, '')"
                    class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                    title="Eliminar registro" data-toggle="tooltip" data-placement="bottom"
                    type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-client-table>
        <payroll-financial-info
            ref="FinancialInfo">
        </payroll-financial-info>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                records: [],
                record: [],
                columns: [
                    'payroll_staff.first_name',
                    'payroll_staff.last_name',
                    'payroll_staff.id_number',
                    'finance_bank.name',
                    'finance_account_type.name',
                    'payroll_account_number',
                    'id'
                ],
            }
        },

        created() {
            this.table_options.headings = {
                'payroll_staff.first_name': 'Nombre',
                'payroll_staff.last_name': 'Apellido',
                'payroll_staff.id_number': 'Cédula',
                'finance_bank.name': 'Banco',
                'finance_account_type.name': 'Tipo de Cuenta',
                'payroll_account_number': 'Número de la cuenta',
                'id': 'Acción'
            };
            this.table_options.sortable = ['payroll_staff.first_name', 'finance_bank.name', 'finance_account_type.name'];
            this.table_options.filterable = [
                'payroll_staff.first_name',
                'payroll_staff.last_name',
                'payroll_staff.id_number',
                'finance_bank.name',
                'finance_account_type.name',
                'payroll_account_number',
            ];
        },

        mounted() {
            this.initRecords(this.route_list, '');
        },

        methods: {
            reset() {
            },

            /**
             * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
             *
             * @method    setDetails
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             * @param     string   ref       Identificador del componente
             * @param     integer  id        Identificador del registro seleccionado
             * @param     object  var_list  Objeto con las variables y valores a asignar en las variables del componente
             */
            setDetails(ref, id, modal ,var_list = null) {
                const vm = this;
                if (var_list) {
                    for(var i in var_list){
                        vm.$refs[ref][i] = var_list[i];
                    }
                }else{
                    vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                        return r.id === id;
                    })[0];
                }
                vm.$refs[ref].id = id;

                $(`#${modal}`).modal('show');
            },

            /**
             * Reescribe el metodo por defecto
             * Método para la eliminación de registros
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
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
                                /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                                vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                    return rec.id !== id;
                                })));
                                vm.showMessage('destroy');
                            }).catch(error => {
                                if (typeof(error.response) !== "undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                        );
                                    }
                                    return false;
                                }
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                            vm.loading = false;
                        }
                    }
                });
            },
        }
    };
</script>
