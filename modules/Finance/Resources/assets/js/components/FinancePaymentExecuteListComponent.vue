<template>
    <div>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Código</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Código"
                    tabindex="1"
                    v-model="filterBy.code"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha de pago</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de pago"
                    tabindex="2"
                    v-model="filterBy.date"
                />
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button
                        class="btn btn-default btn-icon btn-xs-responsive px-3"
                        type="reset"
                        aria-label="Search"
                        title="Limpiar filtro"
                        @click="resetFilters()"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        class="btn btn-info btn-icon btn-xs-responsive px-3"
                        type="button"
                        aria-label="Search"
                        title="Buscar"
                        @click="filterTable()"
                    >
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Final de filtros de la tabla -->
        <hr />
        <!-- Tabla de registros de Emisiones de Pago -->
        <v-server-table
            :columns="columns"
            :url="'finance/payment-execute/vue-list'"
            :options="table_options"
            ref="tableOptions"
        >
            <div slot="code" slot-scope="props" class="text-center">
                <span>{{ props.row.code }}</span>
            </div>
            <div slot="observations" slot-scope="props" class="text-center">
                <div
                    v-html="props.row.observations"
                    v-if="props.row.observations"
                ></div>
                <div v-else></div>
            </div>
            <div slot="status" slot-scope="props" class="text-center">
                <span
                    class="text-danger"
                    v-if="
                        props.row.document_status !== null &&
                        props.row.document_status.action === 'AN' &&
                        props.row.status === 'AN'
                    "
                >
                    Anulado(a)
                </span>
                <span
                    class="text-danger"
                    v-if="
                        props.row.document_status !== null &&
                        props.row.document_status.action === 'RE'
                    "
                >
                    Rechazado(a)
                </span>
                <div v-else>
                    <span class="text-success" v-if="props.row.status === 'PP'"
                        >Parcialmente pagado(a)
                    </span>
                    <span class="text-success" v-if="props.row.status === 'PA'"
                        >Pagado(a)
                    </span>
                    <span class="text-warning" v-if="props.row.status === 'PE'"
                        >Pendiente
                    </span>
                </div>
            </div>
            <div slot="paid_at" slot-scope="props" class="text-center">
                <span>{{ format_date(props.row.paid_at) }}</span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <template
                        v-if="
                            lastYear &&
                            format_date(props.row.paid_at, 'YYYY') <= lastYear
                        "
                    >
                        <button
                            class="btn btn-success btn-xs btn-icon btn-action"
                            type="button"
                            disabled
                        >
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <template v-else>
                        <finance-approve-payment-execute
                            v-show="props.row.status === 'PE'"
                            :id="props.row.id"
                            :code="props.row.code"
                            :fiscal_year="
                                fiscal_years.length > 0
                                    ? fiscal_years[0].text
                                    : ''
                            "
                            :approvePaymentExecutedPermission="
                                approvePaymentExecutedPermission
                            "
                        />
                    </template>
                    <button
                        class="btn btn-info btn-xs btn-icon btn-action"
                        data-title="Ver registro"
                        data-toggle="modal"
                        type="button"
                        data-target="#payOrderDetails"
                        @click="setDetails(props.row)"
                    >
                        <i class="fa fa-eye"></i>
                    </button>
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-warning-alternative"
                        data-title="Generar Comprobante de retencion"
                        title="Generar Comprobante de retencion"
                        data-toggle="modal"
                        type="button"
                        data-target="#payOrderReportIvaModal"
                        @click="setDetailsDeduction(props.row)"
                    >
                        <i class="fa fa-leanpub"></i>
                    </button>
                    <a
                        v-if="props.row.finance_payment_execute_iva"
                        :href="
                            setUrl(
                                `finance/payment-execute/iva/pdf/${props.row.id}`
                            )
                        "
                        target="_blank"
                        class="btn btn-primary btn-xs btn-icon btn-action"
                        title="Imprimir registro Retencion Iva"
                        data-toggle="tooltip"
                    >
                        <i class="fa fa-print"></i>
                    </a>
                    <a
                        :href="
                            setUrl(
                                `finance/payment-execute/pdf/${props.row.id}`
                            )
                        "
                        target="_blank"
                        class="btn btn-primary btn-xs btn-icon btn-action"
                        title="Imprimir registro"
                        data-toggle="tooltip"
                    >
                        <i class="fa fa-print"></i>
                    </a>
                    <template
                        v-if="
                            lastYear &&
                            format_date(props.row.paid_at, 'YYYY') <= lastYear
                        "
                    >
                    </template>
                    <template v-else>
                        <button
                            v-show="
                                !props.row.is_payroll &&
                                props.row.status === 'PE'
                            "
                            @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            type="button"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <finance-cancel-payment-execute
                            v-show="
                                (props.row.status === 'PA' ||
                                    props.row.status === 'PP') &&
                                props.row.status != 'AN' &&
                                !props.row.is_deduction &&
                                cancelPaymentExecutedPermission
                            "
                            :cancelPaymentExecutedPermission="
                                cancelPaymentExecutedPermission
                            "
                            :id="props.row.id"
                            :code="props.row.code"
                            :observations="
                                props.row.observations
                                    ? props.row.observations.replace(
                                          /(<([^>]+)>)/gi,
                                          ''
                                      )
                                    : ''
                            "
                            :is_payroll="props.row.is_payroll"
                            :fiscal_year="
                                fiscal_years.length > 0
                                    ? fiscal_years[0].text
                                    : ''
                            "
                            :is-partial="props.row.is_partial"
                        />
                    </template>
                </div>
            </div>
        </v-server-table>
        <!-- Final de tabla de registros de Emisiones de Pago -->
        <!-- Modal -->
        <div
            id="payOrderDetails"
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog vue-crud" role="document">
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
                            <i class="fa fa-info-circle inline-block"></i>
                            Detalles de la emisión de pago
                        </h6>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-center">Datos de la emisión</h6>
                        <h6
                            v-if="details.document_status_action == 'AN'"
                            class="text-center text-danger"
                        >
                            {{ details.document_status_name }}
                        </h6>
                        <br />
                        <div class="row">
                            <div class="col-md-12">
                                <b>Institución:</b>
                                {{ details.institution_name }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <b>Fecha:</b> {{ format_date(details.paid_at) }}
                            </div>
                            <div class="col-md-8">
                                <b>Tipo de Moneda:</b>
                                {{ details.currency.name }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <b>Cod. Orden(s) de pago(s):</b>
                                <span class="ml-2" v-for="(order, index) in details.finance_pay_orders" :key="index">
                                    {{ order.code }}{{ index < details.finance_pay_orders.length - 1 ? ',' : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Datos de la Factura</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" v-if="details.invoice_date">
                                <b>Fecha de Factura:</b>
                                {{ format_date(details.invoice_date) }}
                            </div>
                            <div class="col-md-4">
                                <b>Nro. Factura:</b>
                                <span class="ml-2">{{ details.payment_number }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Datos del Proveedor ó Beneficiario</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <b>Nombre o Razón Social:</b>
                                {{ details.receiver_name }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <b>Pago parcial:</b>
                                {{ details.is_partial ? "Si" : "No" }}
                            </div>
                            <div class="col-md-3">
                                <b>Monto:</b>
                                {{
                                    formatToCurrency(
                                        parseFloat(details.paid_amount) +
                                            parseFloat(
                                                details.deduction_amount
                                            ),
                                        details.currency.symbol
                                    )
                                }}
                            </div>
                            <div class="col-md-3">
                                <b>Retenciones:</b>
                                {{ details.deduction_amount }}
                            </div>
                            <div class="col-md-3">
                                <b>Monto a pagar</b>
                                {{
                                    formatToCurrency(
                                        details.paid_amount,
                                        details.currency.symbol
                                    )
                                }}
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Datos Bancarios</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <b>Método de pago:</b>
                                {{ details.finance_payment_method.name }}
                            </div>
                            <div class="col-md-4">
                                <b>Banco:</b>
                                {{
                                    details.finance_bank_account
                                        .finance_banking_agency.finance_bank
                                        .name
                                }}
                            </div>
                            <div class="col-md-4">
                                <b>Nro. de Cuenta:</b>
                                {{
                                    format_bank_account(
                                        details.finance_bank_account.ccc_number
                                    )
                                }}
                            </div>
                            <div class="col-4">
                                <b>Observación:</b>
                                <div v-html="details.observations"></div>
                            </div>
                            <div class="col-md-8">
                                <b>Nro. de Referencia bancaria:</b>
                                {{
                                    details.general_bank_reference ?? "No definido"
                                }}
                            </div>
                            <div
                                v-if="
                                    details.status == 'AN' &&
                                    details.description
                                "
                                class="col-12"
                            >
                                <b>Descripción del motivo de la anulación:</b>
                                <div v-html="details.description"></div>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Datos Contables</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table
                                    class="table table-sm table-striped table-hover"
                                >
                                    <col class="col-8" />
                                    <col class="col-2" />
                                    <col class="col-2" />
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase"
                                                width="50%"
                                            >
                                                CÓDIGO DE CUENTA - DENOMINACIÓN
                                            </th>
                                            <th
                                                class="text-uppercase"
                                                width="20%"
                                            >
                                                DEBE
                                            </th>
                                            <th
                                                class="text-uppercase"
                                                width="20%"
                                            >
                                                HABER
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="details.accounting_entryable">
                                        <tr
                                            v-for="(acc, index) in details
                                                .accounting_entryable
                                                .accounting_entry
                                                .accounting_accounts"
                                            :key="index"
                                        >
                                            <td
                                                class="text-justify"
                                                v-if="acc.account"
                                            >
                                                {{ acc.account.group }}.{{
                                                    acc.account.subgroup
                                                }}.{{ acc.account.item }}.
                                                {{ acc.account.generic }}.{{
                                                    acc.account.specific
                                                }}.
                                                {{ acc.account.subspecific }} -
                                                {{ acc.account.denomination }}
                                            </td>
                                            <td class="text-justify" v-else>
                                                No definido
                                            </td>
                                            <td class="text-right">
                                                {{
                                                    formatToCurrency(
                                                        acc.debit,
                                                        details.currency.symbol
                                                    )
                                                }}
                                            </td>
                                            <td class="text-right">
                                                {{
                                                    formatToCurrency(
                                                        acc.assets,
                                                        details.currency.symbol
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td
                                                class="text-uppercase"
                                                colspan="4"
                                            >
                                                Sin Asiento Contable registrado
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-default"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Final de modal -->
        <!-- Modal reporte de iva-->
        <div
            id="payOrderReportIvaModal"
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog vue-crud" role="document">
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
                            <i class="fa fa-info-circle inline-block"></i>
                            Reportes de IVA
                        </h6>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-center">
                            Datos Deduciones de la emisión
                        </h6>
                        <div class="row">
                            <div class="col-12 mb-4">Retenciones</div>
                            <div class="col-md-12 mb-4">
                                <v-multiselect
                                    track_by="name"
                                    :options="select"
                                    v-model="detailsIvaReportList"
                                >
                                </v-multiselect>
                            </div>
                        </div>
                        <h6 class="text-center">
                            Deduciones Selecionadas para el reporte de IVA
                        </h6>
                        <div class="row">
                            <div class="col-12 mb-4">Retenciones</div>
                            <div class="col-md-12 mb-4">
                                <table
                                    class="table table-sm table-striped table-hover"
                                >
                                    <thead>
                                        <tr>
                                            <th class="font-weight-bold">
                                                Tipo de retención
                                            </th>
                                            <th class="font-weight-bold">
                                                % Alicuota
                                            </th>
                                            <th class="font-weight-bold">
                                                Base Imponible
                                            </th>
                                            <th
                                                title="Total de Productos sin iva"
                                                class="font-weight-bold"
                                                data-toggle="tooltip"
                                            >
                                                Total compra sin Iva
                                            </th>
                                            <th class="font-weight-bold">
                                                Impuesto Iva
                                            </th>
                                            <th class="font-weight-bold">
                                                Iva Retenido
                                            </th>
                                            <th class="font-weight-bold">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                ded, index
                                            ) in detailsIvaReportList"
                                            :key="index"
                                        >
                                            <td class="text-center">IVA</td>
                                            <td class="text-center">
                                                <input class="form-control"
                                                type="number" placeholder="%"
                                                tabindex="2"
                                                v-model="percentage"
                                                @input="selectIva(index,percentage,'percentage')"
                                                required />
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    class="form-control"
                                                    type="number"
                                                    placeholder="%"
                                                    tabindex="2"
                                                    v-model="
                                                        total_purchases_iva
                                                    "
                                                    @input="
                                                        selectIva(
                                                            index,
                                                            total_purchases_iva,
                                                            'total_purchases_iva'
                                                        )
                                                    "
                                                    required
                                                />
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    class="form-control"
                                                    type="number"
                                                    placeholder="%"
                                                    tabindex="2"
                                                    value="0"
                                                    lang="es"
                                                    v-model="
                                                        total_purchases_without_iva[
                                                            index
                                                        ]
                                                    "
                                                    @input="
                                                        selectIva(
                                                            index,
                                                            total_purchases_without_iva[
                                                                index
                                                            ],
                                                            'total_purchases_without_iva'
                                                        )
                                                    "
                                                    required
                                                />
                                            </td>
                                            <td class="text-center" v-if="detailsIvaReportList[index].percentage !== 0 && detailsIvaReportList[index].total_purchases_iva !== 0  && detailsIvaReportList[index].total_purchases_without_iva !== 0">
                         {{ new Intl.NumberFormat("de-DE").format(parseFloat((((detailsIvaReportList[index].total_purchases_iva) * detailsIvaReportList[index].percentage )/ 100).toFixed(2) )) }}
                      </td>
                                            <td class="text-center" v-else>
                                                 0,0
                                            </td>
                                            <td
                                                class="text-center"
                                                v-if="
                                                    detailsIvaReportList[index]
                                                        .percentage !== 0 &&
                                                    detailsIvaReportList[index]
                                                        .total_purchases_iva !==
                                                        0 &&
                                                    detailsIvaReportList[index]
                                                        .total_purchases_without_iva !==
                                                        0
                                                "
                                            >
                                                {{ new Intl.NumberFormat("de-DE").format(parseFloat(
                                                        ded.amount
                                                    ).toFixed(2))

                                                }}
                                            </td>
                                            <td class="text-center" v-else>
                                                 0,0
                                            </td>
                                            <td class="text-center">
                                                <a
                                                    class="btn btn-sm btn-danger btn-action"
                                                    href="#"
                                                    @click="
                                                        deleteDeduction(ded.id)
                                                    "
                                                    title="Eliminar este registro"
                                                    data-toggle="tooltip"
                                                >
                                                    <i class="fa fa-trash"></i
                                                ></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-sm btn-round btn-default"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                        <button
                            type="button"
                            @click="createPdf()"
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Final de modal reporte de iva-->
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            cancelPaymentExecutedPermission: false,
            approvePaymentExecutedPermission: false,
            lastYear: "",
            tmpRecords: [],
            fiscal_years: [],
            select: [],
            deductions: [],
            financeRecords: [],
            percentage: [],
            total_purchases_iva: [],
            total_purchases_without_iva: [],
            detailsIvaReportList: [],
            details: {
                ordered_at: "",
                type: "",
                documentType: "",
                document_number: "",
                source_amount: 0,
                amount: 0,
                concept: "",
                is_partial: false,
                observations: "",
                document_sourceable: {
                    reference: "",
                    code: "",
                },
                name_sourceable: {
                    name: "",
                },
                currency: {
                    symbol: "",
                    decimal_places: 2,
                },
                finance_payment_method: {
                    name: "",
                },
                finance_bank_account: {
                    ccc_number: "",
                    finance_banking_agency: {
                        finance_bank: {
                            name: "",
                        },
                    },
                },
                accounting_entryable: {
                    accounting_entry: {
                        accounting_accounts: [],
                    },
                },
            },
            columns: [
                "code",
                "paid_at",
                "receiver_name",
                "observations",
                "status",
                "id",
            ],
            filterBy: {
                code: "",
                date: "",
            },
        };
    },
    methods: {
        /**
         * Establece los datos para mostrar detalles del registro seleccionado
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param   {Object}  record  Datos del registro a mostrar
         */
        setDetails(record) {
            const vm = this;
            vm.details = record;
            this.details.document_status_name =
                this.details.document_status.name;
            this.details.document_status_action =
                this.details.document_status.action;
            vm.details.finance_payment_method = record.finance_payment_method;
            vm.details.finance_bank_account = record.finance_bank_account;
            vm.details.institution_name =
                record.finance_pay_orders[0].institution.name;
        },
        /**
         * Find the index of an item in the list based on its ID.
         *
         * @param {Array} list - The list to search in.
         * @param {number} id - The ID of the item to find.
         * @param {boolean} returnBoolean - Whether to return a boolean or the index.
         * @return {number|boolean} - The index of the item if found, or true if the item is in the list.
         *                           Returns -1 if the item is not found.
         */
        indexOf(list, id, returnBoolean) {
            for (var i = list.length - 1; i >= 0; i--) {
                if (list[i].id == id) {
                    return returnBoolean ? true : i;
                }
            }
            return returnBoolean ? false : -1;
        },
                /**
         * Establece los datos para mostrar detalles del registro seleccionado
         *
         * @param {number} index - Índice del registro seleccionado
         * @param {any} value - Valor a establecer
         * @param {string} expr - Expresión para determinar el campo a actualizar
         * @return {void} Esta función no devuelve nada
         */
         selectIva(index, value, expr) {
            const vm = this;
            if (vm.detailsIvaReportList.length > 0) {
                if(vm.detailsIvaReportList[index].percentage !== 0 && vm.detailsIvaReportList[index].total_purchases_iva !== 0 ){
                    vm.detailsIvaReportList[index].iva = ((vm.detailsIvaReportList[index].total_purchases_iva) * vm.detailsIvaReportList[index].percentage )/ 100;
                }
                switch (expr) {
                    case "total_purchases_iva":
                        vm.detailsIvaReportList[index].total_purchases_iva =
                            value;
                        break;
                    case "total_purchases_without_iva":
                        vm.detailsIvaReportList[
                            index
                        ].total_purchases_without_iva = value;
                        break;
                    default:
                        vm.detailsIvaReportList[index].percentage = value;
                }
            }
        },
        /**
         * Sets the details for the IVA deduction report.
         *
         * @param {Object} record - The record object containing the finance payment deductions.
         * @return {void} This function does not return anything.
         */
        setDetailsDeduction(record) {
            const vm = this;
            vm.detailsIvaReportList = [];
            vm.select = [];
            (vm.percentage = []),
                (vm.total_purchases_iva = []),
                (vm.total_purchases_without_iva = []),
                (vm.deductions = record.finance_payment_deductions);
            vm.deductions.forEach((deduction, indx) => {
                vm.select.push(deduction.deduction);
                if (record.finance_pay_orders[0].document_sourceable) {
                    vm.select[indx].details =
                        record.finance_pay_orders[0].document_sourceable.budget_compromise_details;
                }
                vm.select[indx].amount = deduction.amount;
                vm.select[indx].source_amount = record.source_amount;
                vm.select[indx].id = deduction.id;
                vm.select[indx].percentage = 0;
            });
            vm.financeRecords = record;
        },

        /**
         * Elimina la retención seleccionada
         *
         * @param {number} index - Índice del registro seleccionado
         */
        deleteDeduction(index) {
            let vm = this;
            var pos = vm.indexOf(vm.detailsIvaReportList, index);
            vm.detailsIvaReportList.splice(pos, 1);
        },
        /**
         * Generates a PDF for the payment execution with the provided URL.
         *
         * @param {string} url - The URL for generating the PDF
         */
        createPdf(url) {
            const vm = this;
            vm.loading = true;
            axios
                .post(
                    `finance/payment-execute/iva/pdf/${vm.financeRecords.id}`,
                    vm.detailsIvaReportList
                )
                .then((response) => {
                    let link = document.createElement("a");
                    link.target = "_blank";
                    link.href = vm.setUrl(
                        `finance/payment-execute/iva/pdf/${response.data.records.id}`
                    );
                    link.click();
                    vm.showMessage("store");

                    vm.loading = false;
                })
                .catch((error) => {
                    var errors = [];
                    if (typeof error.response != "undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                        vm.showMessage(
                            "custom",
                            "Error",
                            "danger",
                            "fa-ban",
                            errors
                        );
                    }
                });
        },
        /**
         * Método para reestablecer valores iniciales del formulario de filtros.
         *
         * @method resetFilters
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@cenditel.gob.ve>
         */
        resetFilters() {
            const vm = this;
            vm.filterBy = {
                code: "",
                date: "",
            };
            vm.$refs.tableOptions.refresh();
        },

        /**
         * Método que permite filtrar los datos de la tabla.
         *
         * @method filterTable
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        filterTable() {
            const vm = this;
            let params = {
                query: vm.filterBy.date
                    ? vm.format_date(vm.filterBy.date)
                    : vm.filterBy.code,
                limit: 10,
                ascending: 1,
                page: 1,
                byColumn: 0,
            };

            axios
                .get(`${window.app_url}/finance/payment-execute/vue-list`, {
                    params: params,
                })
                .then((response) => {
                    vm.$refs.tableOptions.data = response.data.data;
                });
        },

        /**
         * Método para aprobar una Emisión de pago
         *
         * @method approvePaymentExecute
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        approvePaymentExecute(id) {
            const vm = this;
            const url = vm.setUrl(
                "finance/payment-execute/change-document-status"
            );
            bootbox.confirm({
                title: "Aprobar registro",
                message:
                    "¿Está seguro? Una vez aprobado el registro no se podrá modificar y/o eliminar.",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                        className: "btn btn-default btn-sm btn-round",
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                        className: "btn btn-primary btn-sm btn-round",
                    },
                },
                callback: function (result) {
                    if (result) {
                        vm.loading = true;
                        axios
                            .post(url, { id: id })
                            .then((response) => {
                                if (response.status == 200) {
                                    vm.showMessage(
                                        "custom",
                                        "¡Éxito!",
                                        "success",
                                        "screen-ok",
                                        "Emisión de pago aprobada"
                                    );
                                    location.reload();
                                }
                            })
                            .catch((error) => {
                                if (typeof error.response != "undefined") {
                                    if (error.response.status == 500) {
                                        const messages =
                                            error.response.data.message;
                                        vm.showMessage(
                                            messages.type,
                                            messages.title,
                                            messages.class,
                                            messages.icon,
                                            messages.text
                                        );
                                    }
                                }
                                console.error(error);
                            });
                        vm.loading = false;
                    }
                },
            });
        },
    },
    created() {
        this.table_options.headings = {
            code: "Código",
            paid_at: "Fecha de pago",
            receiver_name: "Proveedor / Beneficiario",
            observations: "Concepto",
            status: "Estatus",
            id: "Acción",
        };
        this.table_options.columnsClasses = {
            code: "col-md-2",
            paid_at: "col-md-2",
            receiver_name: "col-md-2",
            observations: "col-md-2",
            status: "col-md-2",
            id: "col-md-2",
        };
        this.table_options.sortable = [
            "code",
            "paid_at",
            "receiver_name",
            "observations",
            "status",
        ];
        this.table_options.filterable = [
            "code",
            "paid_at",
            "receiver_name",
            "observations",
            "status",
        ];
    },
    watch: {
    },
    async mounted() {
        const vm = this;
        vm.loadingState(true); // Inicio de spinner de carga.
        axios
            .get(`${window.app_url}/finance/payment-execute/vue-list`)
            .then((response) => {
                vm.records = response.data.records;
                vm.cancelPaymentExecutedPermission =
                    response.data.cancelPaymentExecutedPermission;
                vm.approvePaymentExecutedPermission =
                    response.data.approvePaymentExecutedPermission;
                // Variable usada para el reseteo de los filtros de la tabla.
                vm.tmpRecords = vm.records;
            });
        await vm.queryLastFiscalYear();
        await vm.getOpenedFiscalYears();
        vm.loadingState(); // Finaliza spinner de carga.
    },
};
</script>
