<template>
    <section id="assetCategoriesComponent">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="#" data-toggle="tooltip"
            title="Registros de Categorias Generales de Bienes"
            @click="addRecord('add_category', 'asset/categories', $event)">
            <i class="icofont icofont-listing-number ico-3x"></i>
            <span>Categorias<br>Generales</span>
        </a>
        <div id="add_category" class="modal fade text-left" tabindex="-1" role="dialog">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" type="button" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6 class="d-flex align-items-center">
                            <i class="icofont icofont-listing-number ico-2x mr-2"></i>
                            Nueva Categoria General de Bienes
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
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de bien:</label>
                                    <select2 :options="asset_types" v-model="record.asset_type_id"></select2>
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Código de la categoría general:</label>
                                    <input type="text" placeholder="Código de Categoría General" data-toggle="tooltip"
                                        title="Indique el código de la nueva Categoría General (requerido)"
                                        class="form-control input-sm" v-model="record.code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Categoría general:</label>
                                    <input type="text" placeholder="Nueva Categoría General" data-toggle="tooltip"
                                        v-input-mask data-inputmask-regex="[a-zA-ZÁ-ÿ\s]*$"
                                        title="Indique la nueva Categoría General (requerido)" class="form-control input-sm"
                                        v-model="record.name">
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
                            <button type="button" @click="createRecord('asset/categories')"
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
                                <button @click="deleteRecord(props.row.id, 'asset/categories')"
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
                asset_type_id: '',
                name: '',
                code: ''
            },
            errors: [],
            records: [],
            asset_types: [],
            columns: ['asset_type.name', 'name', 'code', 'id'],
        }
    },
    created() {
        this.table_options.headings = {
            'asset_type.name': 'Tipo de Bien',
            'name': 'Categoria General',
            'code': 'Código',
            'id': 'Acción'
        };
        this.table_options.sortable = ['asset_type.name', 'name', 'code'];
        this.table_options.filterable = ['asset_type.name', 'name', 'code'];
        this.table_options.columnsClasses = {
            'asset_type.name': 'col-md-2',
            'name': 'col-md-6',
            'code': 'col-md-2 text-center',
            'id': 'col-md-2 text-center'
        };
    },
    mounted() {
        this.getAssetTypes();
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset() {
            this.record = {
                id: '',
                asset_type_id: '',
                name: '',
                code: ''
            };
        },
    }
};
</script>
