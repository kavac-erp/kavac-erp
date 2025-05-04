<template>
	<v-client-table :columns="columns" :data="records" :options="table_options">
		<div slot="code" slot-scope="props" class="text-center">
            <span>
                {{ props.row.code }}
            </span>
        </div>
        <div slot="registered_by" slot-scope="props">
            <span>
                {{ props.row.user?props.row.user.name:'No definido' }}
            </span>
        </div>
		<div slot="sale_warehouse" slot-scope="props">
			<span>
				{{ props.row.sale_warehouse_institution_warehouse_end?props.row.sale_warehouse_institution_warehouse_end.sale_warehouse.name:'N/A' }}
			</span>
		</div>
		<div slot="state" slot-scope="props">
			<span>
				{{ props.row.state?props.row.state:'N/A' }}
			</span>
		</div>
        <div slot="created_at" slot-scope="props">
			<span>
				{{ (format_date(props.row.created_at)) }}
			</span>
		</div>
		<div slot="id" slot-scope="props" class="text-center">
			<div class="d-inline-flex">
				<sale-warehouse-reception-info
					:route_list="'/sale/receptions/info/'+ props.row.id">
				</sale-warehouse-reception-info>

				<button @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        title="Modificar registro" data-toggle="tooltip" type="button"
                        :disabled="props.row.state != 'Pendiente'">
                    <i class="fa fa-edit"></i>
                </button>
                <button @click="deleteRecord(props.index, '')"
						class="btn btn-danger btn-xs btn-icon btn-action"
						title="Eliminar registro" data-toggle="tooltip" type="button"
						:disabled="props.row.state != 'Pendiente'">
					<i class="fa fa-trash-o"></i>
				</button>
				<div v-if="has_role == 1">
					<button @click="approvedRequest(props.index)"
	                            class="btn btn-success btn-xs btn-icon btn-action" title="Aceptar Solicitud"
	                            data-toggle="tooltip" type="button"
	                            :disabled="props.row.state != 'Pendiente'">
	                        <i class="fa fa-check"></i>
	                </button>
	                <button @click="rejectedRequest(props.index)"
	                        class="btn btn-danger btn-xs btn-icon btn-action" title="Rechazar Solicitud"
	                        data-toggle="tooltip" type="button"
	                        :disabled="props.row.state != 'Pendiente'">
	                    <i class="fa fa-ban"></i>
	                </button>
				</div>
			</div>
		</div>
	</v-client-table>
</template>

<script>
	export default {
		data() {
			return {
				records: [],
				columns: ['code', 'registered_by', 'description', 'sale_warehouse', 'created_at', 'state', 'id']
			}
		},
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'registered_by': 'Registrado por',
				'description': 'Descripción',
				'sale_warehouse': 'Almacén',
				'created_at': 'Fecha de Ingreso',
				'state': 'Estado de la solicitud',
				'id': 'Acción'
			};
			this.table_options.sortable = ['code', 'registered_by', 'description', 'sale_warehouse', 'created_at', 'state'];
			this.table_options.filterable = ['code', 'registered_by', 'description', 'sale_warehouse', 'created_at', 'state'];
		},
		mounted () {
			this.initRecords(this.route_list, '');
		},
		props: {
			has_role: {
				type: [String, Number],
			}
		},

		methods: {
			/**
			 * Inicializa los datos del formulario
			 *
			 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
			 */
			reset() {

			},
			rejectedRequest(index)
            {
                const vm = this;

                var dialog = bootbox.confirm({
                    title: 'Rechazar operación?',
                    message: "<p>¿Seguro que desea rechazar esta operación?. Una vez rechazada la operación no se podrán realizar cambios en la misma.<p>",
                    size: 'medium',
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
                            var fields = vm.records[index-1];
                            var id = vm.records[index-1].id;

                            axios.put('/'+vm.route_update+'/reception-rejected/'+id, fields).then(response => {
                                if (typeof(response.data.redirect) !== "undefined")
                                    location.href = response.data.redirect;
                            }).catch(error => {
                                vm.errors = [];
                                if (typeof(error.response) !="undefined") {
                                    for (var index in error.response.data.errors) {
                                        if (error.response.data.errors[index]) {
                                            vm.errors.push(error.response.data.errors[index][0]);
                                        }
                                    }
                                }
                            });
                        }
                    }
                });

            },
            approvedRequest(index)
            {
                const vm = this;
                var dialog = bootbox.confirm({
                    title: 'Aprobar operación?',
                    message: "<p>¿Seguro que desea aprobar esta operación?. Una vez aprobada la operación no se podrán realizar cambios en la misma.<p>",
                    size: 'medium',
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
                            var fields = vm.records[index-1];
                            var id = vm.records[index-1].id;

                            axios.put('/'+vm.route_update+'/reception-approved/'+id, fields).then(response => {
                                if (typeof(response.data.redirect) !== "undefined")
                                    location.href = response.data.redirect;
                            }).catch(error => {
                                vm.errors = [];
                                if (typeof(error.response) !="undefined") {
                                    for (var index in error.response.data.errors) {
                                        if (error.response.data.errors[index]) {
                                            vm.errors.push(error.response.data.errors[index][0]);
                                        }
                                    }
                                }
                            });
                        }
                    }
                });

            },
		}
	};
</script>
