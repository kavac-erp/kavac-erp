<template>
    <section>
        <div class="card-body">
            <purchase-show-errors ref="purchaseShowError" />
            <div class="row">
                <div class="col-3" id="helpCurrency">
                    <div class="form-group is-required">
                        <label class="control-label" for="">
                            Fecha de generación
                        </label>
                        <br />
                        <input
                            type="date"
                            class="form-control fiscal-year-restrict"
                            v-model="record.date"
                        />
                    </div>
                </div>
                <div class="col-3" id="helpSuppliers">
                    <div class="form-group is-required">
                        <label class="control-label" for="suppliers">
                            Proveedor
                        </label>
                        <br />
                        <select2
                            :options="suppliers"
                            id="suppliers"
                            v-model="purchase_supplier_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-3" id="helpCurrency">
                    <div class="form-group is-required">
                        <label class="control-label" for="currencies">
                            Tipo de moneda
                        </label>
                        <br />
                        <select2
                            :options="currencies"
                            id="currencies"
                            v-model="currency_id"
                        ></select2>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <h6 class="card-title text-center">
                        Lista de requerimientos en espera por ser
                        procesados / comprados
                    </h6>
                </div>
                <div class="col-12" id="helpListRequirements">
                    <v-client-table
                        :columns="columns"
                        :data="filterRelatable"
                        :options="table_options"
                    >
                        <div
                            slot="purchase_requirement.date"
                            slot-scope="props"
                            class="text-center"
                        >
                            <div class="d-inline-flex">
                                {{ format_date(props.row.date) }}
                            </div>
                        </div>
                        <div
                            slot="requirement_status"
                            slot-scope="props"
                            class="text-center"
                        >
                            <div class="d-inline-flex">
                                <span
                                    class="badge badge-info"
                                    v-show="
                                        props.row.requirement_status
                                            == 'PROCESSED'
                                    "
                                >
                                    <strong>PROCESADO</strong>
                                </span>
                            </div>
                        </div>
                        <div slot="id" slot-scope="props" class="text-center">
                            <div
                                class="feature-list-content-left mr-2"
                                v-if="record.currency"
                            >
                                <label class="custom-control custom-checkbox">
                                    <p-check
                                    class="p-icon p-smooth p-plain p-curve"
                                    color="primary-o"
                                    :value="'_' + props.row.id"
                                    :id="'requirement_check_' + props.row.id"
                                    :checked="
                                        indexOf(
                                            base_budget_list, props.row.id, true
                                        )
                                    "
                                    @change="
                                        recordCheck(props.row, true, $event)
                                    "
                                    >
                                        <i slot="extra" class="icon fa fa-check"></i>
                                    </p-check>
                                </label>
                            </div>
                        </div>
                    </v-client-table>
                </div>
                <div class="col-12">
                    <h6 class="card-title text-center">
                        Lista de Productos
                    </h6>
                    <v-client-table
                        :columns="columns2"
                        :data="record_items"
                        :options="table2_options"
                    >
                        <div
                            slot="technical_specifications"
                            slot-scope="props"
                            class="text-left"
                        >
                            {{
                                props.row.purchase_requirement_item
                                    .technical_specifications
                                ? props.row.purchase_requirement_item
                                    .technical_specifications
                                : ''
                            }}
                        </div>
                        <div slot="quantity" slot-scope="props">
                            <input
                                type="text"
                                min="0"
                                :id="'requirement_item_quantity_' + props.row.id"
                                :name="'requirement_item_quantity_' + props.row.id"
                                oninput="
                                    this.value=this.value.replace(/[^0-9.]/g,'');
                                "
                                v-model="record_items[props.index - 1].quantity"
                                class="form-control"
                                @input="
                                    CalculateTot(
                                        record_items[props.index - 1],
                                        props.index - 1
                                    )
                                "
                            />
                        </div>
                        <div slot="unit_price" slot-scope="props">
                            <input
                                type="text"
                                min="0.1"
                                :id="'requirement_item_price_' + props.row.id"
                                :name="'requirement_item_price_' + props.row.id"
                                oninput="
                                    this.value=this.value.replace(/[^0-9.]/g,'');
                                "
                                v-model="record_items[props.index - 1].unit_price"
                                class="form-control"
                                :step="cualculateLimitDecimal()"
                                @input="
                                    CalculateTot(
                                        record_items[props.index - 1],
                                        props.index - 1
                                    )
                                "
                            />
                        </div>
                        <div slot="qty_price" slot-scope="props">
                            <span>
                                {{ CalculateQtyPrice(
                                    record_items[props.index - 1].qty_price)
                                }}
                            </span>
                        </div>
                        <div slot="iva" slot-scope="props">
                            <span>
                                {{
                                    props.row.purchase_requirement_item
                                        .history_tax.percentage
                                }}%
                            </span>
                        </div>
                        <div slot="id" slot-scope="props" class="text-center">
                            <div
                                class="feature-list-content-left mr-2"
                                v-if="record.currency"
                            >
                                <label class="custom-control custom-checkbox">
                                    <p-check
                                        class="p-icon p-smooth p-plain p-curve"
                                        color="primary-o"
                                        :value="
                                            '_' + props.row.id + props.row.name
                                        "
                                        :id="
                                            'requirement_item_check_' + props.row.id
                                        "
                                        :checked="
                                            indexOfItems(base_budget_list, props.row.id, true)
                                        "
                                        @change="
                                            addToListItem(props.row, props.row.id, $event)
                                        "
                                    >
                                        <i slot="extra" class="icon fa fa-check"></i>
                                    </p-check>
                                </label>
                            </div>
                        </div>
                    </v-client-table>
                </div>
                <div v-if="record_items.length > 0" class="col-12">
                    <table
                        class="table table-striped table-hover"
                        style="border: 1px solid #dee2e6"
                    >
                        <tbody v-for="(base, iva) in bases_imponibles" :key="iva">
                            <tr style="background-color: rgb(242, 242, 242);">
                                <td class="w-75 text-right font-weight-bold">
                                    Base imponible según alícuota {{ iva * 100 / 100 }}%
                                </td>
                                <td class="w-25 border text-center">
                                    {{ currencyFormat(base) }}
                                </td>
                            </tr>
                            <tr style="background-color: rgb(242, 242, 242);">
                                <td class="w-75 text-right font-weight-bold">
                                    Monto total del impuesto
                                    según alícuota {{ iva * 100 / 100 }}%
                                </td>
                                <td class="w-25 border text-center">
                                    {{ currencyFormat(base * iva / 100) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="w-75">
                                    <h6 class="text-right">
                                        TOTAL {{ currency_symbol }}
                                    </h6>
                                </td>
                                <td class="text-center">
                                    <h6>
                                        {{
                                            total.toFixed(record.currency
                                            ? currency_decimal_places
                                            : 2)
                                        }}
                                    </h6>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <buttonsDisplay
                display="false"
            />
        </div>
    </section>
</template>
<script>
export default {
    props: {
        record_edit: {
            type: Object,
            default: function () {
                return null;
            },
        },
        base_budget_edit: {
            type: Array,
            default: function () {
                return null;
            },
        },
        date: {
            type: String,
            default: "",
        },
        tax: {
            type: Object,
            default: function () {
                return null;
            },
        },
        tax_units: {
            type: Object,
            default: function () {
                return null;
            },
        },
        record_budgets: {
            type: Array,
            default: function () {
                return [];
            },
        },
        suppliers: {
            type: Array,
            default: function () {
                return [];
            },
        },
    },
    data() {
        return {
            record: {
                purchase_supplier_id: "",
                purchase_supplier_object: "",
                currency: null,
                date: '',
            },
            record_items: [],
            columns: [
                "purchase_requirement.code",
                "purchase_requirement.date",
                "purchase_requirement.description",
                "purchase_requirement.fiscal_year.year",
                "purchase_requirement.user_department.name",
                "currency.name",
                "id",
            ],
            columns2: [
                "requirement_code",
                "name",
                "technical_specifications",
                "quantity",
                "unit_price",
                "qty_price",
                "iva",
                "id",
            ],
            table2_options: {
                pagination: {
                    edge: true
                },
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
            base_budget_list: [],
            record_base_budgets: [],
            base_copy_budge_list: [],
            use: 0,
            copy_item_list: [],
            copy_record: [],
            base_item_list: [],
            base_item_list_deleted: [],
            base_budget_list_deleted: [],
            sub_total: 0,
            tax_value: 0,
            total: 0,
            currencies: [],
            currency_id: "",
            purchase_supplier_id: "",
            convertion_list: [],
            filterRelatable: [],
            load_data_edit: false,
            files: {
                Acta_de_inicio: null,
                Invitación_de_la_empresa: null,
                Proforma_o_Cotización: null,
            },
            base_budget_id: "",
        };
    },
    created() {
        this.getCurrencies();
        this.table_options.headings = {
            "purchase_requirement.date": "Fecha de generación",
            "purchase_requirement.code": "Código de requerimiento",
            "purchase_requirement.description": "Descripción",
            "purchase_requirement.fiscal_year.year": "Año fiscal",
            "purchase_requirement.user_department.name": "Departamento Usuario",
            "currency.name": "Moneda",
            id: "Acción",
        };
        this.table_options.columnsClasses = {
            "purchase_requirement.date": "col-xs-1 text-center",
            "purchase_requirement.code": "col-xs-1 text-center",
            "purchase_requirement.description": "col-xs-2",
            "purchase_requirement.fiscal_year.year": "col-xs-1 text-center",
            "purchase_requirement.user_department.name": "col-xs-2",
            "currency.name": "col-xs-2 text-center",
            id: "col-xs-1",
        };
        this.table2_options.headings = {
            requirement_code: "Código de requerimiento",
            requirement_date: "Fecha de generación",
            name: "Nombre",
            technical_specifications: "Especificaciones técnicas",
            quantity: "Cantidad",
            unit_price: "Precio unitario sin IVA",
            qty_price: "Cantidad * Precio unitario",
            iva: "IVA",
            id: "Acción",
        };
        this.table2_options.columnsClasses = {
            requirement_date: "col-xs-1 text-center",
            requirement_code: "col-xs-1 text-center",
            technical_specifications: "col-xs-2 text-center",
            name: "col-xs-2",
            quantity: "col-xs-1",
            unit_price: "col-xs-2",
            qty_price: "col-xs-2 text-center",
            iva: "col-xs-2 text-center",
            id: "col-xs-1",
        };
        this.table2_options.filterable = [];
    },
    mounted() {
        const vm = this;
        vm.copy_record = vm.record_budgets;
        vm.record_base_budgets = vm.record_budgets;
        vm.filterRelatable = [];
        vm.record_base_budgets.forEach((element) => {
        if(element.availability == "AP") {
            var newArray = element.relatable.filter(function (el) {
                if (el.purchase_requirement_item != null) {
                    return el.purchase_requirement_item.Quoted == null ;
                }
            });
            localStorage.setItem(element.id, JSON.stringify(newArray));
            element.relatable = newArray;
                vm.filterRelatable.push(element);
            }
        });
        vm.record_base_budgets = [];
        vm.record_base_budgets = vm.filterRelatable;
        if (vm.record_edit) {
            vm.load_data_edit = true;
            vm.currency_id = vm.record_edit.currency_id;
            vm.purchase_supplier_id = vm.record_edit.purchase_supplier_id;
            var prices = [];
            var quantities = [];
            const dataArr = new Set(vm.base_budget_edit);
            const bb_ids = vm.base_budget_edit.map((a) => a.id);
            for (var i = 0; i < vm.record_edit.relatable.length; i++) {
                prices[vm.record_edit.relatable[i].purchase_requirement_item_id] =
                vm.record_edit.relatable[i].unit_price;
                quantities[vm.record_edit.relatable[i].purchase_requirement_item_id] =
                vm.record_edit.relatable[i].quantity;
            }
            vm.record_edit.relatable.forEach((element) => {
                let bb = element.purchase_requirement_item.purchase_requirement.purchase_base_budget;
                if (bb_ids.includes(bb.id)) {
                    vm.recordCheck(bb, false);
                    var actual_record = element.purchase_requirement_item.purchase_requirement.purchase_base_budget;
                    var record = element.purchase_requirement_item.purchase_requirement.purchase_base_budget;
                    for (var i = 0; i < record.relatable.length; i++) {
                        record.relatable[i].name =
                        record.relatable[i].purchase_requirement_item.name;
                        record.relatable[i].id =
                        record.relatable[i].purchase_requirement_item.id;
                        record.relatable[i].requirement_id =
                        record.relatable[i].purchase_requirement_item.purchase_requirement.id;
                        record.relatable[i].quantity = quantities ?
                        (quantities[record.relatable[i].id] > 0 ?
                            quantities[record.relatable[i].id]
                            :record.relatable[i].purchase_requirement_item.quantity)
                        :record.relatable[i].purchase_requirement_item.quantity;

                        if (record.relatable[i].purchase_requirement_item.measurement_unit) {
                            record.relatable[i].measurement_unit_acronym =
                            record.relatable[i].purchase_requirement_item
                                .measurement_unit.acronym;
                        }
                        record.relatable[i].active = true;
                        record.relatable[i].requirement_code = record.relatable[i]
                            .purchase_requirement_item.purchase_requirement.code;
                        record.relatable[i].unit_price = prices
                        ? (prices[record.relatable[i].id] > 0
                        ? prices[record.relatable[i].id]
                            :record.relatable[i].purchase_requirement_item.unit_price)
                        :record.relatable[i].purchase_requirement_item.unit_price;
                        if (record.relatable[i].unit_price == undefined) {
                            record.relatable[i].unit_price = 1;
                        }
                        else {
                            record.relatable[i].qty_price = record.relatable[i]
                                .quantity * record.relatable[i].unit_price;
                            record.relatable[i].tax_percentage = record.relatable[i]
                                .purchase_requirement_item.history_tax_id
                                ? record.relatable[i].purchase_requirement_item
                                .history_tax.percentage : 0.00;
                            record.relatable[i].iva = record.relatable[i]
                                .quantity * record.relatable[i].unit_price
                                * record.relatable[i].purchase_requirement_item
                                    .history_tax_id ? (parseFloat(record.relatable[i]
                                    .purchase_requirement_item.history_tax
                                        .percentage) / 100) : 0.00;
                        }
                    }
                    vm.base_budget_list.push(actual_record);
                    vm.record_items = vm.record_items.concat(record.relatable);
                }
            });
        } else {
            vm.base_budget_id = vm.record_base_budgets.length > 0
                ? vm.record_base_budgets[0].id : [];
        }
    },
    methods: {
        reset() {
            const vm = this;
            vm.record_items = [];
            vm.base_budget_list = [];
            vm.base_copy_budge_list = [];
            vm.base_budget_list_deleted = [];
            vm.record = {
                purchase_supplier_id: "",
                currency: "",
                date: "",
            };
            vm.purchase_supplier_id = "";
            vm.currency_id = "";
            vm.sub_total = 0;
            vm.tax_value = 0;
            vm.total = 0;
            vm.getCurrencies();
            this.$refs.purchaseShowError.reset();
        },

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

        uploadFile(inputID, e) {
            let vm = this;
            const files = e.target.files;
            Array.from(files).forEach((file) => vm.addFile(file, inputID));
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
            }
            else {
                this.files[inputID] = file;
                $("#status_" + inputID).show("slow");
            }
        },

        /**
         * Truncar y redondear una cifra según el número pasado como segundo
         * parámetro del método toFixed().
         */
        addDecimals(value) {
            return parseFloat(value).toFixed(this.currency_decimal_places);
        },

        indexOfItems(list, id, returnBoolean) {
            var index = 0;
            for (let value of list) {
                for (var i = value.relatable.length - 1; i >= 0; i--) {
                    if (value.relatable[i].id == id) {
                        let resp = new Array(index, i);
                        return returnBoolean ? true : resp;
                    }
                }
                index++;
            }
            return returnBoolean ? false : -1;
        },

        indexOf(list, id, returnBoolean) {
            for (var i = list.length - 1; i >= 0; i--) {
                if (list[i].id == id) {
                    return returnBoolean ? true : i;
                }
            }
            return returnBoolean ? false : -1;
        },

        recordCheck(record, edit = true, event) {
            const vm = this;
            axios.get(
                "/purchase/get-convertion/" +
                vm.currency_id +
                "/" +
                record.currency_id
            )
            .then((response) => {
                if (record.currency_id != vm.currency_id && !response.data.record) {
                    if (
                        $("#requirement_check_" + record.id + " input:checkbox").prop(
                                "checked"
                        )
                    ) {
                        vm.showMessage(
                            "custom",
                            "Error",
                            "danger",
                            "screen-error",
                            "Imposible realizar la conversión de " +
                            vm.record.currency.name +
                            " a " +
                            record.currency.name +
                            ". Revisar conversiones configuradas en el sistema."
                        );
                        $("#requirement_check_" + record.id + " input:checkbox").prop(
                            "checked",
                            false
                        );
                    }
                }
                else {
                    vm.convertion_list.push(response.data.record);
                    if (edit) {
                        vm.addToList(record);
                    }
                }
            });
        },

        /**
         *
         * @author Francisco Escala <Fjescala@gmail.com >
         * @param        {[type]} record         [información del registro]
         * @param        {[type]} pos [posición del registro]
         */
        addToListItem: function (record, id, event) {
            const vm = this;
            if (event) {
                $("#requirement_item_price_" + id).prop("disabled", false);
            }
            else {
                $("#requirement_item_price_" + id).prop("disabled", true);
            }
            if (vm.use != 0) {
                return;
            }
            else {
                vm.use = 1;
            }
            var np = vm.indexOfItems(vm.base_budget_list, record.id);
            if (np != -1) {
                var indexA = np[0];
                var indexb = np[1];
                var index_delete = [];
                if (vm.base_budget_list[indexA].relatable.length == 1) {
                    var relatable_id = vm.base_budget_list[indexA]
                        .relatable[indexb].relatable_id;
                    vm.base_budget_list_deleted.push(vm.base_budget_list[indexA]);
                    vm.base_budget_list[indexA].relatable.splice(indexb, 1);
                    vm.base_budget_list.splice(indexA, 1);
                    const to_delete = [relatable_id];
                    const newItems = vm.record_items.filter(function (item) {
                        return !to_delete.includes(item.relatable_id);
                    });
                    vm.record_items = [];
                    setTimeout(function () {
                        newItems.forEach((element) => {
                            vm.record_items.push(element);
                            $("#requirement_item_check_" + record.id + " input:checkbox").prop("checked");
                        });
                    }, 500);
                }
                else {
                    var pos = vm.indexOf(
                        vm.record_items,
                        vm.base_budget_list[indexA].relatable[indexb].id
                    );
                    vm.record_items[pos].qty_price = 0;
                    vm.record_items[pos].iva = 0;
                    vm.record_items[pos].unit_price = 0;
                    vm.base_budget_list[indexA].relatable.splice(indexb, 1);
                }
            }
            else {
                var pos = vm.indexOf(vm.base_budget_list, record.relatable_id);
                vm.base_budget_list[pos].relatable.push(record);
            }
            vm.CalculateTot();
            vm.use = 0;
        },

        addToList: function (record, prices, quantities) {
            const vm = this;
            if (vm.use != 0) {
            return;
            } else {
            vm.use = 1;
        }
        var pos = vm.indexOf(vm.base_budget_list, record.id);
        // se agregan a la lista a guardar
        if (pos == -1) {
            const to_include = [record.id];
            // saca de la lista de registros eliminar
            var index = vm.indexOf(vm.base_budget_list_deleted, record.id);
            record.relatable = JSON.parse(localStorage.getItem(record.id) || "[]");
            if (index != -1) {
                vm.base_budget_list_deleted.splice(index, 1);
            }
            for (var i = 0; i < record.relatable.length; i++) {
                record.relatable[i].name = record.relatable[i].purchase_requirement_item.name;
                record.relatable[i].id = record.relatable[i].purchase_requirement_item.id;
                record.relatable[i].requirement_id = record.relatable[i].purchase_requirement_item.purchase_requirement.id;
                record.relatable[i].quantity = quantities ?
                (quantities[record.relatable[i].id] > 0
                ? quantities[record.relatable[i].id]
                    :record.relatable[i].purchase_requirement_item.quantity)
                :record.relatable[i].purchase_requirement_item.quantity;

                if (record.relatable[i].purchase_requirement_item.measurement_unit) {
                    record.relatable[i].measurement_unit_acronym = record.relatable[i].purchase_requirement_item.measurement_unit.acronym;
                }
                record.relatable[i].active = true;
                record.relatable[i].requirement_code = record.relatable[i].purchase_requirement_item.purchase_requirement.code;
                record.relatable[i].unit_price = prices ? (prices[record.relatable[i].id] ?
                    prices[record.relatable[i].id]
                    :record.relatable[i].purchase_requirement_item.unit_price)
                :record.relatable[i].purchase_requirement_item.unit_price;
            }
            if (record.relatable.length > 0) {
                if (typeof vm.copy_item_list[record.id] != "undefined") {
                    if (vm.copy_item_list[record.id].length > 0) {
                    }
                    else {
                        vm.copy_item_list[record.id] = record.relatable;
                    }
                    }
                else {
                    if (record.relatable.length > 0) {
                    vm.copy_item_list[record.id] = record.relatable;
                    }
                }
            }
            vm.base_budget_list.push(record);
            vm.base_item_list.push(record.relatable);
            vm.record_items = vm.record_items.concat(record.relatable);
        }
        else {
            // se sacan de la lista a guardar
            var record_copy = vm.base_budget_list.splice(pos, 1)[0];
            var pos = vm.indexOf(vm.base_budget_list_deleted, record_copy.id);
            // agrega a la lista de registros a eliminar
            if (pos == -1) {
                vm.base_budget_list_deleted.push(record_copy);
            }
            if (record.relatable.length < 1) {
                const to_include = [record.id];
                const newRecord = vm.copy_record.filter(function (item) {
                return to_include.includes(item.id);
            });
            record = newRecord[0];
            const to_delete = [record.relatable[0].id];
            const newItems = vm.record_items.filter(function (item) {
                return !to_delete.includes(item.relatable_id);
            });
            vm.record_items = newItems;
            }
            else {
                const to_delete = [record.id];
                const newItems = vm.record_items.filter(function (item) {
                return !to_delete.includes(item.relatable_id);
                });
                vm.record_items = [];
                //Usando la milagrosa
                setTimeout(function () {
                newItems.forEach((element) => {
                    vm.record_items.push(element);$(
                        "#requirement_item_check_" + record.id
                        + " input:checkbox").prop("checked"
                    );
                });
                vm.CalculateTot();
                }, 500);
            }
        }
        vm.CalculateTot();
        vm.use = 0;
        },

        /**
         * [CalculateTot Calcula el total del debe y haber del asiento contable]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @author Argenis Osorio <aosorio@cenditel.gob.ve >
         * @param    {[type]} r     [información del registro]
         * @param    {[type]} pos [posición del registro]
         */
        CalculateTot(item, pos) {
            const vm = this;
            vm.total = 0;
            vm.bases_imponibles = {};
            for (var i = vm.record_items.length - 1; i >= 0; i--) {
                var r = vm.record_items[i];
                let iva_percentage = r.purchase_requirement_item.history_tax_id ?
                r.purchase_requirement_item.history_tax.percentage : 0.00;
                r['qty_price'] = r.quantity * r.unit_price;
                r['iva'] = r.quantity * r.unit_price * (iva_percentage / 100);

                /* Verificar si el porcentaje de IVA ya existe en el objeto
                bases_imponibles*/
                if (!(iva_percentage in vm.bases_imponibles)) {
                    // Inicializar el total para el porcentaje de IVA
                    vm.bases_imponibles[iva_percentage] = 0;
                }
                // Acumular el total para el porcentaje de IVA
                vm.bases_imponibles[iva_percentage] += r['qty_price'];
                vm.total += (r['iva'] + r['qty_price']);
            }
        },

        CalculateQtyPrice(qty_price) {
            const vm = this;
            return (qty_price)
                ? (qty_price.toFixed(vm.currency.decimal_places))
                : (qty_price.toFixed(2));
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

        /**
         * Método que permite crear o actualizar un registro
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         */
        createRecord() {
            /** Se obtiene y da formato para enviar el archivo a la ruta */
            const vm = this;
            var stop = false;
            let route = vm.route_list;
            var formData = new FormData();

            /* Validación de que el total de la Cotización sea mayor que 0 */
            if (vm.total == 0) {
                vm.showMessage(
                    "custom",
                    "Error",
                    "danger",
                    "screen-error",
                    "El Total de la cotización no puede ser cero"
                );
                stop = true;
            } else {
                /* Validación de que el total de la Cotización sea menor o igual que
                el total del Presupuesto base asociado */
                axios.get("/purchase/base_budget/" + vm.base_budget_id)
                .then((response) => {
                    var query_total = parseFloat(response.data.records.subtotal);

                    if (parseFloat(vm.total.toFixed(2)) > query_total.toFixed(2)) {
                        vm.showMessage(
                            "custom",
                            "Error",
                            "danger",
                            "screen-error",
                            "El total de la Cotización no puede ser mayor que el"
                            + " total del Presupuesto base asociado"
                        );
                        stop = true;
                    } else {
                        for (var i = 0; i < vm.base_budget_list.length; i++) {
                            var relatable = JSON.parse(
                                localStorage.getItem(vm.base_budget_list[i].id) || "[]"
                            );

                            if (vm.base_budget_list[i].relatable.length == relatable.length) {
                                vm.base_budget_list[i].status = "QUOTED";
                            }
                            else {
                                vm.base_budget_list[i].status = "PARTIALLY_QUOTED";
                            }

                            vm.base_budget_list[i].relatable.forEach((element) => {
                                if (element["unit_price"] == 0) {
                                    vm.showMessage(
                                        "custom",
                                        "Error",
                                        "danger",
                                        "screen-error",
                                        "Imposible realizar la cotizacion el item "
                                        + element.purchase_requirement_item.name
                                        + ". tiene un precio de 0 ."
                                    );
                                    stop = true;
                                }
                            });
                        }

                        if (stop) {
                            return;
                        }

                        if (vm.files["Acta_de_inicio"]) {
                            formData.append(
                                "file_1",
                                vm.files["Acta_de_inicio"],
                                vm.files["Acta_de_inicio"]
                                    ? vm.files["Acta_de_inicio"].name : ""
                            );
                        }

                        if (vm.files["Invitación_de_la_empresa"]) {
                            formData.append(
                                "file_2",
                                vm.files["Invitación_de_la_empresa"],
                                vm.files["Invitación_de_la_empresa"]
                                    ? vm.files["Invitación_de_la_empresa"].name
                                    : ""
                            );
                        }

                        if (vm.files["Proforma_o_Cotización"]) {
                            formData.append(
                                "file_3",
                                vm.files["Proforma_o_Cotización"],
                                vm.files["Proforma_o_Cotización"]
                                ? vm.files["Proforma_o_Cotización"].name
                                : ""
                            );
                        }

                        formData.append("purchase_supplier_id", vm.purchase_supplier_id);
                        formData.append("date", vm.record.date);
                        formData.append("currency_id", vm.currency_id);
                        formData.append("subtotal", vm.sub_total);
                        formData.append("base_budget_list", JSON.stringify(vm.base_budget_list));
                        formData.append("record_items", JSON.stringify(vm.record_items));
                        vm.loading = true;

                        if (!vm.record_edit) {
                            axios.post("/purchase/quotation", formData, {
                                headers: {
                                    "Content-Type": "multipart/form-data",
                                },
                            })
                            .then((response) => {
                                vm.showMessage("store");
                                vm.loading = false;
                                location.href = `${window.app_url}/purchase/quotation`;
                            })
                            .catch((error) => {
                                vm.errors = [];
                                if (typeof error.response != "undefined") {
                                    for (var index in error.response.data.errors) {
                                        if (error.response.data.errors[index]) {
                                            vm.errors.push(error.response.data.errors[index][0]);
                                        }
                                    }
                                }
                                vm.$refs.purchaseShowError.refresh();
                                vm.loading = false;
                            });
                        }
                        else {
                            formData.append(
                                "list_to_delete",
                                JSON.stringify(vm.base_budget_list_deleted)
                            );
                            axios.post("/purchase/quotation/" + vm.record_edit.id, formData, {
                                headers: { "Content-Type": "multipart/form-data" },
                            })
                            .then((response) => {
                                vm.showMessage("update");
                                vm.loading = false;
                                location.href = `${window.app_url}/purchase/quotation`;
                            })
                            .catch((error) => {
                                vm.errors = [];
                                if (typeof error.response != "undefined") {
                                    for (var index in error.response.data.errors) {
                                        if (error.response.data.errors[index]) {
                                            vm.errors.push(error.response.data.errors[index][0]);
                                        }
                                    }
                                }
                                vm.$refs.purchaseShowError.refresh();
                                vm.loading = false;
                            });
                        }
                    }
                })
                .catch((error) => {
                    console.error("Error al obtener los datos:", error);
                });
            }
        },
    },
    watch: {
        currency_id: function (res, ant) {
            const vm = this;
            if (res != ant && !vm.load_data_edit) {
                vm.record_items = [];

                vm.base_budget_list_deleted = [];
                if (vm.base_budget_list.length > 0) {
                    vm.base_budget_list_deleted = vm.base_budget_list;
                }
                vm.base_budget_list = [];
                vm.sub_total = 0;
                vm.tax_value = 0;
                vm.total = 0;
            }
            else {
                vm.load_data_edit = false;
            }

            if (res) {
                axios.get("/currencies/info/" + res).then((response) => {
                    vm.record.currency = response.data.currency;
                });
            }
        },

        purchase_supplier_id: function (res) {
            if (res) {
                axios.get("/purchase/get-purchase-supplier-object/" + res)
                .then((response) => {
                    this.record.purchase_supplier_object = response.data;
                    this.record.purchase_supplier_id = res;
                });
            }
        },
    },
    computed: {
        currency_symbol: function () {
            return this.record.currency ? this.record.currency.symbol : "";
        },

        currency_decimal_places: function () {
            if (this.record.currency) {
                return this.record.currency.decimal_places;
            }
        },

        currency: function () {
            return this.record.currency ? this.record.currency : null;
        },

        getRecordItems: function () {
            return this.record_items;
        },
    },
};
</script>
