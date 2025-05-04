<template>
    <div class="card-body">
        <v-client-table class="text-center" :columns="columns" :data="records" :options="table_options">
            <div slot="code" slot-scope="props">
                <span>{{ props.row.code }}</span>
            </div>
            <div class="text-center" slot="type" slot-scope="props">
                <div v-for="item in types" :key="item.id">
                    <span v-if="props.row.type == item.id">
                        {{ item.text }}
                    </span>
                </div>
            </div>
            <div class="text-center" slot="created_at" slot-scope="props">
                <span>{{ format_date(props.row.created_at) }}</span>
            </div>
            <div class="text-center" slot="motive" slot-scope="props" v-html="props.row.motive"></div>
            <div slot="id" slot-scope="props" class="d-flex justify-content-center">
                <asset-show :route_list="app_url+'/asset/requests/vue-info/'+props.row.id"></asset-show>
                <asset-extension :requestid="props.row.id"
                                 :delivery_date="props.row.delivery_date"
                                 :state="props.row.state"
                                 :hasEquipments="hasEquipments(props.row.asset_request_assets)"
                                 :asset_request_extension="(props.row.asset_request_extension.length == 0)?true:false">
                </asset-extension>
                <asset-events :id="props.row.id"
                              :state="props.row.state"
                              :asset_request_events="(props.row.asset_request_events.length > 0)?true:false"
                              :asset_request_extension="(props.row.asset_request_extension.length == 0)?true:false">
                </asset-events>
                <button class="btn btn-primary btn-xs btn-icon btn-action"
                        type="button" data-toggle="tooltip" title="Entregar Equipos"
                        :disabled="(((props.row.state == 'Aprobado') || (props.row.state == 'Pendiente por entrega')) &&
                                     hasEquipments(props.row.asset_request_assets) && (props.row.asset_request_extension.length == 0)) ? false : true"
                        @click="deliverEquipment(props.index)">
                    <i class="icofont icofont-computer"></i>
                </button>
                <template v-if="(lastYear && format_date(props.row.created_at, 'YYYY') <= lastYear)">
                    <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button class="btn btn-warning btn-xs btn-icon btn-action"
                            type="button" data-toggle="tooltip" title="Editar Solicitud"
                            :disabled="(props.row.state == 'Pendiente')?false:true"
                            @click="(props.row.state == 'Pendiente')?editForm(props.row.id):''">
                        <i class="icofont icofont-edit"></i>
                    </button>
                    <button @click="(props.row.state != 'Pendiente') ?'':deleteRecord(props.index, '')"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            type="button" data-toggle="tooltip" title="Eliminar registro"
                            :disabled="props.row.state != 'Pendiente'">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
            </div>
        </v-client-table>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                errors: [],
                equipments: [],
                columns: ['code', 'type', 'motive', 'created_at', 'state', 'id'],
                types: [
                    {"id":"","text":"Seleccione..."},
                    {"id":1,"text":"Prestamo de Equipos (Uso Interno)"},
                    {"id":2,"text":"Prestamo de Equipos (Uso Externo)"},
                    {"id":3,"text":"Prestamo de Equipos para Agentes Externos"}
                ],
            }
        },
        created() {
            this.table_options.headings = {
                'code': 'Código',
                'type': 'Tipo de solicitud',
                'motive': 'Motivo',
                'created_at': 'Fecha de emisión',
                'state': 'Estado de la solicitud',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'type','motive','created_at','state'];
            this.table_options.filterable = ['code', 'type','motive','created_at','state'];
            this.table_options.orderBy = { 'column': 'id'};
        },
        async mounted () {
            const vm = this;
            await vm.queryLastFiscalYear();
            this.initRecords(this.route_list, '');
        },
        methods: {
            /**
             * Inicializa los datos del formulario
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve | roldandvg@gmail.com>
             */
            reset() {
            },
            deliverEquipment(index) {
                const vm = this;
                var fields = this.records[index-1];
                var id = this.records[index-1].id;

                axios.put(`${window.app_url}/asset/requests/deliver-equipment/${id}`, fields).then(response => {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                    else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.showMessage('update');
                    }
                }).catch(error => {
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }

                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
            },
            /**
             * Reescribe el método deleteRecord para cambiar su comportamiento por defecto
             * Método para la eliminación de registros
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             *
             * @param  {integer} index Elemento seleccionado para su eliminación
             * @param  {string}  url   Ruta que ejecuta la acción para eliminar un registro
             */
            deleteRecord(index, url) {
                var url = (url) ? vm.setUrl(url) : this.route_delete;
                var records = this.records;
                var confirmated = false;
                var index = index - 1;
                const vm = this;

                bootbox.confirm({
                    title: "¿Eliminar registro?",
                    message: "¿Está seguro de eliminar este registro?",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            confirmated = true;
                            axios.delete(url + '/' + records[index].id).then(response => {
                                if (typeof(response.data.error) !== "undefined") {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage('custom', 'Alerta!', 'warning', 'screen-error', response.data.message);
                                    return false;
                                }
                                if (typeof(response.data.redirect) !== "undefined") {
                                    location.href = response.data.redirect;
                                }
                            }).catch(error => {
                                vm.logs('mixins.js', 498, error, 'deleteRecord');
                            });
                        }
                    }
                });

                if (confirmated) {
                    if (typeof(response.data.redirect) !== "undefined") {
                        location.href = response.data.redirect;
                    }
                }
            },

            /**
             * Método que comprueba si una solictud posee equipos en uso
             *
             * @author  Francisco J. P. Ruiz <javierrupe19@gmail.com>
             *
             * @param  {object} equipments Equipos
             */
            hasEquipments(equipments){
                return (equipments.filter(equipments => equipments.asset.asset_status_id == 1).length > 0)? true : false;
            }
        }
    };
</script>
