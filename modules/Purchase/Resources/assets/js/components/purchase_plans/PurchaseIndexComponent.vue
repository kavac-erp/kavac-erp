<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha de inicio</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de inicio"
                    tabindex="1"
                    v-model="filterBy.init_date"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha de culminación</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de culminación"
                    tabindex="2"
                    v-model="filterBy.end_date"
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
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
        >
            <div slot="init_date" slot-scope="props" class="text-center">
                {{ format_date(props.row.init_date) }}
            </div>
            <div slot="end_date" slot-scope="props" class="text-center">
                {{ format_date(props.row.end_date) }}
            </div>
            <div slot="active" slot-scope="props" class="text-center">
                <span class="badge badge-success" v-if="props.row.active">
                    <strong>PROCESADO</strong>
                </span>
                <span class="badge badge-danger" v-else>
                    <strong>NO PROCESADO</strong>
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <purchase-plan-show
                        :id="props.row.id"
                        :route_show="'/purchase/purchase_plans/'+props.row.id"
                    />
                    <template v-if="(lastYear && format_date(props.row.init_date, 'YYYY') <= lastYear)">
                        <button class="btn btn-success btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <template v-else>
                        <purchase-plan-start-diagnosis
                            :id="props.row.id"
                            :route_show="'/purchase/purchase_plans/'+props.row.id"
                            v-if="!props.row.active"
                        />
                        <button
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                            @click="editForm(props.row.id)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                            @click="deleteRecord(props.index,'/purchase/purchase_plans')"
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                </div>
            </div>
        </v-client-table>
    </section>
</template>
<script>
export default {
    props: {
        record_list: {
            type: Array,
            default: function() {
                return [];
            }
        },
    },
    data() {
        return {
            records: [],
            lastYear: "",
            tmpRecords: [],
            columns: [
                'init_date',
                'end_date',
                'purchase_type.name',
                'active',
                'id'
            ],
            filterBy: {
                init_date: '',
                end_date: '',
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
                init_date: '',
                end_date: '',
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
                return (vm.filterBy.init_date) ? (rec.init_date === vm.filterBy.init_date) : true;
            }).filter((rec) => {
                return (vm.filterBy.end_date) ? (rec.end_date === vm.filterBy.end_date) : true;
            })
        },
    },
    created() {
        this.table_options.headings = {
            'init_date': 'Fecha de inicio',
            'end_date': 'Fecha de culminación',
            'purchase_type.name': 'Tipo de compra',
            'purchase_process.name': 'Proceso de compra',
            'active': 'Estatus',
            'id': 'Acción'
        };
        this.table_options.columnsClasses = {
            'init_date': 'col-xs-2 text-center',
            'end_date': 'col-xs-2 text-center',
            'purchase_type.name': 'col-xs-4',
            'purchase_process.name': 'col-xs-2',
            'active': 'col-xs-1',
            'id': 'col-xs-1'
        };
        this.table_options.sortable = ['init_date','end_date'];
        this.table_options.filterable = ['init_date', 'end_date'];
    },
    async mounted () {
        const vm = this;
        await vm.queryLastFiscalYear();
        this.records = this.record_list;
        // Variable usada para el reseteo de los filtros de la tabla.
        this.tmpRecords = this.records;
    }
};
</script>
