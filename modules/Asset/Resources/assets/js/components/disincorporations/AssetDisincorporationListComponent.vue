<template>
	<div class="card-body">
		<v-server-table :url="route_list" :columns="columns" :options="table_options"
			ref="tableDisincorporationResults">
			<div slot="code" slot-scope="props" class="text-center">
				<span>
					{{ props.row.code }}
				</span>
			</div>
			<div slot="motive" slot-scope="props" class="text-center">
				<span>
					{{
						(props.row.asset_disincorporation_motive)
						?props.row.asset_disincorporation_motive.name
						:'N/A'
					}}
				</span>
			</div>
			<div slot="created" slot-scope="props" class="text-center">
				<span>
					{{
						(props.row.date)
						? format_date(props.row.date)
						: format_date(props.row.created_at)
					}}
				</span>
			</div>
			<div
				slot="status"
				slot-scope="props"
				class="text-center"
				:style="{ color: props.row.document_status.color }"
			>
				<span class="badge h6">
					{{ props.row.document_status.name }}
				</span>
			</div>
			<div slot="id" slot-scope="props" class="text-center">
				<div class="d-inline-flex">
					<a
						v-if="!isEnabled(props.row.document_status.action)"
						@click="viewMessage"
						class="btn btn-success btn-xs btn-icon btn-action"
						title="Aprobar" data-toggle="tooltip"
						disabled
					>
						<i class="fa fa-check"></i>
					</a>
					<button v-else
						@click="changeDocumentStatus(
							props.row.id,
							'AP',
							props.row.document_status.id
						)"
						class="btn btn-success btn-xs btn-icon btn-action"
						title="Aprobar" data-toggle="tooltip"
						:disabled="!isEnabled(props.row.document_status.action)"
					>
						<i class="fa fa-check"></i>
					</button>
					<a
						v-if="!isEnabled(props.row.document_status.action)"
						@click="viewMessage"
						class="btn btn-danger btn-xs btn-icon btn-action"
						title="Rechazar"
						data-toggle="tooltip"
						disabled
					>
						<i class="fa fa-ban"></i>
					</a>
					<button v-else
						@click="changeDocumentStatus(
							props.row.id,
							'RE',
							props.row.document_status.id
						)"
						class="btn btn-danger btn-xs btn-icon btn-action"
						title="Rechazar"
						data-toggle="tooltip"
					>
						<i class="fa fa-ban"></i>
					</button>
					<asset-disincorporation-info
						:index="props.row.id"
						:route_list="app_url + '/asset/disincorporations/load-info/' + props.row.id">
					</asset-disincorporation-info>
					<a
						v-if="!isEnabled(props.row.document_status.action)"
						@click="viewMessage"
						class="btn btn-warning btn-xs btn-icon btn-action"
						title="Modificar registro"
						data-toggle="tooltip"
						type="button"
						disabled
					>
						<i class="fa fa-edit"></i>
					</a>
					<button v-else
						@click="editForm(props.row.id)"
						class="btn btn-warning btn-xs btn-icon btn-action"
						title="Modificar registro"
						data-toggle="tooltip"
						type="button"
						>
						<i class="fa fa-edit"></i>
					</button>
					<a v-if="props.row.document_status.action != 'AP'"
						@click="viewMessage"
						class="btn btn-primary btn-xs btn-icon"
						href="#"
                        title="Generar Reporte"
						data-toggle="tooltip"
						v-has-tooltip
						disabled
					>
                        <i class="fa fa-print" style="text-align: center;"></i>
                    </a>
					<a v-else
						class="btn btn-primary btn-xs btn-icon"
						:href="asset_disincorporation_pdf + props.row.id"
                        title="Generar Reporte"
						data-toggle="tooltip"
						v-has-tooltip target="_blank"
					>
                        <i class="fa fa-print" style="text-align: center;"></i>
                    </a>
					<button
						@click="true ? '' : deleteRecord(props.row.id, '')"
						class="btn btn-danger btn-xs btn-icon btn-action d-none"
						title="Eliminar registro"
						data-toggle="tooltip"
						type="button"
					>
						<i class="fa fa-trash-o"></i>
					</button>
				</div>
			</div>
		</v-server-table>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				records: [],
				asset_disincorporation_pdf: `${window.app_url}/asset/disincorporations/disincorporations-record-pdf/`,
				columns: ['code', 'motive', 'created','status', 'id']
			}
		},

		created() {
			this.table_options.headings = {
				'code': 'Código',
				'motive': 'Motivo',
				'created': 'Fecha de desincorporación',
				'status' : 'Estatus',
				'id': 'Acción'
			};
			this.table_options.sortable = ['code', 'motive', 'created','status'];
			this.table_options.filterable = ['code', 'motive', 'created','status'];
			this.table_options.orderBy = { 'column': 'code'};
		},
		mounted () {
		},
		methods: {
			/**
            * Determina si la desincorporación está en proceso.
            *
			* @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
			*
            * @param {string} documentStatusAction - La acción de estado del documento a verificar.
			*
            * @return {boolean} Devuelve true si la acción de estado del documento es "PR"
			* (en proceso), de lo contrario devuelve false.
            */
			isEnabled (documentStatusAction) {
				return documentStatusAction === "PR"
			},
			/**
			 * Funcion que muestra un mensje de alerta cuando se intenta
			 * acceder a una funcionalidad no permitida
			 *
			 * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
			 */
			viewMessage() {
				const vm = this;
				vm.showMessage(
					"custom",
					"Alerta",
					"danger",
					"screen-error",
					"El estatus de este elemento no le permite acceder a esta funcionalidad"
				);
				return false;
    		},
			/**
            * Cambia el estado de un documento.
            *
            * @param {number} id - El ID de la desincorporación
            * @param {string} action - La acción a realizar (AP para Aprobar, RE para Rechazar)
            * @param {number} document_status_id - El ID de estado actual de la desicorporación
			*
			* @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
          	*/
			changeDocumentStatus(id, action, document_status_id){
				const vm = this;
				bootbox.confirm({
					title: `¿${action === 'AP' ? 'Aprobar' : 'Rechazar'} Desincorporación?`,
					message: `¿Está seguro de ${action ? 'Aprobar' : 'Rechazar'} esta Desincorporación?`,
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
							await axios.patch(
									`${window.app_url}/asset/disincorporations/change-status/${id}`,
									{
										action: action,
										document_status_id: document_status_id
									}
							).then(
								(response) => {
									if (typeof(response.data.redirect) !== "undefined") {
										location.href = response.data.redirect;
									}
									else {
										vm.$refs.tableDisincorporationResults.refresh();
										vm.showMessage('update');
									}
								}
							).catch(error => {
								vm.errors = [];

								if (typeof(error.response) !="undefined") {
									if (error.response.status == 403) {
											vm.showMessage(
												'custom',
												'Acceso Denegado',
												'danger',
												'screen-error',
												error.response.data.message
											);
										}
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
			}
		},
	};
</script>
