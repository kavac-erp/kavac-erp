<template>
    <section id="assetFloorsComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="#" title="Registros de niveles de edificaciones"
            data-toggle="tooltip" v-has-tooltip @click="addRecord('add_floor', 'asset/floors', $event)">
            <i class="icofont icofont-shopping-cart ico-3x"></i>
            <span>Niveles</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_floor">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-shopping-cart ico-2x"></i>
                            Nuevo nivel de edificacion
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                    @click.prevent="errors = []">
                                    <span aria-hidden="true">
                                        <i class="now-ui-icons ui-1_simple-remove"></i>
                                    </span>
                                </button>
                                <ul>
                                    <li v-for="error in errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label for="input_buildings">Edificación:</label>
                                    <select2 :options="buildings" v-model="record.building_id">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input id="floor-name" type="text" placeholder="Nombre del nivel de la edificación"
                                        data-toggle="tooltip"
                                        title="Indique el nombre del nivel de la edificacion(requerido)"
                                        class="form-control input-sm" v-model="record.name">
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="floor-description">Descripción:</label>
                                    <input id="floor-description" type="text"
                                        placeholder="Descripción del nivel de  la edificación" data-toggle="tooltip"
                                        title="Indique la descripción del nivel de la edificacion(opcional)"
                                        class="form-control input-sm" v-model="record.description">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('asset/floors')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">

                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action" v-has-tooltip
                                    title="Modificar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'asset/floors')"
                                    class="btn btn-danger btn-xs btn-icon btn-action" v-has-tooltip
                                    title="Eliminar registro" data-toggle="tooltip" type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            record: {
                id: '',
                name: '',
                description: '',
                building_id: '',
            },
            buildings: [],
            errors: [],
            records: [],
            columns: ['name', 'description', 'building.name', 'id'],
        }
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            const vm = this;
            vm.record = {
                id: '',
                name: '',
                description: '',
                building_id: '',
            };
            vm.errors = [];
        },
    },
    created() {
        this.table_options.headings = {
            'name': 'Nombre',
            'description': 'Descripción',
            'building.name': 'Edificación',
            'id': 'Acción',
        };
        this.table_options.sortable = ['name'];
        this.table_options.filterable = ['name'];
        this.table_options.columnsClasses = {
            'name': 'col-md-4',
            'description': 'col-md-3',
            'building.name': 'col-md-3',
            'id': 'col-md-2 text-center'
        };
    },
    mounted() {
        const vm = this;
        $("#add_floor").on('show.bs.modal', function () {
            vm.getBuildings();
        });
    },
};
</script>
