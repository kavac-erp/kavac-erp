<template>
    <div>
        <a v-if="exist_accounting" href="#" class="btn btn-sm btn-primary btn-custom"
           @click="addRecord('asset-depreciations', 'asset/depreciations', $event)"
           title="Crear nuevo registro" data-toggle="tooltip">
           <i class="fa fa-plus-circle"></i>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="asset-depreciations">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            {{ 'Depreciación del año: ' + execution_year }}
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <h6 class="text-center">{{ 'Desea ejecutar la depreciación del año: ' + execution_year }}</h6>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                    data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" @click="storeRecord('asset/depreciations')"
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
                    //
                },
                execution_year: '',
                exist_accounting: false,
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
                //
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
             * Método que registra la depreciación del año fiscal actual
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
             */
            async storeRecord(url) {
                const vm = this;
                await vm.createRecord(url);
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
        },
        mounted() {
            const vm = this;
            vm.getExecutionYear();
            vm.moduleExistAccounting();
        },
    };
</script>
