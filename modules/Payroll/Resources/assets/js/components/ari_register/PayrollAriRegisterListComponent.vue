<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('AriRegisterInfo', props.row.id, 'PayrollAriRegisterInfo')"
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" title="Ver registro" data-toggle="tooltip"
                    data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-xs btn-icon btn-action" data-toggle="tooltip"
                    @click="editForm(props.row.id)" title="Modificar registro" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, 'payroll/delete-ari-register')"
                    class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" title="Eliminar registro"
                    data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </v-client-table>
        <payroll-ari-register-info ref="AriRegisterInfo">
        </payroll-ari-register-info>
    </div>
</template>
<script>
export default {
    data() {
        return {
            records: [],
            record: [],
            columns: ['first_name', 'last_name', 'id_number', 'id'],
        }
    },

    async created() {
        this.table_options.headings = {
            'first_name': 'Nombres',
            'last_name': 'Apellidos',
            'id_number': 'Cédula de Identidad',
            'id': 'Acción'
        };
        this.table_options.sortable = ['first_name', 'last_name', 'id_number'];
        this.table_options.filterable = ['first_name', 'last_name', 'id_number'];
    },

    async mounted() {
        this.initRecords(this.route_list, '');
    },

    methods: {
        /**
        * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
        *
        * @method    setDetails
        *
        * @author     Pablo Sulbaran <psulbaran@cenditel.gob.ve>
        *
        * @param     string   ref       Identificador del componente
        * @param     integer  id        Identificador del registro seleccionado
        * @param     object  var_list  Objeto con las variables y valores a asignar en las variables del componente
        */
        async setDetails(ref, id, modal, var_list = null) {
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
        async reset() {
        },
    }
};
</script>
