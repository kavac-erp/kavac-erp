<template>
    <div>
        <div class="card-btns">
            <div class="d-inline-flex">
                <a href="#" class="btn btn-sm btn-primary btn-custom" @click="redirect_back()"
                    title="Ir atrás" data-toggle="tooltip">
                    <i class="fa fa-reply"></i>
                </a>
                <asset-depreciation-create></asset-depreciation-create>
                <a href="javascript:void(0)" class="card-minimize btn btn-card-action btn-round"
                    title="Minimizar" data-toggle="tooltip">
                    <i class="now-ui-icons arrows-1_minimal-up"></i>
                </a>
            </div>
        </div>
        <v-client-table :columns="columns" :data="records" :options="table_options" ref="tableResults">
            <div slot="amount" slot-scope="props" class="text-center">
                {{ currencyFormat(props.row.amount) }}
            </div>
            <div slot="status" slot-scope="props" class="text-center">
                {{ props.row.document_status.name }}
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <asset-depreciation-info
                        :route_list="app_url + '/asset/depreciations/vue-info/' + props.row.id"
                        :id="props.row.id">
                    </asset-depreciation-info>
                </div>
                <button
                    class="btn btn-success btn-xs btn-icon btn-action"
                    title="Aprobar"
                    data-toggle="tooltip"
                    type="button"
                    @click="'PR' === props.row.document_status.action ? approve(props.row.id) : 'javascript:void(0)'"
                    :disabled="'PR' !== props.row.document_status.action"
                >
                    <i class="fa fa-check"></i>
                </button>
                <button
                    class="btn btn-secondary btn-xs btn-icon btn-action"
                    title="Anular"
                    data-toggle="tooltip"
                    type="button"
                    @click="
                        'AN' === props.row.document_status.action || props.row.has_approved_entry ?
                        'javascript:void(0)' :
                        cancel(props.row.id)
                    "
                    :disabled="'AN' === props.row.document_status.action || props.row.has_approved_entry"
                >
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </v-client-table>
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            supplier: [],
            columns: [
                'code',
                'year',
                'amount',
                'status',
                'id',
            ],
        };
    },
    created() {
        this.table_options.headings = {
            'code': 'Código',
            'year': 'Año fiscal',
            'amount': 'Monto',
            'status': 'Estatus',
            'id': 'Acción'
        };
        this.table_options.sortable = [
            'code',
            'year',
            'amount',
            'status'
        ];
        this.table_options.filterable = [
            'code',
            'year',
            'amount',
            'status'
        ];
        this.table_options.orderBy = {
            column: "id"
        };
    },
    mounted() {
       this.readRecords(this.route_list);
    },
    methods: {
        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
         */
        reset() {
            // 
        },
        redirect_back: function (url) {
            location.href = app_url + '/asset/settings';
        },
        /**
         * Aprueba la depreciación para generar los asientos contables
         */
        approve(id) {
            const vm = this;
            bootbox.confirm({
                title: "¿Aprobar registro?",
                message: "¿Está seguro de aprobar este registro? Al momento de " + 
                         "aprobar se generará el asiento contable de la depreciación.",
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
                        let url = vm.setUrl('asset/depreciations/approve');
                        await axios.post(url + '/' + id).then(response => {
                            if (typeof(response.data.error) !== 'undefined') {
                                /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                vm.showMessage('custom', 'Alerta!', 'danger', 'screen-error', response.data.message);
                                return false;
                            }

                            vm.showMessage('update');
                            vm.loading = false;
                            window.location.reload();
                        }).catch(() => {
                            console.log(error);
                        });
                    }
                }
            });
        },

        /**
         * Anula la depreciación generada
         */
         cancel(id) {
            const vm = this;
            bootbox.confirm({
                title: "¿Anular registro?",
                message: "¿Está seguro de anular este registro? " + 
                         "Se anularán todos los procesos asociados a esta depreciación.",
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
                        let url = vm.setUrl('asset/depreciations/cancel');
                        await axios.post(url + '/' + id).then(response => {
                            if (typeof(response.data.error) !== 'undefined') {
                                /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                vm.showMessage('custom', 'Alerta!', 'danger', 'screen-error', response.data.message);
                                return false;
                            }

                            vm.showMessage('update');
                            vm.loading = false;
                            window.location.reload();
                        }).catch(() => {
                            console.log(error);
                        });
                    }
                }
            });
        }
    },
};
</script>

