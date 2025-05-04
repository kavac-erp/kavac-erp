    <template>
    <v-client-table :columns="columns" :data="records" :options="table_options">
        <div slot="id" slot-scope="props" class="text-center">
            <div class="d-inline-flex">
                <budget-centralized-actions-info
                    :id="props.row.id"
                    :route_list="app_url + '/budget/detail-vue-centralized-actions/' + props.row.id"
                >
                </budget-centralized-actions-info>
                <template v-if="(lastYear && format_date(props.row.from_date, 'YYYY') <= lastYear)">
                    <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        v-if="!props.row.disabled"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        type="button"
                        data-toggle="tooltip"
                        title="Eliminar registro"
                        @click="deleteRecord(props.index, '')"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>


            </div>
        </div>
        <div class="text-center" slot="active" slot-scope="props">
            <span v-if="props.row.active" class="text-success font-weight-bold">SI</span>
            <span v-else class="text-danger font-weight-bold">NO</span>
        </div>
    </v-client-table>
</template>

<script>
    import { format } from 'path';
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                columns: [
                    'code',
                    'name',
                    'active',
                    'id'
                ]
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'name': 'Acción Centralizada',
                'active': 'Activa',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'code',
                'name'
            ];
            this.table_options.filterable = [
                'code',
                'name'
            ];
            this.table_options.columnsClasses = {
                'code': 'col-md-2',
                'name': 'col-md-4',
                'active': 'col-md-1',
                'id': 'col-md-3'
            };
        },
        async mounted() {
            const vm = this;
            vm.initRecords();
            await vm.queryLastFiscalYear();
        },
        methods: {
            initRecords() {
                const vm = this;
                let url = this.setUrl(this.route_list);
                axios.get(url).then(response => {
                    if (typeof(response.data.records) !== "undefined") {
                        vm.records = response.data.records;
                        vm.records.forEach(element => {
                            element.custom_date = this.format_date(element.custom_date);
                        });
                    }
                    if ($("#" + modal_id).length) {
                        $("#" + modal_id).modal('show');
                    }
                })
                .catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                        else {
                            vm.logs('resources/js/all.js', 343, error, 'initRecords');
                        }
                    }
                });
            },

            /**
             * Inicializa datos del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {},

            /**
             * Muestra los detalles de un registro seleccionado
             *
             * @author
             *
             * @param {string} id Identificador del registro para el cual se desea mostrar los detalles
             */
            async details(id) {
                const vm = this;
                vm.loading = true;
                await axios.get('/budget/detail-vue-centralized-actions/'+ id).then(response => {
                    if (response.data.result) {
                        let buget = response.data.buget;
                        let cargo = response.data.cargo;
                        let name = "";
                    }
                })
                .catch(error => {});
                vm.loading = false;
            }
        }
    };
</script>
