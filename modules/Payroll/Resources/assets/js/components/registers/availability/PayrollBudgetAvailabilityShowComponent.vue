<template>
    <div>
        <button
            @click="addRecord('show_base_budget_payroll' + id, route_show, $event)"
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Visualizar requerimiento"
            data-toggle="tooltip"
            v-has-tooltip
        >
            <i class="fa fa-eye"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'show_base_budget_payroll' + id"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="reset"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            Disponibilidad presupuestaria - INFORMACIÓN DE LA NÓMINA
                        </h6>
                    </div>
                    <br><br>
                    <!-- inicio tabla -->
                    <div class="row col-12">
                        <table
                            class="table table-striped table-hover mt-5"
                            style="margin-left: 2rem;"
                        >
                            <thead>
                                <tr>
                                    <th
                                        tabindex="0"
                                        class="col-3"
                                        style="border: 1px solid #dee2e6; position: relative;"
                                    >
                                        Código
                                    </th>
                                    <th
                                        tabindex="0"
                                        class="col-3"
                                        style="border: 1px solid #dee2e6; position: relative;"
                                    >
                                        Nombre
                                    </th>
                                    <th
                                        tabindex="0"
                                        class="col-3"
                                        style="border: 1px solid #dee2e6; position: relative;"
                                    >
                                        Periodo de pago
                                    </th>
                                    <th
                                        tabindex="0"
                                        class="col-3"
                                        style="border: 1px solid #dee2e6; position: relative;"
                                    >
                                        Tipo de pago
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    >
                                        {{ records.payroll.code }}
                                    </td>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    >
                                        {{ records.payroll.name }}
                                    </td>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    >
                                        {{ format_date(records.payroll.payroll_payment_period.start_date) }} - {{ format_date(records.payroll.payroll_payment_period.end_date) }}
                                    </td>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    >
                                    {{ records.payroll.payroll_payment_period.payroll_payment_type.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    ></td>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    ></td>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    >
                                        <h6 align="right">
                                            TOTAL {{ records.payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.symbol }}
                                        </h6>
                                    </td>
                                    <td
                                        style="border: 1px solid #dee2e6;"
                                        tabindex="0"
                                        class="col-3"
                                    >
                                        <h6 align="right">
                                            {{
                                                parseFloat(records.totalAmount).toFixed(
                                                    records.payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.decimal_places
                                                )
                                            }}
                                        </h6>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- fin tabla -->
                    <div class="col-12 mt-2">
                        <div class="form-horizontal">
                            <div>
                                <!-- inicio  cuentas presupuestarias -->
                                <div class="col-12">
                                    <div>
                                        <h6 class="text-center card-title">
                                            Cuentas presupuestarias de gastos
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-12 pad-top-20">
                                                <table
                                                    class="
                                                        table table-hover table-striped
                                                    "
                                                    border="1px"
                                                    cellpadding="0px"
                                                    cellspacing="0px"
                                                >
                                                    <thead>
                                                        <tr>
                                                            <th class="col-3">
                                                                Acción Específica
                                                            </th>
                                                            <th class="col-3">
                                                                Cuenta
                                                            </th>
                                                            <th class="col-2">
                                                                Monto (Concepto)
                                                            </th>
                                                            <th class="col-2">
                                                                Monto (Cuenta)
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(account,
                                                            index) in records.budgetAccounts"
                                                            :key="index"
                                                        >
                                                            <td class="text-center" v-html="account.budget_specific_action_desc">
                                                            </td>
                                                            <td class="text-center">
                                                                {{
                                                                    account.budget_account_code
                                                                }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{
                                                                    parseFloat(account.value).toFixed(
                                                                        records.payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.decimal_places
                                                                    )
                                                                }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{
                                                                    parseFloat(account.budget_account_amount).toFixed(
                                                                        records.payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.decimal_places
                                                                    )
                                                                }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Final modal-body -->
                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                    <!-- Final modal-footer -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["id"],
    data() {
        return {
            records: {
                budgetAccounts: [],
                payroll: {
                    payroll_payment_period: {
                        payroll_payment_type: {
                            payroll_concepts: [
                                {
                                    currency: {
                                        symbol: '',
                                    },
                                },
                            ],
                        },
                    },
                },
                totalAmount: '',
            },
        };
    },
    created() {
        // 
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset() {},
    },
};
</script>
