<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label for="" class="form-label">Fecha</label>
                <input
                    id="prefix"
                    class="form-control"
                    type="date"
                    placeholder="Fecha"
                    tabindex="1"
                    v-model="filterBy.date"
                />
            </div>
            <div class="col-md-2">
                <label for="" class="form-label">Código de requerimiento</label>
                <input
                    id="prefix"
                    class="form-control"
                    type="text"
                    placeholder="Código de requerimiento"
                    tabindex="2"
                    v-model="filterBy.code"
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
        <hr>
        <!-- Final de filtros de la tabla -->
        <v-server-table
            :columns="columns"
            :url="'purchase/base_budget/vue-list'"
            :options="table_options"
            ref="tableOptions"
        >
            <div slot="date" slot-scope="props" class="text-center">
                <div align="center">
                    {{ (props.row.date) ? format_date(props.row.purchase_requirement.date) : format_date(props.row.created_at) }}
                </div>
            </div>
            <div slot="currency.name" slot-scope="props">
                <div align="center">
                    <span v-if="props.row.currency">
                        {{ props.row.currency.symbol }} - {{ props.row.currency.name }}
                    </span>
                    <span v-else>
                        No asignado
                    </span>
                </div>
            </div>
            <div slot="status" slot-scope="props">
                <div class="d-inline-flex">
                    <span class="badge badge-danger" v-show="props.row.status_aux == 'WAIT'">
                        <strong>POR COMPLETAR</strong>
                    </span>
                    <span class="badge badge-primary" v-show="props.row.status_aux == 'WAIT_SEND_NOTIFICATION'">
                        <strong>ESPERA POR ENVIAR SOLICITUD PRESUPUESTARIA</strong>
                    </span>
                    <span class="badge badge-primary" v-show="props.row.status_aux == 'WAIT_BUDGET_AVAILABILITY'">
                        <strong>ESPERA POR DISPONIBILIDAD PRESUPUESTARIA</strong>
                    </span>
                    <span class="badge badge-primary" v-show="props.row.status_aux == 'WAIT_APPROVE_BUDGET_AVAILABILITY'">
                        <strong>ESPERA POR APROBAR DISPONIBILIDAD PRESUPUESTARIA</strong>
                    </span>
                    <span class="badge badge-primary" v-show="props.row.status_aux == 'WAIT_QUOTATION'">
                        <strong>ESPERA POR COTIZACIÓN</strong>
                    </span>
                    <br>
                    <span class="badge badge-primary"
                            v-show="props.row.status_aux == 'WAIT_APPROVE_PARTIAL_QUOTE'"
                        >
                        <strong>ESPERA POR APROBAR COTIZACIÓN PARCIAL</strong>
                    </span>
                    <br>
                    <span class="badge badge-info"
                            v-show="props.row.status_aux == 'PARTIALLY_QUOTED'">
                        <strong>COTIZADO PARCIALMENTE</strong>
                    </span>
                    <br>
                    <span class="badge badge-info"
                            v-show="props.row.status_aux == 'WAIT_APPROVE_QUOTE'">
                        <strong>ESPERA POR APROBAR COTIZACIÓN</strong>
                    </span>
                    <span class="badge badge-info" v-show="props.row.status_aux == 'QUOTED'">
                        <strong>COTIZADO</strong>
                    </span>
                    <span class="badge badge-success" v-show="props.row.status == 'BOUGHT'">
                        <strong>COMPRADO</strong>
                    </span>
                </div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Completar presupuesto base"
                        data-toggle="tooltip"
                        v-has-tooltip
                        v-on:click="editForm(props.row.id)"
                        v-if="props.row.status == 'WAIT'"
                    >
                        <i class="icofont icofont-checked"></i>
                    </button>
                    <send-custom-messages
                        v-if="has_budget && props.row.status_aux == 'WAIT_SEND_NOTIFICATION' "
                        :employments ="employments_users"
                        :id="props.row.id"
                        :has_availability_request_permission="has_availability_request_permission"
                    />
                    <purchase-base-budget-show
                        :id="props.row.id"
                        :route_show="app_url+'/purchase/base_budget/'+props.row.id"
                    />
                    <a
                        class="btn btn-primary btn-xs btn-icon"
                        :href="app_url+'/purchase/base-budget/pdf/'+props.row.id"
                        title="Imprimir Registro"
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
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                            v-on:click="editForm(props.row.id)"
                            v-if="props.row.status != 'WAIT' && props.row.status_aux != 'QUOTED' && props.row.status != 'BOUGHT'"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            v-show="props.row.status_aux != 'QUOTED' && props.row.status != 'PARTIALLY_QUOTED' && props.row.status != 'BOUGHT'"
                            @click="deleteRecord(props.row.id,'/purchase/base_budget')"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                            type="button"
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
        props: {
        employments:{
            type: Array,
            default: function() {
                return [];
            }
        },
        employments_users:{
            type: Array,
            default: function() {
                return [];
            }
        },
        has_budget: {
            type: Boolean,
            default: function() {
                return false;
            }
        },
        has_availability_request_permission: {
            type: Boolean,
            default: function() {
                return false;
            }
        }
    },
    data() {
        return {
            records: [],
            lastYear: "",
            query: [],
            tmpRecords: [],
            columns: [
                'date',
                'purchase_requirement.code',
                'currency.name',
                'status',
                'id'
            ],
            filterBy: {
                date: '',
                code: '',
            },
        }
    },
    created() {
        this.table_options.headings = {
            'date': 'Fecha',
            'purchase_requirement.code': 'Código de requerimiento',
            'currency.name': 'Tipo de moneda',
            'status': 'Estatus',
            'id': 'ACCIÓN'
        };
        this.table_options.columnsClasses = {
            'date': 'col-xs-2',
            'purchase_requirement.code': 'col-xs-2 text-center',
            'currency.name': 'col-xs-4 text-center',
            'status': 'col-xs-2 text-center',
            'id': 'col-xs-2'
        };
        this.table_options.sortable = ['date', 'purchase_requirement.code'];
        this.table_options.filterable = ['date', 'purchase_requirement.code'];
    },
    async mounted () {
        const vm = this;
        vm.loadingState(true); // Inicio de spinner de carga.
        await vm.queryLastFiscalYear();
        vm.loadingState(); // Finaliza spinner de carga.
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
                query: vm.filterBy.date ? vm.format_date(vm.filterBy.date) : vm.filterBy.code ? vm.filterBy.code : '',
                limit: 10,
                ascending: 1,
                page: 1,
                byColumn: 0
            }

            axios.get(`${window.app_url}/purchase/base_budget/vue-list`, {params: params})
            .then(response => {
                vm.$refs.tableOptions.data = response.data.data;
                vm.$refs.tableOptions.count = response.data.count;
            });
        },

        /**
         * Reescribe el metodo para cambiar su comportamiento por defecto
         * Método para la eliminación de registros
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} id    ID del Elemento seleccionado para su eliminación
         * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
         */
        deleteRecord(id, url) {
            const vm = this;
            /** @type {string} URL que atiende la petición de eliminación del registro */
            var url = (url) ? url : vm.route_delete;
            url = vm.setUrl(url);

            bootbox.confirm({
                title: "¿Eliminar registro?",
                message: "¿Esta seguro de eliminar este registro?",
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
                        /** @type {object} Objeto con los datos del registro a eliminar */
                        let recordDelete = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                            return rec.id === id;
                        })[0]));

                        axios.delete(`${url}${url.endsWith('/')?'':'/'}${recordDelete.id}`).then(response => {
                            if (typeof(response.data.error) !== "undefined") {
                                /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                return false;
                            }
                            /** @type {array} Arreglo de registros filtrado sin el elemento eliminado */
                            vm.records = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                                return rec.id !== id;
                            })));
                            vm.showMessage('destroy');
                        }).catch(error => {
                            vm.logs('mixins.js', 498, error, 'deleteRecord');
                        });
                    }
                }
            });
        },
    }
};
</script>
