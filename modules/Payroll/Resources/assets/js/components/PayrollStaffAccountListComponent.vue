<template>
    <div>
        <v-server-table :columns="columns" :data="records" :options="table_options" :url="route_list" ref="tableResults">
            <div slot="payroll_staff" slot-scope="props" class="text-center">
                {{ props.row.payroll_staff.first_name + ' ' +  props.row.payroll_staff.last_name }}
            </div>
            <div slot="accounting_account" slot-scope="props" class="text-center">
                {{ props.row.accounting_account.code + ' - ' + props.row.accounting_account.denomination }}
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('staffAccountInfo', props.row.id, 'PayrollStaffAccountInfo')"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button @click="editForm(props.row.id)" v-if="!props.row.assigned"
                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                        title="Modificar registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button disabled
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        title="Eliminar registro" data-toggle="tooltip" data-placement="bottom"
                        type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span v-if="props.row.active" class="text-success font-weight-bold">SI</span>
                <span v-else class="text-danger font-weight-bold">NO</span>
            </div>
        </v-server-table>
        <payroll-staff-account-info
            ref="staffAccountInfo">
        </payroll-staff-account-info>
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
                    'accounting_account',
                    'id'
                ],
            }
        },

        created() {
            this.table_options.headings = {
                'payroll_staff.first_name': 'Nombres',
                'payroll_staff.last_name' : 'Apellidos',
                'payroll_staff.id_number' : 'Cédula de identidad',
                'accounting_account': 'Cuenta contable',
                'id': 'Acción'
            };
            this.table_options.sortable = ['payroll_staff.first_name', 'accounting_account'];
            this.table_options.filterable = [
                'payroll_staff.first_name',
                'payroll_staff.last_name',
                'payroll_staff.id_number',
                'accounting_account'
            ];
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
        }
    };
</script>
