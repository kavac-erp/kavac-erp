<template>
    <div class="card">
        <div class="card-header">
            <h6 class="card-title text-uppercase">
                Formulación de presupuesto de gastos por sub específicas
                <a id="btnHelp" class="btn-help" href="javascript:void(0)" title="" data-toggle="tooltip"
                    data-original-title="
                        Haz click para ver la ayuda guiada de este elemento
                    " @click="initUIGuide(helpFile)">
                    <i class="ion ion-ios-help-outline cursor-pointer"></i>
                </a>
            </h6>
            <div class="card-btns">
                <a class="btn btn-sm btn-primary btn-custom" data-toggle="tooltip" href="#" title="Ir atrás"
                    @click="redirect_back(route_list)">
                    <i class="fa fa-reply"></i>
                </a>
                <a class="card-minimize btn btn-card-action btn-round" data-toggle="tooltip" href="#" title="Minimizar">
                    <i class="now-ui-icons arrows-1_minimal-up"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="alert-icon">
                    <i class="now-ui-icons objects_support-17"></i>
                </div>
                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="now-ui-icons ui-1_simple-remove"></i>
                    </span>
                </button>
                <ul>
                    <li v-for="(error, index) in errors" :key="index">{{ error }}
                    </li>
                </ul>
            </div>
            <div class="row mb-3" v-if="fiscal_years.length > 0">
                <div id="help_date" class="col-4">
                    <div class="form-group is-required">
                        <label>Fecha de generación:</label>
                        <input type="date" class="form-control input-sm" placeholder="Fecha de generación" tabindex="1"
                            data-toggle="tooltip" title="Fecha de generación" v-model="record.date">
                    </div>
                </div>
                <div id="help_fiscal_years" class="col-4">
                    <label class="control-label">Ejercicio fiscal</label>
                    <select2 id="fiscal_years" tabindex="1" :options="fiscal_years" v-model="record.fiscal_year">
                    </select2>
                </div>
                <div id="help_institution" class="col-4">
                    <div class="form-group is-required">
                        <label class="control-label">Institución</label>
                        <select2 tabindex="2" :options="institutions" v-model="record.institution_id" disabled>
                        </select2>
                        <input type="hidden" v-model="record.id" />
                    </div>
                </div>
                <div id="help_currencies" class="col-4">
                    <div class="form-group is-required" v-if="currencies">
                        <label class="control-label">Moneda</label>
                        <select2 tabindex="3" :options="currencies" v-model="record.currency_id"/>
                    </div>
                </div>
                <div id="help_financement_type" class="col-4">
                    <div class="form-group is-required">
                        <label>Fuente de financiamiento:</label>
                        <select2 tabindex="4" :options="financementTypes"
                            @input="loadFinancementSources ? getFinancementSources() : ''"
                            v-model="record.financement_type_id">
                        </select2>
                    </div>
                </div>
                <div id="help_financement_source" class="col-4">
                    <div class="form-group">
                        <label>Tipo de financiamiento:</label>
                        <select2 tabindex="5" :options="financementSources" v-model="record.financement_source_id">
                        </select2>
                    </div>
                </div>
                <div id="help_amount" class="col-4">
                    <div class="form-group">
                        <label>Monto:</label>
                        <div class="row">
                            <div class="col-12">
                                {{ formatToCurrency(record.financement_amount, '') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="help_project" class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input id="sel_project" class="custom-control-input sel_pry_acc" type="radio"
                            name="project_centralized_action" value="project" />
                        <label class="custom-control-label" for="sel_project">
                            Proyecto
                        </label>
                    </div>
                    <div class="mt-4">
                        <select2 id="project_id" disabled :options="projects" v-model="record.project_id"
                            @input="getSpecificActions('Project')">
                        </select2>
                    </div>
                </div>
                <div id="help_action" class="col-6 mt-4">
                    <div class="custom-control custom-switch">
                        <input id="sel_centralized_action" class="custom-control-input sel_pry_acc" type="radio"
                            name="project_centralized_action" value="centralized_action" />
                        <label class="custom-control-label" for="sel_centralized_action">
                            Acción Centralizada
                        </label>
                    </div>
                    <div class="mt-4">
                        <select2 id="centralized_action_id" disabled :options="centralized_actions"
                            v-model="record.centralized_action_id" @input="getSpecificActions('CentralizedAction')">
                        </select2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="help_specific" class="col-12 mt-4">
                    <div class="form-group is-required">
                        <label class="control-label">Acción Específica</label>
                        <select2 id="specific_action_id" disabled :options="specific_actions"
                            v-model="record.specific_action_id"></select2>
                    </div>
                </div>
            </div>
            <div class="row-flex">
                <label class="control-label"> Distribución financiera</label>
                <div id="help_table_search" class="search-section">
                    <input class="form-control input-sm" type="text"
                        placeholder="Buscador de cuentas por código y/o denominación" @keyup.enter="searchAccounts"
                        v-model="search" />
                    <button type="button" class="btn btn-primary btn-sm btn-round" @click="searchAccounts">
                        Buscar <i class="fa fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm btn-round" @click="cleanSearch">
                        Ver todas <i class="fa fa-eraser"></i>
                    </button>
                </div>
            </div>
            <!-- Botones para importar y exportar la tabla de formulación del presupuesto -->
            <div class="row">
                <div class="col-4 offset-md-8">
                    <div class="float-right">
                        <form action="" id="" method="post" role="form" class="form" enctype="multipart/form-data">
                            <button type="button" data-toggle="tooltip"
                                :disabled="!record.institution_id || !record.currency_id || !record.specific_action_id"
                                class="btn btn-sm btn-info btn-import"
                                title="Presione para importar la información. Los archivos permitidos son: .csv, .ods, .xls o .xlsx"
                                aria-label="Presione para importar la información"
                                @click="setFile('import_formulation')">
                                <i class="fa fa-upload"></i>
                            </button>
                            <button type="button" data-toggle="tooltip" class="btn btn-sm btn-warning btn-import"
                                title="Presione para exportar la información."
                                aria-label="Presione para exportar la información" @click="getExportFormulation()"
                                :disabled="!record.institution_id || !record.currency_id || !record.specific_action_id">
                                <i class="fa fa-download"></i>
                            </button>
                            <input type="file" id="import_formulation" class="nodisplay"
                                @change="getImportFormulation('import_formulation')" />
                        </form>
                    </div>
                </div>
            </div>
            <!-- Final de botones para importar y exportar la tabla de formulación del presupuesto -->

            <!-- Tabla para la formulación del presupuesto -->
            <div id="help_table_formulation" class="table-responsive" style="overflow-y: auto; max-height: 250px;">
                <table class="table table-formulation">
                    <thead class="sticky-top bg-light border-top border-light" style="top: -2px">
                        <tr>
                            <th></th>
                            <th class="text-capitalize" style="min-width: 100px">Código</th>
                            <th class="text-capitalize" style="min-width: 100px">Denominación</th>
                            <th class="text-capitalize" style="min-width: 100px">Real</th>
                            <th class="text-capitalize" style="min-width: 100px">Estimado</th>
                            <th class="text-capitalize" style="min-width: 100px">Total año</th>
                            <th class="text-capitalize" style="min-width: 100px" v-for="(month, index) in months"
                                :key="index">
                                <span v-if="month === 'jan'">Ene</span>
                                <span v-else-if="month === 'apr'">Abr</span>
                                <span v-else-if="month === 'aug'">Ago</span>
                                <span v-else-if="month === 'dec'">Dic</span>
                                <span v-else>{{ month }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(account, index) in records" data-formulated="false" :id="account.id"
                            :class="account.specific === '00' ? 'disable-row' : ''" class="accounts"
                            :data-code="account.code" :data-index="index" :key="index">
                            <td>
                                <i v-if="account.locked" class="fa fa-ban text-white"
                                    title="Elemento bloqueado, de solo lectura" data-toggle="tooltip"></i>
                                <i v-else :id="'add_account_' + account.id" class="fa fa-eye text-blue cursor-pointer"
                                    title="Pulse para agregar esta cuenta presupuestaria a la formulación"
                                    data-toggle="tooltip" @click="validateCurrency(); showAccountInputs(index)"></i>
                            </td>
                            <td class="td-code">{{ account.code }}</td>
                            <td>{{ account.denomination }}</td>
                            <td class="td-with-border">
                                <input class="form-control input-sm total_real" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.total_real_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'real')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm total_estimated" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.total_estimated_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'estimated')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm total_year" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.total_year_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'year')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm jan" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.jan_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm feb" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.feb_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm mar" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.mar_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm apr" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.apr_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm may" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.may_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm jun" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.jun_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm jul" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.jul_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm aug" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.aug_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm sep" type="text" data-toggle="tooltip"
                                    onfocus="this.select()"
                                    oninput="this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');"
                                    :readonly="account.locked" v-model="account.sep_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm oct" type="text" data-toggle="tooltip"
                                    onfocus="this.select()" :readonly="account.locked" v-model="account.oct_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm nov" type="text" data-toggle="tooltip"
                                    onfocus="this.select()" :readonly="account.locked" v-model="account.nov_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                            <td class="td-with-border">
                                <input class="form-control input-sm dec" type="text" data-toggle="tooltip"
                                    onfocus="this.select()" :readonly="account.locked" v-model="account.dec_amount"
                                    v-show="account.locked || account.formulated"
                                    @change="calculateAmounts(index, 'month')" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Final de la tabla para la formulación del presupuesto -->
            <buttonsDisplay :route_list="app_url + '/budget/subspecific-formulations'"></buttonsDisplay>
        </div>
        <div class="card-footer text-right">
            <buttonsDisplay :route_list="app_url + '/budget/subspecific-formulations'" display="false"></buttonsDisplay>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            record: {
                id: '',
                date: '',
                year: '',
                institution_id: '',
                currency_id: '',
                project_id: '',
                centralized_action_id: '',
                specific_action_id: '',
                fiscal_year: '',
                formulated_accounts: [],
                financement_type_id: '',
                financement_source_id: '',
                financement_amount: '',
            },
            createBootbox: false,
            loadFinancementSources: true,
            financementTypes: [],
            financementSources: [],
            errors: [],
            records: [],
            search: '',
            /** @type {Array} Lista de años fiscales abiertos */
            fiscal_years: [],
            currencies: [],
            decimals: 2,
            projects: [],
            centralized_actions: [],
            specific_actions: [],
            months: [
                'jan',
                'feb',
                'mar',
                'apr',
                'may',
                'jun',
                'jul',
                'aug',
                'sep',
                'oct',
                'nov',
                'dec'
            ]
        };
    },
    props: {
        formulationId: Number,
        institutions: Array,
    },
    methods: {
        /**
         * Reinicia los valores de los elementos del formulario
         *
         * @author  In- Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        reset: function () {
            this.record.date = '';
            this.record.year = '';
            this.record.currency_id = '';
            this.record.project_id = '';
            this.record.centralized_action_id = '';
            this.record.specific_action_id = '';
            this.record.formulated_accounts = [];
            this.record.financement_type_id = '',
                this.record.financement_source_id = '',
                this.record.financement_amount = '',
                this.errors = [];

            document.getElementById('fiscal_years').value = '';
            document.getElementById('fiscal_years').dispatchEvent(new Event('change'));
            document.getElementById('sel_centralized_action').checked = false;
            document.getElementById('sel_project').checked = false;
            document.getElementById('project_id').disabled = true;
            document.getElementById('centralized_action_id').disabled = true;
        },
        /**
         * Calcula los montos de las cuentas presupuestarias formuladas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param  {integer} index Indice del elemento
         * @param  {string}  input Nombre del campo que activó el evento
         */
        calculateAmounts(index, input) {
            let vm = this;

            vm.records[index].total_real_amount = parseFloat(vm.records[index].total_real_amount || 0).toFixed(vm.decimals);
            vm.records[index].total_estimated_amount = parseFloat(
                vm.records[index].total_estimated_amount || 0
            ).toFixed(vm.decimals);
            vm.records[index].total_year_amount = parseFloat(vm.records[index].total_year_amount || 0).toFixed(vm.decimals);
            vm.records[index].jan_amount = parseFloat(vm.records[index].jan_amount || 0).toFixed(vm.decimals);
            vm.records[index].feb_amount = parseFloat(vm.records[index].feb_amount || 0).toFixed(vm.decimals);
            vm.records[index].mar_amount = parseFloat(vm.records[index].mar_amount || 0).toFixed(vm.decimals);
            vm.records[index].apr_amount = parseFloat(vm.records[index].apr_amount || 0).toFixed(vm.decimals);
            vm.records[index].may_amount = parseFloat(vm.records[index].may_amount || 0).toFixed(vm.decimals);
            vm.records[index].jun_amount = parseFloat(vm.records[index].jun_amount || 0).toFixed(vm.decimals);
            vm.records[index].jul_amount = parseFloat(vm.records[index].jul_amount || 0).toFixed(vm.decimals);
            vm.records[index].aug_amount = parseFloat(vm.records[index].aug_amount || 0).toFixed(vm.decimals);
            vm.records[index].sep_amount = parseFloat(vm.records[index].sep_amount || 0).toFixed(vm.decimals);
            vm.records[index].oct_amount = parseFloat(vm.records[index].oct_amount || 0).toFixed(vm.decimals);
            vm.records[index].nov_amount = parseFloat(vm.records[index].nov_amount || 0).toFixed(vm.decimals);
            vm.records[index].dec_amount = parseFloat(vm.records[index].dec_amount || 0).toFixed(vm.decimals);

            if (input === 'year') {
                /** Asigna los montos mensuales equitativamente */
                let partial_ammount = parseFloat(
                    parseFloat(vm.records[index].total_year_amount) / 12
                ).toFixed(vm.decimals);
                $.each(vm.months, function () {
                    vm.records[index][this + '_amount'] = partial_ammount;
                });
            } else if (input === 'month') {
                /** Asigna el monto total para la cuenta a formular */
                let total_year = 0;
                $.each(vm.months, function () {
                    total_year += parseFloat(
                        vm.records[index][this + '_amount']
                    );
                });
                vm.records[index].total_year_amount = parseFloat(
                    total_year
                ).toFixed(2);
            }

            /** Filtra las cuentas bloqueadas para solo lectura (cuentas de nivel superior) */
            const lock_acc = vm.records.filter(account => {
                return account.locked;
            });
            /** Filtra las cuentas agregadas para la formulación del presupuesto */
            const form_acc = vm.records.filter(account => {
                return !account.locked && account.formulated;
            });
            /** Filtra las cuentas de grupo */
            const group_acc = vm.records.filter(acc => {
                return (
                    acc.item === '00' &&
                    acc.generic === '00' &&
                    acc.specific === '00' &&
                    acc.subspecific === '00'
                );
            });
            /** Filtra las cuentas de ítems */
            const item_acc = vm.records.filter(acc => {
                return (
                    acc.item !== '00' &&
                    acc.generic === '00' &&
                    acc.specific === '00' &&
                    acc.subspecific === '00'
                );
            });
            /** Filtra las cuentas genéricas */
            const generic_acc = vm.records.filter(acc => {
                return (
                    acc.generic !== '00' &&
                    acc.specific === '00' &&
                    acc.subspecific === '00'
                );
            });
            /** Filtra las cuentas específicas */
            const specific_acc = vm.records.filter(acc => {
                return (
                    acc.specific !== '00' &&
                    acc.subspecific === '00'
                );
            });

            /** Calcula los montos de la cuenta específica */
            $.each(specific_acc, function () {
                let s = this,
                    total_year = 0,
                    total_estimated = 0,
                    total_real = 0;
                let total_jan = 0,
                    total_feb = 0,
                    total_mar = 0,
                    total_apr = 0,
                    total_may = 0,
                    total_jun = 0,
                    total_jul = 0,
                    total_aug = 0,
                    total_sep = 0,
                    total_oct = 0,
                    total_nov = 0,
                    total_dec = 0;

                if (
                    vm.records[index].subspecific !== '00' &&
                    vm.records[index].group === s.group &&
                    vm.records[index].item === s.item &&
                    vm.records[index].generic === s.generic &&
                    vm.records[index].specific === s.specific
                ) {
                    s.total_real_amount = 0;
                    s.total_estimated_amount = 0;
                    s.total_year_amount = 0;
                    s.jan_amount = 0;
                    s.feb_amount = 0;
                    s.mar_amount = 0;
                    s.apr_amount = 0;
                    s.may_amount = 0;
                    s.jun_amount = 0;
                    s.jul_amount = 0;
                    s.aug_amount = 0;
                    s.sep_amount = 0;
                    s.oct_amount = 0;
                    s.nov_amount = 0;
                    s.dec_amount = 0;
                }

                $.each(form_acc, function () {
                    if (
                        this.group === s.group &&
                        this.item === s.item &&
                        this.generic === s.generic &&
                        this.specific === s.specific
                    ) {
                        total_year += parseFloat(this.total_year_amount);
                        total_estimated += parseFloat(
                            this.total_estimated_amount
                        );
                        total_real += parseFloat(this.total_real_amount);
                        total_jan += parseFloat(this.jan_amount);
                        total_feb += parseFloat(this.feb_amount);
                        total_mar += parseFloat(this.mar_amount);
                        total_apr += parseFloat(this.apr_amount);
                        total_may += parseFloat(this.may_amount);
                        total_jun += parseFloat(this.jun_amount);
                        total_jul += parseFloat(this.jul_amount);
                        total_aug += parseFloat(this.aug_amount);
                        total_sep += parseFloat(this.sep_amount);
                        total_oct += parseFloat(this.oct_amount);
                        total_nov += parseFloat(this.nov_amount);
                        total_dec += parseFloat(this.dec_amount);
                    }
                });
                if (parseFloat(total_year) > 0) {
                    s.total_year_amount = parseFloat(total_year).toFixed(
                        vm.decimals
                    );
                    s.total_estimated_amount = parseFloat(
                        total_estimated
                    ).toFixed(vm.decimals);
                    s.total_real_amount = parseFloat(total_real).toFixed(
                        vm.decimals
                    );
                    s.jan_amount = parseFloat(total_jan).toFixed(vm.decimals);
                    s.feb_amount = parseFloat(total_feb).toFixed(vm.decimals);
                    s.mar_amount = parseFloat(total_mar).toFixed(vm.decimals);
                    s.apr_amount = parseFloat(total_apr).toFixed(vm.decimals);
                    s.may_amount = parseFloat(total_may).toFixed(vm.decimals);
                    s.jun_amount = parseFloat(total_jun).toFixed(vm.decimals);
                    s.jul_amount = parseFloat(total_jul).toFixed(vm.decimals);
                    s.aug_amount = parseFloat(total_aug).toFixed(vm.decimals);
                    s.sep_amount = parseFloat(total_sep).toFixed(vm.decimals);
                    s.oct_amount = parseFloat(total_oct).toFixed(vm.decimals);
                    s.nov_amount = parseFloat(total_nov).toFixed(vm.decimals);
                    s.dec_amount = parseFloat(total_dec).toFixed(vm.decimals);
                }
            });

            /** Calcula los montos de la cuenta genérica */
            $.each(generic_acc, function () {
                let g = this,
                    total_year = 0,
                    total_estimated = 0,
                    total_real = 0;
                let total_jan = 0,
                    total_feb = 0,
                    total_mar = 0,
                    total_apr = 0,
                    total_may = 0,
                    total_jun = 0,
                    total_jul = 0,
                    total_aug = 0,
                    total_sep = 0,
                    total_oct = 0,
                    total_nov = 0,
                    total_dec = 0;
                $.each(form_acc, function () {
                    if (
                        this.group === g.group &&
                        this.item === g.item &&
                        this.generic === g.generic
                    ) {
                        total_year += parseFloat(this.total_year_amount);
                        total_estimated += parseFloat(
                            this.total_estimated_amount
                        );
                        total_real += parseFloat(this.total_real_amount);
                        total_jan += parseFloat(this.jan_amount);
                        total_feb += parseFloat(this.feb_amount);
                        total_mar += parseFloat(this.mar_amount);
                        total_apr += parseFloat(this.apr_amount);
                        total_may += parseFloat(this.may_amount);
                        total_jun += parseFloat(this.jun_amount);
                        total_jul += parseFloat(this.jul_amount);
                        total_aug += parseFloat(this.aug_amount);
                        total_sep += parseFloat(this.sep_amount);
                        total_oct += parseFloat(this.oct_amount);
                        total_nov += parseFloat(this.nov_amount);
                        total_dec += parseFloat(this.dec_amount);
                    }
                });
                if (parseFloat(total_year) > 0) {
                    g.total_year_amount = parseFloat(total_year).toFixed(
                        vm.decimals
                    );
                    g.total_estimated_amount = parseFloat(
                        total_estimated
                    ).toFixed(vm.decimals);
                    g.total_real_amount = parseFloat(total_real).toFixed(
                        vm.decimals
                    );
                    g.jan_amount = parseFloat(total_jan).toFixed(vm.decimals);
                    g.feb_amount = parseFloat(total_feb).toFixed(vm.decimals);
                    g.mar_amount = parseFloat(total_mar).toFixed(vm.decimals);
                    g.apr_amount = parseFloat(total_apr).toFixed(vm.decimals);
                    g.may_amount = parseFloat(total_may).toFixed(vm.decimals);
                    g.jun_amount = parseFloat(total_jun).toFixed(vm.decimals);
                    g.jul_amount = parseFloat(total_jul).toFixed(vm.decimals);
                    g.aug_amount = parseFloat(total_aug).toFixed(vm.decimals);
                    g.sep_amount = parseFloat(total_sep).toFixed(vm.decimals);
                    g.oct_amount = parseFloat(total_oct).toFixed(vm.decimals);
                    g.nov_amount = parseFloat(total_nov).toFixed(vm.decimals);
                    g.dec_amount = parseFloat(total_dec).toFixed(vm.decimals);
                }
            });

            /** Calcula los montos de la cuenta ítems */
            $.each(item_acc, function () {
                let i = this,
                    total_year = 0,
                    total_estimated = 0,
                    total_real = 0;
                let total_jan = 0,
                    total_feb = 0,
                    total_mar = 0,
                    total_apr = 0,
                    total_may = 0,
                    total_jun = 0,
                    total_jul = 0,
                    total_aug = 0,
                    total_sep = 0,
                    total_oct = 0,
                    total_nov = 0,
                    total_dec = 0;
                $.each(generic_acc, function () {
                    if (this.group === i.group && this.item === i.item) {
                        total_year += parseFloat(this.total_year_amount);
                        total_estimated += parseFloat(
                            this.total_estimated_amount
                        );
                        total_real += parseFloat(this.total_real_amount);
                        total_jan += parseFloat(this.jan_amount);
                        total_feb += parseFloat(this.feb_amount);
                        total_mar += parseFloat(this.mar_amount);
                        total_apr += parseFloat(this.apr_amount);
                        total_may += parseFloat(this.may_amount);
                        total_jun += parseFloat(this.jun_amount);
                        total_jul += parseFloat(this.jul_amount);
                        total_aug += parseFloat(this.aug_amount);
                        total_sep += parseFloat(this.sep_amount);
                        total_oct += parseFloat(this.oct_amount);
                        total_nov += parseFloat(this.nov_amount);
                        total_dec += parseFloat(this.dec_amount);
                    }
                });
                if (parseFloat(total_year) > 0) {
                    i.total_year_amount = parseFloat(total_year).toFixed(
                        vm.decimals
                    );
                    i.total_estimated_amount = parseFloat(
                        total_estimated
                    ).toFixed(vm.decimals);
                    i.total_real_amount = parseFloat(total_real).toFixed(
                        vm.decimals
                    );
                    i.jan_amount = parseFloat(total_jan).toFixed(vm.decimals);
                    i.feb_amount = parseFloat(total_feb).toFixed(vm.decimals);
                    i.mar_amount = parseFloat(total_mar).toFixed(vm.decimals);
                    i.apr_amount = parseFloat(total_apr).toFixed(vm.decimals);
                    i.may_amount = parseFloat(total_may).toFixed(vm.decimals);
                    i.jun_amount = parseFloat(total_jun).toFixed(vm.decimals);
                    i.jul_amount = parseFloat(total_jul).toFixed(vm.decimals);
                    i.aug_amount = parseFloat(total_aug).toFixed(vm.decimals);
                    i.sep_amount = parseFloat(total_sep).toFixed(vm.decimals);
                    i.oct_amount = parseFloat(total_oct).toFixed(vm.decimals);
                    i.nov_amount = parseFloat(total_nov).toFixed(vm.decimals);
                    i.dec_amount = parseFloat(total_dec).toFixed(vm.decimals);
                }
            });

            /** Calcula los montos de la cuenta de grupo */
            $.each(group_acc, function () {
                let gr = this,
                    total_year = 0,
                    total_estimated = 0,
                    total_real = 0;
                let total_jan = 0,
                    total_feb = 0,
                    total_mar = 0,
                    total_apr = 0,
                    total_may = 0,
                    total_jun = 0,
                    total_jul = 0,
                    total_aug = 0,
                    total_sep = 0,
                    total_oct = 0,
                    total_nov = 0,
                    total_dec = 0;
                $.each(item_acc, function () {
                    if (this.group === gr.group) {
                        total_year += parseFloat(this.total_year_amount);
                        total_estimated += parseFloat(
                            this.total_estimated_amount
                        );
                        total_real += parseFloat(this.total_real_amount);
                        total_jan += parseFloat(this.jan_amount);
                        total_feb += parseFloat(this.feb_amount);
                        total_mar += parseFloat(this.mar_amount);
                        total_apr += parseFloat(this.apr_amount);
                        total_may += parseFloat(this.may_amount);
                        total_jun += parseFloat(this.jun_amount);
                        total_jul += parseFloat(this.jul_amount);
                        total_aug += parseFloat(this.aug_amount);
                        total_sep += parseFloat(this.sep_amount);
                        total_oct += parseFloat(this.oct_amount);
                        total_nov += parseFloat(this.nov_amount);
                        total_dec += parseFloat(this.dec_amount);
                    }
                });
                if (parseFloat(total_year) > 0) {
                    gr.total_year_amount = parseFloat(total_year).toFixed(
                        vm.decimals
                    );
                    gr.total_estimated_amount = parseFloat(
                        total_estimated
                    ).toFixed(vm.decimals);
                    gr.total_real_amount = parseFloat(total_real).toFixed(
                        vm.decimals
                    );
                    gr.jan_amount = parseFloat(total_jan).toFixed(vm.decimals);
                    gr.feb_amount = parseFloat(total_feb).toFixed(vm.decimals);
                    gr.mar_amount = parseFloat(total_mar).toFixed(vm.decimals);
                    gr.apr_amount = parseFloat(total_apr).toFixed(vm.decimals);
                    gr.may_amount = parseFloat(total_may).toFixed(vm.decimals);
                    gr.jun_amount = parseFloat(total_jun).toFixed(vm.decimals);
                    gr.jul_amount = parseFloat(total_jul).toFixed(vm.decimals);
                    gr.aug_amount = parseFloat(total_aug).toFixed(vm.decimals);
                    gr.sep_amount = parseFloat(total_sep).toFixed(vm.decimals);
                    gr.oct_amount = parseFloat(total_oct).toFixed(vm.decimals);
                    gr.nov_amount = parseFloat(total_nov).toFixed(vm.decimals);
                    gr.dec_amount = parseFloat(total_dec).toFixed(vm.decimals);
                }
                // El monto total del año se asigna automáticamente al monto total
                // de la formulación.
                vm.record.financement_amount = parseFloat(total_year);
            });
        },

        validateCurrency() {
            if (!this.record.currency_id) {
                bootbox.alert('Debe seleccionar primero un tipo de moneda');
                return false;
            }
        },
        /**
         * Muestra u oculta los campos de texto para ingresar información sobre los montos
         * a formular para una cuenta presupuestaria
         *
         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param  {integer} index Indice del registro a mostrar u ocultar
         */
        async showAccountInputs(index) {
            let vm = this;
            let add_account = $('#add_account_' + this.records[index].id);

            if (add_account.hasClass('fa-eye')) {
                // Reinicializa campos de la fila en 0;
                this.records[index].total_real_amount = 0;
                this.records[index].total_estimated_amount = 0;
                this.records[index].total_year_amount = 0;
                this.records[index].jan_amount = 0;
                this.records[index].jan_amount = 0;
                this.records[index].feb_amount = 0;
                this.records[index].mar_amount = 0;
                this.records[index].apr_amount = 0;
                this.records[index].may_amount = 0;
                this.records[index].jun_amount = 0;
                this.records[index].jul_amount = 0;
                this.records[index].aug_amount = 0;
                this.records[index].sep_amount = 0;
                this.records[index].oct_amount = 0;
                this.records[index].nov_amount = 0;
                this.records[index].dec_amount = 0;
                this.records[index].formulated = !this.records[index].formulated;
                add_account.removeClass('fa-eye');
                add_account.addClass('fa-eye-slash');
                add_account.removeClass('text-blue');
                add_account.addClass('text-red');
                if (this.records[index].subspecific !== '00') {
                    this.records.forEach(row => {
                        if (
                            row.code.substring(11) === '00' &&
                            row.code.substring(0, 10) === this.records[index].code.substring(0, 10)
                        ) {
                            let hasChild = this.records.filter(child => {
                                return child.code.substring(11) !== '00' &&
                                    child.code.substring(0, 10) === this.records[index].code.substring(0, 10)
                            });
                            row.locked = (hasChild) ? true : false;
                            row.formulated = (hasChild) ? true : false;
                            if (hasChild) {
                                let el = document.getElementById(row.id);
                                el.classList.add('disable-row');
                            }
                        }
                    });
                }
            }
            else if (add_account.hasClass('fa-eye-slash')) {
                // Si confirma, oculta y limpia los campos de texto para una cuenta presupuestaria.
                var data = this.records;

                if (this.createBootbox == false) {
                    this.createBootbox = true;
                    await bootbox.confirm({
                        message: "¿Esta seguro de eliminar esta cuenta presupuestaria de la formulación?",
                        buttons: {
                            confirm: {
                                label:
                                    '<i class="fa fa-check"></i> Confirmar'
                            },
                            cancel: {
                                label:
                                    '<i class="fa fa-times"></i> Cancelar'
                            }
                        },
                        callback: function (result) {
                            if (result == true) {
                                add_account.addClass('fa-eye');
                                add_account.removeClass('fa-eye-slash');
                                add_account.addClass('text-blue');
                                add_account.removeClass('text-red');
                                // Reinicializa campos de la fila en 0;
                                data[index].total_real_amount = 0;
                                data[index].total_estimated_amount = 0;
                                data[index].total_year_amount = 0;
                                data[index].jan_amount = 0;
                                data[index].jan_amount = 0;
                                data[index].feb_amount = 0;
                                data[index].mar_amount = 0;
                                data[index].apr_amount = 0;
                                data[index].may_amount = 0;
                                data[index].jun_amount = 0;
                                data[index].jul_amount = 0;
                                data[index].aug_amount = 0;
                                data[index].sep_amount = 0;
                                data[index].oct_amount = 0;
                                data[index].nov_amount = 0;
                                data[index].dec_amount = 0;
                                data[index].formulated = !data[index].formulated;
                                if (data[index].subspecific !== '00') {
                                    let hasChilds = data.filter(d => {
                                        return (
                                            d.code.substring(0, 10) === data[index].code.substring(0, 10) &&
                                            d.subspecific !== '00' && d.formulated
                                        );
                                    })[0] || null;
                                    if (hasChilds === null) {
                                        data.forEach(row => {
                                            if (
                                                row.code.substring(0, 10) === data[index].code.substring(0, 10) &&
                                                row.subspecific === '00'
                                            ) {
                                                row.locked = false;
                                                row.formulated = false;
                                                let el = document.getElementById(row.id);
                                                el.classList.remove('disable-row');
                                            }
                                        });
                                    }
                                }
                                /*Realiza la sumatoria de "Total año" luego de eliminar
                                la fila.
                                */
                                vm.calculateAmounts(index, 'month');
                                vm.createBootbox = false;
                            }
                            // Si no confirma, deja los campos de texto sin modificar.
                            else {
                                add_account.removeClass('fa-eye');
                                add_account.addClass('fa-eye-slash');
                                add_account.removeClass('text-blue');
                                add_account.addClass('text-red');
                                vm.createBootbox = false;
                            }
                        }
                    });
                }
            }
        },
        /**
         * Obtiene un arreglo con los proyectos
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param  {integer} id Identificador del proyecto a buscar, este parámetro es opcional
         */
        async getProjects(id) {
            const vm = this;
            var project_id = typeof id !== 'undefined' ? '/' + id : '';
            const url = vm.setUrl(`budget/get-projects${project_id}`);
            await axios.get(url).then(response => {
                vm.projects = response.data;
            }).catch(error => {
                console.error(error);
            });
        },
        /**
         * Obtiene un arreglo con las acciones centralizadas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param  {integer} id Identificador de la acción centralizada a buscar, este parámetro es opcional
         */
        async getCentralizedActions(id) {
            const vm = this;
            var centralized_action_id = typeof id !== 'undefined' ? '/' + id : '';
            const url = vm.setUrl(`budget/get-centralized-actions${centralized_action_id}`);
            await axios.get(url).then(response => {
                vm.centralized_actions = response.data;
            });
        },
        /**
         * Obtiene las Acciones Específicas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param {string} type Tipo de registro
         */
        async getSpecificActions(type) {
            const vm = this;
            let id =
                type === 'Project'
                    ? this.record.project_id
                    : this.record.centralized_action_id;

            this.specific_actions = [];

            if (id) {
                await axios.get(
                    `${window.app_url}/budget/get-specific-actions/${type}/${id}/formulation`
                ).then(response => {
                    this.specific_actions = response.data;
                })
                    .catch(error => {
                        vm.logs(
                            'BudgetSubSpecificFormulationComponent.vue',
                            551,
                            error,
                            'getSpecificActions'
                        );
                    });
                if (vm.record.id) {
                    vm.record.specific_action_id = vm.record.specific_action.id;
                }
            }

            var len = this.specific_actions.length;
            $('#specific_action_id').attr('disabled', len == 0);
        },
        /**
         * Crea o actualiza la formulación de presupuesto
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        createRecord() {
            const vm = this;
            /** Filtra las cuentas bloqueadas para solo lectura (cuentas de nivel superior) */
            const lock_acc = vm.records.filter(account => {
                return account.locked;
            });

            /** Asigna las cuentas de solo lectura que hayan sido formuladas */
            $.each(lock_acc, function () {
                this.formulated = parseFloat(this.total_year_amount) > 0;
            });
            /** Filtra todas las cuentas que hayan sido marcadas como formuladas */
            vm.record.formulated_accounts = vm.records.filter(account => {
                return account.formulated;
            });

            if (this.record.id) {
                this.updateFormulation();
            } else {
                var fields = {};
                for (var index in this.record) {
                    fields[index] = this.record[index];
                }
                axios
                    .post(`${window.app_url}/budget/subspecific-formulations`, fields)
                    .then(response => {
                        if (response.data.result) {
                            vm.showMessage('store');
                            bootbox.confirm({
                                title: 'Agregar nuevo registro',
                                message:
                                    'Desea registrar otra formulación de presupuesto?',
                                buttons: {
                                    cancel: {
                                        label:
                                            '<i class="fa fa-times"></i> Cancelar'
                                    },
                                    confirm: {
                                        label:
                                            '<i class="fa fa-check"></i> Confirmar'
                                    }
                                },
                                callback: function (result) {
                                    location.href = result
                                        ? vm.route_create
                                        : `${window.app_url}/budget/subspecific-formulations`;
                                }
                            });
                        } else {
                            let msg = response.data.message;
                            vm.showMessage(
                                msg.type,
                                msg.title,
                                msg.class,
                                msg.icon,
                                msg.text
                            );
                        }
                    })
                    .catch(error => {
                        vm.errors = [];
                        if (typeof error.response != 'undefined') {
                            for (var index in error.response.data.errors) {
                                if (error.response.data.errors[index]) {
                                    vm.errors.push(
                                        error.response.data.errors[index][0]
                                    );
                                }
                            }
                        }
                    });
            }
        },
        /**
         * Carga los datos de la formulación
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param {integer} id Identificador de la formulación a cargar
         */
        async loadFormulation(id) {
            const vm = this;
            vm.record.id = id;
            vm.loading = true;
            const url = vm.setUrl(`budget/get-subspecific-formulation/${id}`);
            await vm.initRecords(`${window.app_url}/budget/accounts/egress-list/true`, '');
            await axios.get(url).then(async response => {
                vm.loadFinancementSources = false;
                /** {String} determina el tipo de acción específica */
                let saType = response.data.formulation.specific_action.specificable_type;
                var pry_acc_id = response.data.formulation.specific_action.specificable.id;
                var pry_acc_type = (saType.indexOf("BudgetProject") >= 0) ? 'Proyecto' : 'Acción Centralizada';
                var formulation = response.data.formulation;

                $('#centralized_action_id').attr('disabled', pry_acc_type === 'Proyecto');
                $('#project_id').attr('disabled', !(pry_acc_type === 'Proyecto'));

                if (pry_acc_type === 'Proyecto') {
                    $('#sel_project').click();
                } else {
                    $('#sel_centralized_action').click();
                }
                $('#specific_action_id').attr('disabled', false);

                await vm.getSpecificActions();
                await vm.getCurrencies().then(() => {
                    vm.record.currency_id = formulation.currency_id;
                });
                vm.record.date = formulation.date;
                vm.record.institution_id = formulation.specific_action.specificable.department.institution_id;
                vm.record.fiscal_year = vm.record.year = formulation.year;
                vm.record.financement_type_id = formulation.budget_financement_type_id;
                vm.record.financement_amount = formulation.financement_amount;
                vm.record.project_id = pry_acc_type === 'Proyecto' ? pry_acc_id : '';
                vm.record.centralized_action_id = !(pry_acc_type === 'Proyecto') ? pry_acc_id : '';
                vm.record.specific_action = formulation.specific_action;

                await vm.getFinancementSources().then(() => {
                    if (vm.financementSources.length > 0) {
                        vm.record.financement_source_id = formulation.budget_financement_source_id;
                    }
                    vm.loadFinancementSources = true;
                });

                /** Carga los datos de las partidas presupuestarias formuladas */
                setTimeout(() => {
                    vm.validateCurrency();
                    $.each(vm.records, function (index, el) {
                        $.each(formulation.account_opens, function () {
                            if (el.id === this.budget_account_id) {
                                vm.showAccountInputs(index);
                                vm.records[
                                    index
                                ].total_year_amount = this.total_year_amount;
                                vm.records[
                                    index
                                ].total_real_amount = this.total_real_amount;
                                vm.records[
                                    index
                                ].total_estimated_amount = this.total_estimated_amount;
                                vm.records[index].jan_amount = this.jan_amount;
                                vm.records[index].feb_amount = this.feb_amount;
                                vm.records[index].mar_amount = this.mar_amount;
                                vm.records[index].apr_amount = this.apr_amount;
                                vm.records[index].may_amount = this.may_amount;
                                vm.records[index].jun_amount = this.jun_amount;
                                vm.records[index].jul_amount = this.jul_amount;
                                vm.records[index].aug_amount = this.aug_amount;
                                vm.records[index].sep_amount = this.sep_amount;
                                vm.records[index].oct_amount = this.oct_amount;
                                vm.records[index].nov_amount = this.nov_amount;
                                vm.records[index].dec_amount = this.dec_amount;
                            }
                        });
                        if (index === vm.records.length - 1) {
                            setTimeout(() => {
                                vm.loading = false;
                            }, 1000);
                        }
                    });
                    if (vm.records.length === 0) {
                        setTimeout(() => {
                            vm.loading = false;
                        }, 1000);
                    }
                }, 100);
            }).catch(error => {
                vm.logs(
                    'BudgetSubSpecificFormulationComponent.vue',
                    636,
                    error,
                    'loadFormulation'
                );

            }).finally(final => {
                vm.loading = false;
            });

            vm.loading = false;
        },
        /**
         * Ejecuta la acción para actualizar datos de la formulación
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        updateFormulation() {
            var fields = {};
            for (var index in this.record) {
                fields[index] = this.record[index];
            }
            axios
                .patch(
                    `${window.app_url}/budget/subspecific-formulations/${this.record.id}`,
                    fields
                )
                .then(response => {
                    if (response.data.result) {
                        this.showMessage('update');
                        setTimeout(function () {
                            location.href = `${window.app_url}/budget/subspecific-formulations`;
                        }, 2000);
                    } else {
                        let msg = response.data.message;
                        this.showMessage(
                            msg.type,
                            msg.title,
                            msg.class,
                            msg.icon,
                            msg.text
                        );
                    }
                })
                .catch(error => {
                    this.errors = [];
                    if (typeof error.response != 'undefined') {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                this.errors.push(
                                    error.response.data.errors[index][0]
                                );
                            }
                        }
                    }
                });
        },
        /**
         * Ejecuta el evento click del campo de tipo archivo al cual cargar información
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param {string} input_id Identificador del campo de tipo archivo
         */
        setFile(input_id) {
            bootbox.confirm({
                title: 'Formato de archivo',
                message: `<h6>EJEMPLO: Formato de hoja de cálculo </h6>
                            <table cellpadding="1" border="1" style="font-size: .70em;">
                                <thead>
                                    <tr>
                                        <td class="text-center"><strong>codigo</strong></td>
                                        <td class="text-center"><strong>total_real</strong></td>
                                        <td class="text-center"><strong>total_estimado</strong></td>
                                        <td class="text-center"><strong>total_anho</strong></td>
                                        <td class="text-center"><strong>ene</strong></td>
                                        <td class="text-center"><strong>feb</strong></td>
                                        <td class="text-center"><strong>mar</strong></td>
                                        <td class="text-center"><strong>abr</strong></td>
                                        <td class="text-center"><strong>may</strong></td>
                                        <td class="text-center"><strong>jun</strong></td>
                                        <td class="text-center"><strong>jul</strong></td>
                                        <td class="text-center"><strong>ago</strong></td>
                                        <td class="text-center"><strong>sep</strong></td>
                                        <td class="text-center"><strong>oct</strong></td>
                                        <td class="text-center"><strong>nov</strong></td>
                                        <td class="text-center"><strong>dic</strong></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">4.01.01.01.01</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">123</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                        <td class="text-center">10.25</td>
                                    </tr>
                                </tbody>
                            </table>
                            <small class="form-text text-muted" style="font-size: .9em">
                                <b>Observaciones:</b>
                                <ul>
                                    <li>Cuando se exporta el archivo de carga masiva, este contiene todas las partidas del clasificador presupuestario. Es necesario depurar este archivo y dejar únicamente las cuentas a formular.</li>
                                    <li>La estructura de la hoja de cálculo no puede ser modificada.</li>
                                    <li>Las columnas total_real y total_estimado pueden estar vacías o contener únicamente el valor cero (0).</li>
                                    <li>En la hoja de cálculo, los montos pueden ser ingresados mensualmente según lo solicitado para la formulación. También es posible colocar el monto total en la columna denominada total_anho, distribuyéndolo uniformemente a lo largo de todos los meses del año, tal como se muestra en el ejemplo del formato de hoja de cálculo.</li>
                                    <li>Los títulos de cada columna deben ser exactos a los del ejemplo</li>
                                    <li>Solo incluya las cuentas a formular, sin las cuentas de nivel superior</li>
                                </ul>
                            </small>`,
                size: 'large',
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
                        $(`#${input_id}`).click();
                    }
                }
            });
        },
        /**
         * Importa información de la formulación a partir de una hoja de cálculo
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         * @param  {string} input_id Identificador del campo del typo file con el
         *                           archivo a ser importado
         */
        async getImportFormulation(input_id) {
            let vm = this;
            vm.loading = true;
            var formData = new FormData();
            var inputFile = document.querySelector(`#${input_id}`);
            formData.append('file', inputFile.files[0]);
            await axios.post(`${window.app_url}/budget/get-import-formulation`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                if (response.data.result && response.data.records) {
                    $.each(response.data.records, function (i, row) {
                        let objData = {};
                        $.each(row, function (j, col) {
                            objData[j] = col ? col : 0;
                        });
                        var accountEl = $(
                            `[data-code="${objData.codigo}"]`
                        );
                        if (accountEl) {
                            vm.createBootbox = true;
                            accountEl.find('[id^=add_account]').click();
                            var index = accountEl.data('index');
                            vm.records[index].total_real_amount =
                                objData.total_real;
                            vm.records[index].total_estimated_amount =
                                objData.total_estimado;
                            vm.records[index].total_year_amount =
                                objData.total_anho;
                            vm.records[index].jan_amount = objData.ene;
                            vm.records[index].feb_amount = objData.feb;
                            vm.records[index].mar_amount = objData.mar;
                            vm.records[index].apr_amount = objData.abr;
                            vm.records[index].may_amount = objData.may;
                            vm.records[index].jun_amount = objData.jun;
                            vm.records[index].jul_amount = objData.jul;
                            vm.records[index].aug_amount = objData.ago;
                            vm.records[index].sep_amount = objData.sep;
                            vm.records[index].oct_amount = objData.oct;
                            vm.records[index].nov_amount = objData.nov;
                            vm.records[index].dec_amount = objData.dic;
                            vm.calculateAmounts(
                                index,
                                objData.total_anho > 0 ? 'year' : 'month'
                            );
                        }
                    });
                    vm.showMessage('update');
                } else {
                    vm.showMessage(
                        'custom',
                        'Error!',
                        'danger',
                        'screen-error',
                        response.data.message
                    );
                }
            }).catch(error => {
                console.log(error);
                vm.showMessage(
                    'custom',
                    'Error!',
                    'danger',
                    'screen-error',
                    'Ah ocurrido un error en la importación de los datos, intente nuevamente'
                );
            });
            vm.loading = false;
            vm.createBootbox = false;
            inputFile.value = '';
        },
        /**
         * Exporta la información de las cuentas formuladas
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        getExportFormulation() {
            const vm = this;
            let csv = 'codigo,total_real,total_estimado,total_anho,ene,feb,mar,abr,may,jun,jul,ago,sep,oct,nov,dic\n';
            let data = vm.records.map(row => {
                return [
                    row.code,
                    row.total_real_amount,
                    row.total_estimated_amount,
                    row.total_year_amount,
                    row.jan_amount,
                    row.feb_amount,
                    row.mar_amount,
                    row.apr_amount,
                    row.may_amount,
                    row.jun_amount,
                    row.jul_amount,
                    row.aug_amount,
                    row.sep_amount,
                    row.oct_amount,
                    row.nov_amount,
                    row.dec_amount
                ];
            });
            data.forEach(row => {
                csv += row.join(',');
                csv += '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', 'download.csv');
            a.click();
        },
        /**
         * Ejecuta la acción para actualizar datos de la formulación
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        searchAccounts() {
            const vm = this;
            let arrayFilter = vm.records.filter(
                (account) => {
                    // let denomination = account.denomination.toLowerCase();
                    let search = vm.search.toLowerCase();
                    return account.code.includes(search) || account.denomination.toLowerCase().includes(search);
                }
            );
            const accountsNodes = document.querySelectorAll(".accounts");
            if (vm.search !== '') {
                accountsNodes.forEach(classAccount => classAccount.classList.add("d-none"));
            } else {
                accountsNodes.forEach(classAccount => classAccount.classList.remove("d-none"));
            }
            if (arrayFilter.length > 0) {
                arrayFilter.forEach((account) => {
                    document.getElementById(account.id).classList.remove("d-none");
                });
            }
        },
        /**
         * Ejecuta la acción para actualizar datos de la formulación
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        cleanSearch() {
            const vm = this;
            vm.search = '';
            vm.searchAccounts();
        }
    },
    watch: {
        /**
         * Función que permite monitorear modificaciones en el campo specific_actions
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        specific_actions: function () {
            $('#specific_action_id').attr(
                'disabled',
                this.specific_actions.length <= 1
            );
        },
        record: {
            deep: true,
            handler: function (newValue, oldValue) {
                this.decimals = 2;
                if (newValue.currency_id) {
                    axios
                        .get(`${window.app_url}/currencies/info/${newValue.currency_id}`)
                        .then(response => {
                            if (response.data.result) {
                                this.decimals =
                                    response.data.currency.decimal_places;
                            }
                        });
                }
            }
        }
    },
    created() {
        this.getFinancementTypes();
        setTimeout(() => {
            this.getCurrencies();
        }, 1000);
        window.addEventListener('updateProjectId', (event) => {
            this.record.project_id = event.value;
        });

        window.addEventListener('updateCentralizedActionId', (event) => {
            this.record.centralized_action_id = event.value;
        });
    },
    async mounted() {
        const vm = this;
        vm.loading = true; // Inicia el spinner de carga
        await vm.getOpenedFiscalYears();

        document.getElementById('loading-message').innerText = 'Cargando catálogo de cuentas. Por favor espere';
        await vm.getProjects();
        await vm.getCentralizedActions();
        if (vm.formulationId) {
            await vm.loadFormulation(vm.formulationId);
        }
        else {
            await vm.initRecords(`${window.app_url}/budget/accounts/egress-list/true`, '');
        }
        vm.record.fiscal_year = window.execution_year;
        setTimeout(() => {
            vm.loading = false;
        }, 800);
        setTimeout(() => {
            document.getElementById('loading-message').innerText = 'Su petición se está cargando. Por favor espere';
        }, 1000);
        /**
         * Evento para determinar los datos a requerir según el tipo de formulación
         * (por proyecto o acción centralizada)
         */
        $('.sel_pry_acc').on('change', function (e) {
            $('#project_id').attr('disabled', e.target.id !== 'sel_project');
            $('#centralized_action_id').attr(
                'disabled',
                e.target.id !== 'sel_centralized_action'
            );
            if (e.target.id === 'sel_project') {
                window.dispatchEvent(new CustomEvent('updateCentralizedActionId', { value: '' }));
                $('#centralized_action_id')
                    .closest('.form-group')
                    .removeClass('is-required');
                $('#project_id')
                    .closest('.form-group')
                    .addClass('is-required');
            } else if (e.target.id === 'sel_centralized_action') {
                window.dispatchEvent(new CustomEvent('updateProjectId', { value: '' }));
                $('#centralized_action_id')
                    .closest('.form-group')
                    .addClass('is-required');
                $('#project_id')
                    .closest('.form-group')
                    .removeClass('is-required');
            }
        });
        vm.institutions ? vm.record.institution_id = vm.institutions[1]['id'] : vm.record.institution_id = '';

        // Si la Formulación no se ha registrado, se asigna la fecha actual al
        // campo Fecha de generación.
        if (!vm.formulationId) {
            vm.record.date = moment().format("YYYY-MM-DD");
        }

        vm.loading = false; // Finaliza el spinner de carga
    }
};
</script>
