<template>
	<v-client-table :columns="columns" :data="records" :options="table_options">
		<div slot="original" slot-scope="props" class="text-center">
			<span v-if="'SI' == props.row.original" class="text-success font-weight-bold">SI</span>
			<span v-else class="text-danger font-weight-bold">NO</span>
		</div>
		<div slot="id" slot-scope="props" class="text-center">
			<button @click="editForm(props.row.id)"
    				class="btn btn-warning btn-xs btn-icon btn-action"
    				title="Modificar registro" data-toggle="tooltip" type="button">
    			<i class="fa fa-edit"></i>
    		</button>
    		<button @click="deleteRecord(props.index, '')"
					class="btn btn-danger btn-xs btn-icon btn-action"
					title="Eliminar registro" data-toggle="tooltip"
					type="button">
				<i class="fa fa-trash-o"></i>
			</button>
		</div>
	</v-client-table>
</template>

<script>
	export default {
		data() {
			return {
				records: [],
				columns: ['code', 'denomination', 'original', 'id']
			}
		},
		created() {
			this.table_options.headings = {
				'code': 'Código',
				'denomination': 'Denominación',
				'original': 'Original',
				'id': 'Acción'
			};
			this.table_options.sortable = ['code', 'denomination', 'original'];
			this.table_options.filterable = ['code', 'denomination', 'original'];
		},
		mounted() {
			this.initRecords(this.route_list, '');
		}
	};
</script>
