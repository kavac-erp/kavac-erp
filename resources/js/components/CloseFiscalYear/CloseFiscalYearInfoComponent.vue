<template>
    <div id="CloseFiscalYearInfo" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="CloseFiscalYearInfoModal" aria-hidden="true">
        <div class="modal-dialog vue-crud" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                         Información detallada del cierre de ejercicio
                    </h6>
                </div>
                <div class="modal-body">
                    <h6>INFORMACIÓN DEL ASIENTO CONTABLE DE INGRESOS</h6>
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr class="row">
                                        <th tabindex="0" class="col-8" style="border: 1px solid #dee2e6; position: relative;">
                                            Denominación
                                        </th>
                                        <th tabindex="0" class="col-2" style="border: 1px solid #dee2e6; position: relative;">
                                            Debe
                                        </th>
                                        <th tabindex="0" class="col-2" style="border: 1px solid #dee2e6; position: relative;">
                                            Haber
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <div v-if="Object.values(record.resource_entries).length > 0">
                                        <tr v-for="(row, index) in record.resource_entries" :key="index" class="row">
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                                                {{ row.account.code }} - {{ row.account.denomination }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ addDecimals(row.debit) }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ addDecimals(row.assets) }}
                                            </td>
                                        </tr>
                                        <tr class="row">
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                                                Totales Debe / Haber
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ getCurrencySymbol() }} {{ addDecimals(getResourceTotalDebit()) }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ getCurrencySymbol() }} {{ addDecimals(getResourceTotalAssets()) }}
                                            </td>
                                        </tr>
                                    </div>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <h6>INFORMACIÓN DEL ASIENTO CONTABLE DE EGRESOS</h6>
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr class="row">
                                        <th tabindex="0" class="col-8" style="border: 1px solid #dee2e6; position: relative;">
                                            Denominación
                                        </th>
                                        <th tabindex="0" class="col-2" style="border: 1px solid #dee2e6; position: relative;">
                                            Debe
                                        </th>
                                        <th tabindex="0" class="col-2" style="border: 1px solid #dee2e6; position: relative;">
                                            Haber
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <div v-if="Object.values(record.egress_entries).length > 0">
                                        <tr v-for="(row, index) in record.egress_entries" :key="index" class="row">
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                                                {{ row.account.code }} - {{ row.account.denomination }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ addDecimals(row.debit) }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ addDecimals(row.assets) }}
                                            </td>
                                        </tr>
                                        <tr class="row">
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                                                Totales Debe / Haber
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ getCurrencySymbol() }} {{ addDecimals(getEgressTotalDebit()) }}
                                            </td>
                                            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                                                {{ getCurrencySymbol() }} {{ addDecimals(getEgressTotalAssets()) }}
                                            </td>
                                        </tr>
                                    </div>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                data-dismiss="modal">
                            Cerrar
                        </button>
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
                    year: '',
                    entries: [],
                    resource_entries: [],
                    egress_entries: [],
                },
                errors: [],
            }
        },
        created() {
            const vm = this;
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            reset() {
            },
            addDecimals(value) {
                let decimal_places = 0;
                let resource_entries = Object.values(this.record.resource_entries);

                if (resource_entries.length > 0) {
                    decimal_places = resource_entries[resource_entries.length - 1].entries.currency.decimal_places;
                } else {
                    decimal_places = 0;
                }

                if (parseFloat(value).toFixed(decimal_places) > 0) {
                    return new Intl.NumberFormat('es-VE').format(
                        parseFloat(value).toFixed(decimal_places),
                    );
                }

                return parseFloat(value).toFixed(decimal_places);
            },
            getCurrencySymbol() {
                let currency_symbol = '';
                let resource_entries = Object.values(this.record.resource_entries);

                if (resource_entries.length > 0) {
                    currency_symbol = resource_entries[resource_entries.length - 1].entries.currency.symbol;
                } else {
                    currency_symbol = '';
                }
                return currency_symbol;
            },
            getResourceTotalDebit() {
                const vm = this;
                let tot_debit = 0;
                let resource_entries = Object.values(vm.record.resource_entries);

                for (let entry of resource_entries) {
                    tot_debit += parseFloat(entry.debit);
                }

                return tot_debit;
            },
            getResourceTotalAssets() {
                const vm = this;
                let tot_assets = 0;
                let resource_entries = Object.values(vm.record.resource_entries);

                for (let entry of resource_entries) {
                    tot_assets += parseFloat(entry.assets);
                }

                return tot_assets;
            },
            getEgressTotalDebit() {
                const vm = this;
                let tot_debit = 0;
                let egress_entries = Object.values(vm.record.egress_entries);

                for (let entry of egress_entries) {
                    tot_debit += parseFloat(entry.debit);
                }

                return tot_debit;
            },
            getEgressTotalAssets() {
                const vm = this;
                let tot_assets = 0;
                let egress_entries = Object.values(vm.record.egress_entries);

                for (let entry of egress_entries) {
                    tot_assets += parseFloat(entry.assets);
                }

                return tot_assets;
            }
        },
    }
</script>