<template>
    <section>
        <!-- card-body -->
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong>
                    Debe verificar los siguientes errores antes de continuar:
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                        @click.prevent="errors = []"
                    >
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="(error, index) in errors" :key="index">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group is-required">
                        <label class="control-label">Fecha</label>
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="record.date"
                        />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group is-required">
                        <label for="description" class="control-label">
                            Descripción
                        </label>
                        <input
                            type="text"
                            class="form-control input-sm"
                            id="description"
                            v-model="record.description"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <br />
                </div>
                <!-- Inicio tabla -->
                <div class="row col-12">
                    <table
                        class="table table-striped table-hover"
                        style="margin-left: 2rem"
                    >
                        <thead>
                            <tr class="row">
                                <th
                                    class="col-1"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Código de requerimiento
                                </th>
                                <th
                                    class="col-2"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Nombre
                                </th>
                                <th
                                    class="col-2"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Especificaciones técnicas
                                </th>
                                <th
                                    class="col-1"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Unidad de medida
                                </th>
                                <th
                                    class="col-1"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Cantidad
                                </th>
                                <th
                                    class="col-2"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Precio unitario sin IVA
                                </th>
                                <th
                                    class="col-2"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    Cantidad * Precio unitario
                                </th>
                                <th
                                    class="col-1"
                                    style="
                                        border: 1px solid #dee2e6;
                                        position: relative;
                                    "
                                >
                                    IVA
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(x, index) in purchase_requirement_items"
                                class="row"
                                :key="index"
                            >
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-1 text-center"
                                >
                                    {{ x.requirement_code }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-2"
                                >
                                    {{ x.name }}
                                </td>
                                <td
                                    v-if="x.technical_specifications"
                                    style="border: 1px solid #dee2e6"
                                    class="col-2"
                                >
                                    {{ x.technical_specifications }}
                                </td>
                                <td
                                    v-else
                                    style="border: 1px solid #dee2e6"
                                    class="col-2 text-left"
                                ></td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-1 text-center"
                                    v-if="x.measurement_unit"
                                >
                                    {{ x.measurement_unit.name }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-1"
                                    v-else
                                ></td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-1 text-center"
                                >
                                    {{ x.quantity }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-2 text-right"
                                >
                                    {{ addDecimals(x.unit_price) }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-2 text-right"
                                >
                                    <span align="right">
                                        {{ CalculateQtyPrice(x.qty_price) }}
                                    </span>
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-1 text-center"
                                >
                                    <span align="right">
                                        {{
                                            x.history_tax_id
                                                ? x.history_tax.percentage
                                                : 0.0
                                        }}%
                                    </span>
                                </td>
                            </tr>
                            <tr
                                v-for="(base, iva) in bases_imponibles"
                                :key="iva"
                                class="row"
                                style="background-color: rgb(242, 242, 242)"
                            >
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-9 text-right"
                                >
                                    <b
                                        >Base imponible según alícuota
                                        {{ iva * 100 }}%</b
                                    >
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-3 text-center"
                                >
                                    {{ currencyFormat(base) }}
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-9 text-right"
                                >
                                    <b
                                        >Monto total del impuesto según alícuota
                                        {{ iva * 100 }}%</b
                                    >
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-3 text-center"
                                >
                                    {{ currencyFormat(base * iva) }}
                                </td>
                            </tr>
                            <tr class="row">
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-9"
                                >
                                    <h6 align="right">
                                        TOTAL {{ currency_symbol }}
                                    </h6>
                                </td>
                                <td
                                    style="border: 1px solid #dee2e6"
                                    class="col-3 text-center"
                                >
                                    <h6>
                                        {{
                                            total.toFixed(
                                                currency
                                                    ? currency.decimal_places
                                                    : 2
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
                            <div class="row">
                                <!-- inicio  cuentas presupuestarias -->
                                <div class="col-12">
                                    <div>
                                        <h6 class="text-center card-title">
                                            Cuentas presupuestarias de gastos
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-12 pad-top-20">
                                                <table
                                                    class="table table-hover table-striped"
                                                    border="1px"
                                                    cellpadding="0px"
                                                    cellspacing="0px"
                                                >
                                                    <thead>
                                                        <tr>
                                                            <th class="col-4">
                                                                Acción
                                                                Específica
                                                            </th>
                                                            <th class="col-2">
                                                                Cuenta
                                                            </th>
                                                            <th class="col-3">
                                                                Descripción
                                                            </th>
                                                            <th class="col-2">
                                                                Monto
                                                            </th>
                                                            <th class="col-1">
                                                                <a
                                                                    id="add_account_info"
                                                                    class="btn btn-sm btn-info btn-action btn-tooltip"
                                                                    href="#"
                                                                    data-original-title="Agregar cuenta presupuestaria"
                                                                    data-toggle="modal"
                                                                    data-target="#add_account"
                                                                >
                                                                    <i
                                                                        class="fa fa-plus-circle"
                                                                    ></i>
                                                                </a>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(
                                                                account, index
                                                            ) in record.accounts"
                                                            :key="index"
                                                        >
                                                            <td
                                                                class="text-center"
                                                            >
                                                                {{
                                                                    account.spac_description
                                                                        ? account.spac_description
                                                                        : "Por asignar"
                                                                }}
                                                            </td>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                {{
                                                                    account.code
                                                                        ? account.code
                                                                        : "Por asignar"
                                                                }}
                                                            </td>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                {{
                                                                    account.description
                                                                }}
                                                            </td>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                {{
                                                                    formatToCurrency(
                                                                        account.amount,
                                                                        ""
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                class="text-center"
                                                            >
                                                                <input
                                                                    type="hidden"
                                                                    name="account_id[]"
                                                                    readonly
                                                                    :value="
                                                                        account.specific_action_id +
                                                                        '|' +
                                                                        account.account_id
                                                                    "
                                                                />
                                                                <input
                                                                    type="hidden"
                                                                    name="budget_account_amount[]"
                                                                    readonly
                                                                    :value="
                                                                        account.amount
                                                                    "
                                                                />
                                                                <a
                                                                    class="btn btn-sm btn-warning btn-action btn-tooltip"
                                                                    href="#"
                                                                    data-original-title="Editar cuenta presupuestaria"
                                                                    data-toggle="modal"
                                                                    data-target="#add_account"
                                                                    @click="
                                                                        editAccount(
                                                                            index
                                                                        )
                                                                    "
                                                                >
                                                                    <i
                                                                        class="fa fa-edit"
                                                                    ></i>
                                                                </a>

                                                                <a
                                                                    class="btn btn-sm btn-danger btn-action"
                                                                    href="#"
                                                                    @click="
                                                                        deleteAccount(
                                                                            index
                                                                        )
                                                                    "
                                                                    title="Eliminar este registro"
                                                                    data-toggle="tooltip"
                                                                >
                                                                    <i
                                                                        class="fa fa-minus-circle"
                                                                    ></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- fin de cuentas presupuestarias  -->

                                <!-- Modal para agregar cuentas presupuestarias -->
                                <div
                                    class="modal fade"
                                    tabindex="-1"
                                    role="dialog"
                                    id="add_account"
                                >
                                    <div
                                        class="modal-dialog vue-crud"
                                        role="document"
                                    >
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button
                                                    type="button"
                                                    class="close"
                                                    data-dismiss="modal"
                                                    aria-label="Close"
                                                >
                                                    <span
                                                        aria-hidden="true"
                                                        @click="resetAccount"
                                                        >×</span
                                                    >
                                                </button>
                                                <h6>
                                                    <i
                                                        class="ion-arrow-graph-up-right"
                                                    ></i>
                                                    Agregar cuentas
                                                </h6>
                                            </div>
                                            <div class="modal-body">
                                                <div
                                                    class="alert alert-danger"
                                                    v-if="errors.length > 0"
                                                >
                                                    <ul>
                                                        <li
                                                            v-for="(
                                                                error, index
                                                            ) in errors"
                                                            :key="index"
                                                        >
                                                            {{ error }}
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div v-if="!has_budget">
                                                    <div class="row">
                                                        <div
                                                            class="col-md-12 mt-4"
                                                        >
                                                            <div
                                                                class="form-group is-required"
                                                            >
                                                                <label
                                                                    >Codigo de
                                                                    la
                                                                    Cuenta:</label
                                                                >
                                                                <input
                                                                    type="text"
                                                                    class="form-control input-sm"
                                                                    data-toggle="tooltip"
                                                                    title="Indique el codigo de la cuenta"
                                                                    v-model="
                                                                        account_code
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div
                                                            class="col-md-12 mt-4"
                                                        >
                                                            <div
                                                                class="form-group is-required"
                                                            >
                                                                <label
                                                                    >Concepto de
                                                                    la
                                                                    Cuenta:</label
                                                                >
                                                                <input
                                                                    type="text"
                                                                    class="form-control input-sm"
                                                                    data-toggle="tooltip"
                                                                    title="Indique el consepto de la cuenta"
                                                                    v-model="
                                                                        account_concept
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="has_budget">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div
                                                                class="form-group is-required"
                                                            >
                                                                <label>
                                                                    Acción
                                                                    Específica:
                                                                </label>
                                                                <select2
                                                                    :options="
                                                                        specific_actions
                                                                    "
                                                                    @input="
                                                                        getAccounts()
                                                                    "
                                                                    v-model="
                                                                        specific_action_id
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div
                                                                class="form-group is-required"
                                                            >
                                                                <label>
                                                                    Cuenta:
                                                                </label>
                                                                <select2
                                                                    id="accounts"
                                                                    :options="
                                                                        accounts
                                                                    "
                                                                    @input="
                                                                        getAmountAccounts()
                                                                    "
                                                                    v-model="
                                                                        account_id
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div
                                                            class="form-group is-required"
                                                        >
                                                            <label
                                                                >Monto:</label
                                                            >
                                                            <input
                                                                type="text"
                                                                v-input-mask
                                                                data-inputmask="
                                                                    'alias': 'numeric',
                                                                    'allowMinus': 'false'
                                                                "
                                                                onfocus="$(this).select()"
                                                                class="form-control input-sm"
                                                                data-toggle="tooltip"
                                                                title="Indique el monto a asignar para la cuenta seleccionada"
                                                                v-model="
                                                                    account_amount
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button
                                                    type="button"
                                                    class="btn btn-default btn-sm btn-round btn-modal-close"
                                                    data-dismiss="modal"
                                                    @click="resetAccount"
                                                >
                                                    Cerrar
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="addAccount"
                                                    class="btn btn-primary btn-sm btn-round btn-modal-save"
                                                >
                                                    Agregar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- fin modal -->
                            </div>
                            <div class="row" v-if="!has_budget">
                                <div class="col-10">
                                    <label></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Final card-body -->
        <!-- Modal para agregar Documentos -->
        <div class="modal fade" tabindex="-1" role="dialog" id="add_document">
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
                            <i class="fa fa-upload"></i>
                            Documento
                        </h6>
                    </div>
                    <div class="modal-body">
                        <div
                            class="alert alert-danger"
                            v-if="errors.length > 0"
                        >
                            <ul>
                                <li
                                    v-for="(error, index) in errors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                        <upload-documents
                            inputLabel="Documento que avala que no hay disponibilidad"
                            inputTooltip="Seleccione el(los) documento(s) que avala(n) la modificación presupuestaria"
                            :parentRecord="'documentFiles'"
                        />
                        <div
                            class="col-12 col-md-4"
                            v-if="
                                typeof records.documentUrl != 'undefined' &&
                                records.documentUrl
                            "
                        >
                            <div class="form-group">
                                <strong>Ver Documento:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <a
                                        :href="showDocument()"
                                        target="_blank"
                                        class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                        data-toggle="tooltip"
                                        title="Ver documento que avala la modificación presupuestaria"
                                    >
                                        <i
                                            class="fa fa-file"
                                            aria-hidden="true"
                                        ></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            @click="uploadComplete"
                            class="btn btn-primary btn-sm btn-round btn-modal-save"
                        >
                            Completar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin modal -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <button
                class="btn btn-success btn-sm"
                title="Hay disponibilidad"
                data-toggle="tooltip"
                v-has-tooltip
                :disabled="record.accounts.length == 0"
                @click="createRecord('/purchase/budgetary_availability', 1)"
            >
                Hay disponibilidad
            </button>
            <button
                class="btn btn-danger btn-sm"
                title="No hay disponibilidad"
                data-toggle="tooltip"
                v-has-tooltip
                @click="continueWithoutAvailability"
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
        records: {
            type: Object,
            default: function () {
                return null;
            },
        },
        document_file: {
            type: Array,
            default: function () {
                return null;
            },
        },
        budget_items: {
            type: Array,
            default: function () {
                return [{ id: "", text: "Seleccione..." }];
            },
        },
        specific_actions: {
            type: Array,
            default: function () {
                return [{ id: "", text: "Seleccione..." }];
            },
        },
        has_budget: {
            type: Boolean,
            default: function () {
                return false;
            },
        },
    },
    data() {
        return {
            record: {
                description: "",
                date: "",
                accounts: [],
                documentFiles: [],
            },
            accounts: [],
            account_id: "",
            account_concept: "",
            account_code: "",
            account_amount: 0,
            selected_account_amount: 0,
            account_tax_id: "",
            tax: 0,
            total: 0,
            sub_total: 0,
            columns: [
                "code",
                "name",
                "description",
                "quantity",
                "base_budget_price",
                "supplier_price",
            ],
            files: {
                Acta_de_disponibilidad_presupuestaria: null,
            },
            editIndex: null,
            specific_action_id: "",
            budget_item_id: "",
            budget_available: 0,
            supplier_price_tot: 0,
            errors: [],
            items: [],
        };
    },
    created() {
        this.table_options.headings = {
            code: "Código de requerimiento",
            name: "Nombre",
            description: "Descripción",
            quantity: "Cantidad",
            base_budget_price: "Precio unitario base",
            supplier_price: "Precio unitario del proveedor",
        };

        this.table_options.columnsClasses = {
            code: "col-xs-1 text-center",
            name: "col-xs-2",
            description: "col-xs-2",
            quantity: "col-xs-1",
            base_budget_price: "col-xs-2 text-right",
            supplier_price: "col-xs-2 text-right",
        };

        this.table_options.filterable = [
            "code",
            "name",
            "description",
            "quantity",
        ];
    },
    watch: {
        budget_item_id(res) {
            if (res) {
                this.getBudgetAvailable();
            }
        },
    },
    mounted() {
        const vm = this;
        if (vm.records) {
            vm.record = vm.records;
            if (vm.document_file.length > 0) {
                vm.records.documentFiles = vm.document_file
                    ? [vm.document_file[0].id]
                    : [];
                vm.records.documentUrl = vm.document_file
                    ? vm.document_file[0].url
                    : "";
            }
            vm.record.accounts = [];
            vm.record.date = vm.records.availabilityitem[0]
                ? vm.records.availabilityitem[
                      vm.records.availabilityitem.length - 1
                  ].date
                    ? vm.records.availabilityitem[
                          vm.records.availabilityitem.length - 1
                      ].date
                    : vm.records.relatable[0].purchase_requirement_item
                          .purchase_requirement.date
                : vm.format_date(new Date().toString(), "YYYY-MM-DD");
            vm.record.description = vm.records.availabilityitem[0]
                ? vm.records.availabilityitem[
                      vm.records.availabilityitem.length - 1
                  ].description
                : "";
            function get_supplier_price(list, id) {
                var price = 0;
                $.each(list, function (index, data) {
                    if (data.purchase_requirement_item_id == id) {
                        price = data.unit_price;
                        vm.supplier_price_tot += parseFloat(data.unit_price);
                        return;
                    }
                });
                return price;
            }

            var newQuotedArray = vm.records.relatable;
            $.each(newQuotedArray, function (y, item) {
                vm.items.push({
                    code: item.purchase_requirement_item.purchase_requirement
                        .code,
                    id: item.purchase_requirement_item.id,
                    name: item.purchase_requirement_item.name,
                    description: item.purchase_requirement_item.description,
                    quantity: item.purchase_requirement_item.quantity,
                    measurement_unit:
                        item.purchase_requirement_item.measurement_unit.acronym,
                    base_budget_price: vm.addDecimals(item.unit_price),
                    supplier_price: item.purchase_requirement_item.unit_price,
                });
            });

            var account = vm.records.availabilityitem;
            $.each(account, function (y, item) {
                vm.record.accounts.push({
                    spac_description: item.spac_description,
                    code: item.item_code,
                    description: item.item_name,
                    amount: item.amount,
                    specific_action_id: item.budget_specific_action_id,
                    account_id: item.budget_account_id,
                    tax_id: "",
                });
            });
        }
    },
    methods: {
        continueWithoutAvailability() {
            const vm = this;
            bootbox.confirm({
                title: "Disponibilidad presupuestaria",
                message:
                    "¿Estás seguro de continuar sin disponibilidad presupuestaria? Esto podría afectar las cuentas destinadas a gastos, lo que significa que podrían excederse los límites presupuestarios establecidos.",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                    },
                },
                callback: function (result) {
                    if (result) {
                        $("#add_document").modal("show");
                    }
                },
            });
        },

        uploadComplete() {
            const vm = this;
            vm.loading = true;
            vm.createRecord("/purchase/budgetary_availability", 1);
            vm.loading = false;
        },

        showDocument() {
            return `${window.app_url}/${this.record.documentUrl}`;
        },

        /**
         * Método que formatea un número a una cantidad de decimales y lo
         * redondea redondea.
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        currencyFormat(number) {
            const vm = this;
            return number
                ? number.toFixed(vm.currency.decimal_places)
                : number.toFixed(2);
        },

        resetAccount() {
            const vm = this;

            vm.account_code = "";
            vm.account_amount = 0;
            vm.account_concept = "";
            vm.account_id = "";
            vm.account_tax_id = "";
            vm.specific_action_id = "";
            vm.editIndex = null;
            vm.selected_account_amount = 0;
        },
        async getSpecificActionDetail(id) {
            const response = await axios.get(
                `${window.app_url}/budget/detail-specific-actions/${id}`
            );
            return response.data;
        },
        async getAccountDetail(id) {
            const response = await axios.get(
                `${window.app_url}/budget/detail-accounts/${id}`
            );
            return response.data;
        },
        uploadFile(inputID, e) {
            let vm = this;
            const files = e.target.files;

            Array.from(files).forEach((file) => vm.addFile(file, inputID));
        },
        async addAccount() {
            const vm = this;

            if (!vm.has_budget) {
                if (
                    !vm.account_code &&
                    !vm.account_concept &&
                    !vm.account_amount
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "warning",
                        "screen-error",
                        "Debe indicar todos los datos solicitados"
                    );
                    return;
                }

                if (
                    parseFloat(vm.account_amount) >
                    parseFloat(vm.selected_account_amount)
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "warning",
                        "screen-error",
                        "El monto a comprometer es mayor al solicitado"
                    );

                    return;
                }

                vm.record.accounts.push({
                    spac_description: `-`,
                    code: vm.account_code,
                    description: vm.account_concept,
                    amount: vm.account_amount,
                    specific_action_id: "999",
                    account_id: "999",
                    tax_id: "999",
                    amountEdit: "",
                    operation: "",
                });
                bootbox.confirm({
                    title: "Agregar cuenta",
                    message: `Desea agregar otra cuenta?`,
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar',
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar',
                        },
                    },
                    callback: function (result) {
                        if (!result) {
                            $("#add_account").find(".close").click();
                        }

                        vm.specific_action_id = "";
                        vm.account_id = "";
                        vm.account_concept = "";
                        vm.account_amount = 0;
                        vm.account_tax_id = "";
                    },
                });
                return;
            }

            if (
                !vm.specific_action_id &&
                !vm.account_id &&
                !vm.account_amount &&
                !vm.account_tax_id
            ) {
                vm.showMessage(
                    "custom",
                    "Alerta!",
                    "warning",
                    "screen-error",
                    "Debe indicar todos los datos solicitados"
                );
                return;
            }

            if (
                parseFloat(vm.account_amount) >
                parseFloat(vm.selected_account_amount)
            ) {
                vm.showMessage(
                    "custom",
                    "Alerta!",
                    "warning",
                    "screen-error",
                    "El monto a comprometer es mayor al solicitado"
                );

                return;
            }

            let specificAction = {};
            let account = {};

            await vm
                .getSpecificActionDetail(vm.specific_action_id)
                .then((detail) => (specificAction = detail.record));

            await vm
                .getAccountDetail(vm.account_id)
                .then((detail) => (account = detail.record));

            if (vm.editIndex != null) {
                let amountEdit = vm.record.accounts[vm.editIndex]["amountEdit"];
                vm.record.accounts.splice(vm.editIndex, 1);
                //vm.record.tax_accounts.splice(vm.editIndex, 1);

                vm.record.accounts.push({
                    spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                    code: account.code,
                    description: account.denomination,
                    amount: vm.account_amount,
                    specific_action_id: vm.specific_action_id,
                    account_id: vm.account_id,
                    tax_id: vm.account_tax_id,
                    amountEdit: amountEdit,
                    operation: "",
                });

                $("#add_account").find(".close").click();

                vm.specific_action_id = "";
                vm.account_id = "";
                vm.account_concept = "";
                vm.account_amount = 0;
                vm.account_tax_id = "";
                vm.editIndex = null;
            } else {
                vm.record.accounts.push({
                    spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                    code: account.code,
                    description: account.denomination,
                    amount: vm.account_amount,
                    specific_action_id: vm.specific_action_id,
                    account_id: vm.account_id,
                    tax_id: vm.account_tax_id,
                });

                bootbox.confirm({
                    title: "Agregar cuenta",
                    message: `Desea agregar otra cuenta?`,
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancelar',
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirmar',
                        },
                    },
                    callback: function (result) {
                        if (!result) {
                            $("#add_account").find(".close").click();
                        }

                        vm.specific_action_id = "";
                        vm.account_id = "";
                        vm.account_concept = "";
                        vm.account_amount = 0;
                        vm.account_tax_id = "";
                    },
                });
            }
        },

        async getSpecificActions() {
            const vm = this;
            vm.loading = true;
            vm.specific_actions = [];
            vm.accounts = [];

            if (
                vm.record.date &&
                vm.record.source_document &&
                vm.record.institution_id
            ) {
                let year = vm.record.date.split("-")[0];
                let url = `${window.app_url}/budget/get-group-specific-actions/${year}/1/${vm.record.institution_id}`;
                await axios
                    .get(url)
                    .then((response) => {
                        vm.specific_actions = response.data;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            } else {
                $("#add_account").find(".close").click();
                bootbox.alert(
                    "Debe indicar los datos del compromiso antes de agregar cuentas"
                );
            }

            if (vm.editIndex != null) {
                vm.specific_action_id =
                    vm.record.accounts[vm.editIndex]["specific_action_id"];
            }

            vm.loading = false;
        },
        async getAccounts() {
            const vm = this;
            vm.loading = true;
            vm.accounts = [];

            if (vm.specific_action_id) {
                let specificActionId = vm.specific_action_id;
                let compromisedAt = vm.record.date;
                await axios
                    .get(
                        `${window.app_url}/budget/get-opened-accounts/${specificActionId}/${compromisedAt}`
                    )
                    .then((response) => {
                        if (response.data.result) {
                            vm.accounts = response.data.records;
                        }
                        if (
                            response.data.records.length === 1 &&
                            response.data.records[0].id === ""
                        ) {
                            vm.showMessage(
                                "custom",
                                "Alerta!",
                                "danger",
                                "screen-error",
                                `No existen cuentas aperturadas para esta acción específica o con saldo para la fecha
                                                                seleccionada`
                            );
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                    });
                if (vm.editIndex != null) {
                    vm.account_id =
                        vm.record.accounts[vm.editIndex]["account_id"];
                }
            }
            vm.isDisable();
            vm.loading = false;
        },
        /**
         * Obtiene las cuentas presupuestarias formuladas de la acción específica seleccionada
         *
         * @method   getAmountAccounts
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getAmountAccounts() {
            const vm = this;
            if (!vm.account_id) return;
            const result = vm.accounts.filter(
                (account) => account.id == vm.account_id
            );
            if (typeof result[0] !== "undefined") {
                vm.selected_account_amount = parseFloat(
                    result[0].amount
                ).toFixed(result[0].currency.decimal_places);

                if (vm.editIndex != null) {
                    vm.account_amount = vm.record.accounts[vm.editIndex].amount;
                    return;
                }

                vm.account_amount = parseFloat(result[0].amount).toFixed(
                    result[0].currency.decimal_places
                );
            }
        },
        /**
         * Método que permite habilitar/deshabilitar las opciones de las cuentas
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        isDisable() {
            const vm = this;
            let accountsTable = [{ id: "", text: "Seleccione..." }];
            for (let [index, acc] of vm.record.accounts.entries()) {
                accountsTable.push(acc);
            }
            for (let [index, acc] of vm.accounts.entries()) {
                for (let account of accountsTable) {
                    if (account.specific_action_id == vm.specific_action_id) {
                        let esc = document.getElementById("accounts");
                        if (acc.id == account.account_id) {
                            $(Object.values(esc.options)[index]).prop(
                                "disabled",
                                "disabled"
                            );
                        } else {
                            $(Object.values(esc.options)[index]).prop(
                                "disabled",
                                false
                            );
                        }
                    }
                }
            }
        },
        addFile(file, inputID) {
            if (!file.type.match("application/pdf")) {
                this.showMessage(
                    "custom",
                    "Error",
                    "danger",
                    "screen-error",
                    "Solo se permiten archivos pdf."
                );
                return;
            } else {
                this.files[inputID] = file;
                $("#status_" + inputID).show("slow");
            }
        },

        CalculateQtyPrice(qty_price) {
            return qty_price
                ? qty_price.toFixed(
                      this.currency ? this.currency.decimal_places : ""
                  )
                : 0;
        },

        addDecimals(value) {
            return parseFloat(value).toFixed(this.currency.decimal_places);
        },

        /**
         * Establece la cantidad de decimales correspondientes a la moneda que se maneja
         *
         * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        cualculateLimitDecimal() {
            var res = "0.";
            if (this.currency) {
                for (var i = 0; i < this.currency.decimal_places - 1; i++) {
                    res += "0";
                }
            }
            res += "1";
            return res;
        },

        async editAccount(index) {
            const vm = this;
            vm.editIndex = index;
            vm.specific_action_id =
                vm.record.accounts[index].specific_action_id;
        },

        deleteAccount(index) {
            let vm = this;
            bootbox.confirm({
                title: "Eliminar cuenta?",
                message: `Esta seguro de eliminar esta cuenta del compromiso actual?`,
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    },
                },
                callback: function (result) {
                    if (result) {
                        vm.record.accounts.splice(index, 1);
                        vm.accounts = [];
                        let p = vm.record.accounts;
                        vm.record.accounts = [];
                        vm.record.accounts = p;
                    }
                },
            });
        },

        /**
         * [CalculateTot Calcula el total del debe y haber del asiento contable]
         * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {[type]} r   [información del registro]
         * @param  {[type]} pos [posición del registro]
         */
        CalculateTot(item, pos) {
            const vm = this;
            vm.total = 0;
            // Objeto para almacenar las bases imponibles según el IVA
            vm.bases_imponibles = {};

            for (let i = vm.record_items.length - 1; i >= 0; i--) {
                let r = vm.record_items[i];
                let iva_percentage = r.history_tax_id
                    ? r.history_tax.percentage / 100
                    : 0.0;

                r["qty_price"] = r.quantity * r.unit_price;
                r["qty_iva_price"] = r.quantity * r.unit_price * iva_percentage;

                // Verificar si el porcentaje de IVA ya existe en el objeto
                // bases_imponibles
                if (!(iva_percentage in vm.bases_imponibles)) {
                    // Inicializar el total para el porcentaje de IVA
                    vm.bases_imponibles[iva_percentage] = 0;
                }
                // Acumular el total para el porcentaje de IVA
                vm.bases_imponibles[iva_percentage] += r["qty_price"];
                vm.total += r["qty_iva_price"] + r["qty_price"];
            }
        },

        /**
         * [getBudgetAvailable Consulta el saldo de la partida presupuestaria]
         * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @return {[type]} [description]
         */
        getBudgetAvailable() {
            const vm = this;
            // ---------------------------------------------------------
            // se consultara el saldo disponible de la cuenta
            // ---------------------------------------------------------
            axios
                .get(
                    `/budget/getBudgetAvailable/${vm.specific_action_id}/${vm.budget_item_id}`
                )
                .then((response) => {
                    this.budget_available = response.data.balance;
                });
        },

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
    computed: {
        record_tax: function () {
            if (this.records.tax && this.records.tax.histories) {
                return this.records.tax ? this.records.tax.histories[0] : null;
            }
        },
        currency_symbol: function () {
            return this.records.currency ? this.records.currency.symbol : "";
        },
        currency_decimal_places: function () {
            if (this.records.currency) {
                return this.records.currency.decimal_places;
            }
        },
        currency: function () {
            return this.records.currency ? this.records.currency : null;
        },
        currency_id: function () {
            return this.records.currency ? this.records.currency.id : null;
        },
        contracting_department: function () {
            if (this.records.purchase_requirements.contrating_department) {
                return this.records.purchase_requirements.contrating_department
                    .name;
            }
        },
        user_department: function () {
            if (this.records.purchase_requirements.user_department) {
                return this.records.purchase_requirements.user_department.name;
            }
        },
        purchase_supplier_object: function () {
            if (this.records.purchase_requirements.purchase_supplier_object) {
                return this.records.purchase_requirements
                    .purchase_supplier_object.name;
            }
        },
        fiscal_year: function () {
            if (this.records.purchase_requirements.fiscal_year) {
                return this.records.purchase_requirements.fiscal_year.year;
            }
        },
        description: function () {
            if (this.records.purchase_requirements.description) {
                return this.records.purchase_requirements.description;
            }
        },
        purchase_requirement: function () {
            if (this.records.purchase_requirement) {
                var r = [];
                r.push(this.records.purchase_requirement);
                return r;
            }
            return [];
        },
        purchase_requirement_items: function () {
            var pur_req_items = [];
            if (this.records.relatable) {
                for (var i = 0; i < this.records.relatable.length; i++) {
                    if (this.records.relatable[i].purchase_requirement_item) {
                        var item =
                            this.records.relatable[i].purchase_requirement_item;
                        item.tax_percentage = this.records.relatable[i]
                            .purchase_requirement_item.history_tax_id
                            ? this.records.relatable[i]
                                  .purchase_requirement_item.history_tax
                                  .percentage
                            : 0.0;
                        item.requirement_code =
                            this.records.relatable[
                                i
                            ].purchase_requirement_item.purchase_requirement.code;
                        item.qty_price =
                            this.records.relatable[i].purchase_requirement_item
                                .quantity *
                            this.records.relatable[i].unit_price;
                        item.qty_iva_price = this.records.relatable[i]
                            .purchase_requirement_item.history_tax_id
                            ? this.records.relatable[i]
                                  .purchase_requirement_item.quantity *
                              this.records.relatable[i]
                                  .purchase_requirement_item.unit_price *
                              (this.records.relatable[i]
                                  .purchase_requirement_item.history_tax
                                  .percentage /
                                  100)
                            : 0.0;
                        item.unit_price = this.records.relatable[i].unit_price;
                        pur_req_items.push(item);
                    }
                }
            }
            this.record_items = pur_req_items;
            this.CalculateTot();
            return pur_req_items;
        },
    },
};
</script>
