<template>
    <section class="PurchaseFormComponent">
        <div class="card-body form-horizontal">
            <purchase-show-errors ref="purchaseShowError" />
            <div class="row">
                <div class="col-12" id="helpRequirementsList">
                    <v-client-table
                        :columns="columns"
                        :data="records"
                        :options="table_options"
                    >
                        <div
                            slot="requirement_status"
                            slot-scope="props"
                            class="text-center"
                        >
                            <div class="d-inline-flex">
                                <span
                                    class="badge badge-danger"
                                    v-show="props.row.requirement_status == 'WAIT'"
                                >
                                    <strong>EN ESPERA</strong>
                                </span>
                                <span
                                    class="badge badge-info"
                                    v-show="props.row.requirement_status == 'PROCESSED'"
                                >
                                    <strong>PROCESADO</strong>
                                </span>
                                <span
                                    class="badge badge-success"
                                    v-show="props.row.requirement_status == 'BOUGHT'"
                                >
                                    <strong>COMPRADO</strong>
                                </span>
                            </div>
                        </div>
                    </v-client-table>
                </div>
            </div>
            <div class="row">
                <div class="col-3" id="helpTypeCurrency">
                    <div class="form-group is-required">
                        <label class="control-label">Tipo de moneda</label>
                        <select2
                            :options="currencies"
                            v-model="currency_id"
                            tabindex="1"
                        ></select2>
                    </div>
                </div>
            </div>
            <br>
            <div class="col-12 form-horizontal" v-if="currency_id && currency">
                <table
                    class="table table-striped table-hover"
                    id="helpEstimatedBudget"
                >
                    <thead>
                        <tr class="row">
                            <th
                                class="col-1"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                Código de <br> requerimiento
                            </th>
                            <th
                                class="col-2"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                Nombre
                            </th>
                            <th
                                class="col-2"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                Especificaciones técnicas
                            </th>
                            <th
                                class="col-1"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                Cantidad
                            </th>
                            <th
                                class="col-1"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                Unidad de medida
                            </th>
                            <th
                                class="col-2"
                                style="border: 1px solid #dee2e6; position: relative;"
                                id="pusiva"
                            >
                                Precio unitario sin IVA
                            </th>
                            <th
                                class="col-2"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                Cantidad * Precio unitario
                            </th>
                            <th
                                class="col-1"
                                style="border: 1px solid #dee2e6; position: relative;"
                            >
                                IVA
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(x, index) in record_items" class="row" :key="index">
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-1 text-center"
                            >
                                {{ x.requirement_code }}
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-2"
                            >
                                {{ x.name }}
                            </td>
                            <td
                                v-if="x.technical_specifications"
                                style="border: 1px solid #dee2e6;"
                                class="col-2"
                            >
                                {{ x.technical_specifications }}
                            </td>
                            <td
                                v-else
                                style="border: 1px solid #dee2e6;"
                                class="col-2"
                            >
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-1 text-center"
                            >
                                {{ x.quantity }}
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-1 text-center"
                            >
                                <p
                                    v-if="x.measurement_unit"
                                >
                                    {{ x.measurement_unit.name }}
                                </p>
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-2 text-right"
                            >
                                <input
                                    type="text"
                                    class="form-control input-sm"
                                    placeholder="Precio"
                                    tabindex="6"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    data-toggle="tooltip"
                                    title="Precio"
                                    v-model="unitPrice"
                                    @input="CalculateTot"
                                >
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-2 text-right"
                            >
                                <span>
                                    {{ CalculateQtyPrice(x.qty_price) }}
                                </span>
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-1 text-center"
                            >
                                <span>
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
                            <td style="border: 1px solid #dee2e6;" class="col-8 text-right">
                                <b>Base imponible según alícuota {{ iva * 100 }}%</b>
                            </td>
                            <td style="border: 1px solid #dee2e6;" class="col-4 text-center">
                                {{ currencyFormat(base) }}
                            </td>
                            <td style="border: 1px solid #dee2e6;" class="col-8 text-right">
                                <b>Monto total del impuesto según alícuota {{ iva * 100 }}%</b>
                            </td>
                            <td style="border: 1px solid #dee2e6;" class="col-4 text-center">
                                {{ currencyFormat(base * iva) }}
                            </td>
                        </tr>
                        <tr
                            class="row"
                            style="background-color: rgb(242, 242, 242);"
                        >
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-8 text-right"
                            >
                                <b>TOTAL {{ currency_symbol }}</b>
                            </td>
                            <td
                                style="border: 1px solid #dee2e6;"
                                class="col-4 text-center"
                            >
                                <b>
                                    {{ currencyFormat(total) }}
                                </b>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <!-- Firmas autorizadas -->
                <div class="col-12 row" id="helpAuthorizedSignatures">
                    <div class="col-12">
                        <h6 class="card-title">Firmas autorizadas</h6>
                    </div>
                    <div class="col-3" id="helpPreparedBy">
                        <div class="form-group is-required">
                            <label class="control-label" for="prepared_by_id">
                                Preparado por
                            </label>
                            <br>
                            <div
                                v-if="base_budget_edit.purchase_requirement.prepared_by_id
                                    && enable_prepared_by_id == false"
                            >
                                <select2
                                    :options="employments"
                                    id="prepared_by_id"
                                    v-model="prepared_by_id"
                                    :disabled="base_budget_edit.purchase_requirement.prepared_by_id"
                                ></select2>
                            </div>
                            <div v-else>
                                <select2
                                    :options="employments"
                                    id="prepared_by_id"
                                    ref="enablePrepared"
                                    v-model="prepared_by_id"
                                ></select2>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" id="helpReviewedBy">
                        <div class="form-group">
                            <label class="control-label" for="reviewed_by_id">
                                Revisado por
                            </label>
                            <br>
                            <div
                                v-if="base_budget_edit.purchase_requirement.reviewed_by_id
                                    && enable_reviewed_by_id == false"
                            >
                                <select2
                                    :options="employments"
                                    id="reviewed_by_id"
                                    v-model="reviewed_by_id"
                                    :disabled="base_budget_edit.purchase_requirement.reviewed_by_id"
                                ></select2>
                            </div>
                            <div v-else>
                                <select2
                                    :options="employments"
                                    id="reviewed_by_id" ref="enableReviewed"
                                    v-model="reviewed_by_id"
                                ></select2>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" id="helpVerifiedBy">
                        <div class="form-group">
                            <label class="control-label" for="verified_by_id">
                                Verificado por
                            </label>
                            <br>
                            <div
                                v-if="base_budget_edit.purchase_requirement.verified_by_id
                                    && enable_verified_by_id == false"
                            >
                                <select2
                                    :options="employments"
                                    id="verified_by_id"
                                    v-model="verified_by_id"
                                    :disabled="base_budget_edit.purchase_requirement.verified_by_id"
                                ></select2>
                            </div>
                            <div v-else>
                                <select2
                                    :options="employments"
                                    id="verified_by_id"
                                    ref="enableVerified"
                                    v-model="verified_by_id"
                                ></select2>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" id="helpFirstSignature">
                        <div class="form-group">
                            <label class="control-label" for="first_signature_id">
                                Firmado por
                            </label>
                            <br>
                            <div
                                v-if="base_budget_edit.purchase_requirement.first_signature_id
                                    && enable_first_signature_id == false"
                            >
                                <select2
                                    :options="employments"
                                    id="first_signature_id"
                                    v-model="first_signature_id"
                                    :disabled="base_budget_edit.purchase_requirement.first_signature_id"
                                ></select2>
                            </div>
                            <div v-else>
                                <select2
                                    :options="employments"
                                    id="first_signature_id"
                                    ref="enableFirst"
                                    v-model="first_signature_id"
                                ></select2>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" id="helpSecondSignature">
                        <div class="form-group">
                            <label class="control-label" for="second_signature_id">
                                Firmado por
                            </label>
                            <br>
                            <div
                                v-if="base_budget_edit.purchase_requirement.second_signature_id
                                    && enable_second_signature_id == false"
                            >
                                <select2
                                    :options="employments"
                                    id="second_signature_id"
                                    v-model="second_signature_id"
                                    :disabled="base_budget_edit.purchase_requirement.second_signature_id"
                                ></select2>
                            </div>
                            <div v-else>
                                <select2
                                    :options="employments"
                                    id="second_signature_id"
                                    ref="enableSecond"
                                    v-model="second_signature_id"
                                ></select2>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Final de Firmas autorizadas -->
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                type="button"
                @click="reset()"
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                title="Borrar datos del formulario"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                @click="redirect_back(url_list)"
                class="btn btn-warning btn-icon btn-round btn-modal-close"
                data-dismiss="modal"
                title="Cancelar y regresar"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                @click="createRecord()"
                class="btn btn-success btn-icon btn-round btn-modal-save"
                title="Guardar registro">
                <i class="fa fa-save"></i>
            </button>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        records: {
            type: Array,
            default: function () {
                return [];
            }
        },
        base_budget_edit: {
            type: Object,
            default: function () {
                return null;
            }
        },
        /** Lista de empleados laborales */
        employments: {
            type: Array,
            default: function () {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
    },
    data() {
        return {
            url_list: `${window.app_url}/purchase/requirements`,
            number_decimals: '',
            record_items: [],
            requirement_list: [],
            requirement_list_deleted: [],
            columns: [
                'code',
                'description',
                'fiscal_year.year',
                'contrating_department.name',
                'user_department.name',
                'purchase_supplier_object.name',
                'requirement_status',
            ],
            errors: [],
            currencies: [],
            currency_id: '',
            prepared_by_id: '',
            reviewed_by_id: '',
            verified_by_id: '',
            first_signature_id: '',
            second_signature_id: '',
            history_tax: {
                id: '',
                percentage: 0,
            },
            currency: null,
            total: 0,
            sub_total_unit_price: 0,
            sub_total_iva: 0,
            requirement_checked: [],
            enable_prepared_by_id: false,
            enable_reviewed_by_id: false,
            enable_verified_by_id: false,
            enable_first_signature_id: false,
            enable_second_signature_id: false
        }
    },
    methods: {
        /**
         * Reinicia los valores de los campos del formulario
         *
         * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve | pedrobui@gmail.com>
         */
        reset() {
            this.currency_id = '';
            this.getCurrencies();
            this.currency = null;
            if (!this.base_budget_edit.purchase_requirement.prepared_by_id
                && !this.enable_prepared_by_id == false) {
                this.prepared_by_id = '';
            }
            if (!this.base_budget_edit.purchase_requirement.reviewed_by_id
                && !this.enable_reviewed_by_id == false) {
                this.reviewed_by_id = '';
            }
            if (!this.base_budget_edit.purchase_requirement.verified_by_id
                && !this.enable_verified_by_id == false) {
                this.verified_by_id = '';
            }
            if (!this.base_budget_edit.purchase_requirement.first_signature_id
                && !this.enable_first_signature_id == false) {
                this.first_signature_id = '';
            }
            if (!this.base_budget_edit.purchase_requirement.second_signature_id
                && !this.enable_second_signature_id == false) {
                this.second_signature_id = '';
            }
            for (var i = this.record_items.length - 1; i >= 0; i--) {
                var r = this.record_items[i];
                r.unit_price = 0;
            }
        },

        /**
         * Obtiene un arreglo con las monedas registradas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} id Identificador de la moneda a buscar, este parámetro es opcional
         */
        async getCurrencies(id) {
            const vm = this;
            let currency_id = (typeof (id) !== "undefined") ? '/' + id : '';
            const url = vm.setUrl(`get-currencies${currency_id}`);
            vm.currencies = [];
            await axios.get(url).then(response => {
                vm.currencies = [{'id': '', 'text': 'Seleccione...'}].concat(response.data);
            }).catch(error => {
                console.error(error);
            });
            vm.currency_id = vm.base_budget_edit.currency_id;
            vm.currency = vm.base_budget_edit.currency_id;
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

        createRecord() {
            const vm = this;
            vm.errors = [];
            if (!vm.currency_id) {
                vm.errors.push('Debe seleccionar un tipo de moneda.');
                return;
            }
            for (var i = 0; i < vm.record_items.length; i++) {
                if (!vm.record_items[i].qty_price) {
                    vm.errors.push('El precio unitario de los registros es obligatorio.');
                    return;
                }
                vm.record_items[i].qty_price
                = (vm.record_items[i].qty_price).toFixed((vm.currency)
                ? vm.currency.decimal_places : '');
            }
            vm.loading = true;
            if (!vm.base_budget_edit) {
                axios.post('/purchase/base_budget', {
                    'list': vm.requirement_list,
                    'currency_id': vm.currency_id,
                    'total': vm.total,
                    'prepared_by_id': vm.prepared_by_id,
                    'reviewed_by_id': vm.reviewed_by_id,
                    'verified_by_id': vm.verified_by_id,
                    'first_signature_id': vm.first_signature_id,
                    'second_signature_id': vm.second_signature_id,
                }).then(response => {
                    vm.loading = false;
                    vm.showMessage('store');
                    setTimeout(function () {
                        location.href = `${window.app_url}/purchase/requirements`;
                    }, 2000);
                }).catch(error => {
                    vm.loading = false;
                    vm.errors = [];
                    if (typeof (error.response) != 'undefined') {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
            } else {
                axios.put('/purchase/base_budget/' + vm.base_budget_edit.id, {
                    'list': vm.requirement_list,
                    'list_to_delete': vm.requirement_list_deleted,
                    'currency_id': vm.currency_id,
                    'total': vm.total,
                    'prepared_by_id': vm.prepared_by_id,
                    'reviewed_by_id': vm.reviewed_by_id,
                    'verified_by_id': vm.verified_by_id,
                    'first_signature_id': vm.first_signature_id,
                    'second_signature_id': vm.second_signature_id,
                }).then(response => {
                    vm.loading = false;
                    vm.showMessage('update');
                    setTimeout(function () {
                        //location.href = '/purchase/requirements';
                        location.href = `${window.app_url}/purchase/requirements`;
                    }, 2000);
                }).catch(error => {
                    vm.loading = false;
                    vm.errors = [];
                    if (typeof (error.response) != 'undefined') {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                });
            }
            $(".PurchaseFormComponent").offset().top
        },

        /**
         * Calcula un total y trunca y redondea la cifra dependiendo del valor
         * de currency.decimal_places que indica el número de decimales a usar.
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @author Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        CalculateQtyPrice(qty_price) {
            return (qty_price) ? qty_price.toFixed(this.currency.decimal_places) : 0;
        },

        /**
         * Calcula el total y sub total de la tabla de productos
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @author Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        CalculateTot() {
            const vm = this;
            vm.sub_total_unit_price = 0;
            vm.sub_total_iva = 0;
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
                vm.sub_total_unit_price += r['qty_price'];
                vm.sub_total_iva += r['qty_iva_price'];
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

    },
    created() {
        this.getCurrencies();
        this.table_options.headings = {
            'code': 'Código',
            'description': 'Descripción',
            'fiscal_year.year': 'Año fiscal',
            'contrating_department.name': 'Departamento contratante',
            'user_department.name': 'Departamento Usuario',
            'purchase_supplier_object.name': 'Tipo',
            'requirement_status': 'Estado',
        };
        this.table_options.columnsClasses = {
            'code': 'col-xs-1 text-center',
            'description': 'col-xs-3',
            'fiscal_year.year': 'col-xs-1 text-center',
            'contrating_department.name': 'col-xs-2',
            'user_department.name': 'col-xs-2',
            'purchase_supplier_object.name': 'col-xs-2',
            'requirement_status': 'col-xs-1',
        };
    },
    mounted() {
        const vm = this;
        if (vm.base_budget_edit) {
            vm.currency_id = vm.base_budget_edit.currency_id;
            vm.prepared_by_id = vm.base_budget_edit.purchase_requirement.prepared_by_id;
            vm.reviewed_by_id = vm.base_budget_edit.purchase_requirement.reviewed_by_id;
            vm.verified_by_id = vm.base_budget_edit.purchase_requirement.verified_by_id;
            vm.first_signature_id = vm.base_budget_edit.purchase_requirement.first_signature_id;
            vm.second_signature_id = vm.base_budget_edit.purchase_requirement.second_signature_id;
            var prices = [];
            for (var i = 0; i < vm.base_budget_edit.relatable.length; i++) {
                prices[vm.base_budget_edit.relatable[i].purchase_requirement_item_id] = vm.base_budget_edit.relatable[i].unit_price;
            }
            var requirement = vm.base_budget_edit.purchase_requirement;
            vm.requirement_list.push(requirement);
            var items = requirement.purchase_requirement_items;
            for (var x = 0; x < items.length; x++) {
                items[x].requirement_code = requirement.code;
                items[x].unit_price = (prices && prices[items[x].id]) ? prices[items[x].id] : 0;
                items[x].qty_price = items[x].quantity * items[x].unit_price;
                items[x].qty_iva_price = items[x].history_tax_id ?
                    items[x].quantity * items[x].unit_price *
                    (items[x].history_tax.percentage / 100) :
                    0.00;
                vm.record_items = vm.record_items.concat(items[x]);
            }
            vm.CalculateTot();
        }
    },
    watch: {
        currency_id(newVal) {
            if (newVal) {
                axios.get('/currencies/info/' + newVal).then(response => {
                    this.currency = response.data.currency;
                    this.CalculateTot();
                });
            }
            setTimeout(() => {
                if (this.$refs.enablePrepared) {
                    this.enable_prepared_by_id = true;
                }
                if (this.$refs.enableReviewed) {
                    this.enable_reviewed_by_id = true;
                }
                if (this.$refs.enableVerified) {
                    this.enable_verified_by_id = true;
                }
                if (this.$refs.enableFirst) {
                    this.enable_first_signature_id = true;
                }
                if (this.$refs.enableSecond) {
                    this.enable_second_signature_id = true;
                }
            }, 1000);
        },
    },
    computed: {
        currency_symbol: function () {
            return (this.currency) ? this.currency.symbol : '';
        },
        unitPrice: {
            get() {
                return (this.x.pivot_purchase) ? this.x.pivot_purchase.unit_price : this.x.unit_price;
            },
            set(value) {
                if (this.x.pivot_purchase) {
                    this.x.pivot_purchase.unit_price = value;
                } else {
                    this.x.unit_price = value;
                }
            }
        }
    }
};
</script>
