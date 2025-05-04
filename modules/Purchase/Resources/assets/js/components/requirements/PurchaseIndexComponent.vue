<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha"
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
                <label class="form-label">Estado</label>
                <select2
                    :options="status_options"
                    v-model="filterBy.status"
                    tabindex="3"
                />
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button
                        type="reset"
                        class="btn btn-default btn-icon btn-xs-responsive px-3"
                        aria-label="Search"
                        @click="resetFilters()"
                        title="Limpiar filtro"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-info btn-icon btn-xs-responsive px-3"
                        aria-label="Search"
                        @click="filterTable()"
                        title="Buscar"
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
            :url="'purchase/requirements/vue-list'"
            :options="table_options"
            ref="tableOptions"
        >
            <div slot="date" slot-scope="props">
                {{ format_date(props.row.date, 'DD/MM/YYYY') }}
            </div>
            <div
                slot="purchase_supplier_object"
                slot-scope="props"
                class="text-left"
            >
                <div class="d-inline-flex">
                    <span v-if="props.row.purchase_supplier_object.type == 'B'">
                        Bienes
                    </span>
                    <strong v-else-if="props.row.purchase_supplier_object.type == 'O'">
                        Obras
                    </strong>
                    <strong v-else-if="props.row.purchase_supplier_object.type == 'S'">
                        Servicios
                    </strong>
                    &nbsp;- {{ props.row.purchase_supplier_object.name }}
                </div>
            </div>
            <div
                slot="requirement_status"
                slot-scope="props"
                class="text-center"
            >
                <div class="d-inline-flex">
                    <span
                        class="badge badge-danger"
                        v-show="props.row.requirement_status == 'WAIT'"
                    >
                        <strong>EN ESPERA</strong>
                    </span>
                    <span
                        class="badge badge-info"
                        v-show="props.row.requirement_status == 'PROCESSED'"
                    >
                        <strong>PROCESADO</strong>
                    </span>
                    <span
                        class="badge badge-success"
                        v-show="props.row.requirement_status == 'BOUGHT'"
                    >
                        <strong>COMPRADO</strong>
                    </span>
                </div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <purchase-requirements-show
                        :id="props.row.id"
                        :route_show="app_url+'/purchase/requirements/'+props.row.id"
                    />
                    <a
                        class="btn btn-primary btn-xs btn-icon"
                        :href="app_url+'/purchase/requirements/pdf/'+props.row.id"
                        title="Imprimir Registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        target="_blank"
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
                            v-if="props.row.requirement_status == 'WAIT'"
                            @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            v-if="props.row.requirement_status == 'WAIT'"
                            @click="deleteRecord(props.row.id,'/purchase/requirements')"
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
            columns: [
                'date',
                'code',
                'description',
                'contrating_department.name',
                'user_department.name',
                'purchase_supplier_object',
                'requirement_status',
                'id'
            ],
            filterBy: {
                date: '',
                code: '',
                status: '',
            },
            status_options: [
                { "id": "", "text": "Seleccione..." },
                { "id": "WAIT", "text": "EN ESPERA" },
                { "id": "PROCESSED", "text": "PROCESADO" },
                { "id": "BOUGHT", "text": "COMPRADO" },
            ],
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
                status: '',
            };
            vm.$refs.tableOptions.refresh();
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
            let params = {
                query: vm.filterBy.date ?
                    vm.format_date(vm.filterBy.date)
                    : vm.filterBy.code
                        ? vm.filterBy.code
                        : vm.filterBy.status
                            ? vm.filterBy.status : '',
                limit: 10,
                ascending: 1,
                page: 1,
                byColumn: 0
            }

            axios.get(`${window.app_url}/purchase/requirements/vue-list`, {params: params})
            .then(response => {
                vm.$refs.tableOptions.data = response.data.data;
                vm.$refs.tableOptions.count = response.data.count;
            });
        },
    },
    created() {
        this.table_options.headings = {
            'date': 'Fecha',
            'code': 'Código',
            'description': 'Descripción',
            'contrating_department.name': 'Departamento contratante',
            'user_department.name': 'Departamento usuario',
            'purchase_supplier_object': 'Tipo',
            'requirement_status': 'Estado',
            'id': 'ACCIÓN'
        };
        this.table_options.columnsClasses = {
            'code': 'col-xs-1',
            'description': 'col-xs-2',
            'date': 'text-center col-xs-1',
            'contrating_department.name': 'col-xs-2',
            'user_department.name': 'col-xs-2',
            'purchase_supplier_object': 'col-xs-2',
            'requirement_status': 'col-xs-1',
            'id': 'col-xs-1'
        };
        this.table_options.sortable = ['code', 'date'];
        this.table_options.filterable = ['code', 'date'];
    },
    async mounted () {
        const vm = this;
        await vm.queryLastFiscalYear();
    }
};
</script>
