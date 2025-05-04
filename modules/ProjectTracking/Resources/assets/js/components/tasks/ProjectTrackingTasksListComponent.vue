<template>
    <div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="number" slot-scope="props" class="text-center">
                {{ getTaskNumber(props.row.id) }}
            </div>
            <div slot="associate_to" slot-scope="props" class="text-center">
                <div v-if="props.row.project_name && props.row.project">
                    {{ 'Proyecto: ' + props.row.project.name }}
                </div>
                <div v-else-if="props.row.product_name && props.row.product">
                    {{ 'Producto: ' + props.row.product.name }}
                </div>
                <div v-else>
                    {{ 'Subproyecto: ' + props.row.subproject.name }}
                </div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <project-tracking-task-info :modal_id="props.row.id"
                        :url="'projecttracking/get-task-info/' + props.row.id">
                    </project-tracking-task-info>
                    <button @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip" title="Modificar registro"
                        data-toggle="tooltip" data-placement="bottom" type="button">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteRecord(props.row.id, props.row.index)"
                        class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" title="Eliminar registro"
                        data-toggle="tooltip" data-placement="bottom" type="button">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </v-client-table>
    </div>
</template>
<script>
export default {
    props: {
        modal_id: {
            type: Number
        },
        url: {
            type: String
        },
        route_list: {
            type: String
        },
        route_edit: {
            type: String
        },
        route_delete: {
            type: String
        }
    },
    data() {
        return {
            associate_to: '',
            records: [],
            record: [],
            columns: ['number', 'name', 'associate_to', 'employers_name', 'end_date', 'activity_status.name', 'priority.name', 'weight', 'id'],
        }
    },

    created() {
        this.table_options.headings = {
            'number': 'N°',
            'name': 'Nombre de la Tarea',
            'associate_to': 'Asociada a',
            'employers_name': 'Responsable de la Tarea',
            'end_date': 'Fecha de Entrega',
            'activity_status.name': 'Estatus',
            'priority.name': 'Prioridad',
            'weight': 'Peso',
            'id': 'Acción'
        };
        this.table_options.sortable = ['number', 'name', 'associate_to', 'employers_name', 'end_date', 'activity_status.name', 'priority.name', 'weight'];
        this.table_options.filterable = ['number', 'name', 'associate_to', 'employers_name', 'end_date', 'activity_status.name', 'priority.name', 'weight'];
        this.table_options.columnsClasses = {
            'number': 'text-center',
            'name': 'text-center',
            'associate_to': 'text-center',
            'employers_name': 'text-center',
            'end_date': 'text-center',
            'activity_status.name': 'text-center',
            'priority.name': 'text-center',
            'weight': 'text-center',
            'id': 'text-center'
        };
    },

    methods: {
        getTaskNumber(param) {
            const vm = this
            let index = vm.records.findIndex(obj => { return obj.id == param })
            return index + 1
        },
        /**
 * Método que borra un registro de la tabla
 * 
 * @author  Pedro Contreras <pdrocont@gmail.com>
 */
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

                        await axios.delete(`${window.app_url}/projecttracking/tasks/delete/${id}`).then(response => {
                            vm.records.splice(index, 1);
                            vm.showMessage('destroy');
                            vm.loading = false;
                        });
                    }
                }
            });

        }
    },

    mounted() {
        this.initRecords(this.route_list, '');
    },

    reset() {
        //
    },
};
</script>