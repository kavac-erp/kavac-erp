<template>
    <div>
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
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
                        <li v-for="error in errors" :key="error">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">Institución</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="institution" class="form-group is-required">
                        <select2
                            :options="institutions"
                            v-model="record.institution_id"
                        ></select2>
                        <input type="hidden" v-model="record.id" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">Fecha</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <input
                            type="date"
                            class="form-control input-sm"
                            v-model="record.compromised_at"
                            title="Indique la fecha del compromiso"
                            id="compromised_at"
                            data-toggle="tooltip"
                        />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">
                            Documento Origen
                            <a
                                class="btn btn-sm btn-info btn-action btn-tooltip"
                                href="javascript:void(0)"
                                data-original-title="Buscar documento"
                                title="Buscar documento"
                                data-toggle="modal"
                                data-target="#add_source"
                                v-if="record.institution_id"
                            >
                                <i class="fa fa-search"></i>
                            </a>
                            <a
                                class="btn btn-sm btn-default btn-action btn-tooltip"
                                href="javascript:void(0)"
                                data-original-title="Quitar documento de origen"
                                title="Quitar documento de origen"
                                v-if="document_number !== ''"
                                data-toggle="tooltip"
                            >
                                <i class="icofont icofont-eraser"></i>
                            </a>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <input
                            @input="enableField()"
                            :disabled="disableSource"
                            id="document_number"
                            type="text"
                            v-model="record.source_document"
                            class="form-control input-sm"
                            title="Indique el número de documento de origen que genera el compromiso"
                            data-toggle="tooltip"
                            :readonly="document_number !== ''"
                            maxlength="20"
                        />
                    </div>
                </div>
                <!-- Modal para agregar documentos de origen que generaron un precompromiso -->
                <div
                    class="modal fade"
                    tabindex="-1"
                    role="dialog"
                    id="add_source"
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
                                    <i class="ion-arrow-graph-up-right"></i>
                                    Agregar documento
                                </h6>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 pad-top-20">
                                        <table
                                            class="table table-hover table-striped"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Fecha</th>
                                                    <th>Monto</th>
                                                    <th>Sel.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(source,
                                                    index) in document_sources"
                                                    :key="index"
                                                >
                                                    <td class="text-center">
                                                        {{
                                                            source.sourceable
                                                                ? source.sourceable_type == 'Modules\\Payroll\\Models\\Payroll'
                                                                    ? source.document_number
                                                                    : source.sourceable.code
                                                                : ""
                                                        }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{
                                                            format_date(
                                                                source.sourceable
                                                                    ? source.sourceable.date ?
                                                                        source.sourceable.date :
                                                                        source.sourceable.created_at
                                                                    : source.created_at
                                                            )
                                                        }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{
                                                            formatToCurrency(
                                                                source
                                                                    .budget_stages[0]
                                                                    .amount,
                                                                ""
                                                            )
                                                        }}
                                                    </td>
                                                    <td class="text-center">
                                                        <a
                                                            href="#"
                                                            data-original-title="Agregar documento"
                                                            class="btn btn-sm btn-info btn-action btn-tooltip"
                                                            @click="
                                                                addDocument(
                                                                    source.id
                                                                );
                                                                currencySymbol(
                                                                    source.budget_compromise_details[0].budget_sub_specific_formulation ?
                                                                    source.budget_compromise_details[0].budget_sub_specific_formulation.currency.id :
                                                                    source.sourceable.currency_id
                                                                );
                                                            "
                                                            data-dismiss="modal"
                                                        >
                                                            <i
                                                                class="fa fa-plus-circle"
                                                            ></i>
                                                        </a>
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
                                    class="btn btn-default btn-sm btn-round btn-modal-close"
                                    data-dismiss="modal"
                                >
                                    Cerrar
                                </button>
                                <!--<button type="button" @click="addDocument"
                                                                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                                                        Agregar
                                                                </button>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">Descripción</label>
                    </div>
                </div>
                <div class="col-md-10">
                    <div id="descriptionText" class="form-group is-required">
                        <ckeditor
                            :editor="ckeditor.editor"
                            id="description"
                            data-toggle="tooltip"
                            title="Indique una descripción para el compromiso"
                            :config="ckeditor.editorConfig"
                            class="form-control"
                            name="description"
                            tag-name="textarea"
                            rows="3"
                            v-model="record.description"
                        ></ckeditor>
                    </div>
                </div>
            </div>
            <hr v-if="enableFields" />
            <h6 v-if="enableFields" class="text-left card-title">
                Beneficiario
            </h6>
            <div class="row" v-if="enableFields">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">Beneficiario</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <v-multiselect
                            :options="all_receivers"
                            track_by="text"
                            :hide_selected="false"
                            v-model="record.receiver"
                            :multiple="false"
                            :search_change="(query) => applyFunctionDebounce(query, searchReceivers)"
                            :internal_search="false"
                            :searchable="true"
                            :taggable="true"
                            :add_tag="addTag"
                            :group_values="'group'"
                            :group_label="'label'"
                            @input="selectAccountingAccount"
                            style="margin-top: -25px;"
                        >
                        </v-multiselect>
                    </div>
                </div>
                <div class="col-md-2" v-if="accounting">
                    <div class="form-group">
                        <label for="" class="control-label"
                            >Cuenta contable</label
                        >
                    </div>
                </div>
                <div class="col-md-4" v-if="accounting">
                    <div class="form-group is-required">
                        <select2
                            :options="accounting_accounts"
                            v-model="record.accounting_account_id"
                        ></select2>
                    </div>
                </div>
            </div>
            <hr />
            <div class="pad-top-40">
                <h6 class="text-center card-title">
                    Cuentas presupuestarias de gastos
                </h6>
                <div class="row">
                    <div class="col-md-12 pad-top-20">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th class="col-1">ID</th>
                                    <th class="col-4">Acción Específica</th>
                                    <th class="col-2">Cuenta</th>
                                    <th class="col-2">Descripción</th>
                                    <th class="col-2">Monto</th>
                                    <th class="col-1">
                                        <a
                                            id="add_account_info"
                                            class="btn btn-sm btn-info btn-action btn-tooltip"
                                            href="#"
                                            data-original-title="Agregar cuenta presupuestaria"
                                            data-toggle="modal"
                                            data-target="#add_account"
                                        >
                                            <i class="fa fa-plus-circle"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(account, index) in record.accounts"
                                    :key="index"
                                >
                                    <td
                                        v-if="!account.tax_id"
                                        class="text-center"
                                    >
                                        {{
                                            account.budget_tax_key
                                                ? account.budget_tax_key
                                                : index + 1
                                        }}
                                    </td>
                                    <td
                                        v-if="!account.tax_id"
                                        class="text-justify"
                                    >
                                        {{
                                            account.spac_description
                                                ? account.spac_description
                                                : "Por asignar"
                                        }}
                                    </td>
                                    <td
                                        v-if="!account.tax_id"
                                        class="text-center"
                                    >
                                        {{
                                            account.code
                                                ? account.code
                                                : "Por asignar"
                                        }}
                                    </td>
                                    <td
                                        v-if="!account.tax_id"
                                        class="text-center"
                                        v-html="account.description"
                                    >
                                    </td>
                                    <td
                                        v-if="!account.tax_id"
                                        class="text-center"
                                    >
                                        {{
                                            formatToCurrency(account.amount, "")
                                        }}
                                    </td>
                                    <td
                                        v-if="!account.tax_id"
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
                                            :value="account.amount"
                                        />
                                        <a
                                            class="btn btn-sm btn-warning btn-action btn-tooltip"
                                            href="#"
                                            data-original-title="Editar cuenta presupuestaria"
                                            data-toggle="modal"
                                            data-target="#add_account"
                                            @click="editAccount(index)"
                                        >
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a
                                            class="btn btn-sm btn-danger btn-action"
                                            href="#"
                                            @click="deleteAccount(index)"
                                            title="Eliminar este registro"
                                            data-toggle="tooltip"
                                        >
                                            <i class="fa fa-minus-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="record.accounts.length > 0"
                                    style="
                                        background-color: rgba(0, 0, 0, 0.05) !important;
                                    "
                                >
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <b>TOTAL {{ currency_symbol }}</b>
                                    </td>
                                    <td class="text-center">
                                        <b>
                                            {{
                                                formatToCurrency(total, "")
                                            }}
                                        </b>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="pad-top-40">
                <h6 class="text-center card-title">
                    Cuentas presupuestarias de impuestos
                </h6>
                <div class="row">
                    <div class="col-md-12 pad-top-20">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th class="col-1">ID</th>
                                    <th class="col-4">Acción Específica</th>
                                    <th class="col-2">Cuenta</th>
                                    <th class="col-2">Descripción</th>
                                    <th class="col-2">Monto</th>
                                    <th class="col-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(tax_account,
                                    index) in record.tax_accounts"
                                    :id="tax_account.parent_account_id"
                                    :key="index"
                                >
                                    <td
                                        v-if="tax_account.tax_id"
                                        class="text-center"
                                    >
                                        {{
                                            tax_account.budget_tax_key
                                                ? tax_account.budget_tax_key
                                                : index + 1
                                        }}
                                    </td>
                                    <td
                                        v-if="tax_account.tax_id"
                                        class="text-justify"
                                    >
                                        {{
                                            tax_account.spac_description
                                                ? tax_account.spac_description
                                                : "Por asignar"
                                        }}
                                    </td>
                                    <td
                                        v-if="tax_account.tax_id"
                                        class="text-center"
                                    >
                                        {{
                                            tax_account.code
                                                ? tax_account.code
                                                : "Por asignar"
                                        }}
                                    </td>
                                    <td
                                        v-if="tax_account.tax_id"
                                        class="text-center"
                                    >
                                        {{ tax_account.description }}
                                    </td>
                                    <td
                                        v-if="tax_account.tax_id"
                                        class="text-center"
                                    >
                                        {{
                                            formatToCurrency(
                                                tax_account.amount,
                                                ""
                                            )
                                        }}
                                    </td>
                                    <td
                                        v-if="tax_account.tax_id"
                                        class="text-center"
                                    >
                                        <input
                                            type="hidden"
                                            name="account_id[]"
                                            readonly
                                            :value="
                                                tax_account.specific_action_id +
                                                    '|' +
                                                    tax_account.account_id
                                            "
                                        />
                                        <input
                                            type="hidden"
                                            name="budget_tax_account_amount[]"
                                            readonly
                                            :value="tax_account.amount"
                                        />
                                    </td>
                                </tr>
                                <tr v-if="record.tax_accounts.length > 0"
                                    style="
                                        background-color: rgba(0, 0, 0, 0.05) !important;
                                    "
                                >
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <b>TOTAL {{ currency_symbol }}</b>
                                    </td>
                                    <td class="text-center">
                                        <b>
                                            {{
                                                formatToCurrency(totalTax, "")
                                            }}
                                        </b>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Modal para agregar cuentas presupuestarias -->
                <div
                    class="modal fade"
                    tabindex="-1"
                    role="dialog"
                    id="add_account"
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
                                    <i class="ion-arrow-graph-up-right"></i>
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
                                            v-for="(error, index) in errors"
                                            :key="index"
                                        >
                                            {{ error }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="row">
                                    <div
                                        class="col-12"
                                        v-if="hasDocumentSelected()"
                                    >
                                        {{ setItemCompromise() }}
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group is-required">
                                            <label>Acción Específica:</label>
                                            <select2
                                                :options="specific_actions"
                                                @input="
                                                    getAccounts();
                                                    getTaxAccounts();
                                                "
                                                v-model="specific_action_id"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group is-required">
                                            <label>Cuenta:</label>
                                            <select2
                                                id="accounts"
                                                :options="accounts"
                                                @input="getAmountAccounts()"
                                                v-model="account_id"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group is-required">
                                            <label>Concepto:</label>
                                            <input
                                                type="text"
                                                class="form-control input-sm"
                                                data-toggle="tooltip"
                                                v-model="account_concept"
                                                title="Indique el concepto de la cuenta presupuestaria a agregar"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mt-4">
                                        <div class="form-group is-required">
                                            <label>Monto:</label>
                                            <input
                                                type="text"
                                                v-input-mask
                                                data-inputmask="
                                                    'alias': 'numeric',
                                                    'allowMinus': 'false'"
                                                onfocus="$(this).select()"
                                                class="form-control input-sm"
                                                data-toggle="tooltip"
                                                title="Indique el monto a asignar para la cuenta seleccionada"
                                                v-model="account_amount"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-4">
                                        <div class="form-group">
                                            <label>Impuesto:</label>
                                            <select2
                                                :options="taxes"
                                                v-model="account_tax_id"
                                                @input="getTaxAccounts()"
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
            </div>
        </div>
        <div class="card-footer text-right">
            <button
                type="reset"
                class="btn btn-default btn-icon btn-round"
                data-toggle="tooltip"
                title="Borrar datos del formulario"
                @click="reset"
            >
                <i class="fa fa-eraser"></i>
            </button>
            <button
                type="button"
                class="btn btn-warning btn-icon btn-round"
                data-toggle="tooltip"
                title="Cancelar y regresar"
                @click="redirect_back(route_list)"
            >
                <i class="fa fa-ban"></i>
            </button>
            <button
                type="button"
                class="btn btn-success btn-icon btn-round"
                data-toggle="tooltip"
                title="Guardar registro"
                @click="createRecord('budget/compromises')"
            >
                <i class="fa fa-save"></i>
            </button>
        </div>
    </div>
</template>

<script>

export default {
    data() {
        return {
            record: {
                id: "",
                institution_id: "",
                compromised_at: "",
                source_document: "",
                description: "",
                accounting_account_id: "",
                receiver: "",
                compromised_manual: false,
                accounts: {},
                tax_accounts: {},
                documentToCompromise: {},
            },
            errors: [],
            institutions: [],

            /**
             * Campos temporales para agregar las cuentas presupuestarias a comprometer
             */
            taxes: [{ id: "", text: "Seleccione..." }],
            taxesData: [],
            specific_actions: [],
            specific_action_id: "",
            accounts: [],
            tax_accounts: [],
            account_id: "",
            account_original: "",
            account_amount_original: 0,
            account_concept: "",
            account_amount: 0,
            old_acc_id: "",
            tax_amount: 0,
            selected_account_amount: 0,
            account_tax_id: "",
            amountError: false,
            accsCount: 0,
            disableSource: false,
            enableFields: false,
            accounting_accounts: [],
            all_receivers: [],
            disableSourceEdit: false,
            total: 0,
            totalTax: 0,
            currency_symbol: "",
            currency_symbol_tmp: "",
            pre_comp: false,

            /**
             * Campos temporales para agregar documentos al compromiso
             */
            document_sources: [],
            document_number: "",
            editIndex: null,
            arrayKeys: [],
        };
    },
    props: {
        edit_object: {
            type: String,
            required: false,
        },
        accounting: {
            type: String,
            required: true,
        },
        budget_class: {
            type: String,
            required: false,
        },
    },
    watch: {
        record: {
            deep: true,
            handler: function() {
                //
            },
        },
    },
    methods: {
        /**
         * Inicializa las variables del compromiso a registrar
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset: function() {
            /**
             * Campos con información a ser almacenada
             */
            this.record.id = "";
            this.record.institution_id = "";
            this.record.compromised_at = "";
            this.record.accounts = [];
            this.record.tax_accounts = [];
            this.source_document = "";
            this.record.source_document = "";
            this.record.description = "";
            this.description = "";
            this.errors = [];

            /**
             * Campos temporales para agregar las cuentas presupuestarias a comprometer
             */
            this.specific_action_id = "";
            this.account_id = "";
            this.account_concept = "";
            this.account_amount = 0;
            this.account_tax_id = "";

            /**
             * Campos temporales para agregar documentos al compromiso
             */
            this.document_sources = [];
            this.document_number = "";
            this.enableFields = false;
            this.disableSource = false;
            this.arrayKeys = [];
        },
        async loadEditData() {
            let vm = this;

            let editData = JSON.parse(vm.edit_object);
            vm.record.id = editData.id;
            vm.arrayKeys = [];

            vm.record.compromised_at = moment(editData.compromised_at)
                .add(1, "days")
                .format("YYYY-MM-DD");

            vm.record.institution_id = editData.institution_id;
            vm.record.description = editData.description;
            vm.record.source_document = editData.document_number;

            if (editData.receiver && editData.receiver.associateable_id) {
                axios
                    .get(`${window.app_url}/all-receivers`, {
                        params: { query: editData.receiver.description },
                    })
                    .then((response) => {
                        this.all_receivers = response.data;
                    });

                setTimeout(() => {
                    let receiver = {
                        text: editData.receiver.description,
                        id: editData.receiver.receiverable_id,
                        class: editData.receiver.receiverable_type,
                        group: editData.receiver.group,
                        accounting_account_id:
                            editData.receiver.associateable_id,
                    };

                    vm.record.receiver = receiver;
                    vm.record.accounting_account_id =
                        editData.receiver.associateable_id;
                }, 1000);

                vm.enableFields = true;
                vm.disableSource = editData.sourceable_type === "Modules\\Payroll\\Models\\Payroll"
                                    || editData.sourceable_type === "Modules\\Purchase\\Models\\PurchaseDirectHire";
                vm.disableSourceEdit = editData.sourceable_type === "Modules\\Purchase\\Models\\PurchaseDirectHire";
            } else {
                vm.disableSource = editData.sourceable_type === "Modules\\Purchase\\Models\\PurchaseDirectHire";
                vm.disableSourceEdit = editData.sourceable_type === "Modules\\Purchase\\Models\\PurchaseDirectHire";
            }
            let a = 0;
            editData.budget_compromise_details.forEach(async function(word) {
                let specificAction = {};
                let account = {};
                vm.specific_action_id =
                    word.budget_sub_specific_formulation.budget_specific_action_id;
                vm.account_id = word.budget_account_id;

                await vm
                    .getSpecificActionDetail(
                        word.budget_sub_specific_formulation
                            .budget_specific_action_id
                    )
                    .then((detail) => (specificAction = detail.record));

                await vm
                    .getAccountDetail(word.budget_account_id)
                    .then((detail) => (account = detail.record));
                let item = account.code.split(".");
                if (item[3] != "00") {
                    if (!word.tax_id) {
                        if (word.budget_tax_key) vm.arrayKeys.push(word.budget_tax_key);
                        vm.record.accounts.push({
                            spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                            code: account.code,
                            description: word.description,
                            amount: word.amount,
                            amountEdit: word.amount,
                            operation: "",
                            budgetCompromiseDetails: word.id,
                            specific_action_id:
                                word.budget_sub_specific_formulation
                                    .budget_specific_action_id,
                            account_id: word.budget_account_id,
                            account_original: word.budget_account_id,
                            account_amount_original: word.amount,
                            tax_id: word.tax_id,
                            budget_tax_key: word.budget_tax_key
                        });
                    };
                }

                if (word.tax_id) {
                    let tax;
                    let tax_percentage;
                    let tax_description;
                    for (tax of vm.taxesData) {
                        if (word.tax_id == tax.id) {
                            tax_description = tax.description;
                            tax_percentage = tax.histories[0].percentage;
                            break;
                        }
                    }
                    vm.record.tax_accounts.push({
                        spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                        code: account.code,
                        description: tax_description ?? word.description,
                        amount: word.amount,
                        amountEdit: word.amount,
                        specific_action_id:
                            word.budget_sub_specific_formulation
                                .budget_specific_action_id,
                        account_id: word.budget_account_id,
                        tax_id: word.tax_id,
                        account_amount_original: word.amount,
                        tax_account_original: word.budget_account_id,
                        budget_tax_key: word.budget_tax_key
                    });
                }
                vm.accsCount = 0;

                for (let accs of vm.record.accounts) {
                    vm.accsCount++;
                }

                await vm.calculateTot();
                await vm.calculateTotalTax();

                // Consultar la moneda asociada al compromiso.
                if (
                    editData.budget_compromise_details &&
                    editData.budget_compromise_details.length > 0 &&
                    editData.budget_compromise_details[0]
                        .budget_sub_specific_formulation
                ) {
                    await vm.currencySymbol(editData.budget_compromise_details[0]
                        .budget_sub_specific_formulation.currency_id);
                } else {
                    // Tomar la moneda configurada por defecto en el sistema.
                    await vm.currencySymbol();
                }
            });
        },

        /**
         * Calcula el total de la suma de las cuentas.
         *
         * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
         */
        calculateTot() {
            const vm = this;
            vm.total = 0;

            for (let i = 0; i < vm.record.accounts.length; i++) {
                let r = vm.record.accounts[i];
                vm.total += parseFloat(r.amount);
            }

            return vm.total;
        },

        /**
         * Calcula el total de la suma de los impuestos.
         *
         * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
         */
        calculateTotalTax() {
            const vm = this;
            vm.totalTax = 0;

            for (let i = 0; i < vm.record.tax_accounts.length; i++) {
                let t = vm.record.tax_accounts[i];
                vm.totalTax += parseFloat(t.amount);
            }

            return vm.totalTax;
        },

        /**
         * Obtener el símbolo de la moneda asociada al precomprimiso
         * seleccionado en el modal de Documento Origen.
         *
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        currencySymbol(id) {
            const vm = this;
            vm.currency_symbol = "";

            if (id) {
                axios.get(`${window.app_url}/currencies/info/` + id).then(response => {
                    if (response.data.result) {
                        vm.loading = true;
                        const currency = response.data.currency;
                        vm.currency_symbol = currency.symbol;
                        vm.currency_symbol_tmp = currency.symbol;
                    } else {
                        console.log("Error en la respuesta de la información de la moneda");
                    }
                    vm.loading = false;
                    return vm.currency_symbol;
                })
                .catch(error => {
                    console.error("Error obteniendo la información de la moneda:", error);
                    vm.loading = false;
                });
            } else {
                /*
                Si no se pasa el id de la moneda se muestra el símbolo de la
                moneda por defecto configurada.
                */
                axios.get(`${window.app_url}/get-currencies/{currency_id?}`).then(response => {
                vm.currencies = response.data;
                const defaultCurrency = vm.currencies.find(currency => currency.default == true);
                    if (defaultCurrency) {
                        vm.currency_symbol = defaultCurrency.text.split(" - ")[0];
                    }
                });
                return vm.currency_symbol;
            }
        },

        /**
         * Elimina una cuenta del listado de cuentas agregadas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param  {integer} index Índice del elemento a eliminar
         */
        deleteAccount(index) {
            let vm = this;
            bootbox.confirm({
                title: "Eliminar cuenta?",
                message: `¿Está seguro de eliminar esta cuenta del compromiso actual?`,
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    },
                },
                callback: function(result) {
                    if (result) {
                        let budget_tax_key = vm.record.accounts[index].budget_tax_key;
                        vm.record.accounts.splice(index, 1);

                        vm.record.tax_accounts = vm.record.tax_accounts.filter(
                            (item) => item.budget_tax_key != budget_tax_key
                        );
                        vm.calculateTot();
                        vm.calculateTotalTax();

                        vm.currency_symbol = vm.currency_symbol_tmp;
                    }
                },
            });
        },
        /**
         * Edita una cuenta del listado de cuentas agregadas
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         * @param  {integer} index Índice del elemento a editar
         */
        editAccount(index) {
            const vm = this;
            vm.account_amount = "";
            vm.account_concept = "";
            vm.account_id = "";
            vm.account_original = "";
            vm.account_amount_original = "";
            vm.account_tax_id = "";
            vm.specific_action_id = "";
            vm.editIndex = index;

            vm.account_amount = vm.record.accounts[vm.editIndex]["amount"];
            vm.account_concept =
                vm.record.accounts[vm.editIndex]["description"];
            vm.account_id = vm.record.accounts[vm.editIndex]["account_id"];
            vm.account_original =
                vm.record.accounts[vm.editIndex]["account_id"];
            vm.account_amount_original =
                vm.record.accounts[vm.editIndex]["account_amount_original"];
            vm.specific_action_id =
                vm.record.accounts[vm.editIndex]["specific_action_id"];
            vm.old_acc_id =

            vm.record.accounts[vm.editIndex]["id"] ??
                vm.record.accounts[vm.editIndex]["old_acc_id"];

            if (!vm.account_id) {
                vm.pre_comp = true;
            } else {
                vm.pre_comp = false;
            }

            let index_tax = vm.record.tax_accounts.findIndex(
                (tax_account) => tax_account.budget_tax_key == vm.record.accounts[vm.editIndex]["budget_tax_key"]
            );
            vm.account_tax_id = index_tax != -1 ? vm.record.tax_accounts[index_tax]["tax_id"] : "";
            vm.getTaxAccounts();

            event.preventDefault();
        },
        /**
         * Elimina los valores de los campos en el modal de las cuentas
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         * @param  {integer} index Índice del elemento a editar
         */
        resetAccount() {
            const vm = this;
            vm.account_amount = "";
            vm.account_concept = "";
            vm.account_id = "";
            vm.account_original = "";
            vm.account_amount_original = "";
            vm.account_tax_id = "";
            vm.specific_action_id = "";
            vm.old_acc_id = "";
            vm.editIndex = null;
        },
        /**
         * Agrega una cuenta presupuestaria al compromiso
         *
         * @method     addAccount
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async addAccount() {
            const vm = this;
            vm.amountError = false;

            if (
                !vm.specific_action_id ||
                !vm.account_id ||
                !vm.account_concept ||
                vm.account_amount === ""
            ) {
                vm.showMessage(
                    "custom",
                    "Alerta!",
                    "warning",
                    "screen-error",
                    "Debe indicar todos los datos requeridos (*)"
                );
                return;
            }
            vm.loading = true;

            let specificAction = {};
            let account = {};
            let tax_account = {};

            await vm
                .getSpecificActionDetail(vm.specific_action_id)
                .then((detail) => (specificAction = detail.record));

            await vm
                .getAccountDetail(vm.account_id)
                .then((detail) => (account = detail.record));

            if (vm.editIndex != null) {
                if (
                    Number(vm.account_amount) -
                        Number(vm.account_amount_original) >
                    Number(vm.selected_account_amount)
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "danger",
                        "screen-error",
                        "El monto a comprometer no puede ser mayor al asignado"
                    );
                    setTimeout(() => {
                        vm.loading = false;
                    }, 500);
                    return;
                }

                if (
                    Number(vm.account_amount) == 0
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "danger",
                        "screen-error",
                        "El monto a comprometer no puede ser cero"
                    );
                    setTimeout(() => {
                        vm.loading = false;
                    }, 500);
                    return;
                }

                if (vm.account_tax_id) {
                    if (vm.tax_accounts.length > 0) {
                        await vm.maxAmount(
                            vm.editIndex,
                            vm.record.tax_accounts,
                            true,
                            vm.account_tax_id
                        );
                    } else {
                        if (vm.record.tax_accounts.length > 0) {
                            for (let tax_account of vm.record.tax_accounts) {
                                if (
                                    tax_account.account_id == vm.account_id &&
                                    tax_account.specific_action_id ==
                                        vm.specific_action_id
                                ) {
                                    vm.tax_amount =
                                        vm.tax_amount + tax_account.amount;
                                    break;
                                }
                            }
                        }

                        await vm.maxAmount(
                            vm.editIndex,
                            vm.record.accounts,
                            false,
                            vm.account_tax_id
                        );
                    }

                    if (vm.amountError == true) {
                        setTimeout(() => {
                            vm.loading = false;
                        })
                        return;
                    }
                } else {
                    if (vm.tax_accounts.length == 0) {
                        if (vm.record.tax_accounts.length > 0) {
                            for (let tax_account of vm.record.tax_accounts) {
                                if (
                                    tax_account.account_id == vm.account_id &&
                                    tax_account.specific_action_id ==
                                        vm.specific_action_id
                                ) {
                                    vm.tax_amount =
                                        vm.tax_amount + tax_account.amount;
                                    break;
                                }
                            }
                        }
                    }
                }

                await vm.maxAmount(
                    vm.editIndex,
                    vm.record.accounts,
                    false,
                    false
                );

                if (vm.amountError == true) {
                    setTimeout(() => {
                        vm.loading = false;
                    })
                    return;
                }

                //se Reescriben los datos de las cuentas presupuestarias de gastos si esta ha sido modificada
                vm.record.accounts[vm.editIndex]["spac_description"] = `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`;
                vm.record.accounts[vm.editIndex]["code"] = account.code;
                vm.record.accounts[vm.editIndex]["description"] = vm.account_concept;
                vm.record.accounts[vm.editIndex]["amount"] = vm.account_amount;
                vm.record.accounts[vm.editIndex]["specific_action_id"] = vm.specific_action_id;
                vm.record.accounts[vm.editIndex]["account_id"] = vm.account_id;
                vm.record.accounts[vm.editIndex]["account_original"] = vm.account_original;
                vm.record.accounts[vm.editIndex]["account_amount_original"] = vm.account_amount_original;
                vm.record.accounts[vm.editIndex]["tax_id"] = "";
                vm.record.accounts[vm.editIndex]["operation"] = "";
                vm.record.accounts[vm.editIndex]["old_acc_id"] = vm.old_acc_id;

                let budget_tax_key = vm.record.accounts[vm.editIndex]["budget_tax_key"];
                if (vm.account_tax_id) {
                    let tax;
                    let tax_percentage;
                    let tax_description;
                    for (tax of vm.taxesData) {
                        if (vm.account_tax_id == tax.id) {
                            tax_description = tax.description;
                            tax_percentage = tax.histories[0].percentage;
                        }
                    }

                    //se verifica que si la cuenta de gastos tiene una cuenta de impuesto asocidada
                    let index = vm.record.tax_accounts.findIndex(
                        (tax_account) => tax_account.budget_tax_key == budget_tax_key
                    );
                    if (index != -1) {
                        let amountTaxEdit =
                        vm.record.tax_accounts[index]["amountEdit"];
                        let taxAccountOriginal =
                            vm.record.tax_accounts[index][
                                "tax_account_original"
                            ];
                        let taxAmountOriginal =
                            vm.record.tax_accounts[index][
                                "account_amount_original"
                            ];
                        if (vm.tax_accounts.length > 0) {

                            vm.record.tax_accounts = vm.record.tax_accounts.filter(
                                (item) => item.budget_tax_key != budget_tax_key
                            );
                            for (let tax_account of vm.tax_accounts) {
                                vm.record.tax_accounts.push({
                                    spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                                    code: tax_account.code,
                                    description: tax_description,
                                    amount:
                                        (vm.account_amount * tax_percentage) /
                                        100 /
                                        vm.tax_accounts.length,
                                    specific_action_id: vm.specific_action_id,
                                    account_id: tax_account.id,
                                    account_amount_original: taxAmountOriginal,
                                    tax_account_original: taxAccountOriginal,
                                    tax_id: vm.account_tax_id,
                                    amountEdit: amountTaxEdit,
                                    operation: "",
                                    old_acc_id: vm.old_acc_id,
                                    budget_tax_key: budget_tax_key
                                });
                            }
                        } else {
                            vm.record.tax_accounts[index]["spac_description"] = `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`;
                            vm.record.tax_accounts[index]["code"] = account.code;
                            vm.record.tax_accounts[index]["description"] = tax_description;
                            vm.record.tax_accounts[index]["amount"] = (vm.account_amount * tax_percentage) / 100;
                            vm.record.tax_accounts[index]["specific_action_id"] = vm.specific_action_id;
                            vm.record.tax_accounts[index]["account_id"] = tax_account && tax_account.id ? tax_account.id : account.id;
                            vm.record.tax_accounts[index]["account_amount_original"] = taxAmountOriginal;
                            vm.record.tax_accounts[index]["tax_account_original"] = taxAccountOriginal;
                            vm.record.tax_accounts[index]["tax_id"] = vm.account_tax_id;
                            vm.record.tax_accounts[index]["amountEdit"] = amountTaxEdit;
                            vm.record.tax_accounts[index]["operation"] = "";
                            vm.record.tax_accounts[index]["old_acc_id"] = vm.old_acc_id;
                        }
                    } else {
                        if (vm.tax_accounts.length > 0) {
                            for (let tax_account of vm.tax_accounts) {
                                vm.record.tax_accounts.push({
                                    spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                                    code: tax_account.code,
                                    description: tax_description,
                                    amount:
                                        (vm.account_amount * tax_percentage) /
                                        100 /
                                        vm.tax_accounts.length,
                                    specific_action_id: vm.specific_action_id,
                                    account_id: tax_account.id,
                                    tax_id: vm.account_tax_id,
                                    budget_tax_key: budget_tax_key,
                                });
                            }
                        } else {
                            vm.record.tax_accounts.push({
                                spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                                code: account.code,
                                description: tax_description,
                                amount: (vm.account_amount * tax_percentage) / 100,
                                specific_action_id: vm.specific_action_id,
                                account_id: vm.account_id,
                                tax_id: vm.account_tax_id,
                                budget_tax_key: budget_tax_key,
                            });
                        }
                    }
                } else {
                    vm.record.tax_accounts = vm.record.tax_accounts.filter(
                        (item) => item.budget_tax_key != budget_tax_key
                    );
                }

                $("#add_account")
                    .find(".close")
                    .click();

                vm.specific_action_id = "";
                vm.account_id = "";
                vm.account_concept = "";
                vm.account_amount = 0;
                vm.account_tax_id = "";
                vm.editIndex = null;
                await vm.calculateTot();
                await vm.calculateTotalTax();

                vm.currency_symbol = vm.currency_symbol_tmp;
            } else {
                if (
                    Number(vm.account_amount) >
                    Number(vm.selected_account_amount)
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "danger",
                        "screen-error",
                        "El monto a comprometer no puede ser mayor al asignado"
                    );
                    setTimeout(() => {
                        vm.loading = false;
                    }, 500);
                    return;
                }

                if (
                    Number(vm.account_amount) == 0
                ) {
                    vm.showMessage(
                        "custom",
                        "Alerta!",
                        "danger",
                        "screen-error",
                        "El monto a comprometer no puede ser cero"
                    );
                    setTimeout(() => {
                        vm.loading = false;
                    }, 500);
                    return;
                }

                if (vm.account_tax_id) {
                    if (vm.tax_accounts.length > 0) {
                        await vm.maxAmount(
                            null,
                            vm.record.tax_accounts,
                            true,
                            vm.account_tax_id
                        );
                    } else {
                        if (vm.record.tax_accounts.length > 0) {
                            for (let tax_account of vm.record.tax_accounts) {
                                if (
                                    tax_account.account_id == vm.account_id &&
                                    tax_account.specific_action_id ==
                                        vm.specific_action_id
                                ) {
                                    vm.tax_amount =
                                        vm.tax_amount + tax_account.amount;
                                    break;
                                }
                            }
                        }

                        await vm.maxAmount(
                            null,
                            vm.record.accounts,
                            false,
                            vm.account_tax_id
                        );
                    }

                    if (vm.amountError == true) {
                        setTimeout(() => {
                            vm.loading = false;
                        })
                        return;
                    }
                } else {
                    if (vm.tax_accounts.length == 0) {
                        if (vm.record.tax_accounts.length > 0) {
                            for (let tax_account of vm.record.tax_accounts) {
                                if (
                                    tax_account.account_id == vm.account_id &&
                                    tax_account.specific_action_id ==
                                        vm.specific_action_id
                                ) {
                                    vm.tax_amount =
                                        vm.tax_amount + tax_account.amount;
                                    break;
                                }
                            }
                        }
                    }
                }

                await vm.maxAmount(null, vm.record.accounts, false, false);

                if (vm.amountError == true) {
                    setTimeout(() => {
                        vm.loading = false;
                    })
                    return;
                }
                let budget_tax_key = vm.generateUnitKey();
                vm.record.accounts.push({
                    spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                    code: account.code,
                    description: vm.account_concept,
                    amount: vm.account_amount,
                    specific_action_id: vm.specific_action_id,
                    account_id: vm.account_id,
                    tax_id: "",
                    budget_tax_key: budget_tax_key,
                });

                if (vm.account_tax_id) {
                    let tax;
                    let tax_percentage;
                    let tax_description;
                    for (tax of vm.taxesData) {
                        if (vm.account_tax_id == tax.id) {
                            tax_description = tax.description;
                            tax_percentage = tax.histories[0].percentage;
                            break;
                        }
                    }

                    if (vm.tax_accounts.length > 0) {
                        for (let tax_account of vm.tax_accounts) {
                            vm.record.tax_accounts.push({
                                spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                                code: tax_account.code,
                                description: tax_description,
                                amount:
                                    (vm.account_amount * tax_percentage) /
                                    100 /
                                    vm.tax_accounts.length,
                                specific_action_id: vm.specific_action_id,
                                account_id: tax_account.id,
                                tax_id: vm.account_tax_id,
                                budget_tax_key: budget_tax_key,
                            });
                        }
                    } else {
                        vm.record.tax_accounts.push({
                            spac_description: `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`,
                            code: account.code,
                            description: tax_description,
                            amount: (vm.account_amount * tax_percentage) / 100,
                            specific_action_id: vm.specific_action_id,
                            account_id: vm.account_id,
                            tax_id: vm.account_tax_id,
                            budget_tax_key: budget_tax_key,
                        });
                    }
                }

                vm.pre_comp = false;

                await vm.calculateTot();
                await vm.calculateTotalTax();
                await vm.currencySymbol();

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
                    callback: function(result) {
                        if (!result) {
                            $("#add_account")
                                .find(".close")
                                .click();
                        }

                        vm.specific_action_id = "";
                        vm.account_id = "";
                        vm.account_concept = "";
                        vm.account_amount = 0;
                        vm.account_tax_id = "";
                        vm.tax_accounts = [];
                    },
                });
            }
            setTimeout(() => {
                vm.loading = false;
            }, 500);
        },

        /**
         * Agrega un documento al compromiso
         *
         * @method     addDocument
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async addDocument(sourceId) {
            const vm = this;
            vm.loading = true;
            vm.enableFields = false;
            vm.record.compromised_manual = false;
            vm.record.receiver = "";
            vm.disableSource = true;
            vm.record.accounting_account_id = "";
            vm.record.accounts = [];
            vm.record.tax_accounts = [];
            vm.arrayKeys = [];

            vm.record.documentToCompromise = JSON.parse(
                JSON.stringify(
                    vm.document_sources.filter((doc) => {
                        return doc.id === sourceId;
                    })[0]
                )
            );

            if (
                vm.record.documentToCompromise &&
                vm.record.documentToCompromise.sourceable
            ) {
                let doc = vm.record.documentToCompromise;
                let date = vm.format_date(doc.sourceable.date ?? doc.sourceable.compromised_at ?? doc.sourceable.created_at, "YYYY-MM-DD");

                vm.record.id = doc.id;
                vm.record.compromised_at = date;
                vm.record.source_document = doc.sourceable
                    ? doc.sourceable_type == 'Modules\\Payroll\\Models\\Payroll'
                        ? doc.document_number
                        : doc.sourceable.code
                    : doc.sourceable.code;
                vm.record.description = doc.description;

                doc.budget_compromise_details.forEach(async function(detail) {
                    let account = {};
                    let specificAction = {};
                    let budget_tax_key;
                    if (detail.budget_tax_key) {
                        vm.arrayKeys.push(detail.budget_tax_key);
                        budget_tax_key = detail.budget_tax_key
                    } else {
                        budget_tax_key = vm.generateUnitKey();
                    }

                    vm.disableSourceEdit = false;
                    if (detail.budget_sub_specific_formulation){
                        await vm
                        .getSpecificActionDetail(detail.budget_sub_specific_formulation.budget_specific_action_id)
                        .then((word) => (specificAction = word.record));

                        await vm
                        .getAccountDetail(detail.budget_account_id)
                        .then((word) => (account = word.record));

                        detail.spac_description = `${specificAction.specificable.code}-${specificAction.code} | ${specificAction.name}`;
                        detail.code = account.code;
                        vm.disableSourceEdit = true;
                    }

                    detail.description = detail.description.replace(
                        /(<([^>]+)>)/gi,
                        ""
                    );

                    detail.specific_action_id = specificAction.id;
                    detail.account_id = account.id;
                    detail.account_original = account.id;
                    detail.account_amount_original = detail.amount;
                    detail.amountEdit = detail.amount;
                    detail.budget_tax_key = budget_tax_key;

                    if (detail.tax_id) {
                        let tax;
                        let tax_percentage;
                        let tax_description;
                        for (tax of vm.taxesData) {
                            if (detail.tax_id == tax.id) {
                                tax_description = tax.description;
                                tax_percentage = tax.histories[0].percentage;
                                break;
                            }
                        }

                        vm.record.tax_accounts.push({
                            spac_description: "",
                            code: "",
                            description: tax_description,
                            amount: (detail.amount * tax_percentage) / 100,
                            specific_action_id: "",
                            account_id: detail.id,
                            tax_id: detail.tax_id,
                            budget_tax_key: detail.budget_tax_key,
                        });

                        detail.tax_id = "";
                    }

                    vm.record.accounts.push(detail);
                });

                await vm.calculateTot();
                await vm.calculateTotalTax();
                await vm.currencySymbol(
                    vm.record.documentToCompromise.sourceable ?
                    vm.record.documentToCompromise.sourceable.currency_id :
                    ''
                );
            }
            vm.loading = false;
        },

        /**
         * Genera una clave única para la lista de cuentas presupuestarias de gastos
         * y de impuestos.
         *
         * @return {string} La clave única generada.
         */
        generateUnitKey() {
            const vm = this;
            let key = vm.arrayKeys.length > 0 ? vm.arrayKeys[vm.arrayKeys.length - 1] : 1;

            while (vm.arrayKeys.indexOf(key) !== -1) {
                key ++;
            }
            vm.arrayKeys.push(key);
            return key;
        },
        /**
         * Obtiene las Acciones Específicas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param {string} type Tipo de registro
         */
        async getSpecificActions() {
            const vm = this;
            vm.loading = true;
            vm.specific_actions = [];
            vm.accounts = [];

            if (
                vm.record.compromised_at &&
                vm.record.source_document &&
                vm.record.institution_id
            ) {
                let year = vm.record.compromised_at.split("-")[0];
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
                $("#add_account")
                    .find(".close")
                    .click();
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
        /**
         * Obtiene las cuentas presupuestarias formuladas de la acción específica seleccionada
         *
         * @method    getAccounts
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getAccounts() {
            const vm = this;
            vm.loading = true;
            vm.accounts = [];

            if (vm.specific_action_id) {
                let specificActionId = vm.specific_action_id;
                let compromisedAt = vm.record.compromised_at;
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

            vm.loading = false;
        },
        /**
         * Obtiene las cuentas presupuestarias formuladas de la acción específica seleccionada
         * que son para impuestos
         *
         * @method    getTaxAccounts
         *
         * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        async getTaxAccounts() {
            const vm = this;
            vm.loading = true;
            vm.tax_accounts = [];

            if (vm.specific_action_id) {
                let specificActionId = vm.specific_action_id;
                let compromisedAt = vm.record.compromised_at;
                await axios
                    .get(
                        `${window.app_url}/budget/get-opened-tax-accounts/${specificActionId}/${compromisedAt}`
                    )
                    .then((response) => {
                        if (response.data.result) {
                            vm.tax_accounts = response.data.records;
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
                    vm.tax_account_id = vm.record.tax_accounts[vm.editIndex]
                        ? vm.record.tax_accounts[vm.editIndex]["tax_account_id"]
                        : vm.record.accounts[vm.editIndex]["tax_account_id"];
                }
            }

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

            const result = vm.accounts.filter(
                (account) => account.id == vm.account_id
            );
            if (typeof result[0] !== "undefined") {
                vm.selected_account_amount = result[0].amount;
            }
        },

        /**
         * Obtiene los registros precomprometidos que aún no han sido comprometidos
         *
         * @method     getDocumentSources
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getDocumentSources() {
            let vm = this;
            let appUrl = window.app_url;
            let institutionId = vm.record.institution_id;
            let year = window.execution_year;
            vm.loading = true;
            axios
                .get(
                    `${appUrl}/budget/compromises/get-document-sources/${institutionId}/${year}`
                )
                .then((response) => {
                    vm.document_sources = response.data.records;
                    vm.loading = false;
                })
                .catch((error) => {
                    console.warn(error);
                });
        },
        /**
         * Determina si se ha seleccionado un documento desde otras fuentes para ser comprometido
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @return    {Boolean}              Devuelve verdadero si tiene un documento seleccionado,
         *                                   de lo contrario devuelve falso
         */
        hasDocumentSelected() {
            const vm = this;
            let compromise = vm.record.documentToCompromise;
            return (
                typeof compromise.budget_compromise_details !== "undefined" &&
                compromise.budget_compromise_details.length > 0
            );
        },
        /**
         * Muestra el item del compromiso proveniente de fuentes externas que se esta comprometiendo
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @return {String} Texto con información del ítem a comprometer
         */
        setItemCompromise() {
            const vm = this;
            let totalItems =
                vm.record.documentToCompromise.budget_compromise_details.length;
            let currentItem = vm.record.accounts.length;
            return `Item ${currentItem} / ${totalItems}`;
        },
        /**
         * Listado de impuestos
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getTaxes() {
            const vm = this;
            axios
                .get(`${window.app_url}/get-taxes`)
                .then((response) => {
                    if (response.data.records.length > 0) {
                        vm.taxesData = response.data.records;
                        for (let tax of vm.taxesData) {
                            vm.taxes.push({
                                id: tax.id,
                                text:
                                    tax.name +
                                    " " +
                                    tax.histories[0].percentage +
                                    "%",
                            });
                        }
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        /**
         * Método que permite actualizar información
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         * @param  {string} url Ruta de la acci´on que modificará los datos
         */
        updateRecord(url) {
            const vm = this;
            vm.loading = true;
            var fields = {};
            url = vm.setUrl(url);

            for (let account of vm.record.accounts) {
                if (account.account_id != account.account_original) {
                    account.equal = "N";
                } else {
                    account.equal = "S";
                }

                if (
                    parseFloat(account.amountEdit) ==
                        parseFloat(account.amount) &&
                    account.equal == "S"
                ) {
                    account.operation = "I";
                    account.amountEdit = account.amountEdit;
                } else if (
                    parseFloat(account.amountEdit) ==
                        parseFloat(account.amount) &&
                    account.equal == "N"
                ) {
                    account.operation = "C";
                    account.amountEdit = account.amountEdit;
                }

                if (
                    parseFloat(account.amountEdit) > parseFloat(account.amount)
                ) {
                    if (account.equal == "S") {
                        account.operation = "S";
                        account.amountEdit =
                            parseFloat(account.account_amount_original) -
                            parseFloat(account.amount);
                    } else {
                        account.operation = "S";
                        account.amountEdit = parseFloat(account.amount);
                    }
                } else if (
                    parseFloat(account.amountEdit) < parseFloat(account.amount)
                ) {
                    if (account.equal == "S") {
                        account.operation = "R";
                        account.amountEdit =
                            parseFloat(account.account_amount_original) -
                            parseFloat(account.amount);
                    } else {
                        account.operation = "R";
                        account.amountEdit = parseFloat(account.amount);
                    }
                }
                if (typeof account.amountEdit === "undefined") {
                    account.amountEdit = 0;
                }

                if (typeof account.operation === "undefined") {
                    account.operation = "";
                }
            }

            for (let tax_account of vm.record.tax_accounts) {
                if (
                    tax_account.account_id != tax_account.tax_account_original
                ) {
                    tax_account.equal = "N";
                } else {
                    tax_account.equal = "S";
                }

                if (
                    parseFloat(tax_account.amountEdit) ==
                        parseFloat(tax_account.amount) &&
                    tax_account.equal == "S"
                ) {
                    tax_account.operation = "I";
                    tax_account.amountEdit = tax_account.amountEdit;
                } else if (
                    parseFloat(tax_account.amountEdit) ==
                        parseFloat(tax_account.amount) &&
                    tax_account.equal == "N"
                ) {
                    tax_account.operation = "C";
                    tax_account.amountEdit = tax_account.amountEdit;
                }

                if (
                    parseFloat(tax_account.amountEdit) >
                    parseFloat(tax_account.amount)
                ) {
                    if (tax_account.equal == "S") {
                        tax_account.operation = "S";
                        tax_account.amountEdit =
                            parseFloat(tax_account.account_amount_original) -
                            parseFloat(tax_account.amount);
                    } else {
                        tax_account.operation = "S";
                        tax_account.amountEdit = parseFloat(tax_account.amount);
                    }
                } else if (
                    parseFloat(tax_account.amountEdit) <
                    parseFloat(tax_account.amount)
                ) {
                    if (tax_account.equal == "S") {
                        tax_account.operation = "R";
                        tax_account.amountEdit =
                            parseFloat(tax_account.account_amount_original) -
                            parseFloat(tax_account.amount);
                    } else {
                        tax_account.operation = "R";
                        tax_account.amountEdit = parseFloat(tax_account.amount);
                    }
                }
                if (typeof tax_account.amountEdit === "undefined") {
                    tax_account.amountEdit = 0;
                }

                if (typeof tax_account.operation === "undefined") {
                    tax_account.operation = "";
                }
            }

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            axios
                .patch(
                    `${url}${url.endsWith("/") ? "" : "/"}${vm.record.id}`,
                    fields
                )
                .then((response) => {
                    if (typeof response.data.redirect !== "undefined") {
                        location.href = response.data.redirect;
                    } else {
                        vm.readRecords(url);
                        vm.reset();
                        vm.loading = false;
                        vm.showMessage("update");
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

        /**
         * Método que valida que el monto a agregar no supere el existente en las cuentas
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        async maxAmount(editIndex, accounts, hasTaxAccount, taxId) {
            const vm = this;

            let fields = {};

            vm.record.has_tax_account = 0;

            if (typeof editIndex != "undefined" && vm.pre_comp == false) {
                vm.record.specific_action_id = vm.specific_action_id;
                vm.record.account_amount = parseFloat(vm.account_amount);
                vm.record.account_id = vm.account_id;
                vm.record.editIndex = editIndex;
                vm.record.account_amount_original = vm.account_amount_original;
                vm.record.accs_count = vm.accsCount;
                vm.record.tax_account_id = taxId;
                vm.record.use_accounts = accounts;
                vm.record.selected_account_amount = vm.selected_account_amount;
            } else {
                vm.record.specific_action_id = vm.specific_action_id;
                vm.record.account_amount = parseFloat(vm.account_amount);
                vm.record.account_id = vm.account_id;
                vm.record.tax_account_id = taxId;
                vm.record.accs_count = vm.accsCount;
                vm.record.use_accounts = accounts;
                vm.record.selected_account_amount = vm.selected_account_amount;
            }

            if (hasTaxAccount) {
                vm.record.has_tax_account = 1;

                for (let taxAccount of vm.tax_accounts) {
                    vm.record.account_id = taxAccount.id;
                    vm.record.selected_account_amount = taxAccount.amount;

                    await axios
                        .post(
                            `${window.app_url}/budget/compromises/max-amount-tax-accounts`,
                            vm.record
                        )
                        .then((response) => {
                            if (typeof response.data !== "undefined") {
                                return response.data.result;
                            } else {
                                vm.errors = [];
                            }
                        })
                        .catch((error) => {
                            vm.showMessage(
                                "custom",
                                "Alerta!",
                                "danger",
                                "screen-error",
                                "El monto a comprometer no puede ser mayor al asignado"
                            );

                            vm.amountError = true;
                        });
                }
            }

            if (!hasTaxAccount) {
                await axios
                    .post(
                        `${window.app_url}/budget/compromises/max-amount-accounts`,
                        vm.record
                    )
                    .then((response) => {
                        if (typeof response.data !== "undefined") {
                            return response.data.result;
                        } else {
                            vm.errors = [];
                        }
                    })
                    .catch((error) => {
                        vm.showMessage(
                            "custom",
                            "Alerta!",
                            "danger",
                            "screen-error",
                            "El monto a comprometer no puede ser mayor al asignado"
                        );

                        vm.amountError = true;
                    });
            }

            vm.tax_amount = 0;
        },

        /**
         * Habilita/Deshabilita los campos de beneficiario asociado al compromiso
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        enableField() {
            const vm = this;
            if (vm.record.source_document != "") {
                vm.enableFields = true;
                vm.record.compromised_manual = true;
            } else {
                vm.enableFields = false;
                vm.record.compromised_manual = false;
                vm.record.receiver = "";
                vm.record.accounting_account_id = "";
            }
        },

        /**
         * Obtiene un listado de cuentas patrimoniales
         *
         * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getAccountingAccounts() {
            const vm = this;

            if (!vm.accounting) {
                return;
            }

            vm.accounting_accounts = [];
            axios
                .get(`${window.app_url}/accounting/accounts`)
                .then((response) => {
                    if (response.data.records.length > 0) {
                        vm.accounting_accounts.push({
                            id: "",
                            text: "Seleccione...",
                        });
                        $.each(response.data.records, function() {
                            vm.accounting_accounts.push({
                                id: this.id,
                                text: `${this.code} - ${this.denomination}`,
                                disabled: `${this.code}`.split('.')[6] == '000' ? true : false
                            });
                        });
                    }
                })
                .catch((error) => {
                    vm.logs(
                        "PayrollConceptsComponent",
                        258,
                        error,
                        "getAccountingAccounts"
                    );
                });
        },

        /**
         * Método que realiza una consulta para obtener todos los receptores que coincidan
         * con el query de la búsqueda
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        searchReceivers(query) {
            this.all_receivers = [];

            axios
                .get(`${window.app_url}/all-receivers`, {
                    params: { query: query },
                })
                .then((response) => {
                    this.all_receivers = response.data;
                });
        },

        selectAccountingAccount() {
            const vm = this;

            if (vm.record.receiver.accounting_account_id) {
                vm.record.accounting_account_id =
                    vm.record.receiver.accounting_account_id;
            } else {
                vm.record.accounting_account_id = "";
            }
        },

        addTag(newTag) {
            let tag = [
                {
                    label: "Otros",
                    group: [
                        {
                            id: newTag,
                            text: newTag,
                            class: this.budget_class,
                            group: "Otros",
                        },
                    ],
                },
            ];

            this.all_receivers.push(tag);
            this.record.receiver = tag[0]["group"][0];
        },
    },
    mounted() {
        let vm = this;
        vm.reset();
        vm.getInstitutions();
        vm.taxesData = [];
        vm.getTaxes();
        if (vm.accounting) {
            vm.getAccountingAccounts();
        }
        setTimeout(() => {
            if (vm.edit_object) {
                vm.loadEditData();
            }
            else {
                // Si el Compromiso no se ha registrado, se asigna la fecha
                // actual al campo Fecha del formulario.
                vm.record.compromised_at = moment().format("YYYY-MM-DD");
            }
        }, 1000);

        $("#add_source")
            .on("shown.bs.modal", function() {
                /** Carga los documentos que faltan por comprometer */
                vm.getDocumentSources();
            })
            .on("hide.bs.modal", function() {
                /** @type array Inicializa el arreglo de los documentos por comprometer */
                vm.document_sources = [];
            });

        $("#add_account")
            .on("shown.bs.modal", function() {
                if (vm.specific_actions.length === 0) {
                    /** Carga las acciones específicas para la respectiva formulación */
                    vm.getSpecificActions();
                }
            })
            .on("hide.bs.modal", function() {
                /** @type {Array} Inicializa el arreglo de acciones específicas a seleccionar */
                vm.specific_actions = [];
                /** @type array Inicializa el arreglo de las cuentas presupuestarias seleccionadas */
                vm.accounts = [];
            });
    },
};
</script>
