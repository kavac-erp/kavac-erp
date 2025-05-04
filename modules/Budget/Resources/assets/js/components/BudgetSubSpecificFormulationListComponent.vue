<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label for="" class="form-label">Fecha</label>
                <input id="prefix" class="form-control" type="date" placeholder="Fecha" tabindex="1"
                    v-model="filterBy.date" />
            </div>
            <div class="col-md-2">
                <label for="" class="form-label">Código</label>
                <input id="prefix" class="form-control" type="text" placeholder="Código" tabindex="2"
                    v-model="filterBy.code" />
            </div>
            <div class="col-md-2">
                <label class="form-label">Asignado</label>
                <select2 tabindex="2" :options="assignedOptions" v-model="filterBy.assigned">
                </select2>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button type="reset" class="btn btn-default btn-icon btn-xs-responsive px-3"
                        aria-label="Limpiar filtro" @click="resetFilters()" title="Limpiar filtro">
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button type="button" class="btn btn-info btn-icon btn-xs-responsive px-3" aria-label="Buscar"
                        @click="filterTable()" title="Buscar">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <hr>
        <!-- Final de filtros de la tabla -->
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="date" slot-scope="props" class="text-center">
                {{ props.row.date ? format_date(props.row.date, 'DD/MM/YYYY') : "Sin fecha asignada" }}
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button @click="showRecord(props.row.id)" v-if="route_show"
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip" title="Ver registro"
                    aria-label="Ver registro" data-toggle="tooltip" data-placement="bottom" type="button">
                    <i class="fa fa-eye"></i>
                </button>
                <template v-if="(lastYear && format_date(props.row.date, 'YYYY') <= lastYear)">
                    <button class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip" type="button" disabled
                        aria-label="Editar registro">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip" type="button" disabled
                        aria-label="Eliminar registro">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <button class="btn btn-success btn-xs btn-icon btn-action btn-tooltip" type="button" disabled
                        aria-label="Asignar Presupuesto">
                        <i class="fa fa-check"></i>
                    </button>
                </template>
                <template v-else>
                    <button v-if="!props.row.assigned" class="btn btn-warning btn-xs btn-icon btn-action btn-tooltip"
                        type="button" title="Modificar registro" aria-label="Editar registro" data-placement="bottom"
                        data-toggle="tooltip" @click="editForm(props.row.id)">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button v-if="!props.row.assigned" class="btn btn-danger btn-xs btn-icon btn-action btn-tooltip"
                        title="Eliminar registro" aria-label="Eliminar registro" data-placement="bottom"
                        data-toggle="tooltip" type="button" @click="deleteRecord(props.row.id, '')">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <button v-if="!props.row.assigned" class="btn btn-success btn-xs btn-icon btn-action btn-tooltip"
                        type="button" data-placement="bottom" data-toggle="tooltip" title="Asignar Presupuesto"
                        aria-label="Asignar Presupuesto" @click="asignR(props.row.id)">
                        <i class="fa fa-check"></i>
                    </button>
                </template>
            </div>
            <div slot="code" slot-scope="props" class="text-center">
                {{ props.row.code }}
            </div>
            <div slot="year" slot-scope="props" class="text-center">
                {{ props.row.year }}
            </div>
            <div slot="specific_action" slot-scope="props">
                {{ props.row.specific_action ? props.row.specific_action.code + ' - ' + props.row.specific_action.name :
                    'No definido' }}
            </div>
            <div slot="total_formulated" slot-scope="props" class="text-right">
                {{
                    formatToCurrency(props.row.total_formulated, props.row.currency.symbol)
                }}
            </div>
            <div slot="assigned" slot-scope="props">
                <span class="text-danger text-bold" v-if="!props.row.assigned">NO</span>
                <span class="text-success text-bold" v-else>SI</span>
            </div>
        </v-client-table>
    </section>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            tmpRecords: [],
            assigned: {
                _method: "PUT",
                assigned: "1",
            },
            columns: [
                "date",
                "code",
                "year",
                "specific_action",
                "total_formulated",
                "assigned",
                "id",
            ],
            filterBy: {
                date: '',
                code: '',
                assigned: '',
            },
            assignedOptions: [
                { id: '', text: "-- Seleccione --" },
                { id: true, text: "Si" },
                { id: false, text: "No" }
            ],
            lastYear: '',
        };
    },
    created() {
        this.table_options.headings = {
            date: "Fecha de generación",
            code: "Código",
            year: "Año",
            specific_action: "Acción Específica",
            total_formulated: "Total Formulado",
            assigned: "Asignado",
            id: "Acción",
        };
        this.table_options.sortable = [
            "date",
            "code",
            "year",
            "specific_action"
        ];
        this.table_options.filterable = [
            "date",
            "code",
            "year",
            "specific_action"
        ];
        this.table_options.columnsClasses = {
            date: "col-md-1",
            code: "col-md-1",
            year: "col-md-1",
            name: "col-md-3",
            specific_action: "col-md-4",
            total_formulated: "col-md-2",
            assigned: "col-md-1 text-center",
            id: "col-md-1",
        };
        this.table_options.orderBy = {
            column: "date"
        };
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
                assigned: '',
            };
            vm.records = vm.tmpRecords;
        },

        /**
         * Método que permite filtrar los datos de la tabla.
         *
         * @method filterTable
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@gmail.com>
         */
        filterTable() {
            const vm = this;
            var varAssigned;
            if (vm.filterBy.assigned == "true") {
                varAssigned = true;
            }
            else if (vm.filterBy.assigned == "false") {
                varAssigned = false;
            }
            vm.records = vm.tmpRecords.filter((rec) => {
                return (vm.filterBy.date) ? (rec.date === vm.filterBy.date) : true;
            }).filter((rec) => {
                return (vm.filterBy.code) ? (rec.code === vm.filterBy.code) : true;
            }).filter((rec) => {
                return (vm.filterBy.assigned) ? (rec.assigned === varAssigned) : true;
            })
        },

        /**
         * Inicializa los datos del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() { },
        asignR(id) {
            const vm = this;
            var dialog = bootbox.confirm({
                title: "Esta seguro de asignar esta formulación?",
                message:
                    "Una vez asignado no puede ser modificado",
                size: "medium",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    },
                },
                callback: function (result) {
                    if (result) {
                        axios({
                            method: "post",
                            url: `${window.app_url}/budget/subspecific-formulations/${id}`,
                            data: vm.assigned,
                        })
                            .then((response) => {
                                vm.errors = [];
                                vm.showMessage("store");
                            })
                            .catch(error => {
                                if (typeof (error.response) !== "undefined") {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                            });
                    }
                },
            });
            setTimeout(function () {
                vm.initRecords(vm.route_list, "");
            }, 2000);
        },
    },
    mounted() {
        // this.initRecords(this.route_list, "");
        axios.get('budget/subspecific-formulations/vue-list').then(response => {
            this.records = response.data.records;
            // Variable usada para el reseteo de los filtros de la tabla.
            this.tmpRecords = response.data.records;
        });
    },
};
</script>
