<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">RIF</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="RIF"
                    tabindex="1"
                    v-model="filterBy.rif"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Nombre</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Nombre"
                    tabindex="2"
                    v-model="filterBy.name"
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
        <v-client-table :columns="columns" :data="records" :options="table_options">
            <div slot="city_id" slot-scope="props">
                {{ props.row.city.name }}
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <asset-suppliers-show
                        :id="props.row.id"
                        :route_show="'/asset/suppliers/'+props.row.id"
                    />
                    <button
                        @click="editForm(props.row.id)"
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        data-placement="bottom"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        type="button"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        @click="deleteRecord(props.row.id, '')"
                        class="btn btn-danger btn-xs btn-icon btn-action"
                        title="Eliminar registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        data-placement="bottom"
                        type="button"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
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
            columns: [
                'rif',
                'name',
                'city_id',
                'id',
            ],
            filterBy: {
                rif: '',
                name: '',
            },
        }
    },
    created() {
        this.table_options.headings = {
            'rif': 'R.I.F.',
            'name': 'Nombre',
            'city_id': 'Ciudad',
            'id': 'Acción'
        };
        this.table_options.sortable = ['rif', 'name', 'city_id'];
        this.table_options.filterable = ['rif', 'name', 'city_id'];
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
                rif: '',
                name: '',
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
                return (vm.filterBy.rif) ? (rec.rif === vm.filterBy.rif) : true;
            }).filter((rec) => {
                return (vm.filterBy.name) ? (rec.name === vm.filterBy.name) : true;
            })
        },

        /**
         * Redirecciona al formulario de actualización de datos
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param  {integer} id Identificador del registro a actualizar
         */
        editForm(id) {
            const vm = this;
            let route = vm.route_edit.indexOf("{id}") >= 0 ?
                vm.route_edit.replace("{id}", id) :
                vm.route_edit + '/' + id;

            location.href = `${window.app_url}${route}`;
        },

        /**
         * Migración de los datos Rama y Especialización a las tablas pivotes
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
         *
         * @param  
         */
        DataMigratePivote() {
            console.log("DataMigratePivote");
            const vm = this;
            axios.get('/asset/supplier-data-migrate-pivote').then(response => {});
        },
    },
    mounted() {
        const vm = this;
        axios.get(`${window.app_url}/asset/suppliers/vue-list`)
            .then(response => {
            vm.records = response.data.records;
            // Variable usada para el reseteo de los filtros de la tabla.
            vm.tmpRecords = vm.records;
        });
    },
};
</script>
