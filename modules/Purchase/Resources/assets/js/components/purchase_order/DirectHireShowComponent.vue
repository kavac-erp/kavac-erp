<template>
    <section>
        <button
            @click="
                addRecord('show_purchase_order_direct_hire_'+id,
                getUrlShow(`/purchase/direct_hire/${id}`), $event)
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
            :id="'show_purchase_order_direct_hire_'+id"
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
                            Información de la orden de compra / servicio
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body" v-if="records">
                        <div class="row">
                            <div class="col-3">
                                <strong class="d-block">
                                    Fecha de generación
                                </strong>
                                {{
                                    format_date(
                                        records.date ? records.date
                                        : "Sin fecha asignada"
                                    )
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Código</strong>
                                {{ records.code }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">
                                    Descripción de contratación
                                </strong>
                                {{ records.description }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">
                                    Unidad contratante
                                </strong>
                                {{ records.contrating_department.name }}
                            </div>
                            <div class="col-3 mt-2">
                                <strong class="d-block">
                                    Unidad usuario
                                </strong>
                                {{ records.user_department.name }}
                            </div>
                        </div>
                        <h6 class="card-title mt-3">Datos de proveedor</h6>
                        <div class="row">
                            <div class="col-3">
                                <strong class="d-block">
                                    Nombre o Razón social
                                </strong>
                                {{ records.purchase_supplier.name }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">RIF</strong>
                                {{ records.purchase_supplier.rif }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">
                                    Dirección fiscal
                                </strong>
                                <p v-html="records.purchase_supplier.direction"></p>
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Plazo de entrega</strong>
                                {{ records.due_date }} {{ timeFrame[records.time_frame] }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">
                                    Forma de pago
                                </strong>
                                {{ payment_methods[records.payment_methods] }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Número de Expediente</strong>
                                {{ records.purchase_supplier.file_number || 'NO REGISTRADO' }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Número de certificado (RNC)</strong>
                                {{
                                    records.purchase_supplier.rnc_certificate_number
                                    ? records.purchase_supplier
                                        .rnc_status+' - '+records.purchase_supplier
                                            .rnc_certificate_number
                                    : 'NO REGISTRADO'
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Lugar de entrega</strong>
                                <span>
                                    <p v-html="records.institution.legal_address"></p>
                                </span>
                            </div>
                        </div>
                        <h6
                            v-if="records.quatations[0]"
                            class="card-title text-center mt-3"
                        >
                            Lista de productos
                        </h6>
                        <div class="row" v-if="records.quatations[0]">
                            <table
                                class="table table-striped table-hover"
                                style="margin-left: 2rem; margin-right: 2rem;"
                            >
                                <thead>
                                    <tr class="row">
                                        <th class="col-2">
                                            Código de requerimiento
                                        </th>
                                        <th class="col-2">
                                            Nombre
                                        </th>
                                        <th class="col-2">
                                            Especificaciones técnicas
                                        </th>
                                        <th class="col-1">
                                            Cantidad
                                        </th>
                                        <th class="col-2">
                                            Precio unitario sin IVA
                                        </th>
                                        <th class="col-2">
                                            Cantidad * Precio unitario
                                        </th>
                                        <th class="col-1">
                                            IVA
                                        </th>
                                    </tr>
                                </thead>
                                <tbody v-if="records.quatations[0]">
                                    <tr
                                        class="row"
                                        v-for="
                                            (item, key)
                                            in records.quatations[0].relatable
                                        "
                                        :key="key"
                                    >
                                        <td class="col-2 text-center">
                                            {{
                                                item.purchase_requirement_item
                                                    .purchase_requirement.code
                                            }}
                                        </td>
                                        <td class="col-2">
                                            {{
                                                item.purchase_requirement_item.name
                                            }}
                                        </td>
                                        <td class="col-2">
                                            {{ item.purchase_requirement_item
                                                ? item.purchase_requirement_item
                                                    .technical_specifications
                                                : ''
                                            }}
                                        </td>
                                        <td class="col-1 text-center">
                                            {{
                                                item.quantity > 0
                                                ? item.quantity
                                                : item.purchase_requirement_item
                                                    .quantity
                                            }}
                                        </td>
                                        <td class="col-2 text-right">
                                            {{ item.unit_price }}
                                        </td>
                                        <td class="col-2 text-right">
                                            {{
                                                addDecimals((item.quantity > 0
                                                ? item.quantity
                                                : item.purchase_requirement_item
                                                    .quantity)
                                                * (item.unit_price))
                                            }}
                                        </td>
                                        <td class="col-1 text-center">
                                            {{
                                                item.purchase_requirement_item
                                                    .history_tax !== null
                                                ? item.purchase_requirement_item
                                                    .history_tax.percentage
                                                : 0.00
                                            }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <table
                            class="table"
                            style="
                                border: 1px solid #dee2e6;
                                background-color: rgb(242, 242, 242);
                            "
                        >
                            <tbody
                                v-for="(base, iva) in bases_imponibles"
                                :key="iva"
                            >
                                <tr>
                                    <td class="w-75 text-right font-weight-bold">
                                        <b>
                                            Base imponible según
                                            alícuota {{ iva * 100 }}%
                                        </b>
                                    </td>
                                    <td class="w-25 border text-center">
                                        {{
                                            addDecimals(base)
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="w-75 text-right font-weight-bold">
                                        <b>
                                            Monto total del impuesto según
                                            alícuota {{ iva * 100 }}%
                                        </b>
                                    </td>
                                    <td class="w-25 border text-center">
                                        {{
                                            addDecimals(base * iva)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="w-75 text-right">
                                        <b>TOTAL {{ currency_symbol }}</b>
                                    </td>
                                    <td class="text-center">
                                        <h6>
                                            {{ totalAmount }}
                                        </h6>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
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
                                    <template
                                        v-for="record in records.base_budget"
                                    >
                                        <tr
                                            v-for="
                                                (availability, index)
                                                in record.relatable.availabilityitem
                                            "
                                            :key="index"
                                        >
                                            <td class="col-md-3 text-center">
                                                {{ availability.item_code }}
                                            </td>
                                            <td class="col-md-3 text-left">
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
                        <h6 class="card-title">Firmas autorizadas</h6>
                        <div class="row">
                            <div class="col-3">
                                <strong class="d-block">Preparado por</strong>
                                {{
                                    records.prepared_by
                                        ? records.prepared_by.payroll_staff
                                            ? records.prepared_by.payroll_staff
                                                .first_name +' '+ records.prepared_by
                                                    .payroll_staff.last_name
                                            : 'No definido'
                                        : 'No definido'
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Revisado por</strong>
                                {{
                                    records.reviewed_by
                                        ? records.reviewed_by.payroll_staff
                                            ? records.reviewed_by.payroll_staff
                                                .first_name +' '+ records.reviewed_by
                                                    .payroll_staff.last_name
                                            : 'No definido'
                                        : 'No definido'
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Verificado por</strong>
                                {{
                                    records.verified_by
                                        ? records.verified_by.payroll_staff
                                            ? records.verified_by.payroll_staff
                                                .first_name +' '+ records.verified_by
                                                    .payroll_staff.last_name
                                            : 'No definido'
                                        : 'No definido'
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Firmado por</strong>
                                {{
                                    records.first_signature
                                        ? records.first_signature.payroll_staff
                                            ? records.first_signature.payroll_staff
                                                .first_name +' '+ records.first_signature
                                                    .payroll_staff.last_name
                                            : 'No definido'
                                        : 'No definido'
                                }}
                            </div>
                            <div class="col-3 mt-2">
                                <strong class="d-block">Firmado por</strong>
                                {{
                                    records.second_signature
                                        ? records.second_signature.payroll_staff
                                            ? records.second_signature
                                                .payroll_staff.first_name +' '+ records
                                                    .second_signature
                                                    .payroll_staff.last_name
                                            : 'No definido'
                                        : 'No definido'
                                }}
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
    </section>
</template>
<script>
export default {
    props: ['id'],
    data() {
        return {
            records: null,
            files: {},
            bases_imponibles: {},
            condition: false,
            payment_methods: {
                pay_order: 'Orden de pago',
                direct: 'Directa',
                credit: 'Crédito',
                advance: 'Avances',
                others: 'Otras',
            },
            total: 0,

            timeFrame: {
                delivery: 'Entrega inmediata',
                day:      'Día(s)',
                week:     'Semana(s)',
                month:    'Mes(es)',
            }
        }
    },
    methods: {
        /**
         * Truncar y redondear una cifra según el número pasado como segundo
         * parámetro del método toFixed().
         */
        addDecimals(value) {
            return parseFloat(value).toFixed(
                this.records.currency.decimal_places
            );
        },

        getUrlShow(url){
            return `${window.app_url}${url}`;
        },

        /**
         * Calcula el total de la suma de la Cantidad * Precio unitario más el
         * iva de todos los productos.
         *
         * @method CalculateTotal
         *
         * @author Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        CalculateTotal() {
            this.total = 0;
            let bases_imponibles = {};
            if (this.records.quatations[0]) {
                for (let item of this.records.quatations[0].relatable) {
                    let percentage = (item.purchase_requirement_item.Quoted.quantity > 0
                    ? item.purchase_requirement_item.Quoted.quantity
                    : item.purchase_requirement_item.quantity) *
                    (item.purchase_requirement_item.Quoted.unit_price) *
                    ((item.purchase_requirement_item.history_tax_id ?
                    item.purchase_requirement_item.history_tax.percentage
                    : 0.00) / 100)

                    let quantityxInitPrice = (item.purchase_requirement_item
                        .Quoted.quantity > 0
                    ? item.purchase_requirement_item.Quoted.quantity
                    : item.purchase_requirement_item.quantity)
                    * item.purchase_requirement_item.Quoted.unit_price;
                    let iva_percentage = ((item.purchase_requirement_item
                        .history_tax_id ? item.purchase_requirement_item.history_tax
                            .percentage
                    : 0.00) / 100);

                    // Verificar si el porcentaje de IVA ya existe en el objeto
                    // bases_imponibles
                    if (!(iva_percentage in bases_imponibles)) {
                        // Inicializar el total para el porcentaje de IVA
                        bases_imponibles[iva_percentage] = 0;
                    }
                    // Acumular el total para el porcentaje de IVA
                    bases_imponibles[iva_percentage] += quantityxInitPrice;

                    this.bases_imponibles = bases_imponibles;

                    this.total += ((item.purchase_requirement_item.Quoted.quantity
                    > 0 ? item.purchase_requirement_item.Quoted.quantity
                    : item.purchase_requirement_item.quantity) *
                    item.purchase_requirement_item.Quoted.unit_price) + percentage;
                }
                if (this.records.quatations[0]) {
                    return this.addDecimals(this.total);
                }
                return this.addDecimals(this.total);
            }
        },

        /**
         * Inicializa los registros base del formulario
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param {string}  url       Ruta que obtiene los datos a ser mostrado en listados
         * @param {string}  modal_id  Identificador del modal a mostrar con la información solicitada
         */
        initRecords(url, modal_id) {
            this.errors = [];
            const vm = this;
            url = this.setUrl(url);

            axios.get(url).then(response => {
                if (typeof(response.data.records) !== "undefined") {
                    vm.records = response.data.records;
                    vm.haveDocsPurchseType(vm.records.documents);
                }
                if ($("#" + modal_id).length) {
                    $("#" + modal_id).modal('show');
                }
            }).catch(error => {
                if (typeof(error.response) !== "undefined") {
                    if (error.response.status == 403) {
                        vm.showMessage(
                            'custom',
                            'Acceso Denegado',
                            'danger',
                            'screen-error',
                            error.response.data.message
                        );
                    }
                    else {
                        vm.logs('resources/js/all.js', 343, error, 'initRecords');
                    }
                }
            });
        },

        /**
         * Método que comprueba si existen documentos para la modalidad de compra
         *
         * @author  Francisco J. P. Ruiz <javierrupe19@gmail.com>
         */
        haveDocsPurchseType(docs){
            this.condition = false;
            var arr = '';
            if(this.records.documents.length > 0){

                this.condition = true;
            }
        }
    },
    computed: {
        currency_symbol: function() {
            return (this.records.currency) ? this.records.currency.symbol : '';
        },
        /* Ejecuta CalculateTotal cuando se han cargado el componente */
        totalAmount() {
            return this.CalculateTotal();
        }
    },
};
</script>
