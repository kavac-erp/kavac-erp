<template>
    <div class="text-center">
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)" title="Condiciones generales de compras / servicios"
            data-toggle="tooltip" v-has-tooltip
            @click="addRecord('add_general_conditions', '/purchase/general-conditions', $event)"
        >
            <div>
                <i class="icofont icofont-law-document ico-3x"></i>
            </div>
            <span>Condiciones Generales</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_general_conditions">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="ion icofont icofont-pay inline-block"></i>
                            Condiciones generales de compra / servicio
                        </h6>
                    </div>
                    <div class="modal-body" style="max-height: 450px; overflow-y: auto">
                        <form-errors :listErrors="errors"/>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">Condiciones generales de compra:</label>
                                    <ckeditor
                                        :editor="ckeditor.editor" id="purchase_general_conditions"
                                        :config="ckeditor.editorConfig" data-toggle="tooltip"
                                        title="Indique las condiciones generales del pliego de condiciones para la compra de bienes o productos"
                                        class="form-control input-sm" name="purchase_general_conditions"
                                        tag-name="textarea" rows="5"
                                        v-model="record.purchase_general_conditions"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <hr>
                                <div class="form-group">
                                    <label class="font-weight-bold">Condiciones generales de servicios:</label>
                                    <ckeditor
                                        :editor="ckeditor.editor" id="service_general_conditions"
                                        :config="ckeditor.editorConfig" data-toggle="tooltip"
                                        title="Indique las condiciones generales del pliego de condiciones para los servicios"
                                        class="form-control input-sm" name="service_general_conditions"
                                        tag-name="textarea" rows="5"
                                        v-model="record.service_general_conditions"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters" data-dismiss="modal"
                            >
                                    Cerrar
                            </button>
                            <button
                                type="button"
                                class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()" :disabled="cancelDisabled"
                            >
                                    Cancelar
                            </button>
                            <button
                                type="button"
                                @click="createRecord('purchase/general-conditions')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                                :disabled="saveDisabled"
                            >
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
                    purchase_general_conditions: '',
                    service_general_conditions: ''
                },
                records: [],
                saveDisabled: true,
                cancelDisabled: false,
                errors: []
            }
        },
        watch: {
            record: {
				deep: true,
				handler: function() {
					this.saveDisabled = (this.record.purchase_general_conditions === '' || this.record.service_general_conditions === '');
                    this.cancelDisabled = (this.record.purchase_general_conditions !== '' && this.record.service_general_conditions !== '');
				}
			},
            records: {
                deep: true,
                handler: function() {
                    if (this.records?.purchase?.p_value) {
                        this.record.purchase_general_conditions = this.records.purchase.p_value;
                    }
                    if (this.records?.service?.p_value) {
                        this.record.service_general_conditions = this.records.service.p_value;
                    }
                }
            }
        },
        methods: {
            reset() {
                this.record.purchase_general_conditions = '';
                this.record.service_general_conditions = '';
                this.saveDisabled = true;
            }
        },
        mounted() {
            const vm = this;
            vm.reset();
            vm.saveDisabled = (this.record.purchase_general_conditions === '' || this.record.service_general_conditions === '');
        },
        updated() {
            $('.ck-editor__editable').css('max-height', '300px');
        }
    }
</script>