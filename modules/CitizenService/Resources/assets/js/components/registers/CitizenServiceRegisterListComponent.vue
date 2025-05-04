<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="id" slot-scope="props" class="text-center">
                <button @click.prevent="setDetails('vue-info', props.row.id, 'CitizenServiceRegisterInfo')"
                        class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                        title="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <button @click="editForm(props.row.id)" v-if="!props.row.assigned"
                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                        title="Modificar registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.row.id, props.row.index)"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        title="Eliminar registro" data-toggle="tooltip" data-placement="bottom"
                        type="button">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
            <div slot="payroll_staff" slot-scope="props">
				<span>
					 {{
					 	props.row.payroll_staff
					 	?
	                       (props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name)
					 	: 'No definido'
	                 }}
				</span>
			</div>

        </v-client-table>
        <citizenservice-register-info ref="vue-info"></citizenservice-register-info>
    </div>
</template>


<script>
	export default {
		props: {
			route_delete: {
				type: String
			}
		},
		data() {
			return {
				records: [],
				record: [],
				columns: ['request_code', 'date_register', 'payroll_staff', 'activities', 'id']
			}
		},
		created() {
			this.table_options.headings = {
				'request_code': 'Código de la solicitud',
				'payroll_staff': 'Nombre del director',
				'date_register': 'Fecha del registro',
				'activities': 'Actividades',
				'id': 'Acción'
			};
			this.table_options.sortable = ['request_code', 'payroll_staff', 'date_register', 'activities'];
			this.table_options.filterable = ['request_code', 'payroll_staff', 'date_register', 'activities'];
		},
		mounted () {
			this.initRecords(this.route_list, '');
		},
		methods: {
			/**
			 * Inicializa los datos del formulario
			 *
			 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
			 */
			reset() {

			},

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

            deleteRecord(id, index) {
                const vm = this;

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

                            await axios.delete(`${window.app_url}/citizenservice/registers/delete/${id}`).then(response => {
                                vm.records.splice(index, 1);
                                vm.showMessage('destroy');
                                vm.loading = false;
                            });
                        }
                    }
                });

            }
		}
	};
</script>
