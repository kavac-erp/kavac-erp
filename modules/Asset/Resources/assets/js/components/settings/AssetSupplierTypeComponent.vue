<template>
    <div class="text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="#"
            title="Registros de tipos de proveedores"
            data-toggle="tooltip"
            v-has-tooltip
            @click="addRecord('add_type', '/asset/supplier-types', $event)">
            <i class="icofont icofont-truck-loaded ico-3x"></i>
            <span>Tipos de<br>Proveedor</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_type">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont icofont-truck-loaded inline-block"></i>
                            Tipo de Proveedor
                        </h6>
                    </div>
                    <div class="modal-body">
                        <!-- Componente para mostrar errores en el formulario -->
                        <asset-show-errors ref="assetShowError" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input type="hidden" v-model="record.id">
                                    <input type="text" placeholder="Nombre del tipo de proveedor" data-toggle="tooltip" v-has-tooltip v-model="record.name" title="Indique el nombre del tipo de proveedor (requerido)" class="form-control input-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <modal-form-buttons saveRoute="asset/supplier-types"></modal-form-buttons>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)" class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar registro" data-toggle="tooltip" v-has-tooltip type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, '/asset/supplier-types')" class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar registro" data-toggle="tooltip" v-has-tooltip type="button">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            record: {
                id: '',
                name: '',
            },
            errors: [],
            records: [],
            columns: ['name', 'id'],
        }
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
                name: '',
            };
        },
    },
    created() {
        this.table_options.headings = {
            'name': 'Nombre',
            'id': 'Acción'
        };
        this.table_options.sortable = ['name'];
        this.table_options.filterable = ['name'];
        this.table_options.columnsClasses = {
            'name': 'col-md-10',
            'id': 'col-md-2'
        };
    },
};
</script>
