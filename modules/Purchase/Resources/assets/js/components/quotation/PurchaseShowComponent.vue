<template>
    <div>
        <button
            @click="
                addRecord('show_purchase_quotation_' + id,
                (getUrlShow(`/purchase/quotation/${id}`)), $event)
            "
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Visualizar registro"
            data-toggle="tooltip"
            v-has-tooltip>
            <i class="fa fa-eye"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'show_purchase_quotation_'+id"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
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
                            INFORMACIÓN DE LA COTIZACIÓN
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body" v-if="records">
                        <div class="row">
                            <div class="col-4">
                                <strong>Código de Cotización:</strong>
                                {{
                                    records.code ? records.code
                                    : "Sin código asignado"
                                }}
                            </div>
                            <div class="col-4">
                                <strong>Fecha de generación:</strong>
                                {{
                                    records.date ? format_date(records.date)
                                    : "Sin fecha asignada"
                                }}
                            </div>
                            <div class="col-4">
                                <strong>Proveedor:</strong>
                                {{ getNameStr(records.purchase_supplier) }}
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-4">
                                <strong>Tipo de moneda:</strong>
                                {{ getNameStr(records.currency) }}
                            </div>
                        </div>
                        <br>
                        <br>
                        <h6 class="card-title text-center">
                            Lista de Requerimientos
                        </h6>
                        <v-client-table
                            :columns="columns"
                            :data="getPurchaseRequirementItems1()"
                            :options="table_options"
                        >
                            <div
                                slot="purchase_requirement.date"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{
                                    format_date(
                                        props.row.purchase_requirement.date
                                    )
                                }}
                            </div>
                            <div
                                slot="purchase_requirement.description"
                                slot-scope="props"
                                class="text-left"
                            >
                                {{ props.row.purchase_requirement.description }}
                            </div>
                            <div
                                slot="purchase_requirement.code"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{ props.row.purchase_requirement.code }}
                            </div>
                        </v-client-table>
                        <h6 class="card-title text-center">
                            Lista de Productos
                        </h6>
                        <div class="row col-12">
                            <table
                                class="table table-striped table-hover"
                                style="
                                    margin-left: 1rem;
                                    margin-right: -1rem;
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
                                            class="col-3"
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
                                            Cantidad
                                        </th>
                                        <th
                                            class="col-1"
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
                                        v-for="
                                            (x, key) in getPurchaseRequirementItems()
                                        "
                                        :key="key"
                                        class="row"
                                    >
                                        <td class="col-2 text-center">
                                            {{ x.purchase_requirement.code }}
                                        </td>
                                        <td class="col-3">
                                            {{ x.name }}
                                        </td>
                                        <td class="col-2">
                                            {{ x.technical_specifications
                                                ? x.technical_specifications
                                                : ''
                                            }}
                                        </td>
                                        <td class="col-1 text-center">
                                            {{
                                                x.Quoted.quantity > 0
                                                ? x.Quoted.quantity
                                                : x.quantity
                                            }}
                                        </td>
                                        <td class="col-1 text-right">
                                            {{ x.Quoted.unit_price }}
                                        </td>
                                        <td class="col-2" align="right">
                                            <span align="right">
                                                {{
                                                    addDecimals(
                                                        (x.Quoted.quantity > 0
                                                        ? x.Quoted.quantity
                                                        : x.quantity)
                                                        * (x.Quoted.unit_price)
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="col-1 text-center">
                                            <span>
                                                {{
                                                    x.history_tax !== null
                                                    ? x.history_tax.percentage
                                                    : 0.00
                                                }}%
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr
                                        v-for="(base, iva) in bases_imponibles"
                                        :key="iva"
                                        class="row"
                                        style="background-color: rgb(242, 242, 242);"
                                    >
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-9 text-right">
                                            <b>
                                                Base imponible según
                                                alícuota {{ iva * 100 }}%
                                            </b>
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-3 text-center"
                                        >
                                            {{ addDecimals(base) }}
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-9 text-right"
                                        >
                                            <b>
                                                Monto total del impuesto según
                                                alícuota {{ iva * 100 }}%
                                            </b>
                                        </td>
                                        <td
                                            style="border: 1px solid #dee2e6;"
                                            class="col-3 text-center"
                                        >
                                            {{ addDecimals(base * iva) }}
                                        </td>
                                    </tr>
                                    <tr
                                        class="row"
                                        style="
                                            background-color: rgba(0, 0, 0, 0.05) !important;
                                        "
                                    >
                                        <td class="col-9 text-right">
                                            <b>TOTAL {{ getCurrencySymbol() }}</b>
                                        </td>
                                        <td class="col-3 text-center">
                                            <b>{{ totalAmount }}</b>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <br />
                        <div v-if="records.base_budget != ''">
                            <h6 class="card-title text-center">
                                Cuentas presupuestarias de gastos
                            </h6>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="col-md-3">Cuenta</th>
                                        <th class="col-md-3">Nombre</th>
                                        <th class="col-md-3">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="record in records.base_budget">
                                        <tr v-for="(availability, index) in record.relatable.availabilityitem" :key="index">
                                            <td class="col-md-3 text-center">
                                                {{ availability.item_code }}
                                            </td>
                                            <td
                                                class="col-md-3 text-left"
                                            >
                                                {{ availability.item_name }}
                                            </td>
                                            <td class="col-md-3 text-center">
                                                {{ availability.amount }}
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <!-- Fin de Cuentas presupuestarias de gastos -->
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
    props: ['id'],
    data() {
        return {
            purchase_requirement_items_array: [],
            records: [],
            bases_imponibles: {},
            files: {},
            columns: [
                'purchase_requirement.date',
                'purchase_requirement.code',
                'purchase_requirement.description',
                'purchase_requirement.contrating_department.name',
                'purchase_requirement.user_department.name',
            ],
            taxPercentage: 0,
            sub_total: 0,
            total: 0,
        }
    },
    created() {
        this.table_options.headings = {
            'purchase_requirement.date': 'Fecha de generación',
            'purchase_requirement.code': 'Código de requerimiento',
            'purchase_requirement.description': 'Descripción',
            'purchase_requirement.contrating_department.name': 'Departamento contratante',
            'purchase_requirement.user_department.name': 'Departamento usuario',
        };
        this.table_options.columnsClasses = {
            'purchase_requirement.date': 'col-xs-2',
            'purchase_requirement.code': 'col-xs-2',
            'purchase_requirement.description': 'col-xs-3',
        };
    },
    methods: {
        getUrlShow(url){
            return `${window.app_url}${url}`;
        },

        /**
         * Método que permite mostrar una ventana emergente con la información registrada
         * y la nueva a registrar
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param {string} modal_id Identificador de la ventana modal
         * @param {string} url      Ruta para acceder a los datos solicitados
         * @param {object} event    Objeto que gestiona los eventos
         */
        async addRecord(modal_id, url, event) {
            event.preventDefault();
            this.loading = true;
            await this.initRecords(url, modal_id);
            setTimeout(() => {
                this.addItems();
            }, 1000);
        },

        addItems() {
            this.purchase_requirement_items_array
                = this.records.relatable?.map(record => record
                .purchase_requirement_item) || [];
            this.loading = false;
        },

        /**
         * Obtener el nombre asociado al registro.
         */
        getNameStr(str) {
            if(str) {
                for (const element in str) {
                    if(element == 'name') {
                        return str[element];
                    }
                }
            } else return '';
        },

        /**
         * Truncar y redondear una cifra según el número pasado como segundo
         * parámetro del método toFixed().
         */
        addDecimals(value) {
            return parseFloat(value).toFixed((this.records.currency)
                ? this.records.currency.decimal_places
                : 2
            );
        },

        /**
         * Método que devuelve los elementos a mostrar en la tabla Listado de
         * productos.
         *
         * @author Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        getPurchaseRequirementItems() {
            if (this.records.relatable) {
                var r = [];
                if (this.records.relatable.length > 0) {
                    for (var index in this.records.relatable) {
                        r.push(this.records.relatable[index]
                            .purchase_requirement_item);
                    }
                    return r;
                }
            }
            return [];
        },

        /**
         * Método que devuelve los elementos a mostrar en la tabla Listado de
         * Requerimientos.
         *
         * @author Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        getPurchaseRequirementItems1() {
            if (this.records.relatable) {
                var q = [];
                var seenCodes = {}; // objeto para almacenar los códigos vistos y sus fechas
                for (var i in this.records.relatable) {
                    var item = this.records.relatable[i].purchase_requirement_item;
                    var code = item.purchase_requirement.code;
                    var date = item.purchase_requirement.date;

                    // Verificar si el código ya ha sido visto
                    if (!(code in seenCodes)) {
                        seenCodes[code] = {};
                        seenCodes[code][date] = true; // agregar fecha al código visto
                        q.push(item);
                    }
                    else {
                        // Verificar si la fecha del código ya ha sido vista
                        if (!(date in seenCodes[code])) {
                            seenCodes[code][date] = true; // agregar fecha al código visto
                            q.push(item);
                        }
                    }
                }
                return q;
            }
            return [];
        },

        /**
         * Obtener el símbolo de la moneda activa configurtada.
         */
        getCurrencySymbol() {
            return (this.records.currency) ? this.records.currency.symbol : '';
        },

        /**
         * Calcula el total de la suma de la Cantidad * Precio unitario más el
         * iva de todos los productos.
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        CalculateTot() {
            const vm = this;
            let total = 0;
            // Objeto para almacenar las bases imponibles según el IVA
            let bases_imponibles = {};
            for (let item of vm.purchase_requirement_items_array) {
                let percentage = (item.Quoted?.quantity > 0
                ? item.Quoted.quantity : item.quantity)
                * (item.Quoted?.unit_price)
                * ((item.history_tax_id
                ? item.history_tax.percentage : 0.00) / 100)

                let quantityxInitPrice = item.quantity * item.Quoted.unit_price;
                let iva_percentage = ((item.history_tax_id ? item.history_tax.percentage
                : 0.00) / 100);

                // Verificar si el porcentaje de IVA ya existe en el objeto
                // bases_imponibles
                if (!(iva_percentage in bases_imponibles)) {
                    // Inicializar el total para el porcentaje de IVA
                    bases_imponibles[iva_percentage] = 0;
                }
                // Acumular el total para el porcentaje de IVA
                bases_imponibles[iva_percentage] += quantityxInitPrice;

                vm.bases_imponibles = bases_imponibles;

                total += ((item.Quoted.quantity > 0 ? item.Quoted.quantity
                    : item.quantity)* item.Quoted.unit_price) + percentage;
            }
            if (vm.records.relatable) {
                return vm.addDecimals(total);
            }
            return vm.addDecimals(total);
        },
    },
    watch: {
        /* Ejecuta calculatedTotal() cuando total cambia de valor */
        calculatedTotal(newTotal) {
            this.total = newTotal;
        },
    },
    computed: {
        /* Ejecuta CalculateTot() cuando se han cargado el componente */
        totalAmount() {
            return this.CalculateTot();
        }
    },
};
</script>
