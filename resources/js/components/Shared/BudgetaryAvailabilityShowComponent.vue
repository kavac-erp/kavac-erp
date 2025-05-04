<template>
    <div>
        <button
            @click="addRecord('show_base_budget_' + id, route_show, $event)"
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
            :id="'show_base_budget_' + id"
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
                            Disponibilidad presupuestaria - INFORMACIÓN DE LOS REQUERIMIENTOS
                        </h6>
                    </div>
                    <!-- modal-body -->
                    <div class="modal-body">

                        <div v-if="records?.purchaseBudgetaryAvailabilityDocument && records?.purchaseBudgetaryAvailabilityDocument?.url" class="col-md-12">
                            <div class="form-group">
                                <strong>Ver Documento:</strong>
                                <div class="row" style="margin: 1px 0">
                                    <a
                                        :href="showDocument()" target="_blank"
                                        class="btn btn-primary btn-xs btn-icon btn-action btn-tooltip"
                                        data-toggle="tooltip" title="Ver documento que avala la modificación presupuestaria"
                                    >
                                        <i class="fa fa-file" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <v-client-table
                            :columns="column_requirements"
                            :data="purchase_requirement"
                            :options="table_option_requirements"
                        >
                            <div
                                slot="purchase_supplier_object.name"
                                slot-scope="props"
                                class="text-center"
                            >
                                <div
                                    v-if="
                                        props.row.purchase_supplier_object &&
                                        props.row.purchase_supplier_object.name
                                    "
                                >
                                    <strong
                                        v-if="
                                            props.row.purchase_supplier_object
                                                .type == 'B'
                                        "
                                    >
                                        Bienes
                                    </strong>
                                    <strong
                                        v-else-if="
                                            props.row.purchase_supplier_object
                                                .type == 'O'
                                        "
                                    >
                                        Obras
                                    </strong>
                                    <strong
                                        v-else-if="
                                            props.row.purchase_supplier_object
                                                .type == 'S'
                                        "
                                    >
                                        Servicios
                                    </strong>
                                    -
                                    {{
                                        props.row.purchase_supplier_object.name
                                    }}
                                </div>
                            </div>
                        </v-client-table>
                        <div class="row col-12">
                            <table
                                class="table table-striped table-hover"
                                style="
                                    margin-left: 1rem;
                                    margin-right: -1rem
                                "
                            >
                                <thead>
                                    <tr class="row">
                                        <th
                                            class="col-2"
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
                                            class="col-2"
                                            style="
                                                border: 1px solid #dee2e6;
                                                position: relative;
                                            "
                                        >
                                            Cantidad - Unidad de medida
                                        </th>
                                        <th
                                            class="col-2"
                                            style="
                                                border: 1px solid #dee2e6;
                                                position: relative;
                                            "
                                        >
                                            Precio Unitario sin IVA
                                        </th>
                                        <th
                                            class="col-1"
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
                                        :key="index" class="row"
                                    >
                                        <td
                                            style="border: 1px solid #dee2e6"
                                            class="col-2 text-center"
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
                                            class="col-2 text-left"
                                        >
                                            {{ x.technical_specifications }}
                                        </td>
                                        <td
                                            v-else
                                            style="border: 1px solid #dee2e6;"
                                            class="col-2 text-left"
                                        >
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6"
                                            class="col-2 text-center"
                                            v-if="
                                                x.measurement_unit
                                            "
                                        >
                                            {{ x.quantity }} {{
                                                x.measurement_unit.name
                                            }}
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6"
                                            class="col-2 text-right"
                                        >
                                            {{ addDecimals(x.unit_price) }}
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6"
                                            class="col-1 text-right"
                                        >
                                            <span align="right">
                                                {{
                                                    CalculateQtyPrice(
                                                        x.qty_price
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6"
                                            class="col-1 text-center"
                                        >
                                            <span align="center">
                                                {{
                                                    x.history_tax_id ?
                                                    x.history_tax.percentage
                                                    : 0.00
                                                }}%
                                            </span>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(base, iva) in bases_imponibles"
                                        :key="iva"
                                        class="row"
                                        style="background-color: rgb(242, 242, 242);"
                                    >
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-10 text-right">
                                            <b>Base imponible según alícuota {{ iva * 100 }}%</b>
                                        </td>
                                        <td style="border: 1px solid #dee2e6;" class="col-2 text-center">
                                            {{ currencyFormat(base) }}
                                        </td>
                                        <td style="border: 1px solid #dee2e6;" class="col-10 text-right">
                                            <b>Monto total del impuesto según alícuota {{ iva * 100 }}%</b>
                                        </td>
                                        <td style="border: 1px solid #dee2e6;" class="col-2 text-center">
                                            {{ currencyFormat(base * iva) }}
                                        </td>
                                    </tr>
                                    <tr
                                        class="row"
                                        style="background-color: rgba(0, 0, 0, 0.05) !important;"
                                    >
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-10 text-right"
                                        >
                                            <b>TOTAL {{ currency_symbol }}</b>
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-2"
                                        >
                                            <h6 align="center">
                                                {{
                                                    currencyFormat(
                                                        total, currency
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
                        <br>
                        <h6 class="card-title">Firmas autorizadas</h6>
                        <div class="row">
                            <div class="col-3">
                                <strong class="d-block">Preparado por</strong>
                                {{
                                    records.prepared_by
                                        ? records.prepared_by.payroll_staff
                                            ? records.prepared_by.payroll_staff
                                            .first_name +
                                            " " +
                                            records.prepared_by.payroll_staff
                                                .last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Revisado por</strong>
                                {{
                                    records.reviewed_by
                                        ? records.reviewed_by.payroll_staff
                                            ? records.reviewed_by.payroll_staff
                                                .first_name +
                                            " " +
                                            records.reviewed_by.payroll_staff
                                                .last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Verificado por</strong>
                                {{
                                    records.verified_by
                                        ? records.verified_by.payroll_staff
                                            ? records.verified_by.payroll_staff
                                                .first_name +
                                            " " +
                                            records.verified_by.payroll_staff
                                                .last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Firmado por</strong>
                                {{
                                    records.first_signature
                                        ? records.first_signature.payroll_staff
                                            ? records.first_signature
                                                .payroll_staff.first_name +
                                            " " +
                                            records.first_signature
                                                .payroll_staff.last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Firmado por</strong>
                                {{
                                    records.second_signature
                                        ? records.second_signature.payroll_staff
                                            ? records.second_signature
                                                .payroll_staff.first_name +
                                            " " +
                                            records.second_signature
                                                .payroll_staff.last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                        </div>
                        <!-- Cuentas presupuestarias de gastos -->
                        <h6 class="text-center">
                            Cuentas presupuestarias de gastos
                        </h6>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="col-md-3">Cuenta</th>
                                    <th class="col-md-3">Nombre</th>
                                    <th class="col-md-3">Descripción</th>
                                    <th class="col-md-3">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(account, index) in records.availabilityitem" :key="index">
                                    <td class="col-md-3 text-center">
                                        {{ account.item_code }}
                                    </td>
                                    <td class="col-md-3 text-center">
                                        {{ account.item_name }}
                                    </td>
                                    <td class="col-md-3 text-center">
                                        {{ account.description }}
                                    </td>
                                    <td class="col-md-3 text-center">
                                        {{ account.amount }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Fianl de Cuentas presupuestarias de gastos -->
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
            records: [],
            column_requirements: [
                "code",
                "description",
                "fiscal_year.year",
                "contrating_department.name",
                "user_department.name",
                "purchase_supplier_object.name",
            ],
            table_option_requirements: {
                pagination: { edge: true },
                highlightMatches: true,
                texts: {
                    filter: "Buscar:",
                    filterBy: "Buscar por {column}",
                    count: " ",
                    first: "PRIMERO",
                    last: "ÚLTIMO",
                    limit: "Registros",
                    noResults: "No existen registros",
                },
                sortIcon: {
                    is: "fa-sort cursor-pointer",
                    base: "fa",
                    up: "fa-sort-up cursor-pointer",
                    down: "fa-sort-down cursor-pointer",
                },
            },
            sub_total: 0,
            tax: 0,
            total: 0,
            record_items: [],
        };
    },
    created() {
        this.table_option_requirements.headings = {
            code: "Código",
            description: "Descripción",
            "fiscal_year.year": "Año fiscal",
            "contrating_department.name": "Departamento contratante",
            "user_department.name": "Departamento Usuario",
            "purchase_supplier_object.name": "Tipo",
        };
        this.table_option_requirements.columnsClasses = {
            code: "col-xs-1 text-center",
            description: "col-xs-4",
            "fiscal_year.year": "col-xs-1 text-center",
            "contrating_department.name": "col-xs-2",
            "user_department.name": "col-xs-2",
            "purchase_supplier_object.name": "col-xs-2",
        };
    },
    methods: {
        /**
        * Método que formatea un número a una cantidad de decimales y lo
        * redondea redondea.
        *
        * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
        */
        currencyFormat(number) {
            const vm = this;
            return (number)
                ? (number.toFixed(vm.currency.decimal_places))
                : (number.toFixed(2));
        },

        addDecimals(value) {
            return parseFloat(value).toFixed(this.currency_decimal_places);
        },

        CalculateQtyPrice(qty_price) {
            const vm = this;
            return (qty_price)
                ? (qty_price.toFixed(vm.currency.decimal_places))
                : (qty_price.toFixed(2));
        },

        /**
         * Calcula el total del debe y haber del asiento contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        CalculateTot() {
            const vm = this;
            vm.total = 0;
             // Objeto para almacenar las bases imponibles según el IVA
            vm.bases_imponibles = {};

            for (let i = vm.record_items.length - 1; i >= 0; i--) {
                let r = vm.record_items[i];
                let iva_percentage = r.history_tax_id ?
                    r.history_tax.percentage / 100 : 0.00;

                r['qty_price'] = r.quantity * r.unit_price;
                r['qty_iva_price'] = r.quantity * r.unit_price * iva_percentage;

                // Verificar si el porcentaje de IVA ya existe en el objeto
                // bases_imponibles
                if (!(iva_percentage in vm.bases_imponibles)) {
                    // Inicializar el total para el porcentaje de IVA
                    vm.bases_imponibles[iva_percentage] = 0;
                }
                // Acumular el total para el porcentaje de IVA
                vm.bases_imponibles[iva_percentage] += r['qty_price'];
                vm.total += (r['qty_iva_price'] + r['qty_price']);
            }
        },

        /**
         * Establece la cantidad de decimales correspondientes a la moneda que se maneja
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
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

        showDocument() {
            return `${window.app_url}/${this.records?.purchaseBudgetaryAvailabilityDocument?.url}`;
        }
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
        purchase_requirement_items: function() {
            var pur_req_items = [];
            if (this.records.relatable) {
                for (var i = 0; i < this.records.relatable.length; i++) {
                    if (this.records.relatable[i].purchase_requirement_item) {
                        var item = this.records.relatable[i].purchase_requirement_item;
                        item.tax_percentage = this.records.relatable[i].purchase_requirement_item.history_tax_id ?
                        this.records.relatable[i].purchase_requirement_item.history_tax.percentage :
                        0.00;
                        item.requirement_code = this.records.relatable[i].purchase_requirement_item.purchase_requirement.code;
                        item.qty_price = this.records.relatable[i].purchase_requirement_item.quantity *
                            this.records.relatable[i].unit_price;
                        item.qty_iva_price = this.records.relatable[i].purchase_requirement_item.history_tax_id ?
                        this.records.relatable[i].purchase_requirement_item.quantity *
                        this.records.relatable[i].purchase_requirement_item.unit_price *
                        (this.records.relatable[i].purchase_requirement_item.history_tax.percentage / 100) :
                        0.00;
                        item.unit_price = this.records.relatable[i].unit_price;
                        pur_req_items.push(item)
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
