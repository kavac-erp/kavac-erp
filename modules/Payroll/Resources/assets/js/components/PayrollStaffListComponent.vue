<template>
    <div>
        <v-server-table :columns="columns" :options="table_options" :url="route_list" ref="tableResults">
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="
            setDetails('StaffInfo', props.row.id, 'PayrollStaffInfo')
            " class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" title="Ver registro" data-toggle="tooltip"
                    data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button @click="editForm(props.row.id)" v-if="!props.row.assigned"
                    class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip" title="Modificar registro"
                    data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, route_delete)"
                    class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" title="Eliminar registro"
                    data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span v-if="props.row.active">SI</span>
                <span v-else>NO</span>
            </div>
            <div slot="payroll_gender" slot-scope="props">
                <span>
                    {{ props.row.payroll_gender ? props.row.payroll_gender.name : 'No definido' }}
                </span>
            </div>
            <div slot="birthdate" slot-scope="props">
                <span>
                    {{ format_date(props.row.birthdate) }}
                </span>
            </div>
            <div slot="email" slot-scope="props">
                <span>
                    {{ props.row.email ? props.row.email : 'No definido' }}
                </span>
            </div>
        </v-server-table>
        <payroll-staff-info ref="StaffInfo"></payroll-staff-info>
    </div>
</template>
<script>
export default {
    data() {
        return {
            record: [],
            columns: [
                'first_name',
                'last_name',
                'id_number',
                'birthdate',
                'payroll_gender',
                'email',
                'id'
            ],
        }
    },

    created() {
        this.table_options.headings = {
            'first_name': 'Nombres',
            'last_name': 'Apellidos',
            'id_number': 'Cédula de Identidad',
            'birthdate': 'Fecha de Nacimiento',
            'payroll_gender': 'Género',
            'email': 'Correo Electrónico',
            'id': 'Acción'
        };
        this.table_options.sortable = [
            'first_name',
            'last_name',
            'id_number',
            'birthdate',
            'payroll_gender'

        ];
        this.table_options.filterable = [
            'first_name',
            'last_name',
            'id_number',
            'birthdate',
            'payroll_gender',
            'email'
        ];
    },

    methods: {
        /**
         * Método que establece los datos del registro seleccionado para el
         * cual se desea mostrar detalles.
         *
         * @method    setDetails
         *
         * @author     Pablo Sulbaran <psulbaran@cenditel.gob.ve>
         *
         * @param     string   ref       Identificador del componente
         * @param     integer  id        Identificador del registro seleccionado
         * @param     object  var_list  Objeto con las variables y valores
         * a asignar en las variables del componente
         */
        setDetails(ref, id, modal, var_list = null) {
            const vm = this;
            if (var_list) {
                for (var i in var_list) {
                    vm.$refs[ref][i] = var_list[i];
                }
            } else {
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
            var url = vm.setUrl((url) ? url : vm.route_delete);

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
                        await axios.delete(`${url}${url.endsWith('/') ? '' : '/'}${id}`).then(response => {
                            if (response.status === 200) {
                                vm.showMessage('destroy');
                            }
                        }).catch(error => {
                            vm.logs('mixins.js', 498, error, 'deleteRecord');
                            vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', error.response.data.message);
                        });
                        vm.loading = false;
                        vm.$refs.tableResults.refresh();
                    }
                }
            });
        },
    }
};
</script>
