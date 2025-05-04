<template>
	<section>
    	<v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="payroll_staff" slot-scope="props">
                <span>
                    {{
                        props.row.payroll_staff
                            ? props.row.payroll_staff.id
                                ? props.row.payroll_staff.first_name + ' ' + props.row.payroll_staff.last_name
                                : 'No definido'
                            : 'No definido'

                    }}
                </span>
            </div>
    		<div slot="id" slot-scope="props" class="text-center">
    			<section class="d-inline-flex">
    				<payroll-permission-request-info
    					:route_list="'payroll/permission-requests/vue-info/' + props.row.id">
    				</payroll-permission-request-info>

    	    		<button @click="editForm(props.row.id)"
    	    				class="btn btn-warning btn-xs btn-icon btn-action"
    	    				title="Modificar registro" data-toggle="tooltip" type="button" v-has-tooltip
    	    				:disabled="props.row.status != 'Pendiente'">
    	    			<i class="fa fa-edit"></i>
    	    		</button>
    	    		<button @click="deleteRecord(props.index, '')"
    						class="btn btn-danger btn-xs btn-icon btn-action"
    						title="Eliminar registro" data-toggle="tooltip" v-has-tooltip type="button"
    						:disabled="props.row.status != 'Pendiente'">
    					<i class="fa fa-trash-o"></i>
    				</button>
    				<button @click="acceptRequest(props.index)"
						class="btn btn-success btn-xs btn-icon btn-action"
						title="Aceptar Solicitud" data-toggle="tooltip" v-has-tooltip type="button"
						:disabled="props.row.status != 'Pendiente'">
					<i class="fa fa-check"></i>
					</button>

					<button @click="rejectedRequest(props.index)"
						class="btn btn-danger btn-xs btn-icon btn-action" title="Rechazar Solicitud" data-toggle="tooltip" v-has-tooltip type="button"
						:disabled="props.row.status != 'Pendiente'">
					<i class="fa fa-ban"></i>
					</button>
    			</section>
    		</div>
    	</v-client-table>
	</section>
</template>

<script>
	export default {
		data() {
			return {
				records: [],
				columns: ['payroll_staff', 'motive_permission', 'status', 'id']
			}
		},
		created() {
			this.table_options.headings = {
				'payroll_staff': 'Trabajador',
				'motive_permission': 'Motivo del permiso',
				'status':        'Estatus de la solicitud',
				'id': 'Acción'
			};
			this.table_options.sortable = ['payroll_staff', 'motive_permission', 'status'];
			this.table_options.filterable = ['payroll_staff', 'motive_permission', 'status'];
		},
		mounted () {
			this.initRecords(this.route_list, '');
		},
		methods: {
			/**
			 * Inicializa los datos del formulario
			 *
			 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
			 */
			reset() {

			},
			deleteRecord(index, url) {
				const vm = this;
	            var records = vm.records;
	            var confirmated = false;
	            var index = index - 1;
	            url = (url)?vm.setUrl(url):vm.route_delete;

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
	                        axios.delete(url + '/' + records[index].id).then(response => {
	                            if (typeof(response.data.error) !== "undefined") {
	                                /** Muestra un mensaje de error si sucede algún evento en la eliminación */
	                                vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
	                                return false;
	                            }
	                            records.splice(index, 1);
	                            vm.showMessage('destroy');
	                        }).catch(error => {
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
	        acceptRequest(index)
			{
				const vm = this;
				var fields = this.records[index-1];
				var id = this.records[index-1].id;

				axios.put(`${vm.route_update}/request-approved/${id}`, fields).then(response => {
					if (typeof(response.data.redirect) !== "undefined") {
						location.href = response.data.redirect;
					}
					else {
						vm.readRecords(url);
						vm.reset();
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
			},
			rejectedRequest(index)
			{
				const vm = this;
				var fields = this.records[index-1];
				var id = this.records[index-1].id;

				axios.put(`${vm.route_update}/request-rejected/${id}`, fields).then(response => {
					if (typeof(response.data.redirect) !== "undefined") {
						location.href = response.data.redirect;
					}
					else {
						vm.readRecords(url);
						vm.reset();
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
			},
		},
	};
</script>
