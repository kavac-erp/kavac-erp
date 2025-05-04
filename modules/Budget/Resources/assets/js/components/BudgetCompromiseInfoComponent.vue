<template>
    <div
        id="BudgetCompromiseInfo"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="BudgetCompromiseInfoModal"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg" role="document" style="max-width:60rem">
            <div class="modal-content">
                <div class="modal-header">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                    <h6>
                        <i class="icofont icofont-read-book ico-2x"></i>
                        Información detallada del compromiso
                    </h6>
                </div>
                <div class="modal-body">
                    <h6 v-if="record.document_status_id == '5'" class="text-center text-danger">
                        {{ record.document_status.name }}
                    </h6>
                    <div class="tab-content">   
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Institución:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.institution.name}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ format_date(record.compromised_at) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Documento origen:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.document_number }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Descripción:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span
                                                class="col-md-12"
                                                v-html="
                                                    record.description.
                                                        replace(/(<([^>]+)>)/gi, '')
                                                "
                                            >
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr v-if="record.receiver && record.receiver.associateable">
                            <h6 class="text-left" v-if="record.receiver && record.receiver.associateable">
                                Beneficiario
                            </h6>
                            <div class="row" v-if="record.receiver && record.receiver.associateable">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Beneficiario:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.receiver.description }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong>Cuenta contable:</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{
                                                    `${record.receiver.associateable.group}.${record.receiver.associateable.subgroup}.
                                                     ${record.receiver.associateable.item}.${record.receiver.associateable.generic}.
                                                     ${record.receiver.associateable.specific}.${record.receiver.associateable.subspecific}-
                                                     ${record.receiver.associateable.institutional} 
                                                     - ${record.receiver.associateable.denomination}`
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br v-if="record.receiver && record.receiver.associateable">
                        </div>
                    </div>
                    <h6 class="text-center">
                        Cuentas presupuestarias de gastos
                    </h6>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-md-3">Acción Específica</th>
                                <th class="col-md-3">Cuenta</th>
                                <th class="col-md-3">Descripción</th>
                                <th class="col-md-3">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(account, index) in record.budget_compromise_details"
                                :key="index"
                            >
                                <td v-if="account.tax_id == null" class="col-md-3 text-center">
                                    {{
                                        account.budget_sub_specific_formulation.specific_action.specificable.code
                                        + ' - ' + account.budget_sub_specific_formulation.specific_action.code
                                        + ' | ' + account.budget_sub_specific_formulation.specific_action.name
                                    }}
                                </td>
                                <td v-if="account.tax_id == null" class="col-md-3 text-center">
                                    {{ account.budget_account.code }}
                                </td>
                                <td v-if="account.tax_id == null" class="col-md-3 text-center">{{ account.budget_account.denomination }}</td>
                                <td v-if="account.tax_id == null" class="col-md-3 text-center">
                                    {{
                                        parseFloat(account.amount).toFixed(account.budget_sub_specific_formulation.currency.decimal_places)
                                    }}
                                </td>
                            </tr>
                            <tr
                                style="
                                    background-color: rgba(0, 0, 0, 0.05) !important;
                                "
                            >
                                <td class="col-md-3 text-center"></td>
                                <td class="col-md-3 text-center"></td>
                                <td class="text-center">
                                        <b>TOTAL {{ record.currency_symbol }}</b>
                                </td>
                                <td class="col-md-3 text-center">
                                    <b>
                                        {{
                                            formatToCurrency(record.total, "")
                                        }}
                                    </b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <h6 class="text-center">Cuentas presupuestarias de impuestos</h6>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-md-3">Acción Específica</th>
                                <th class="col-md-3">Cuenta</th>
                                <th class="col-md-3">Descripción</th>
                                <th class="col-md-3">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(account, index) in record.budget_compromise_details"
                                :key="index"
                            >
                                <td v-if="account.tax_id" class="col-md-3 text-center">
                                    {{
                                        account.tax_id ? account.budget_sub_specific_formulation.specific_action.specificable.code
                                        + ' - ' + account.budget_sub_specific_formulation.specific_action.code
                                        + ' | ' + account.budget_sub_specific_formulation.specific_action.name : ''
                                    }}
                                </td>
                                <td v-if="account.tax_id" class="col-md-3 text-center">
                                    {{ account.tax_id ? account.budget_account.code : '' }}
                                </td>
                                <td v-if="account.tax_id" class="col-md-3 text-center">{{ account.budget_account.denomination }}</td>
                                <td v-if="account.tax_id" class="col-md-3 text-center">
                                    {{
                                        account.tax_id ?
                                        parseFloat(account.tax_amount).toFixed(account.budget_sub_specific_formulation.currency.decimal_places) :
                                        ''
                                    }}
                                </td>
                            </tr>
                            <tr
                                style="
                                    background-color: rgba(0, 0, 0, 0.05) !important;
                                "
                            >
                                <td class="col-md-3 text-center"></td>
                                <td class="col-md-3 text-center"></td>
                                <td class="text-center">
                                        <b>TOTAL {{ record.currency_symbol }}</b>
                                </td>
                                <td class="col-md-3 text-center">
                                    <b>
                                        {{
                                            formatToCurrency(record.totalTax, "")
                                        }}
                                    </b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-default btn-sm btn-round btn-modal-close"
                        data-dismiss="modal"
                    >
                        Cerrar
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
                    id: "",
                    institution_id: "",
                    compromised_at: "",
                    source_document: "",
                    description: "",
                    accounts: [],
                    tax_accounts: [],
                    institution: {},
                    total: 0,
                    totalTax: 0,
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
            reset() {},
        },
    }
</script>
