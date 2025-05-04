<template>
    <div
        id="PayrollTimeSheetPendingConcepts"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollTimeSheetPendingConceptsModalLabel"
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
                        Agregar conceptos para hoja de pendientes
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div v-for="(payment_type, index) in record.payroll_payment_types"
                                :key="index" class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Tipo de pago:</label>
                                        <select2 :options="payroll_payment_types"
                                                 v-model="payment_type.payroll_payment_type_id">
                                        </select2>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Conceptos:</label>
                                        <multiselectComponent :id="'multiselect'+index"
                                                       :options="payroll_concepts"
                                                       track-by="text"
                                                       label="text"
                                                       :multiple="true"
                                                       deselect-label="Eliminar"
                                                       :limit="3"
                                                       placeholder="Seleccione..."
                                                       select-label="Seleccionar"
                                                       selected-label="Seleccionado"
                                                       :limitText="count => count > 1 ? `+ ${count} seleccionados` : `+ ${count} seleccionado`"
                                                       v-model="payment_type.payroll_concepts">
                                        </multiselectComponent>
                                    </div>
                                </div>
                                <div class="col-md-2" v-if="index > 0">
                                    <button
                                        class="mt-4 btn btn-sm btn-danger btn-action"
                                        type="button"
                                        @click="removeRow(index, record.payroll_payment_types)"
                                    >
                                        <i class="fa fa-minus-circle"></i>
                                    </button>
                                </div>
                                <div class="col-md-2" v-else>
                                    <button type="button" @click="addPaymentType()"
                                        class="mt-4 btn btn-sm btn-primary btn-action" 
                                        title="Agregar" data-toggle="tooltip">
                                        <i class="fa fa-plus-circle"></i>
                                    </button>
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
                    payroll_payment_types: [],
                },
                errors: [],
                payroll_payment_types: [],
                payroll_concepts: [],
            }
        },
        methods: {
            addPaymentType() {
                const vm = this;
                vm.record.payroll_payment_types.push({
                    payroll_payment_type_id: '',
                    payroll_concepts: [],
                });
            },

            removeRow(index, el) {
                const vm = this;
                el.splice(index, 1);
            },

            reset() {
                const vm = this;
                vm.record.id = '';

                vm.record.payroll_payment_types = [
                    {
                        payroll_payment_type_id: '',
                        payroll_concepts: [],
                    }
                ];
            },

            setData() {
                const vm = this;

                Vue.set(vm.$parent.$refs.draggableTable.inputValues, 'Conceptos-' + vm.record.id, vm.record.payroll_payment_types);
                $('#PayrollTimeSheetPendingConcepts').modal('hide');
                vm.reset();
            }
        },
        mounted() {
            const vm = this;

            $("#PayrollTimeSheetPendingConcepts").on('show.bs.modal', function() {
                if (vm.record.payroll_payment_types == []) {
                    vm.record.payroll_payment_types = [{
                        payroll_payment_type_id: '',
                        payroll_concepts: [],
                    }];
                }
            });

            vm.getPayrollConcepts();
            vm.getPayrollPaymentTypes(true);
        }
    }
</script>
