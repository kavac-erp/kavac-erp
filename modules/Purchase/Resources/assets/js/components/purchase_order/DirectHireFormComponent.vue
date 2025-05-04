<template>
    <section>
        <!-- card-body -->
        <div class="card-body">
            <purchase-show-errors ref="purchaseShowError" />
            <ul
                class="nav nav-tabs custom-tabs border-bottom-0 px-0"
                role="tablist"
            >
                <li id="helpBasicData" class="nav-item">
                    <a
                        href="#default_data"
                        class="nav-link active"
                        data-toggle="tab"
                        title="Datos básicos del regitro"
                    >
                        Datos básicos
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="default_data" class="tab-pane  active" role="tabpanel">
                    <div class="row">
                        <div id="helpFiscalYear" class="d-none">
                            <label class="control-label">
                                Ejercicio económico
                            </label>
                            <h5>{{ (fiscalYear)?fiscalYear.year:'' }}</h5>
                        </div>
                        <div
                            id="helpIsOrder"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label class="control-label" for="is_order">
                                    ¿Es una orden de compra?
                                </label>
                                <select2
                                    id="is_order"
                                    :options="orderOrServices"
                                    v-model="record.is_order"
                                ></select2>
                            </div>
                        </div>
                        <div
                            id="helpDate"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label class="control-label">
                                    Fecha de generación
                                </label>
                                <input
                                    class="
                                        form-control input-sm fiscal-year-restrict
                                    "
                                    type="date"
                                    data-toggle="tooltip"
                                    title="Fecha de generación"
                                    v-model="record.date"
                                />
                            </div>
                        </div>
                        <div
                            id="helpCurrency"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label class="control-label" for="currencies">
                                    Tipo de moneda
                                </label>
                                <select2
                                    id="currencies"
                                    :options="currencies"
                                    v-model="currency_id"
                                ></select2>
                            </div>
                        </div>
                        <div
                            v-if="institutions.length > 1"
                            id="helpInstitution"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label class="control-label" for="institutions">
                                    Institución
                                </label>
                                <select2
                                    id="institutions"
                                    :options="institutions"
                                    v-model="record.institution_id"
                                    @input="getDepartments()"
                                ></select2>
                            </div>
                        </div>
                        <div
                            v-if="departments.length > 0"
                            id="helpDepartament"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label class="control-label" for="departments1">
                                    Unidad contratante
                                </label>
                                <select2
                                    id="departmentsss"
                                    :options="departments"
                                    v-model="record.contracting_department_id"
                                ></select2>
                            </div>
                        </div>
                        <div
                            v-if="departments.length > 0"
                            id="helpDepartament2"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label class="control-label" for="departments2">
                                    Unidad usuaria
                                </label>
                                <select2
                                    id="departments2"
                                    :options="departments"
                                    v-model="record.user_department_id"
                                ></select2>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div
                                class="form-group is-required"
                                id="helpSupplier"
                            >
                                <label class="control-label" for="suppliers">
                                    Proveedor
                                </label>
                                <select2
                                    id="suppliers"
                                    :options="suppliers"
                                    v-model="purchase_supplier_id"
                                ></select2>
                            </div>
                        </div>
                        <div
                            id="helpSupplierDirection"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group">
                                <label for="supplier_direction">
                                    Dirección fiscal del proveedor
                                </label>
                                <p v-html="supplier.direction"></p>
                            </div>
                        </div>
                        <div
                            id="helpPurchaseSupplierObject"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label for="purchase_supplier_objects">
                                    Denominación del requerimiento
                                </label>
                                <select2
                                    id="purchase_supplier_objects"
                                    :options="purchase_supplier_objects"
                                    v-model='record.purchase_supplier_object_id'
                                ></select2>
                            </div>
                        </div>
                        <div
                            id="helpFundingSource"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label for="funding_source">
                                    Fuente de financiamiento
                                </label>
                                <input
                                    id="funding_source"
                                    class="form-control"
                                    type="text"
                                    v-model="record.funding_source"
                                >
                            </div>
                        </div>
                        <div
                            id="helpDescription"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label for="description">
                                    Descripción de contratación
                                </label>
                                <input
                                    id="description"
                                    class="form-control"
                                    type="text"
                                    v-model="record.description"
                                >
                            </div>
                        </div>
                        <div
                            id="helpSupplierCertificateNumber"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group">
                                <label for="supplier_rnc">
                                    Número de certificado (RNC)
                                </label>
                                <p>
                                    {{
                                        supplier.rnc_certificate_number
                                        ? supplier.rnc_status+' - '+
                                            supplier.rnc_certificate_number
                                        : 'No definido'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div
                            id="helpPurchaseType"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group is-required">
                                <label
                                    class="control-label"
                                    for="purchase_type_id"
                                >
                                    Modalidad de compra
                                </label>
                                <select2
                                    id="purchase_type_id"
                                    :options="purchase_type"
                                    v-model="record.purchase_type_id"
                                    @input="
                                        getPurchaseTypeId(record.purchase_type_id)
                                    "
                                ></select2>
                            </div>
                        </div>

                        <div id="" class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="row">
                                <div
                                class="col-md-6"
                                >
                                    <div :class="['form-group', record.time_frame != 'delivery'? 'is-required' : '']">
                                        <label class="control-label">
                                            Plazo de entrega
                                        </label>
                                        <input
                                            type="number"
                                            pattern="^[0-9]"
                                            min="1"
                                            step="1"
                                            id="due_date"
                                            v-model="record.due_date"
                                            class="form-control"
                                            :disabled="!record.time_frame || record.time_frame == 'delivery'"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div
                                        class="form-group is-required"
                                        id="time_frame"
                                    >
                                        <label class="control-label">
                                            Período
                                        </label>
                                        <select2
                                            :options="timeFrame"
                                            v-model="record.time_frame"
                                        ></select2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            id="helpHiringNumber"
                            class="col-12 col-sm-6 col-md-4 col-lg-3"
                        >
                            <div class="form-group">
                                <label for="funding_source">
                                    Número de contratación
                                </label>
                                <input
                                    type="text"
                                    id="hiring_number"
                                    v-model="record.hiring_number"
                                    class="form-control"
                                >
                            </div>
                        </div>
                    </div>

                    <h6 class="card-title text-center">
                        Lista de Cotizaciones
                    </h6>

                    <div id="helpFilterQuotation" class="col-12 px-0">
                        <v-client-table
                            :columns="columns"
                            :data="filterQuotations"
                            :options="table_options"
                        >
                            <div
                                slot="requirement_status"
                                slot-scope="props"
                                class="text-center"
                            >
                                <div class="d-inline-flex">
                                    <span
                                        class="badge badge-info"
                                        v-show="props.row.requirement_status ==
                                            'PROCESSED'"
                                    >
                                        <strong>PROCESADO</strong>
                                    </span>
                                </div>
                            </div>
                            <div
                                slot="contrating_department_name"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{
                                    props.row.pivot_recordable.length > 0 &&
                                    props.row.pivot_recordable[0].relatable
                                        .purchase_requirement
                                            .contrating_department.name
                                    ? (props.row.pivot_recordable[0]
                                        .relatable.purchase_requirement
                                            .contrating_department.name)
                                    : 'No definido'
                                }}
                            </div>
                            <div
                                slot="user_department_name"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{
                                    props.row.pivot_recordable.length > 0 &&
                                    props.row.pivot_recordable[0].relatable
                                        .purchase_requirement.user_department.name
                                    ? (props.row.pivot_recordable[0]
                                        .relatable.purchase_requirement
                                            .user_department.name)
                                    : 'No definido'
                                }}
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <div class="feature-list-content-left mr-2"
                                    v-if="record.currency">
                                    <label class="custom-control custom-checkbox">
                                        <p-check class="p-icon p-smooth p-plain p-curve"
                                            color="primary-o"
                                            :value="'_'+props.row.id"
                                            :id="'requirement_check_'+props.row.id"
                                            :checked="
                                                indexOf(quotation_list, props.row.id, true)
                                            "
                                            @change="requirementCheck(props.row)">
                                            <i
                                                slot="extra"
                                                class="icon fa fa-check"
                                            ></i>
                                        </p-check>
                                    </label>
                                </div>
                            </div>
                        </v-client-table>
                    </div>

                    <h6 class="card-title text-center">
                        Lista de Productos
                    </h6>

                    <div id="helpFilterItem" class="col-12 px-0">
                        <v-client-table
                            :columns="columns2"
                            :data="record_items"
                            :options="table2_options"
                            class="mb-0"
                        >
                            <div
                                slot="technical_specifications"
                                slot-scope="props"
                            >
                                {{
                                    props.row.purchase_requirement_item.Quoted
                                        .technical_specifications > 0
                                    ? props.row.purchase_requirement_item.Quoted
                                        .technical_specifications
                                    : props.row.purchase_requirement_item
                                        .technical_specifications
                                }}
                            </div>
                            <div
                                slot="quantity"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{
                                    props.row.purchase_requirement_item
                                        .Quoted.quantity > 0
                                    ? props.row.purchase_requirement_item.Quoted
                                        .quantity
                                    : ''
                                }}
                                {{
                                    props.row.purchase_requirement_item
                                        .Quoted.quantity > 0
                                    ? props.row.purchase_requirement_item
                                        .measurement_unit.name
                                    : ''
                                }}
                            </div>
                            <div
                                slot="unit_price"
                                slot-scope="props"
                                class="text-right"
                            >
                                {{
                                    props.row.purchase_requirement_item
                                        .Quoted.unit_price > 0
                                    ? props.row.purchase_requirement_item.Quoted
                                        .unit_price
                                    : ''
                                }}
                            </div>
                            <div
                                slot="qty_price"
                                slot-scope="props"
                                class="text-right"
                            >
                                <span>
                                    {{
                                        CalculateQtyPrice(
                                            props.row.purchase_requirement_item
                                                .Quoted.unit_price
                                            * (props.row.purchase_requirement_item
                                                .Quoted.quantity > 0
                                            ? props.row.purchase_requirement_item
                                                .Quoted.quantity
                                            : props.row.purchase_requirement_item
                                                .quantity)
                                        )
                                    }}
                                </span>
                            </div>
                            <div
                                slot="iva"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span>
                                    {{
                                        props.row.purchase_requirement_item
                                            .history_tax
                                        ? props.row.purchase_requirement_item
                                            .history_tax.percentage
                                        : 0.00
                                    }}%
                                </span>
                            </div>
                        </v-client-table>
                    </div>

                    <!-- Totales -->
                    <div v-if="record_items.length > 0">
                        <table
                            class="table table-hover"
                            style="
                                border: 1px solid #dee2e6;
                                background-color: rgb(242, 242, 242);
                            "
                        >
                            <tbody v-for="(base, iva) in bases_imponibles" :key="iva">
                                <tr>
                                    <td class="w-75 text-right font-weight-bold">
                                        <b>
                                            Base imponible según
                                            alícuota {{ iva * 100 }}%
                                        </b>
                                    </td>
                                    <td class="w-25 border text-center">
                                        {{
                                            (base).toFixed((record.currency)
                                            ? currency_decimal_places
                                            : 2)
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
                                            (base * iva).toFixed((record.currency)
                                            ? currency_decimal_places
                                            : 2)
                                        }}
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
                                                (total).toFixed((record.currency)
                                                ? currency_decimal_places
                                                : 2)
                                            }}
                                        </h6>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Final de Totales -->

                    <!-- Formas de pago -->
                    <div id="helpPaymentMethod" class="row mt-4">
                        <div class="col-12">
                            <h6 class="card-title">Formas de pago</h6>
                        </div>
                        <div class="col d-flex align-items-center">
                            <label class="mb-0 mr-2" for="pay_order">
                                Orden de pago
                            </label>
                            <input
                                id="pay_order"
                                type="radio"
                                value="pay_order"
                                v-model="record.payment_methods"
                            >
                        </div>
                        <div class="col d-flex align-items-center">
                            <label class="mb-0 mr-2" for="direct">
                                Directa
                            </label>
                            <input
                                id="direct"
                                type="radio"
                                value="direct"
                                v-model="record.payment_methods"
                            >
                        </div>
                        <div class="col d-flex align-items-center">
                            <label class="mb-0 mr-2" for="credit">
                                Crédito
                            </label>
                            <input
                                id="credit"
                                type="radio"
                                value="credit"
                                v-model="record.payment_methods"
                            >
                        </div>
                        <div class="col d-flex align-items-center">
                            <label class="mb-0 mr-2" for="advance">
                                Avances
                            </label>
                            <input
                                id="advance"
                                type="radio"
                                value="advance"
                                v-model="record.payment_methods"
                            >
                        </div>
                        <div class="col d-flex align-items-center">
                            <label class="mb-0 mr-2" for="others">Otras</label>
                            <input
                                id="others"
                                type="radio"
                                value="others"
                                v-model="record.payment_methods"
                            >
                        </div>
                    </div>
                    <hr class="mb-4">
                    <!-- ./Formas de pago -->

                    <!-- Firmas autorizadas -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="card-title">Firmas autorizadas</h6>
                        </div>
                        <div id="helpPreparedBy" class="col-12 col-md-3">
                            <div class="form-group is-required">
                                <label
                                    class="control-label"
                                    for="prepared_by_id"
                                >
                                    Preparado por
                                </label>
                                <select2
                                    :options="employments"
                                    id="prepared_by_id"
                                    v-model="record.prepared_by_id"
                                ></select2>
                            </div>
                        </div>
                        <div id="helpReviewedBy" class="col-12 col-md-3">
                            <div class="form-group">
                                <label
                                    class="control-label"
                                    for="reviewed_by_id"
                                >
                                    Revisado por</label>
                                <select2
                                    :options="employments"
                                    id="reviewed_by_id"
                                    v-model="record.reviewed_by_id"
                                ></select2>
                            </div>
                        </div>
                        <div id="helpVerifiedBy" class="col-12 col-md-3">
                            <div class="form-group">
                                <label
                                    class="control-label"
                                    for="verified_by_id"
                                >
                                    Verificado por
                                </label>
                                <select2
                                    :options="employments"
                                    id="verified_by_id"
                                    v-model="record.verified_by_id"
                                ></select2>
                            </div>
                        </div>
                        <div id="helpFirstSignature" class="col-12 col-md-3">
                            <div class="form-group">
                                <label
                                    class="control-label"
                                    for="first_signature_id"
                                >
                                    Firmado por
                                </label>
                                <select2
                                    :options="employments"
                                    id="first_signature_id"
                                    v-model="record.first_signature_id"
                                ></select2>
                            </div>
                        </div>
                        <div id="helpSecondAsignature" class="col-12 col-md-3">
                            <div class="form-group">
                                <label
                                    class="control-label"
                                    for="second_signature_id"
                                >
                                    Firmado por
                                </label>
                                <select2
                                    :options="employments"
                                    id="second_signature_id"
                                    v-model="record.second_signature_id"
                                ></select2>
                            </div>
                        </div>
                    </div>
                    <!-- ./Firmas autorizadas -->
                </div>
                <div id="requirement_docs" class="tab-pane" role="tabpanel">
                    <div class="col-12">
                        <h6 class="card-title">
                            Lista de documentos requeridos para la
                            modalidad de compra
                        </h6>
                    </div>
                    <div
                        class="col-6" v-for="(file, idx) in files_purchase_type"
                        :key="idx"
                        id="helpRequirementDoc"
                    >
                        <ul class="feature-list list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="feature-list-indicator bg-info">
                                    <label style="margin-left: 2rem; width: 5rem;">
                                        {{ files_purchase_type_name[idx] }}
                                    </label>
                                </div>
                                <div
                                    class="feature-list-content p-0"
                                    style="margin-left: 8rem;"
                                >
                                    <div class="feature-list-content-wrapper">
                                        <div class="feature-list-content-left mr-2">
                                            <label class="custom-control">
                                                <button type="button"
                                                    data-toggle="tooltip"
                                                    v-has-tooltip class="
                                                        btn btn-sm btn-info btn-import
                                                    "
                                                    title="
                                                        Presione para subir el archivo.
                                                    "
                                                    @click="setFile(idx)">
                                                    <i class="fa fa-upload"></i>
                                                </button>
                                                <input
                                                    type="file"
                                                    :id="`${idx}`"
                                                    @change="
                                                        uploadFile(
                                                            'file_puchase_type',
                                                            idx,
                                                            $event
                                                        )
                                                    "
                                                    style="display:none;"
                                                >
                                            </label>
                                        </div>
                                        <div class="feature-list-content-left">
                                            <div class="feature-list-subheading">
                                                <div v-if="files_purchase_type[idx]">
                                                    {{ files_purchase_type[idx].name }}
                                                </div>
                                                <div v-show="!files_purchase_type[idx]">
                                                    Cargar documento.
                                                </div>
                                            </div>
                                            <div
                                                class="feature-list-subheading"
                                                :id="`status_${idx}`"
                                                style="display:none;"
                                            >
                                                <span class="badge badge-info">
                                                    <strong>Documento Cargado.</strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Final de card-body -->
        <!-- card-footer -->
        <div class="card-footer text-right">
            <button
                class="btn btn-default btn-icon btn-round"
                type="button"
                data-toggle="tooltip"
                title="Borrar datos del formulario"
                @click="reset()"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                class="btn btn-warning btn-icon btn-round"
                type="button"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(url_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                class="btn btn-success btn-icon btn-round"
                type="button"
                data-toggle="tooltip"
                title="Guardar registro"
                @click="createRecord()"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
        <!-- Final de card-footer -->
    </section>
</template>
<script>
export default {
    props: {
        record_edit: {
            type: Array,
            default: function() {
                return null;
            }
        },
        tax: {
            type: Object,
            default: function() {
                return null;
            }
        },
        tax_units: {
            type: Object,
            default: function() {
                return null;
            }
        },
        quotations: {
            type: Array,
            default: function() {
                return [];
            }
        },
        suppliers: {
            type: Array,
            default: function() {
                return [];
            }
        },
        purchase_supplier_objects: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
        /** Lista de empleados laborales */
        employments: {
            type: Array,
            default: function() {
                return [{ id: '', text: 'Seleccione...' }];
            }
        },
    },
    data() {
        return {
            url_list: `${window.app_url}/purchase/direct_hire`,
            errors:[],
            records: [],
            filterQuotations: [],
            bases_imponibles: {},
            record: {
                institution_id: '',
                contracting_department_id: '',
                user_department_id: '',
                warehouse_id: '',
                purchase_supplier_object_id: '',
                funding_source:'',
                description: '',
                fiscal_year_id: '',
                products: [],
                purchase_supplier_id: '',
                purchase_supplier_object: '',
                currency: null,
                contract_number: '',
                delivery_time: '',
                payment_methods: 'pay_order',
                // variables para firmas
                prepared_by_id: '',
                reviewed_by_id: '',
                verified_by_id: '',
                first_signature_id: '',
                second_signature_id: '',
                date: '',
                purchase_type_id: '',
                due_date: '',
                time_frame: '',
                hiring_number: '',
                is_order: '',
            },
            // Editar registro
            recordEdit: false,
            // variables para proveedor
            purchase_supplier_id: '',
            supplier: {
                address: '',
                rnc: ''
            },
            // Variables para proveedor
            fiscalYear: null,
            institutions: [{ id: '', text: 'Seleccione...' }],
            departments: [],
            record_items: [],
            quotation_list: [],
            quotation_list_deleted: [],
            sub_total: 0,
            tax_value: 0,
            total: 0,
            currencies: [],
            currency_id: '',
            convertion_list: [],
            load_data_edit: false,
            //Modalidades de compra
            purchase_type: [{ id: '', text: 'Seleccione...' }],
            files_purchase_type: {},
            files_purchase_type_name: {},
            files: {
                'start_minutes': null,
                'company_invitation': null,
                'certificate_receipt_of_offer': null,
                'motivated_act': null,
                'budget_availability': null,
            },
            columns: [
                'code',
                'contrating_department_name',
                'user_department_name',
                'currency.name',
                'id'
            ],
            columns2: [
                'requirement_code',
                'purchase_requirement_item.name',
                'technical_specifications',
                'quantity',
                'unit_price',
                'qty_price',
                'iva',
            ],
            table2_options: {
                pagination: { edge: true },
                //filterByColumn: true,
                highlightMatches: true,
                texts: {
                    filter: "Buscar:",
                    filterBy: 'Buscar por {column}',
                    //count:'Página {page}',
                    count: ' ',
                    first: 'PRIMERO',
                    last: 'ÚLTIMO',
                    limit: 'Registros',
                    //page: 'Página:',
                    noResults: 'No existen registros',
                },
                sortIcon: {
                    is: 'fa-sort cursor-pointer',
                    base: 'fa',
                    up: 'fa-sort-up cursor-pointer',
                    down: 'fa-sort-down cursor-pointer'
                },
            },
            orderOrServices: [
                {'id': '', 'text': 'Seleccione...'},
                {'id': 'compra', 'text': 'Orden de compra'},
                {'id': 'servicio', 'text': 'Orden de servicio'}
            ],

            timeFrame: [
                {'id': '',          'text': 'Seleccione...'},
                {'id': 'delivery',  'text': 'Entrega inmediata'},
                {'id': 'day',       'text': 'Día(s)'},
                {'id': 'week',      'text': 'Semana(s)'},
                {'id': 'month',     'text': 'Mes(es)'},
            ]
        }
    },
    created() {
        const vm = this;
        vm.getCurrencies();

        vm.table_options.headings = {
            'code': 'Código',
            'contrating_department_name': 'Unidad contratante',
            'user_department_name': 'Unidad Usuario',
            'currency.name': 'Moneda',
            'id': 'Acción'
        };

        vm.table_options.columnsClasses = {
            'code': 'col-xs-1 text-center',
            'contrating_department_name': 'col-xs-2',
            'user_department_name': 'col-xs-2',
            'currency.name': 'col-xs-2 text-center',
            'id': 'col-xs-1'
        };

        vm.table2_options.headings = {
            'requirement_code': 'Código de requerimiento',
            'purchase_requirement_item.name': 'Nombre',
            'technical_specifications': 'Especificaciones técnicas',
            'quantity': 'Cantidad y Unidad de medida',
            'unit_price': 'Precio unitario sin IVA',
            'qty_price': 'Cantidad * Precio unitario',
            'iva': 'IVA',
        };

        vm.table2_options.columnsClasses = {
            'requirement_code': 'col-xs-1',
            'purchase_requirement_item.name': 'col-xs-2',
            'technical_specifications': 'col-xs-2',
            'quantity': 'col-xs-2',
            'unit_price': 'col-xs-2',
            'qty_price': 'col-xs-2',
            'iva' : 'col-xs-1',
        };

        vm.table2_options.filterable = [];
    },
    mounted() {
        const vm = this;
        vm.reset();

        axios.get('/purchase/get-institutions').then(response => {
            vm.institutions = response.data.institutions;

            if (vm.record_edit) {
                vm.record.institution_id = vm.record_edit[0]['institution_id'];
                vm.getDepartments();
            }
        });

        /**
         * Se obtiene un listado de las modalidades de compra
         */
        axios.get('/purchase/get-purchase-type').then(response => {
            vm.purchase_type = response.data.purchase_types;

            if (vm.record_edit) {
                setTimeout(() => {
                    vm.record.purchase_type_id = vm.record_edit[0]['purchase_type_id'];
                }, 1000);
            }
        });

        vm.filterQuotations = [];

        vm.quotations.forEach((element) => {
            // borrar si pasa el cambio de mover toda la disponibilidad
            // presupuestaria al presupuesto base
            vm.filterQuotations.push(element);
        });
        vm.records = vm.filterQuotations;
        vm.records = vm.quotations;

        if(vm.record_edit) {
            for( var i=0; i<vm.record_edit.length; i++ ) {
                vm.record.purchase_supplier_object_id = vm.record_edit[i]
                    ['purchase_supplier_object_id'];
                vm.record.funding_source = vm.record_edit[i]['funding_source'];
                vm.record.description = vm.record_edit[i]['description'];
                vm.record.fiscal_year_id = vm.record_edit[i]['fiscal_year_id'];
                vm.record.date = vm.record_edit[i]['date'];
                vm.record.purchase_supplier_id = vm.record_edit[i]['purchase_supplier_id'];
                vm.purchase_supplier_id = vm.record_edit[i]['purchase_supplier_id'];
                vm.currency_id = vm.record_edit[i]['currency_id'];
                vm.record.payment_methods = vm.record_edit[i]['payment_methods'];
                // variables para firmas
                vm.record.prepared_by_id = vm.record_edit[i]['prepared_by_id'];
                vm.record.reviewed_by_id = vm.record_edit[i]['reviewed_by_id'];
                vm.record.verified_by_id = vm.record_edit[i]['verified_by_id'];
                vm.record.first_signature_id = vm.record_edit[i]['first_signature_id'];
                vm.record.second_signature_id = vm.record_edit[i]['second_signature_id'];
                vm.record.is_order = vm.record_edit[i]['is_order'];
                vm.record.due_date = vm.record_edit[i]['due_date'];
                Vue.set(vm.record, 'time_frame', vm.record_edit[i]['time_frame']);
                vm.record.hiring_number = vm.record_edit[i]['hiring_number'];

                vm.indexOf(vm.quotations,vm.quotations.id,true);

                setTimeout(() => {
                    for (var j = 0; j < vm.quotations.length; j++) {
                        if(vm.quotations[j]['orderable_id']) {
                            vm.requirementCheck(vm.quotations[j]);
                        }
                    }
                }, 500);
            }
        }
        axios.get('/purchase/get-fiscal-year').then(response => {
            vm.fiscalYear = response.data.fiscal_year;
        });
    },
    methods: {
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
            if (vm.record_edit) {
                for (var i = 0; i < vm.record_edit.length; i++) {
                    vm.currency_id = vm.record_edit[i]['currency_id'];
                }
            }
        },

        reset() {
            const vm = this;
            vm.record_items = [];
            vm.quotation_list = [];
            vm.quotation_list_deleted = [];
            vm.record = {
                institution_id: '',
                contracting_department_id: '',
                user_department_id: '',
                warehouse_id: '',
                purchase_supplier_object_id: '',
                funding_source:'',
                description: '',
                fiscal_year_id: '',
                products: [],
                purchase_supplier_id: '',
                purchase_supplier_object: '',
                currency: null,
                contract_number: '',
                delivery_time: '',
                payment_methods: 'pay_order',
                // Variables para firmas
                prepared_by_id: '',
                reviewed_by_id: '',
                verified_by_id: '',
                first_signature_id: '',
                second_signature_id: '',
                purchase_type_id: '',
                due_date: '',
                hiring_number: '',
            };
            vm.sub_total = 0;
            vm.tax_value = 0;
            vm.total = 0;
            vm.getCurrencies();
        },

        uploadFile(opc, inputID, e) {
            let vm = this;
            const files = e.target.files;
            Array.from(files).forEach(file => vm.addFile(opc, file, inputID));
        },

        addFile(opc = '', file, inputID) {
            if (!file.type.match('application/pdf')) {
                this.showMessage(
                    'custom', 'Error', 'danger', 'screen-error', 'Solo se permiten archivos pdf.'
                );
                return;
            } else {
                if(opc == 'file_puchase_type'){
                    this.files_purchase_type[inputID] = file;
                }
                $(`#status_${inputID}`).show("slow");
            }
        },

        /**
         * Truncar y redondear una cifra según el número pasado como segundo
         * parámetro del método toFixed().
         */
        addDecimals(value) {
            return parseFloat(value).toFixed(this.currency_decimal_places);
        },

        indexOf(list, id, returnBoolean) {
            for (var i = list.length - 1; i >= 0; i--) {
                if (list[i].id == id) {
                    return (returnBoolean) ? true : i;
                }
            }
            return (returnBoolean) ? false : -1;
        },

        requirementCheck(record) {
            axios.get('/purchase/get-convertion/' + this.currency_id + '/' + record.currency_id)
                .then(response => {
                    if (record.currency_id != this.currency_id && !response.data.record) {

                        if ($('#requirement_check_' + record.id + ' input:checkbox').prop('checked')) {
                            this.showMessage(
                                'custom', 'Error', 'danger', 'screen-error',
                                "No se puede realizar la conversión de " + this.record.currency.name +
                                " a " + record.currency.name +
                                " ya que no existe una tasa asignada. Revisar las conversiones configuradas en el sistema."
                            );
                            $('#requirement_check_' + record.id + ' input:checkbox').prop('checked', false);
                        }
                    } else {
                        this.convertion_list.push(response.data.record);
                        this.addToList(record);
                        let indexing_data = record.pivot_recordable[0]
                            .relatable.purchase_requirement;
                        // Datos de la cotización que se autocompletan en el formulario
                        this.record.institution_id = indexing_data.institution_id;
                        this.purchase_supplier_id = record.purchase_supplier_id;
                        this.record.purchase_supplier_object_id = indexing_data.purchase_supplier_object_id;
                        setTimeout(
                            () => {
                                this.record.contracting_department_id = indexing_data.contracting_department_id;
                                this.record.user_department_id = indexing_data.user_department_id;
                            },
                            2500
                        )
                    }
                });
        },

        addToList: function(record, prices) {
            var pos = this.indexOf(this.quotation_list, record.id);
            // se agregan a la lista a guardar
            if (pos == -1) {
                for (var i = 0; i < record.relatable.length; i++) {
                    record.relatable[i].requirement_code = record.code;
                    record.relatable[i].unit_price = (prices)
                    ? prices[record.relatable[i].id] : 0;
                }

                // saca de la lista de registros eliminar
                pos = this.indexOf(this.quotation_list_deleted, record.id);
                if (pos != -1) {
                    this.quotation_list_deleted.splice(pos, 1);
                }

                this.quotation_list.push(record);
                this.record_items = this.record_items.concat(record.relatable);
            } else {
                // se sacan de la lista a guardar
                var record_copy = this.quotation_list.splice(pos, 1)[0];
                var pos = this.indexOf(this.quotation_list_deleted, record_copy.id);

                // agrega a la lista de registros a eliminar
                if (pos == -1) {
                    this.quotation_list_deleted.push(record_copy);
                }

                for (var i = 0; i < record.relatable.length; i++) {
                    for (var x = 0; x < this.record_items.length; x++) {
                        if (this.record_items[x].id == record.relatable[i].id) {
                            delete this.record_items[x].qty_price;
                            delete this.record_items[x].qty_iva_price;
                            this.record_items.splice(x, 1);
                            break;
                        }
                    }
                }
            }
            this.CalculateTot();
        },

        /**
         * [CalculateTot Calcula el total e impuesto de los productos de la cotización]
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         * @param  {[type]} r   [información del registro]
         * @param  {[type]} pos [posición del registro]
         */
        CalculateTot(item, pos) {
            const vm = this;
            this.total = 0;
            let bases_imponibles = {};
            for (let item of this.record_items) {
                let percentage = (item.purchase_requirement_item.Quoted.quantity > 0
                ? item.purchase_requirement_item.Quoted.quantity
                : item.purchase_requirement_item.quantity) *
                (item.purchase_requirement_item.Quoted.unit_price) *
                ((item.purchase_requirement_item.history_tax_id ?
                item.purchase_requirement_item.history_tax.percentage
                : 0.00) / 100)

                let quantityxInitPrice = item.purchase_requirement_item.Quoted
                    .quantity * item.purchase_requirement_item.Quoted.unit_price;
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

                this.total += ((item.purchase_requirement_item.Quoted.quantity > 0
                ? item.purchase_requirement_item.Quoted.quantity
                : item.purchase_requirement_item.quantity) *
                item.purchase_requirement_item.Quoted.unit_price) + percentage
            }
        },

        CalculateQtyPrice(qty_price) {
            return (qty_price)
            ? (qty_price).toFixed((this.record.currency)
                ? this.currency_decimal_places
                : '')
            : 0;
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

        createRecord() {
            /** Se obtiene y da formato para enviar el archivo a la ruta */
            let vm = this;
            var formData = new FormData();
            let route = vm.route_list;
            let i = 0;
            for (const key in vm.files_purchase_type) {
                if (vm.files_purchase_type[key]) {
                    formData.append(`files_purchase_type[${key}]`, vm.files_purchase_type[key]);
                    i += 1;
                }
            }
            if(i == 0) formData.append(`files_purchase_type`, '');

            formData.append("purchase_supplier_id", vm.purchase_supplier_id);
            formData.append("currency_id", vm.currency_id);

            if(vm.quotation_list.length > 0){
                for(let i = 0; i < vm.quotation_list.length; i++){
                    formData.append(
                        "quotation_list[]", JSON.stringify(vm.quotation_list[i])
                    );
                }
            } else {
                formData.append("quotation_list", '');
            }

            if(vm.record_items.length > 0){
                for(let i = 0; i < vm.record_items.length; i++){
                    formData.append(
                        "record_items[]", JSON.stringify(vm.record_items[i])
                    );
                }
            } else {
                formData.append("record_items", '');
            }

            formData.append("date", vm.record.date);
            formData.append("institution_id", vm.record.institution_id);
            formData.append("is_order", vm.record.is_order);
            formData.append("contracting_department_id", vm.record.contracting_department_id);
            formData.append("user_department_id", vm.record.user_department_id);
            formData.append("fiscal_year_id", vm.fiscalYear.id);
            formData.append("purchase_supplier_id", vm.record.purchase_supplier_id);
            formData.append("purchase_supplier_object_id", vm.record.purchase_supplier_object_id);
            formData.append("funding_source", vm.record.funding_source);
            formData.append("description", vm.record.description);
            formData.append("payment_methods", vm.record.payment_methods);

            // variables para firmas
            formData.append("prepared_by_id", vm.record.prepared_by_id);
            formData.append("reviewed_by_id", vm.record.reviewed_by_id);
            formData.append("verified_by_id", vm.record.verified_by_id);
            formData.append("first_signature_id", vm.record.first_signature_id);
            formData.append("second_signature_id", vm.record.second_signature_id);

            formData.append("purchase_type_id", vm.record.purchase_type_id);
            formData.append("due_date", vm.record.due_date);
            formData.append("time_frame", vm.record.time_frame);
            formData.append("hiring_number", vm.record.hiring_number);

            vm.loading = true;

            if (!vm.record_edit) {
                axios.post('/purchase/direct_hire', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    vm.showMessage('store');
                    vm.$refs.purchaseShowError.refresh();
                    vm.loading = false;
                    location.href = `${window.app_url}${route}`;
                }).catch(error => {
                    vm.errors = [];
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 422 || error.response.status == 500) {
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(error.response.data.errors[index][0]);
                                }
                            }
                        }
                    }
                    vm.$refs.purchaseShowError.refresh();
                    vm.loading = false;
                });
            } else {
                formData.append(
                    "list_to_delete", JSON.stringify(this.quotation_list_deleted)
                );

                axios.post('/purchase/direct_hire/' + vm.record_edit[0]['id'], formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    vm.showMessage('update');
                    vm.loading = false;
                    location.href = `${window.app_url}${route}`;

                }).catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 422 || error.response.status == 500) {
                            for (const i in error.response.data.errors) {
                                vm.showMessage(
                                    'custom', 'Error', 'danger', 'screen-error', error.response.data.errors[i][0]
                                );
                            }
                        }
                    }
                    vm.loading = false;
                });
            }
        },

        /**
         * Obtiene un listado de los departamebtos de una institucion
         */
        getDepartments() {
            const vm = this;
            vm.departments = [];

            if (vm.record.institution_id != '') {
                axios.get('/get-departments/' + vm.record.institution_id)
                    .then(response => {
                        vm.departments = response.data;
                        if (vm.record_edit) {
                            vm.record.contracting_department_id
                            = vm.record_edit[0]['contracting_department_id'];
                            vm.record.user_department_id
                            = vm.record_edit[0]['user_department_id'];
                        }
                        vm.getWarehouseProducts();
                    });
            }
        },

        /**
         * Obtiene un listado de los productos en almacen
         */
        getWarehouseProducts() {
            this.products = [];
            axios.get('/warehouse/get-warehouse-products/').then(response => {
                this.products = response.data;
            });
        },

        searchBaseBudgetUnitPrice(list, purchase_base_budget_id){
            for (let idx = 0; idx < list.length; idx++) {
                if (list[idx].relatable_type
                    == 'Modules\\Purchase\\Models\\PurchaseQuotation'
                    && list[idx].relatable_id == purchase_base_budget_id ) {
                        return list[idx].unit_price;
                }
            }
        },

        /**
         * Obtiene una modalidad de compra dado su id.
         */
        getPurchaseTypeId(id){
            this.files_purchase_type = {};
            let url = this.setUrl(`purchase/purchase_types/${id}`);

            return axios.get(url).then(response => {
                    if (typeof(response.data.purchase_type) != 'undefined'){
                        this.files_purchase_type =  response.data.purchase_type;
                        this.files_purchase_type_name = response.data.purchase_type_name;
                    }

                }).catch(error => {
                    if (typeof(error.response) !== "undefined") {
                        if (error.response.status == 422 || error.response.status == 500) {
                            for (const i in error.response.data.errors) {
                                vm.showMessage(
                                    'custom', 'Error', 'danger', 'screen-error',
                                    error.response.data.errors[i][0]
                                );
                            }
                        }
                    }
                });
        },

    },
    watch: {
        "record.time_frame":function(newVal){
            newVal == 'delivery' && (this.record.due_date = '');
        },
        currency_id: function(res, ant) {
            if (res != ant && !this.load_data_edit) {
                this.record_items = [];
                this.quotation_list_deleted = [];
                if (this.quotation_list.length > 0) {
                    this.quotation_list_deleted = this.quotation_list;
                }
                this.quotation_list = [];
                this.sub_total = 0;
                this.tax_value = 0;
                this.total = 0;
            } else {
                this.load_data_edit = false;
            }
            if (res) {
                axios.get('/currencies/info/' + res).then(response => {
                    this.record.currency = response.data.currency;
                })
            }
        },
        purchase_supplier_id(newVal) {
            if (newVal) {
                axios.get('/purchase/get-purchase-supplier-object/' + newVal)
                    .then(response => {
                        this.record.purchase_supplier_object = response.data;
                        this.record.purchase_supplier_id = newVal;
                });
                axios.get('/purchase/suppliers/' + newVal).then(response => {
                    this.supplier = response.data.records;
                });
            }
        },
    },
    computed: {
        currency_symbol: function() {
            return (this.record.currency) ? this.record.currency.symbol : '';
        },
        currency_decimal_places: function() {
            if (this.record.currency) {
                return this.record.currency.decimal_places;
            }
        },
        currency: function() {
            return (this.record.currency) ? this.record.currency : null;
        },
        getRecordItems: function() {
            return this.record_items;
        },
    }
};
</script>
