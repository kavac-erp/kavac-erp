<template>
    <div>
        <a v-if="exist_accounting || exist_asset || exist_warehouse" href="#" class="btn btn-sm btn-primary btn-custom"
           @click="addRecord('close-fiscal-year', 'close-fiscal-year/registers', $event)"
           title="Crear nuevo registro" data-toggle="tooltip">
           <i class="fa fa-plus-circle"></i>
        </a>
        <a v-else href="#" class="btn btn-sm btn-primary btn-custom"
           @click="storeRecord('close-fiscal-year/registers')"
           title="Crear nuevo registro" data-toggle="tooltip">
           <i class="fa fa-plus-circle"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="close-fiscal-year">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="icofont ion-lock-combination inline-block"></i>
                            {{ 'Cierre de ejercicio año fiscal: ' + execution_year }}
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <h6 class="text-center">Verificación de cierre</h6>
                        <div class="row">
                            <div v-if="exist_accounting" class="form-group col-3">
                                <label for="" class="control-label">Análisis de cuentas</label>
                                <div class="col-12">
                                    <div class="custom-control custom-switch" data-toggle="tolltip" 
                                         title="Indica si es una cuenta de ingresos (Recursos)" >
                                        <input type="checkbox" class="custom-control-input" id="accountAnalysis" v-model="record.account_analysis" 
                                                 value="resource">
                                        <label class="custom-control-label" for="accountAnalysis"></label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="exist_accounting" class="form-group col-3">
                                <label for="" class="control-label">Asientos de ajustes</label>
                                <div class="col-12">
                                    <div class="custom-control custom-switch" data-toggle="tolltip" 
                                         title="Indica si es una cuenta de ingresos (Recursos)" >
                                        <input type="checkbox" class="custom-control-input" id="AdjustmentEntries" v-model="record.adjustment_entries" 
                                                 value="resource">
                                        <label class="custom-control-label" for="AdjustmentEntries"></label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="exist_asset" class="form-group col-3">
                                <label for="" class="control-label">Depreciación</label>
                                <div class="col-12">
                                    <div class="custom-control custom-switch" data-toggle="tolltip" 
                                         title="Indica si es una cuenta de ingresos (Recursos)" >
                                        <input type="checkbox" class="custom-control-input" id="depreciation" v-model="record.depreciation" 
                                                 value="resource">
                                        <label class="custom-control-label" for="depreciation"></label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="exist_warehouse" class="form-group col-3">
                                <label for="" class="control-label">Cierre de inventario</label>
                                <div class="col-12">
                                    <div class="custom-control custom-switch" data-toggle="tolltip" 
                                         title="Indica si es una cuenta de ingresos (Recursos)" >
                                        <input type="checkbox" class="custom-control-input" id="inventoryClosing" v-model="record.inventory_closing" 
                                                 value="resource">
                                        <label class="custom-control-label" for="inventoryClosing"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
                                    @click="clearFilters()" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" 
                                    @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="storeRecord('close-fiscal-year/registers')" 
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
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
                    account_analysis: '',
                    adjustment_entries: '',
                    depreciation: '',
                    inventory_closing: ''
                },
                execution_year: '',
                exist_accounting: false,
                exist_asset: false,
                exist_warehouse: false,
                errors: [],
                records: [],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    account_analysis: '',
                    adjustment_entries: '',
                    depreciation: '',
                    inventory_closing: ''
                };
            },

            /**
             * Método que consulta el año fiscal actual
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            getExecutionYear() {
                const vm = this;
                axios.get(`${window.app_url}/get-execution-year`, {}).then(response => {
                    if (response.data.result) {
                        vm.execution_year = response.data.year;
                    }
                })
            },

            /**
             * Método que registra el cierre del año fiscal actual
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async storeRecord(url) {
                const vm = this;
                await vm.createRecord(url);

                if (vm.errors.length == 0) {
                    $('#close-fiscal-year').modal('hide');

                    const timeOpen = setTimeout(reload, 1000);
                    function reload () {
                        window.location.reload();
                    }
                }
            },

            /**
             * Método que evalúa si el módulo de contabilidad está instalado en el sistema
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async moduleExistAccounting() {
                const vm = this;

                await axios.get(`${window.app_url}/modules/details/accounting`, {}).then(response => {
                    if (response.data.result) {
                        vm.exist_accounting = response.data.result;
                    }
                })
            },

            /**
             * Método que evalúa si el módulo de bienes está instalado en el sistema
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async moduleExistAsset() {
                const vm = this;

                await axios.get(`${window.app_url}/modules/details/asset`, {}).then(response => {
                    if (response.data.result) {
                        vm.exist_asset = response.data.result;
                    }
                })
            },

            /**
             * Método que evalúa si el módulo de almacén está instalado en el sistema
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async moduleExistWarehouse() {
                const vm = this;

                await axios.get(`${window.app_url}/modules/details/warehouse`, {}).then(response => {
                    if (response.data.result) {
                        vm.exist_warehouse = response.data.result;
                    }
                })
            }
        },
        mounted() {
            const vm = this;
            vm.getExecutionYear();
            vm.moduleExistAccounting();
            vm.moduleExistAsset();
            vm.moduleExistWarehouse();
        },
    };
</script>
