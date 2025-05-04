<template>
    <v-client-table :columns="columns" :data="records" :options="table_options">
        <div slot="code" slot-scope="props" class="text-center">
            <span>
                {{ props.row.code }}
            </span>
        </div>
        <div slot="department" slot-scope="props">
            <span>
                {{ (props.row.department)?props.row.department.name:'N/A' }}
            </span>
        </div>
        <div slot="motive" slot-scope="props" class="text-center"
             v-html="prepareText(props.row.motive)">
        </div>
        <div slot="request_date" slot-scope="props">
            <span>
                {{ (props.row.request_date) ? format_date(props.row.request_date):format_date(props.row.created_at) }}
            </span>
        </div>
        <div slot="id" slot-scope="props" class="text-center">
            <div class="d-inline-flex">
                <warehouse-req-info
                    :route_list="app_url + '/warehouse/requests/info/'+ props.row.id"
                    :infoid="props.row.id">
                </warehouse-req-info>
                <template v-if="(lastYear && format_date(props.row.created_at, 'YYYY') <= lastYear)">
                    <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
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
                </template>
            </div>
        </div>
    </v-client-table>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            lastYear: "",
            columns: ['code', 'department', 'motive', 'state', 'request_date', 'id']
        }
    },
    created() {
        this.table_options.headings = {
            'code':       'Código',
            'department': 'Departamento solicitante',
            'motive': 	  'Motivo',
            'state':      'Estado de la solicitud',
            'request_date': 'Fecha de la solicitud',
            'id':         'Acción'
        };
        this.table_options.sortable = ['code', 'department', 'motive', 'state', 'request_date'];
        this.table_options.filterable = ['code', 'department', 'motive', 'state', 'request_date'];
    },
    async mounted() {
        this.initRecords(this.route_list, '');
        const vm = this;
        await vm.queryLastFiscalYear();
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        prepareText(text) {
            return text.substr(3, text.length-4);

        },
        reset() {

        },
        deleteRecord(index, url) {
            var url = (url)?url:this.route_delete;
            var records = this.records;
            var confirmated = false;
            var index = index - 1;
            const vm = this;
            url = vm.setUrl(url);

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
                        axios.delete(`${url}/${records[index].id}`).then(response => {
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
    }
};
</script>
