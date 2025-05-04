<template>
    <section>
        <!-- card-body -->
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button" class="close" data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li
                            v-for="error in errors"
                            :key="error">{{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <!-- inicio tabla -->
                <div class="row col-12">
                    <table
                        class="table table-striped table-hover"
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
                                    {{ payroll.code }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6;"
                                    tabindex="0"
                                    class="col-3"
                                >
                                    {{ payroll.name }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6;"
                                    tabindex="0"
                                    class="col-3"
                                >
                                    {{ format_date(payroll.payroll_payment_period.start_date) }} - {{ format_date(payroll.payroll_payment_period.end_date) }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6;"
                                    tabindex="0"
                                    class="col-3"
                                >
                                {{ payroll.payroll_payment_period.payroll_payment_type.name }}
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
                                        TOTAL {{ payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.symbol }}
                                    </h6>
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6;"
                                    tabindex="0"
                                    class="col-3"
                                >
                                    <h6 align="right">
                                        {{
                                            parseFloat(totalamount).toFixed(
                                                payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.decimal_places
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
                                                        index) in budgetaccounts"
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
                                                                    payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.decimal_places
                                                                )
                                                            }}
                                                        </td>
                                                        <td class="text-center">
                                                            {{
                                                                parseFloat(account.budget_account_amount).toFixed(
                                                                    payroll.payroll_payment_period.payroll_payment_type.payroll_concepts[0].currency.decimal_places
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
            </div>
        </div>
        <!-- Final card-body -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <button
                class="btn btn-success btn-sm"
                title="Hay disponibilidad"
                data-toggle="tooltip"
                v-has-tooltip
                @click="
                    createRecord(
                        '/payroll/registers/availability',
                        1
                    )
                "
            >
                Hay disponibilidad
            </button>
            <button
                class="btn btn-danger btn-sm"
                title="No hay disponibilidad"
                data-toggle="tooltip"
                v-has-tooltip
                @click="
                    createRecord(
                        '/payroll/registers/availability',
                        0
                    )
                "
            >
                No hay disponibilidad
            </button>
        </div>
        <!-- Final card-footer -->
    </section>
</template>
<script>
export default {
    props: {
        payroll: {
            type: Object,
            default: function() {
                return {};
            },
        },
        budgetaccounts: {
            type: Object,
            default: function() {
                return {};
            },
        },
        totalamount: {
            type: Number,
            default: 0.00,
        },
    },
    data() {
        return {
            record: {
                availability: "",
                payroll_id: "",
            },
            errors: [],
        };
    },
    methods: {
        /**
         * Reescribe el Método createRecord para cambiar su comportamiento por defecto
         * Método que permite crear o actualizar un registro
         *
         * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
         * @param  {string} state  Respuesta sobre el estado de disponibilidad de la cotizacion.
         * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
         *                         El valor por defecto es verdadero.
         */
        createRecord(url, state) {
            const vm = this;
            url = vm.setUrl(url);
            vm.loading = true;
            vm.record.availability = state;
            vm.record.payroll_id = vm.payroll.id;
            vm.record.budget_accounts = vm.budgetaccounts;
            axios
                .post(url, vm.record)
                .then((response) => {
                    if (response.data.error) {
                        vm.errors.push(response.data.error);
                    } else {
                        vm.errors = [];
                        vm.loading = false;
                        vm.showMessage("store");
                        location.href = `${window.app_url}/purchase/budgetary_availability`;
                    }
                })
                .catch((error) => {
                    vm.errors = [];
                    if (typeof error.response != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                    }

                    vm.loading = false;
                });
        },
    },
};
</script>
