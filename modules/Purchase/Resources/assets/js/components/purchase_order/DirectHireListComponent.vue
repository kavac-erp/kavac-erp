<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha de generación</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de generación"
                    tabindex="1"
                    v-model="filterBy.date"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Código</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Código"
                    tabindex="2"
                    v-model="filterBy.code"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Fuente de financiamiento</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Fuente de financiamiento"
                    tabindex="2"
                    v-model="filterBy.funding_source"
                />
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button
                        class="btn btn-default btn-icon btn-xs-responsive px-3"
                        type="reset"
                        aria-label="Search"
                        title="Limpiar filtro"
                        @click="resetFilters()"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        class="btn btn-info btn-icon btn-xs-responsive px-3"
                        type="button"
                        aria-label="Search"
                        title="Buscar"
                        @click="filterTable()"
                    >
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Final de filtros de la tabla -->
        <hr>
        <v-server-table
            :columns="columns"
            :url="'purchase/direct_hire/vue-list'"
            :options="table_options"
        >
            <div slot="date" slot-scope="props" class="text-center">
                <span>{{ format_date(props.row.date) }}</span>
            </div>
            <div slot="status" slot-scope="props">
                {{ getStatus(props.row) }}
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <template v-if="(lastYear && format_date(props.row.date, 'YYYY') <= lastYear)">
                        <button class="btn btn-success btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            class="btn btn-success btn-xs btn-icon btn-action"
                            type="button"
                            data-toggle="tooltip"
                            title="Aprobar"
                            v-show="props.row.status == 'WAIT'"
                            @click="approveDirectHire(props.row.id)"
                        >
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <purchase-order-direct-hire-show :id="props.row.id">
                    </purchase-order-direct-hire-show>
                    <a
                        class="btn btn-primary btn-xs btn-icon"
                        :href="purchase_order_pdf + props.row.id"
                        title="Imprimir registro"
                        data-toggle="tooltip"
                        v-has-tooltip target="_blank"
                    >
                        <i class="fa fa-print" style="text-align: center;"></i>
                    </a>
                    <template v-if="(lastYear && format_date(props.row.date, 'YYYY') <= lastYear)">
                        <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            v-show="props.row.status == 'WAIT'"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                            @click="editForm(props.row.id)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            v-show="props.row.status == 'WAIT'"
                            @click="deleteRecord(props.row.id, '/purchase/direct_hire')"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                </div>
            </div>
        </v-server-table>
    </section>
</template>
<script>
export default {
    data() {
        return {
            records: [],
            lastYear: "",
            tmpRecords: [],
            url_start_certificate: `
                ${window.app_url}/purchase/direct_hire/start_certificate/pdf/
            `,
            purchase_order_pdf: `
                ${window.app_url}/purchase/direct_hire/purchase_order/pdf/
            `,
            columns: [
                'date',
                'code',
                'funding_source',
                'description',
                'status',
                'id'
            ],
            filterBy: {
                date: '',
                code: '',
            },
        }
    },
    methods: {
        /**
         * Método para reestablecer valores iniciales del formulario de filtros.
         *
         * @method resetFilters
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@cenditel.gob.ve>
         */
        resetFilters() {
            const vm = this;
            vm.filterBy = {
                date: '',
                code: '',
                funding_source: ''
            };
            vm.records = vm.tmpRecords;
        },

        /**
         * Método que permite filtrar los datos de la tabla.
         *
         * @method filterTable
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        filterTable() {
            const vm = this;
            vm.records = vm.tmpRecords.filter((rec) => {
                return (vm.filterBy.date) ? (rec.date === vm.filterBy.date) : true;
            }).filter((rec) => {
                return (vm.filterBy.code) ? (rec.code === vm.filterBy.code) : true;
            }).filter((rec) => {
                return (vm.filterBy.funding_source) ? (rec.funding_source
                    === vm.filterBy.funding_source) : true;
            })
        },

        /**
         * Método para aprobar una orden de compra
         *
         * @method approveDirectHire
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        approveDirectHire(id) {
            const vm = this;
            const url = vm.setUrl('purchase/change-direct-hire-status');
            bootbox.confirm({
                title: "Aprobar registro",
                message: `
                    ¿Está seguro? Una vez aprobado el registro no se podrá
                    modificar y/o eliminar la(s) cotización(es).
                `,
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar'
                    }
                },
                callback: function(result) {
                    if (result) {
                        vm.loading = true;
                        axios.post(url, { id: id }).then(response => {
                            if (response.status == 200){
                                vm.showMessage(
                                    'custom',
                                    '¡Éxito!',
                                    'success',
                                    'screen-ok',
                                    'Orden de compra/servicio aprobada con éxito'
                                );
                                location.reload();
                            }
                        }).catch(error => {
                            console.error(error);
                        });
                        vm.loading = false;
                    }
                }
            });
        },

        /**
         * Método para obtener el estatus de una orden de pago asociada a una orde de compra
         *
         * @method getStatus
         *
         *
         */
        getStatus(direct_hire){
            if(direct_hire.status === 'APPROVED') {
                if (!direct_hire.status_pay_order || direct_hire.status_pay_order === 'AN') { return "En espera por crear orden de pago" }
                if (direct_hire.status_pay_order === 'PE') { return "En espera por Aprobar orden de pago" }
                if (direct_hire.status_pay_order === 'AP') { return "Aprobado" }
                if (direct_hire.status_pay_order === 'PA') { return "Pagado"}
            } else if(direct_hire.status === 'WAIT') { return "En espera" }
        }

    },
    created() {
        this.table_options.headings = {
            'date': 'Fecha de generación',
            'code': 'Código',
            'funding_source': 'Fuente de financiamiento',
            'description': 'Denominación especifica del requerimiento',
            'status': 'Estado',
            'id': 'Acción'
        };
        this.table_options.columnsClasses = {
            'date': 'col-xs-2 text-center',
            'code': 'col-xs-2 text-center',
            'funding_source': 'col-xs-3 text-center',
            'description': 'col-xs-4 text-center',
            'status': 'col-xs-4 text-center',
            'id': 'col-xs-1'
        };
        this.table_options.sortable = ['date'];
        this.table_options.filterable = ['date'];
    },
    async mounted () {
        const vm = this;
        await vm.queryLastFiscalYear();
        vm.loadingState(true); // Inicio de spinner de carga.
        axios.get(`${window.app_url}/purchase/direct_hire/vue-list`)
            .then(response => {
            vm.records = response.data.records;
            // Variable usada para el reseteo de los filtros de la tabla.
            vm.tmpRecords = vm.records;
            vm.loadingState(); // Finaliza spinner de carga.
        });
    }
};
</script>
