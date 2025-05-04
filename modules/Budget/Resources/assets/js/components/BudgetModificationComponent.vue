<template>
    <div>
        <div class="card-body">
            <div class="container">
                <div class="alert alert-danger" v-if="errors.length > 0">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes
                    errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
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
                <div class="col-md-2" id="approved_at">
                    <div class="form-group is-required">
                        <label class="control-label" for="approved_at">
                            Fecha de creación
                        </label>
                        <input type="date" name="approved_at" class="form-control input-sm" placeholder="dd/mm/YY"
                            data-toggle="tooltip" v-model="record.approved_at" @input="getSpecificActions()" title="
                                Fecha en la que se aprobó la modificación presupuestaria
                            ">
                    </div>
                </div>
                <div class="col-md-6" id="institution_id" v-if="institutions.length > 0">
                    <div class="form-group is-required">
                        <label class="control-label" for="institution_id">
                            Institución
                        </label>
                        <select2 :options="institutions" v-model="record.institution_id"></select2>
                    </div>
                </div>
                <div class="col-4" v-if="type_modification === 'AC'">
                    <div class="form-group is-required">
                        <label class="control-label">Moneda</label>
                        <select2 tabindex="3" :options="currencies" id="currencyy" data-toggle="tooltip"
                            title="Seleccione el tipo de moneda (requerido)" v-model="record.currency_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="document">
                    <div class="form-group is-required">
                        <label class="control-label" for="document">
                            Nro. Documento
                        </label>
                        <input type="text" name="document" class="form-control input-sm" placeholder="Nro. Documento"
                            data-toggle="tooltip" v-model="record.document" title="
                                Número del documento, decreto o misiva que avala
                                la modificación presupuestaria
                            " maxlength="20">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <upload-documents
                        inputLabel="Documento" inputTooltip="Seleccione el(los) documento(s) que avala(n) la modificación presupuestaria"
                        :parentRecord="'documentFiles'"
                    />
                </div>
                <div class="col-12 col-md-4" v-if="typeof(record.documentUrl) != 'undefined' && record.documentUrl">
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
            </div>
            <div class="row">
                <div class="col-12" id="description">
                    <div class="form-group is-required">
                        <label class="control-label" for="description">
                            Descripción
                        </label>
                        <input type="text" name="description" class="form-control input-sm"
                            placeholder="Descripción / Detalles" data-toggle="tooltip" title="
                                Descripción o detalle de la modificación presupuestaria
                            " v-model="record.description">
                    </div>
                </div>
            </div>
            <div class="pad-top-40">
                <h6 class="text-center card-title">Cuentas presupuestarias</h6>
                <div class="row">
                    <div class="col-12 pad-top-20" v-if="!(type_modification === 'TR')">
                        <table border="1px" cellpadding="0px" cellspacing="0px" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Acción Específica</th>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>
                                        <a class="btn btn-sm btn-info btn-action btn-tooltip" href="#"
                                            data-original-title="Agregar nuevo registro" data-toggle="modal"
                                            data-target="#add_account" v-if="
                                                record.approved_at &&
                                                record.institution_id &&
                                                record.document && record.description
                                            ">
                                            <i class="fa fa-plus-circle"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(account, index) in modification_accounts" :key="index">
                                    <td class="text-center">{{ account.from_spac_description }}</td>
                                    <td class="text-center">{{ account.from_code }}</td>
                                    <td>{{ account.from_description }}</td>
                                    <td class="text-center">
                                        {{ account.from_amount }}
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="from_account_id[]" readonly
                                            :value="account.from_specific_action_id + '|' + account.from_account_id">
                                        <input type="hidden" name="from_budget_account_amount[]" readonly
                                            :value="account.from_amount">
                                        <a class="btn btn-sm btn-warning btn-action btn-tooltip" href="#"
                                            data-original-title="Editar cuenta presupuestaria" data-toggle="modal"
                                            data-target="#add_account" @click="editAccount(index)">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a v-if="edit_object == ''" class="btn btn-sm btn-danger btn-action" href="#"
                                            @click="deleteAccount(index)" title="Eliminar este registro"
                                            data-toggle="tooltip">
                                            <i class="fa fa-minus-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12 pad-top-20" v-else>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th colspan="4" class="border-right">
                                        Datos de Origen
                                    </th>
                                    <th colspan="4">
                                        Datos de Destino
                                    </th>
                                    <th>
                                        <a class="btn btn-sm btn-info btn-action btn-tooltip" href="#"
                                            data-original-title="Agregar nuevo registro" data-toggle="modal"
                                            data-target="#add_account" v-if="record.approved_at && record.institution_id &&
                                                record.document && record.description">
                                            <i class="fa fa-plus-circle"></i>
                                        </a>
                                    </th>
                                </tr>
                                <tr>
                                    <th>Acción Específica</th>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th class="border-right">Monto</th>
                                    <th>Acción Específica</th>
                                    <th>Cuenta</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(account, index) in modification_accounts" :key="index">
                                    <td>{{ account.from_spac_description }}</td>
                                    <td>{{ account.from_code }}</td>
                                    <td>{{ account.from_description }}</td>
                                    <td class="text-right border-right">
                                        {{ account.from_amount }}
                                    </td>
                                    <td>{{ account.to_spac_description }}</td>
                                    <td>{{ account.to_code }}</td>
                                    <td>{{ account.to_description }}</td>
                                    <td class="text-right">{{ account.to_amount }}</td>
                                    <td class="text-center">
                                        <input type="hidden" name="from_account_id[]" readonly
                                            :value="account.from_specific_action_id + '|' + account.from_account_id">
                                        <input type="hidden" name="from_budget_account_amount[]" readonly
                                            :value="account.from_amount">
                                        <a class="btn btn-sm btn-warning btn-action btn-tooltip" href="#"
                                            data-original-title="Editar cuenta presupuestaria" data-toggle="modal"
                                            data-target="#add_account" @click="editAccount(index)">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a v-if="edit_object == ''" class="btn btn-sm btn-danger btn-action" href="#"
                                            @click="deleteAccount(index)" title="Eliminar este registro"
                                            data-toggle="tooltip">
                                            <i class="fa fa-minus-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal fade" tabindex="-1" role="dialog" id="add_account">
                    <div class="modal-dialog vue-crud" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <h6>
                                    <i class="ion-arrow-graph-up-right"></i>
                                    Agregar Cuenta{{ (type_modification === 'TR') ? 's' : '' }}
                                </h6>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger" v-if="errors.length > 0">
                                    <ul>
                                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                    </ul>
                                </div>
                                <div class="row" v-if="(type_modification === 'TR')">
                                    <div class="col-12">
                                        <h6 class="text-center">
                                            Cuenta cedentes
                                        </h6>
                                    </div>
                                </div>
                                <div v-if="(type_modification === 'TR') || (type_modification === 'RE')">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group is-required">
                                                <label>Acción Específica:</label>
                                                <select2 :options="specific_actions" @input="getAccounts()"
                                                    v-model="from_specific_action_id" />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group is-required">
                                                <label>Cuenta:</label>
                                                <select2 id="accounts" :options="accounts" @input="isDisableTR()"
                                                    v-model="from_account_id" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="(type_modification === 'AC')">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group is-required">
                                                <label>Acción Específica:</label>
                                                <select2 :options="specific_actions"
                                                    v-model="from_specific_action_id" />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group is-required">
                                                <label>Cuenta:</label>
                                                <select2 :options="accountsAC" v-model="from_account_id" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group is-required">
                                            <label>Monto:</label>
                                            <input type="text" v-input-mask data-inputmask="
                                                    'alias': 'numeric',
                                                    'allowMinus': 'false'
                                                " onfocus="$(this).select()" class="form-control input-sm"
                                                data-toggle="tooltip" v-model="from_amount"
                                                title="Indique el monto a asignar para la cuenta seleccionada">
                                        </div>
                                    </div>
                                </div>
                                <div v-if="(type_modification === 'TR')">
                                    <div class="row">
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-center">
                                                Cuenta a Acreditar
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group is-required">
                                                <label>Acción Específica:</label>
                                                <select2 :options="specific_actions" v-model="to_specific_action_id"
                                                    @input="isDisableTR()" />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group is-required">
                                                <label>Cuenta:</label>
                                                <select2 id="accountsAC" :options="accountsAC"
                                                    v-model="to_account_id" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group is-required">
                                                <label>Monto:</label>
                                                <input type="text" v-input-mask data-inputmask="
                                                        'alias': 'numeric',
                                                        'allowMinus': 'false'
                                                    " onfocus="$(this).select()" class="form-control input-sm"
                                                    data-toggle="tooltip" readonly title="
                                                        Indique el monto a asignar para la cuenta seleccionada
                                                    " v-model="to_amount">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" @click="resetAccount"
                                    class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal">
                                    Cerrar
                                </button>
                                <button type="button" @click="addAccount"
                                    class="btn btn-primary btn-sm btn-round btn-modal-save">
                                    Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="reset" class="btn btn-default btn-icon btn-round" data-toggle="tooltip"
                title="Borrar datos del formulario" @click='reset'>
                <i class="fa fa-eraser"></i>
            </button>
            <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                title="Cancelar y regresar" @click="redirect_back(route_list)">
                <i class="fa fa-ban"></i>
            </button>
            <button type="button" class="btn btn-success btn-icon btn-round" data-toggle="tooltip"
                title="Guardar registro" @click="createRecord('budget/modifications')">
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
                type: '',
                approved_at: '',
                institution_id: '',
                currency_id: '',
                document: '',
                description: '',
                budget_account_id: [],
                documentFiles: []
            },
            institutions: [],
            currencies: [],
            specific_actions: [],
            accounts: [{
                id: '',
                text: 'Seleccione...'
            }],
            accountsAC: [{
                id: '',
                text: 'Seleccione...'
            }],
            /*
             * Variables para cuentas a agregar en créditos adicionales, traspasos y reducciones
             */
            from_specific_action_id: '',
            from_account_id: '',
            from_account_original: '',
            from_amount: 0,
            from_amount_original: 0,
            /*
             * Variables para cuentas a agregar en traspasos
             */
            to_specific_action_id: '',
            to_account_id: '',
            to_account_original: '',
            to_amount: 0,
            to_amount_original: 0,
            errors: [],
            modification_accounts: [],
            editIndex: null,
        }
    },
    props: {
        type_modification: {
            type: String,
            required: true,
        },
        edit_object: {
            type: String,
            required: false
        }
    },
    watch: {
        /**
         * Monitorea modificaciones a las cuentas agregadas para guardarlas
         * temporalmente en un localStorage
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        modification_accounts: {
            deep: true,
            handler: function () {
                if (this.modification_accounts.length === 0) {
                    localStorage.removeItem('modification_accounts');
                }
                else {
                    this.record.budget_account_id = this.modification_accounts;
                    localStorage.modification_accounts = JSON.stringify(
                        this.modification_accounts
                    );
                }
            }
        },
        /**
         * Asigna el monto desde la cuenta de origen a la cuenta de destino en traspasos
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        from_amount: function () {
            if (this.type_modification === "TR") {
                this.to_amount = this.from_amount;
            }
        }
    },
    methods: {
        /**
         * Inicializa las variables de las cuentas a agregar
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset: function () {
            this.from_specific_action_id = '';
            this.from_account_id = '';
            this.from_amount = 0;
            this.to_specific_action_id = '';
            this.to_account_id = '';
            this.to_amount = 0;
            this.editIndex = null;
        },
        showDocument() {
            return `${window.app_url}/${this.record.documentUrl}`;
        },
        /**
         * Carga los datos de los tipos de moneda
         *
         * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
         */
        async getCurrencies() {
            const vm = this;
            const response = await axios.get(`${window.app_url}/get-currencies/{currency_id?}`);
            vm.currencies = response.data;
            return response.data;
        },
        /**
         * Método que permite dar formato a una fecha
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         *
         * @param  {string} value  Fecha ser formateada
         * @param  {string} format Formato de la fecha
         *
         * @return {string}       Fecha con el formato establecido
         */
        format_date: function (value, format = 'DD/MM/YYYY') {
            return moment.utc(value).format(format);
        },

        /**
         * Carga los datos para ser editados
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        loadEditData: function () {
            let vm = this;
            let editData = JSON.parse(vm.edit_object);

            vm.record.id = editData.id;
            vm.record.approved_at = vm.format_date(editData.approved_at, 'YYYY-MM-DD');
            vm.record.document = editData.document;
            vm.record.description = editData.description;
            vm.record.institution_id = editData.institution_id;
            vm.record.currency_id = editData.currency_id;
            vm.record.documentFiles = (editData.document_file) ? [editData.document_file.id] : [];
            vm.record.documentUrl = (editData.document_file) ? editData.document_file.url : '';

            let array_accounts = [];

            var from_add = {
                spac_description: '',
                code: '',
                description: '',
                amount: 0,
                account_id: '',
                specific_action_id: '',
            };

            var to_add = {
                spac_description: '',
                code: '',
                description: '',
                amount: 0,
                account_id: '',
                specific_action_id: '',
            };

            var i = 0;
            $.each(editData.budget_modification_accounts, function (index, account) {
                var sp = account.budget_sub_specific_formulation.specific_action;
                var spac_desc = `${sp.specificable.code} - ${sp.code} | ${sp.name}`;
                var acc = account.budget_account;
                if (acc.specific != '00') {
                    var code = `${acc.group}.${acc.item}.${acc.generic}.${acc.specific}.${acc.subspecific}`;
                    if (account.operation === "D" || (account.operation === "I" && vm.type_modification !== "TR")) {
                        from_add.spac_description = spac_desc;
                        from_add.code = code;
                        from_add.description = account.budget_account.denomination;
                        from_add.amount = account.amount;
                        from_add.amount_edit = account.amount;
                        from_add.account_id = acc.id;
                        from_add.account_original = acc.id;
                        from_add.specific_action_id = sp.id;
                    }
                    else {
                        to_add.spac_description = spac_desc;
                        to_add.code = code;
                        to_add.description = account.budget_account.denomination;
                        to_add.amount = account.amount;
                        to_add.amount_edit = account.amount;
                        to_add.account_id = acc.id;
                        to_add.account_original = acc.id;
                        to_add.specific_action_id = sp.id;
                    }

                    if ((index % 2) === 1 || vm.type_modification !== "TR") {
                        array_accounts[i] = {
                            from_spac_description: from_add.spac_description,
                            from_code: from_add.code,
                            from_description: from_add.description,
                            from_amount: from_add.amount,
                            from_amount_edit: from_add.amount_edit,
                            from_account_id: from_add.account_id,
                            from_account_original: from_add.account_id,
                            from_amount_original: from_add.amount,
                            from_specific_action_id: from_add.specific_action_id,
                            to_spac_description: to_add.spac_description,
                            to_code: to_add.code,
                            to_description: to_add.description,
                            to_amount: to_add.amount,
                            to_amount_edit: to_add.amount_edit,
                            to_account_id: to_add.account_id,
                            to_account_original: to_add.account_id,
                            to_amount_original: to_add.amount,
                            to_specific_action_id: to_add.specific_action_id,
                            operation: ''
                        };
                        i++;
                    }
                }

            });
            vm.modification_accounts = array_accounts;
            vm.showAccountsTable();
            vm.getSpecificActions();
        },
        /**
         * Agrega una cuenta para el registro del crédito adicional
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @return {boolean} Devuelve falso si no se ha indicado alguna información requerida
         */
        addAccount: function () {
            const vm = this;
            let amountEdit;
            if (vm.editIndex != null) {
                amountEdit = vm.modification_accounts[vm.editIndex]['from_amount_edit'];
            }
            let to_add = {
                from_spac_description: '',
                from_code: '',
                from_description: '',
                from_amount: 0,
                from_amount_edit: amountEdit,
                from_account_id: '',
                from_account_original: '',
                from_amount_original: '',
                from_specific_action_id: '',
                to_spac_description: '',
                to_code: '',
                to_description: '',
                to_amount: 0,
                to_amount_edit: 0,
                to_account_id: '',
                to_account_original: '',
                to_amount_original: '',
                to_specific_action_id: '',
                operation: '',
            };

            if (!vm.from_specific_action_id) {
                vm.showMessage(
                    'custom', 'Alerta!', 'danger', 'screen-error',
                    'Debe seleccionar una acción específica'
                );
                return false;
            }
            if (!vm.from_account_id) {
                vm.showMessage(
                    'custom', 'Alerta!', 'danger', 'screen-error',
                    'Debe seleccionar una cuenta presupuestaria'
                );
                return false;
            }
            if (vm.from_amount <= 0) {
                vm.showMessage(
                    'custom', 'Alerta!', 'danger', 'screen-error',
                    'Debe indicar un monto'
                );
                return false;
            }
            if (vm.type_modification == 'RE' || vm.type_modification == 'TR') {
                for (let acc of vm.accounts) {
                    if (acc.id == vm.from_account_id) {
                        if (Number(vm.from_amount) > Number(acc.amount) + Number(vm.from_amount_original)) {
                            vm.showMessage(
                                "custom",
                                "Alerta!",
                                "warning",
                                "screen-error",
                                "El campo monto no puede ser mayor al asignado"
                            );
                            return;
                        }
                    }
                }
            }

            /** Obtiene datos de la acción específica seleccionada */
            axios.get(
                `${window.app_url}/budget/detail-specific-actions/${vm.from_specific_action_id}`
            ).then(response => {
                if (response.data.result) {
                    let spec = response.data.record;

                    /** Obtiene datos de la cuenta presupuestaria */
                    axios.get(
                        `${window.app_url}/budget/detail-accounts/${vm.from_account_id}`
                    ).then(response => {
                        if (response.data.result) {
                            let acc = response.data.record;
                            to_add.from_code = `${acc.group}.${acc.item}.${acc.generic}.${acc.specific}.${acc.subspecific}`;
                            to_add.from_description = acc.denomination;
                            to_add.from_spac_description = `${spec.specificable.code} - ${spec.code} | ${spec.name}`;
                            to_add.from_amount = vm.from_amount;
                            to_add.from_account_id = vm.from_account_id;
                            to_add.from_account_original = vm.from_account_original;
                            to_add.from_amount_original = vm.from_amount_original;
                            to_add.from_specific_action_id = vm.from_specific_action_id;

                            if (this.type_modification === "TR") {
                                axios.get(
                                    `${window.app_url}/budget/detail-specific-actions/${vm.to_specific_action_id}`
                                ).then(response => {
                                    if (response.data.result) {
                                        let to_spec = response.data.record;

                                        /** Obtiene datos de la cuenta presupuestaria */
                                        axios.get(
                                            `${window.app_url}/budget/detail-accounts/${vm.to_account_id}`
                                        ).then(response => {
                                            if (response.data.result) {
                                                if (vm.editIndex != null) {
                                                    vm.modification_accounts.splice(vm.editIndex, 1);
                                                    let to_acc = response.data.record;
                                                    to_add.to_code = `${to_acc.group}.${to_acc.item}.${to_acc.generic}.${to_acc.specific}.${to_acc.subspecific}`;
                                                    to_add.to_description = to_acc.denomination;
                                                    to_add.to_spac_description = `${to_spec.specificable.code} - ${to_spec.code} | ${to_spec.name}`;
                                                    to_add.to_amount = vm.to_amount;
                                                    to_add.to_amount_edit = amountEdit;
                                                    to_add.to_account_id = vm.to_account_id;
                                                    to_add.to_account_original = vm.to_account_original;
                                                    to_add.to_amount_original = vm.to_amount_original;
                                                    to_add.to_specific_action_id = vm.to_specific_action_id;
                                                    vm.modification_accounts.push(to_add);

                                                    $('.close').click();
                                                    vm.reset();
                                                } else {
                                                    let to_acc = response.data.record;
                                                    to_add.to_code = `${to_acc.group}.${to_acc.item}.${to_acc.generic}.${to_acc.specific}.${to_acc.subspecific}`;
                                                    to_add.to_description = to_acc.denomination;
                                                    to_add.to_spac_description = `${to_spec.specificable.code} - ${to_spec.code} | ${to_spec.name}`;
                                                    to_add.to_amount = vm.to_amount;
                                                    to_add.to_account_id = vm.to_account_id;
                                                    to_add.to_account_original = vm.to_account_original;
                                                    to_add.to_amount_original = vm.to_amount_original;
                                                    to_add.to_specific_action_id = vm.to_specific_action_id;
                                                    vm.modification_accounts.push(to_add);

                                                    $('.close').click();
                                                    vm.reset();
                                                }
                                            }
                                        }).catch(error => {
                                            console.log(error);
                                        });
                                    }
                                }).catch(error => {
                                    console.log(error);
                                });
                            }
                            else {
                                if (vm.editIndex != null) {
                                    vm.modification_accounts.splice(vm.editIndex, 1);
                                    vm.modification_accounts.push(to_add);
                                    $('.close').click();
                                    vm.reset();
                                } else {
                                    vm.modification_accounts.push(to_add);
                                    $('.close').click();
                                    vm.reset();
                                }
                            }

                        }
                    }).catch(error => {
                        console.log(error);
                    });
                }
            }).catch(error => {
                console.log(error);
            });
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
                message: `Esta seguro de eliminar esta cuenta del registro de la modificación
                        presupuestaria?`,
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar'
                    }
                },
                callback: function (result) {
                    if (result) {
                        vm.modification_accounts.splice(index, 1);
                    }
                }
            });
        },
        /**
         * Elimina los valores de los campos en el modal de las cuentas
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         * @param  {integer} index Índice del elemento a editar
         */
        resetAccount() {
            const vm = this;
            vm.from_account_id = '';
            vm.from_amount = '';
            vm.from_specific_action_id = '';
            vm.editIndex = null;
        },
        /**
         * Edita una cuenta del listado de cuentas agregadas
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
         * @param  {integer} index Índice del elemento a editar
         */
        editAccount(index) {
            const vm = this;
            vm.from_account_id = '';
            vm.from_account_original = '';
            vm.from_amount_original = '';
            vm.from_amount = '';
            vm.from_specific_action_id = '';
            vm.to_account_id = '';
            vm.to_account_original = '';
            vm.to_amount_original = '';
            vm.to_amount = '';
            vm.to_specific_action_id = '';
            vm.editIndex = index;

            vm.from_account_id = vm.record.budget_account_id[vm.editIndex]['from_account_id'];
            vm.from_account_original = vm.record.budget_account_id[vm.editIndex]['from_account_original'];
            vm.from_amount_original = vm.record.budget_account_id[vm.editIndex]['from_amount_original'];
            vm.from_amount = vm.record.budget_account_id[vm.editIndex]['from_amount'];
            vm.from_specific_action_id = vm.record.budget_account_id[vm.editIndex]['from_specific_action_id'];
            vm.to_account_original = vm.record.budget_account_id[vm.editIndex]['to_account_original'];
            vm.to_amount_original = vm.record.budget_account_id[vm.editIndex]['to_amount_original'];
            vm.to_amount = vm.record.budget_account_id[vm.editIndex]['to_amount'];
            vm.to_specific_action_id = vm.record.budget_account_id[vm.editIndex]['to_specific_action_id'];
        },

        /**
         * Obtiene las Acciones Específicas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        async getSpecificActions() {
            const vm = this;
            vm.loading = true;
            vm.specific_actions = [];
            vm.accounts = [];

            let year = vm.record.approved_at.split("-")[0];
            let url = `${window.app_url}/budget/get-group-specific-actions/${year}/true`;
            await axios.get(url).then(response => {
                vm.specific_actions = response.data;
            }).catch(error => {
                console.error(error);
            });

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

            if (vm.from_specific_action_id) {
                let specificActionId = vm.from_specific_action_id;
                let approvedAt = vm.record.approved_at;
                await axios.get(
                    `${window.app_url}/budget/get-opened-accounts/${specificActionId}/${approvedAt}`
                ).then(response => {
                    if (response.data.result) {
                        vm.accounts = response.data.records;
                    }
                    if (response.data.records.length === 1 && response.data.records[0].id === "") {
                        vm.showMessage(
                            'custom', 'Alerta!', 'danger', 'screen-error',
                            `No existen cuentas aperturadas para esta acción específica o con saldo para la fecha
                                seleccionada`
                        );
                    }
                }).catch(error => {
                    console.error(error);
                });

                if (vm.editIndex != null) {
                    vm.from_account_id = vm.record.budget_account_id[vm.editIndex]['from_account_id'];
                    vm.to_account_id = vm.record.budget_account_id[vm.editIndex]['to_account_id'];
                }
            }
            vm.isDisable();

            vm.loading = false;
        },

        /**
         * Obtiene el listado de cuentas resupuestarias a mostrar para su selección
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getAccountsAC: function () {
            const vm = this;
            vm.accountsAC = [{
                id: '',
                text: 'Seleccione...',
                title: ''
            }];
            axios.get(`${window.app_url}/budget/accounts/egress-list/`).then(response => {
                if (!$.isEmptyObject(response.data.records)) {
                    $.each(response.data.records, function () {
                        if (this.specific !== "00") {
                            vm.accountsAC.push({
                                id: this.id,
                                text: `${this.code} - ${this.denomination}`,
                                title: 'Disponible: '
                            });
                        }
                    });
                }
            }).catch(error => {
                vm.logs('BudgetModificationComponent.vue', 415, error, 'getAccounts');
            });
        },

        /**
         * Método para visualizar solo las cuentas que no son padres en la tabla
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        showAccountsTable() {
            const vm = this;

            for (let [index, account] of vm.modification_accounts.entries()) {
                let item = account.from_code.split('.');
                if (item[3] == '00') {
                    vm.modification_accounts.splice(index, 1);
                }
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
            let accountsTable = [{ "id": "", "text": "Seleccione..." }];
            for (let acc of vm.modification_accounts) {
                accountsTable.push(acc);
            }
            for (let [index, acc] of vm.accounts.entries()) {
                for (let account of accountsTable) {
                    if (account.from_specific_action_id == vm.from_specific_action_id) {
                        let esc = document.getElementById('accounts')
                        if (acc.id == account.from_account_id) {
                            $(Object.values(esc.options)[index]).prop("disabled", true);
                        }
                    }
                }
            }
        },

        /**
         * Método que permite habilitar/deshabilitar las opciones de las cuentas
         * al realizar un traspaso
         *
         * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
         *
         */
        isDisableTR() {
            const vm = this;

            if (vm.from_specific_action_id == vm.to_specific_action_id) {
                for (let [index, account] of vm.accountsAC.entries()) {
                    let acc = document.getElementById('accountsAC');
                    if (acc) {
                        if (account.id == vm.from_account_id) {
                            $(Object.values(acc.options)[index]).prop("disabled", 'disabled');
                        } else {
                            $(Object.values(acc.options)[index]).prop("disabled", false);
                        }
                    }
                }
            } else {
                for (let [index, account] of vm.accountsAC.entries()) {
                    let acc = document.getElementById('accountsAC');
                    if (acc) {
                        $(Object.values(acc.options)[index]).prop("disabled", false);
                    }
                }
            }
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

            for (let account of vm.record.budget_account_id) {
                if (vm.record.type === 'AC') {
                    if (account.from_account_id != account.from_account_original) {
                        account.equal = 'N';
                    } else {
                        account.equal = 'S';
                    }

                    if (parseFloat(account.from_amount_edit) == parseFloat(account.from_amount) && account.equal == 'S') {
                        account.operation = 'I';
                        account.from_amount_edit = account.from_amount_edit;
                    } else if (parseFloat(account.from_amount_edit) == parseFloat(account.from_amount) && account.equal == 'N') {
                        account.operation = 'C';
                        account.from_amount_edit = account.from_amount_edit;
                    }

                    if (parseFloat(account.from_amount_edit) < parseFloat(account.from_amount)) {
                        if (account.equal == 'S') {
                            account.operation = 'S';
                            account.from_amount_edit = parseFloat(account.from_amount_edit) - parseFloat(account.from_amount);
                        } else {
                            account.operation = 'S';
                            account.from_amount_edit = parseFloat(account.from_amount);
                        }
                    }
                    if (parseFloat(account.from_amount_edit) > parseFloat(account.from_amount)) {
                        if (account.equal == 'S') {
                            account.operation = 'R';
                            account.from_amount_edit = parseFloat(account.from_amount_edit) - parseFloat(account.from_amount);
                        } else {
                            account.operation = 'R';
                            account.from_amount_edit = parseFloat(account.from_amount);
                        }
                    }
                    if (typeof account.from_amount_edit === 'undefined') {
                        account.from_amount_edit = 0;
                    }

                    if (typeof account.operation === 'undefined') {
                        account.operation = '';
                    }
                }

                if (vm.record.type === 'RE') {
                    if (account.from_account_id != account.from_account_original) {
                        account.equal = 'N';
                    } else {
                        account.equal = 'S';
                    }

                    if (parseFloat(account.from_amount_edit) == parseFloat(account.from_amount) && account.equal == 'S') {
                        account.operation = 'I';
                        account.from_amount_edit = account.from_amount_edit;
                    } else if (parseFloat(account.from_amount_edit) == parseFloat(account.from_amount) && account.equal == 'N') {
                        account.operation = 'C';
                        account.from_amount_edit = account.from_amount_edit;
                    }

                    if (parseFloat(account.from_amount_edit) > parseFloat(account.from_amount)) {
                        if (account.equal == 'S') {
                            account.operation = 'S';
                            account.from_amount_edit = parseFloat(account.from_amount_edit) - parseFloat(account.from_amount);
                        } else {
                            account.operation = 'S';
                            account.from_amount_edit = parseFloat(account.from_amount);
                        }
                    }
                    if (parseFloat(account.from_amount_edit) < parseFloat(account.from_amount)) {
                        if (account.equal == 'S') {
                            account.operation = 'R';
                            account.from_amount_edit = parseFloat(account.from_amount_edit) - parseFloat(account.from_amount);
                        } else {
                            account.operation = 'R';
                            account.from_amount_edit = parseFloat(account.from_amount);
                        }
                    }
                    if (typeof account.from_amount_edit === 'undefined') {
                        account.from_amount_edit = 0;
                    }

                    if (typeof account.operation === 'undefined') {
                        account.operation = '';
                    }
                }
                if (vm.record.type === 'TR') {
                    if (account.from_account_id != account.from_account_original) {
                        account.from_equal = 'N';
                    } else {
                        account.from_equal = 'S';
                    }

                    if (parseFloat(account.from_amount_edit) == parseFloat(account.from_amount) && account.from_equal == 'S') {
                        account.from_operation = 'I';
                        account.from_amount_edit = account.from_amount_edit;
                    } else if (parseFloat(account.from_amount_edit) == parseFloat(account.from_amount) && account.from_equal == 'N') {
                        account.from_operation = 'C';
                        account.from_amount_edit = account.from_amount_edit;
                    } else if (parseFloat(account.from_amount_edit) > parseFloat(account.from_amount)) {
                        if (account.from_equal == 'S') {
                            account.from_operation = 'S';
                            account.from_amount_edit = parseFloat(account.from_amount_edit) - parseFloat(account.from_amount);
                        } else {
                            account.from_operation = 'S';
                            account.from_amount_edit = parseFloat(account.from_amount);
                        }
                    } else {
                        if (account.from_equal == 'S') {
                            account.from_operation = 'R';
                            account.from_amount_edit = parseFloat(account.from_amount_edit) - parseFloat(account.from_amount);
                        } else {
                            account.from_operation = 'R';
                            account.from_amount_edit = parseFloat(account.from_amount);
                        }
                    }

                    if (account.to_account_id != account.to_account_original) {
                        account.to_equal = 'N';
                    } else {
                        account.to_equal = 'S';
                    }

                    if (parseFloat(account.to_amount_edit) == parseFloat(account.to_amount) && account.to_equal == 'S') {
                        account.to_operation = 'I';
                        account.to_amount_edit = account.to_amount_edit;
                    } else if (parseFloat(account.to_amount_edit) == parseFloat(account.to_amount) && account.to_equal == 'N') {
                        account.to_operation = 'C';
                        account.to_amount_edit = account.to_amount_edit;
                    } else if (parseFloat(account.to_amount_edit) > parseFloat(account.to_amount)) {
                        if (account.to_equal == 'S') {
                            account.to_operation = 'S';
                            account.to_amount_edit = parseFloat(account.to_amount_edit) - parseFloat(account.to_amount);
                        } else {
                            account.to_operation = 'S';
                            account.to_amount_edit = parseFloat(account.to_amount);
                        }
                    } else {
                        if (account.to_equal == 'S') {
                            account.to_operation = 'R';
                            account.to_amount_edit = parseFloat(account.to_amount_edit) - parseFloat(account.to_amount);
                        } else {
                            account.to_operation = 'R';
                            account.to_amount_edit = parseFloat(account.to_amount);
                        }
                    }
                    if (typeof account.from_amount_edit === 'undefined' || typeof account.to_amount_edit === 'undefined') {
                        account.from_amount_edit = 0;
                        account.to_amount_edit = 0;
                    }

                    if (typeof account.operation === 'undefined') {
                        account.operation = '';
                    }
                }
            }

            for (var index in vm.record) {
                fields[index] = vm.record[index];
            }
            axios.put(`${url}${(url.endsWith('/')) ? '' : '/'}${vm.record.id}`, fields).then(response => {
                if (typeof (response.data.redirect) !== "undefined") {
                    location.href = response.data.redirect;
                }
                else {
                    vm.readRecords(url);
                    vm.reset();
                    vm.loading = false;
                    vm.showMessage('update');
                }

            }).catch(error => {
                vm.errors = [];

                if (typeof (error.response) != "undefined") {
                    for (var index in error.response.data.errors) {
                        if (error.response.data.errors[index]) {
                            vm.errors.push(error.response.data.errors[index][0]);
                        }
                    }
                }
                vm.loading = false;
            });
        },
    },
    mounted() {
        const vm = this;
        vm.reset();

        vm.getInstitutions();
        vm.record.type = vm.type_modification;
        if (vm.type_modification === 'AC' || vm.type_modification === 'TR') {
            vm.getAccountsAC();
        }

        vm.getCurrencies().then(() => {
            if (vm.edit_object) {
                vm.loadEditData()
            } else {
                vm.record.currency_id = vm.currencies.filter((item) => item['default'] == true)[0].id;
                /** Si el crédito adicional no se ha registrado, muestra la fecha actual por defecto. */
                vm.record.approved_at = moment().format("YYYY-MM-DD");
                vm.getSpecificActions();
            }
        });
    },
};
</script>
