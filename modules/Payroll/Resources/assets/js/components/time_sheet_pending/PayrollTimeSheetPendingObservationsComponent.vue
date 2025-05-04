<template>
    <div
        id="PayrollTimeSheetPendingObservations"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollTimeSheetPendingObservationsModalLabel"
        aria-hidden="true"
    >
        <div
            class="modal-dialog modal-lg"
            role="document"
            style="max-width:60%"
        >
            <div class="modal-content">
                <div class="modal-header">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                        @click="reset()"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Agregar observación para hoja de pendientes
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Observaciones:</label>
                                        <ckeditor
                                            id="observation"
                                            class="form-control"
                                            data-toggle="tooltip"
                                            name="observation"
                                            tag-name="textarea"
                                            title="Indique las observaciones del parámetro"
                                            :config="ckeditor.editorConfig"
                                            :editor="ckeditor.editor"
                                            v-model="record.observation"
                                        ></ckeditor>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" 
                                @click="reset()" data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" 
                                @click="reset()">
                            Limpiar
                        </button>
                        <button type="button" 
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                                @click="setData()">
                            Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect';

    /** Elimina el prop loading por conflicto con el mixin de la aplicación */
    delete Multiselect.props.loading;

    Vue.component('multiselectComponent', Multiselect);

    export default {
        data() {
            return {
                record: {
                    id: '',
                    observation: '',
                },
                errors: [],
            }
        },
        methods: {
            reset() {
                const vm = this;
                vm.record.observation = '';
            },

            setData() {
                const vm = this;

                Vue.set(vm.$parent.$refs.draggableTable.inputValues, 'Observación-' + vm.record.id, vm.record.observation);
                $('#PayrollTimeSheetPendingObservations').modal('hide');
                vm.reset();
            }
        },
        mounted() {
            const vm = this;

            //
        }
    }
</script>
