<template>
	<div >
		<div class="card">
			<div class="card-header">
				<h6 class="card-title text-uppercase">Historial de Inventario de Bienes</h6>
				<div class="card-btns">
					<a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back(route_list)"
					   title="Ir atrás" data-toggle="tooltip">
						<i class="fa fa-reply"></i>
					</a>
					<a href="#" class="btn btn-sm btn-primary btn-custom" @click="createRecord('asset/inventory-history')"
					   title="Guardar estado actual de inventario" data-toggle="tooltip">
						<i class="fa fa-plus-circle"></i>
					</a>
					<a href="#" class="card-minimize btn btn-card-action btn-round" title="Minimizar"
					   data-toggle="tooltip">
						<i class="now-ui-icons arrows-1_minimal-up"></i>
					</a>
				</div>
			</div>

			<div class="card-body col-md-12">
				<v-client-table :columns="columns" :data="records" :options="table_options">

					<div slot="created_at" slot-scope="props" class="text-center">
						<span>
							{{ (props.row.created_at)? format_date(props.row.created_at):'N/A' }}
						</span>
					</div>
					<div slot="id" slot-scope="props" class="text-center">

						<div class="d-inline-flex">

							<button @click="createReport(props.row.code, 'create_report')"
									class="btn btn-primary btn-xs btn-icon btn-action"
									title="Generar reporte de bienes" data-toggle="tooltip"
									type="button" v-has-tooltip>
								<i class="fa fa-file-pdf-o"></i>
							</button>

				    		<button @click="deleteRecord(props.row.id, 'asset/inventory-history/delete')"
									class="btn btn-danger btn-xs btn-icon btn-action"
									title="Eliminar registro" data-toggle="tooltip"
									type="button" v-has-tooltip>
								<i class="fa fa-trash-o"></i>
							</button>
						</div>
					</div>
				</v-client-table>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					code: '',
					type_report: '',
				},
				records: [],

				columns: ['code', 'created_at', 'registered', 'assigned', 'disincorporated', 'id'],
			}
		},
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'created_at': 'Fecha de creación',
				'registered': 'Bienes registrados',
				'assigned': 'Bienes asignados',
				'disincorporated': 'Bienes desincorporados',
				'id': 'Acción'
			};
			this.table_options.sortable = ['code', 'created_at', 'registered', 'assigned', 'disincorporated'];
			this.table_options.filterable = ['code', 'created_at', 'registered', 'assigned', 'disincorporated'];
			this.table_options.orderBy = { 'column': 'id'};
		},
		mounted () {
			this.readRecords(`${window.app_url}/asset/inventory-history/vue-list`);
			this.switchHandler('type_report');
		},
		methods: {
			/**
			 * Inicializa los datos del formulario
			 *
			 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
			 */
			reset() {
				this.record = {
					id: '',
					code: '',
					type_report: 'general',
				}
			},

			createReport(code, modal_id) {
				const vm = this;
				vm.record.code = code;
				if (vm.record.type_report == 'dependence') {
					return false;
				}
				var url = `${window.app_url}/asset/reports/general/show/${vm.record.code}`;
				window.open(url, '_blank');
				if ($("#" + modal_id).length) {
					$("#" + modal_id).modal('hide');
				}
			}
		}
	};
</script>
