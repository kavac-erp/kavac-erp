<template>
    <div
        id="PayrollGuardSchemePeriodConfirm"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="PayrollGuardSchemePeriodConfirmModalLabel"
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
                        aria-label="Close"
                        @click="reset(); closeModal();"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        ¿Confirmar período de esquema de guardias?
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <strong>Desde:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12">
                                        {{ format_date(record.from_date, "DD/MM/YYYY") }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <strong>Hasta:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <span class="col-md-12">
                                        {{ format_date(record.to_date, "DD/MM/YYYY") }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-xs-3 col-md-3">
                            <div class="form-group">
                                <label for="total_confirm">Confirmación total</label>
                                <div class="col-12">
                                    <p-radio class="pretty p-switch p-fill p-bigger"
                                            color="success" off-color="text-gray" toggle
                                            data-toggle="tooltip"
                                            title="Indique si desea utilizar una confirmación total, bloquea los registros del periodo de la hoja de tiempo correspondiente"
                                            v-model="confirm_value" value="total">
                                        <label slot="off-label"></label>
                                    </p-radio>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3 col-md-3">
                            <div class="form-group">
                                <label for="partial_confirm">Confirmación parcial</label>
                                <div class="col-12">
                                    <p-radio class="pretty p-switch p-fill p-bigger"
                                            color="success" off-color="text-gray" toggle
                                            data-toggle="tooltip"
                                            title="Indique si desea utilizar una confirmación parcial, mantiene activos los registros del período de la hoja de tiempo correspondiente para su edición"
                                            v-model="confirm_value" value="partial">
                                        <label slot="off-label"></label>
                                    </p-radio>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button data-bb-handler="cancel" type="button" class="btn btn-default btn-modal-close"
                        @click="reset(); closeModal();">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button data-bb-handler="confirm" type="button" class="btn btn-primary btn-modal-save"
                        @click="createRecord()">
                        <i class="fa fa-check"></i> Confirmar
                    </button>
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
                    observation: '',
                },
                confirm_value: 'total',
                errors: [],
            }
        },
        methods: {
            reset() {
                const vm = this;
                vm.record = {
                    id: '',
                    observation: '',
                };
                vm.confirm_value = 'total';
            },
            async createRecord() {
                const vm = this;
                const url = vm.setUrl(`payroll/guard-schemes/periods/confirm/${vm.record.id}`);
                vm.loading = true;
                await axios.put(url, {
                    observation: ('total' == vm.confirm_value)
                        ? 'Confirmación total'
                        : (('partial' == vm.confirm_value)
                            ? 'Confirmación parcial'
                            : '')
                }).then(response => {
                    if ('undefined' !== typeof(response.data.record)) {
                        vm.$nextTick(() => {
                            let index = vm.$parent.record.payroll_guard_scheme_periods.findIndex((element) => element.id == response.data.record.id);
                            vm.removeRow(index, vm.$parent.record.payroll_guard_scheme_periods);
                            vm.$parent.record.payroll_guard_scheme_periods.push(response.data.record);
                            vm.$parent.record.payroll_guard_scheme_periods.sort((a, b) => new Date(a.to_date) - new Date(b.to_date));
                        });
                    }
                    vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Periodo de esquema de guardias confirmado correctamente');
                    vm.$parent.$parent.readRecords(vm.$parent.$parent.route_list);
                    vm.closeModal();
                }).catch(error => {
                    if (typeof(error.response) !="undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                            );
                        }
                    }
                });
                vm.loading = false;
            },
            closeModal() {
                $("#PayrollGuardSchemePeriodConfirm").modal('hide');
            }
        },
        mounted() {
            const vm = this;

            //
        }
    }
</script>
