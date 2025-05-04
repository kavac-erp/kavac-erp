<template>
    <section id="FinanceConciliacionForm">
        <div class="card-body">
            <div class="alert alert-danger" v-if="errors.length > 0">
                <div class="container">
                    <div class="alert-icon">
                        <i class="now-ui-icons objects_support-17"></i>
                    </div>
                    <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                        @click.prevent="errors = []">
                        <span aria-hidden="true">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </span>
                    </button>
                    <ul>
                        <li v-for="error in errors" :key="error">{{ error }}</li>
                    </ul>
                </div>
            </div>
            <!-- Sección para registrar banco a conciliar -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h6>Datos del banco a conciliar</h6>
                </div>
                <div class="col-md-4" id="helpFinanceInstitution">
                    <div class="form-group is-required">
                        <label>Institución:</label>
                        <select2 :options="institutions" v-model="record.institution_id"></select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceBankAccount">
                    <div class="form-group is-required">
                        <label>Nro. de cuenta:</label>
                        <select2 :options="bank_accounts" @input="getBankAccountData()"
                            v-model="record.finance_bank_account_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceCurrency">
                    <div class="form-group is-required">
                        <label>Tipo de moneda:</label>
                        <select2 :options="currencies" @input="changeCurrency(record.currency_id)"
                            v-model="record.currency_id">
                        </select2>
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceBankName" v-if="record.finance_bank_account_id">
                    <div class="form-group is-required">
                        <label>Banco:</label>
                        <input type="text" class="form-control input-sm" v-model="record.bank" disabled />
                    </div>
                </div>
                <div class="col-md-4" id="helpFinanceTypeAccount" v-if="record.finance_bank_account_id">
                    <div class="form-group is-required">
                        <label>Tipo de cuenta:</label>
                        <input type="text" class="form-control input-sm" v-model="record.account_type" disabled />
                    </div>
                </div>
                <div class="col-md-4" id="helpFinancePaymentDate">
                    <label for="" class="control-label">Mes:</label>
                    <select2 :options="months" v-model="record.month" />
                </div>
                <div class="col-md-4">
                    <div class="form-group is-required">
                        <label>Año:</label>
                        <select2 :options="years" v-model="record.year">
                        </select2>
                    </div>
                </div>
                <div class="col-12">
                    <hr>
                </div>

                <div class="col-6 mt-4">
                    <h6>EJEMPLO DE CARGA</h6>
                </div>
                <div class="col-6 mt-4">
                    <div class="d-flex justify-content-end">
                        <div class="form-group">
                            <form method="post" enctype="multipart/form-data" @submit.prevent="">
                                <label>Cargar Hoja de calculo. Formatos permitidos:<strong>.xls .xlsx</strong></label><br>
                                <div class="d-flex justify-content-end">
                                    <button type="button" data-toggle="tooltip" class="btn btn-sm btn-info btn-custom" v-has-tooltip
                                        title="Presione para importar la información. Los archivos permitidos son: .xls .xlsx"
                                        @click="openFileInput">
                                        <i class="fa fa-cloud-upload"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary btn-custom" v-has-tooltip @click="exportToExcel"
                                        title="Presione para exportar la información.">
                                        <i class="fa fa-cloud-download"></i>
                                    </button>
                                </div>
                                <input ref="fileInput" type="file" multiple accept=".xls, .xlsx" style="display: none"
                                    @change="onFileChange" />
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 card-body" style="margin-top: -30px; padding: 0px 20px;">
                    <h6>Formato del archivo</h6>
                    <table cellpadding="1" border="1">
                        <thead>
                            <tr>
                                <td class="text-center"><strong class="uppercase">Fecha</strong></td>
                                <td class="text-center"><strong class="uppercase">Descripción</strong></td>
                                <td class="text-center"><strong class="uppercase">Referencia bancaria</strong></td>
                                <td class="text-center"><strong class="uppercase">Débito</strong></td>
                                <td class="text-center"><strong class="uppercase">Crédito</strong></td>
                                <td class="text-center"><strong class="uppercase">Saldo</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">20XX-0X-0X</td>
                                <td class="text-center"></td>
                                <td class="text-center">0123456</td>
                                <td class="text-center">1.00</td>
                                <td class="text-center">1.00</td>
                                <td class="text-center">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="card-footer text-right">

                    </div>
                </div>
                <div class="col-12">
                    <hr>
                </div>
                <div class="col-12 text-right" v-if="formAndFileLoaded">
                    <div class="form-group">
                        <button class="btn btn-sm btn-info btn-custom"
                            @click="getMovementsApprovedByAccount(record.finance_bank_account_id)">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-4" v-if="movementSystemList.length > 0">
            <div class="col-12 text-left">
                <div class="form-group">
                    <button class="btn btn-sm btn-info btn-custom" @click="autoSelection()" data-toggle="tooltip"
                        v-has-tooltip
                        title="Selecciona de forma automática todos los movimientos que concuerden en fecha, monto y referencia bancaria">
                        Sincronizar movimientos automáticamente
                    </button>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group is-required">
                    <label>Movimientos en el sistema:</label>
                    <select2 :options="getMovementSystemListComputed" v-model="movementSystemSelected"
                        :disabled="fileMovementListOriginal.length < 1"></select2>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group is-required">
                    <label>Movimientos bancarios:</label>
                    <select2 :options="fileMovementListCpValue" v-model="fileMovementSelected"
                        :disabled="!movementSystemSelected"></select2>
                </div>
            </div>
            <div class="col-12 text-right">
                <div class="form-group">
                    <button class="btn btn-sm btn-info btn-custom" @click="addConsolidation()"
                        :disabled="!movementSystemSelected || !fileMovementSelected">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="row mx-4 mt-4 mb-0" v-if="record.movementToConsolidate.length > 0">
            <div class="col-6 text-center bg-dark">
                <span class="text-white">Sistema</span>
            </div>
            <div class="col-6 text-center bg-dark">
                <span class="text-white">Banco</span>
            </div>
        </div>
        <div class="row mx-4 mt-0 mb-4" v-if="record.movementToConsolidate.length > 0">
            <div class="col-6 text-center border border-dark">
                <div class="row ml-4 my-1" v-for="(concolidate, index) in record.movementToConsolidate" :key="index">
                    <button class="btn btn-danger btn-xs btn-icon btn-action" @click="removeToList(index)">
                        <i class="fa fa-trash"></i>
                    </button>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        {{ concolidate.movement.date }}
                    </label>
                    <label style="margin-top: 8px;">|</label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        {{ concolidate.movement.concept }}
                    </label>
                    <label style="margin-top: 8px;">---</label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        <strong>{{ concolidate.movement.amount }} {{ data.currency.symbol ?? '' }}</strong>
                    </label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        Ref Bco. {{ concolidate.movement.bank_reference }}
                    </label>
                </div>
            </div>
            <div class="col-6 text-center border border-dark">
                <div class="row ml-4 my-1" v-for="(concolidate, index) in record.movementToConsolidate" :key="index">
                    <!-- <input class="form-check-input" type="checkbox" value="" :id="`defaultCheck${index}`"> -->
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        {{ concolidate.consolidation.date }}
                    </label>
                    <label style="margin-top: 8px;">|</label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        {{ concolidate.consolidation.concept }}
                    </label>
                    <label style="margin-top: 8px;">---</label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        Débito: <strong>{{ concolidate.consolidation.debit }} {{ data.currency.symbol ?? '' }}</strong>
                    </label>
                    <label style="margin-top: 8px;">|</label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        Crédito: <strong>{{ concolidate.consolidation.assets }} {{ data.currency.symbol ?? ''
                            }}</strong>
                    </label>
                    <label class="form-check-label mx-1" style="margin-top: 8px;" :for="`defaultCheck${index}`">
                        Ref Bco. {{ concolidate.consolidation.bank_reference }}
                    </label>
                </div>
            </div>
            <div class="col-12 text-right mt-4" id="helpParamButtons">
                <button class="btn btn-default btn-icon btn-round" data-toggle="tooltip" type="button"
                    title="Borrar datos del formulario" @click="reset"><i class="fa fa-eraser"></i>
                </button>
                <button type="button" class="btn btn-warning btn-icon btn-round" data-toggle="tooltip"
                    title="Cancelar y regresar" @click="redirect_back(route_list)">
                    <i class="fa fa-ban"></i>
                </button>
                <button type="button" @click="updateOrCreate('finance/conciliation')" data-toggle="tooltip"
                    title="Guardar registro" class="btn btn-success btn-icon btn-round">
                    <i class="fa fa-save"></i>
                </button>
            </div>
        </div>
    </section>
</template>

<script>
import * as XLSX from 'xlsx';
import moment from 'moment';

export default {
    props: ['route_list', 'conciliation'],
    data() {
        return {
            months: [
                { id: '', text: 'Seleccione...' },
                { id: 1, text: 'Enero' },
                { id: 2, text: 'Febrero' },
                { id: 3, text: 'Marzo' },
                { id: 4, text: 'Abril' },
                { id: 5, text: 'Mayo' },
                { id: 6, text: 'Junio' },
                { id: 7, text: 'Julio' },
                { id: 8, text: 'Agosto' },
                { id: 9, text: 'Septiembre' },
                { id: 10, text: 'Octubre' },
                { id: 11, text: 'Noviembre' },
                { id: 12, text: 'Diciembre' }
            ],
            years: [],
            errors: [],
            record: {
                start_date: '',
                end_date: '',
                finance_bank_account_id: '',
                currency_id: null,
                institution_id: '',
                movementToConsolidate: [],
                bank_balance: 0.00,
            },
            bank_accounts: [],
            currencies: [],
            institutions: [],
            data: {
                currency: null,
                currency_id: '',
            },
            movementSystemList: [],
            movementSystemSelected: '',
            fileMovementListOriginal: [],
            fileMovementListCpValue: [],
            fileMovementSelected: '',
        };
    },

    created() {
        const vm = this;
        vm.getInstitutions();
        vm.getCurrencies();
        vm.getFiscalYears();
    },

    mounted() {
        const vm = this;
        vm.getFinanceBankAccounts(this.conciliation);
    },

    computed: {
        formAndFileLoaded() {
            return (this.fileMovementListOriginal.length > 0 &&
                    this.record.finance_bank_account_id &&
                    this.record.start_date &&
                    this.record.end_date &&
                    this.record.institution_id &&
                    this.record.currency_id)
        },
        getMovementSystemListComputed() {
            return this.movementSystemList.map((x) => {
                const xId = x.id.split('|')[0];

                if (typeof this.record.movementToConsolidate !== 'string') {
                    x.disabled = this.record.movementToConsolidate.find((v) => v.movement.id == xId) ? true : false;
                } else {
                    x.disabled = false;
                }

                return x;
            })
        }
    },

    watch: {
        movementSystemSelected(newVal, oldval) {
            const vm = this;
            vm.fileMovementListCpValue = [];
            const listValues = newVal.split('|');
            const values = {
                id: listValues[0],
                date: listValues[1],
                code: listValues[2],
                concept: listValues[3],
                amount: listValues[4],
            }

            let movementListAvailable = [
                {
                    default: true,
                    id: '',
                    text: 'Seleccione...'
                }
            ];

            let n = vm.fileMovementListOriginal.filter((el) => (el[0] === values.date && (el[3] == values.amount || el[4] == values.amount)));

            n = n.map((val) => {
                return {
                    id: `${val[0]}|${val[1]}|${val[3]}|${val[4]}|${val[5]}|${val[2]}`,
                    text: `${val[0]}|${val[1]}| Débito:${val[3]} | Crédito:${val[4]}  ${val[2] ? "| Ref Bco. " + val[2] : ""}`,
                }
            })

            vm.fileMovementListCpValue = movementListAvailable.concat(n);
        },
    },

    methods: {
        getMonthDates(month, year) {
            let firstDay = new Date(year, month - 1, 1).getDate();

            let lastDay = new Date(year, month, 0).getDate();

            return {
                firstDay: firstDay,
                lastDay: lastDay
            };
        },
        getFiscalYears() {
            const vm = this;

            axios.get(`${window.app_url}/fiscal-years/opened/list`).then(response => {
                const today = new Date();
                const year = today.getFullYear();

                vm.years.push({
                    id: '',
                    text: 'Seleccione...',
                });

                for (let index = response.data.records[0].id; index <= year; index++) {
                    vm.years.push({
                        id: index,
                        text: index,
                    });
                }
            })
        },
        /**
         * Carga el select de años desde el año de inicio de operaciones de
         * la organización hasta el año fiscal.
         *
         * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
         */
        async getInstitutionStartOperationYear() {
            let vm = this;
            await axios.get(`${vm.app_url}/finance/get-institution`).then(response => {
                var currentTime = new Date();
                var year = currentTime.getFullYear()
                let start_operations_date = response.data.institution.start_operations_date;
                const d = new Date(start_operations_date);
                let start_operations_year = d.getFullYear();
                for (var i = start_operations_year; i < year + 1; i++) {
                    vm.years.push({ "id": i, "text": i });
                }
            }).catch(error => {
                console.log("Error");
            });
        },
        removeToList(index) {
            const vm = this;
            vm.record.movementToConsolidate.splice(index, 1);
        },
        selectedAllMovements() {
            for (let index = 1; index < this.movementSystemList.length; index++) {
                const element = this.movementSystemList[index];
                if (!element.default && element.disabled === false) {
                    return false;
                }
            }
            return true;
        },
        /**
         * Reescribe el Método updateOrCreate para cambiar su comportamiento por defecto
         * Método que permite crear o actualizar un registro
         *
         * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
         *                         El valor por defecto es verdadero.
         */
        updateOrCreate(url) {
            const vm = this;

            if (!this.selectedAllMovements()) {
                vm.showMessage(
                    'custom', 'Error', 'danger', 'screen-error', 'Debe seleccionar todos los movimientos ejecutados en este mes.'
                );
            }

            url = vm.setUrl(url);

            if (vm.fileMovementListOriginal.length > 0) {
                vm.record.bank_balance = vm.fileMovementListOriginal[vm.fileMovementListOriginal.length - 1][4];
            }

            if (vm.record.id) {
                vm.updateRecord(url);
            } else {
                vm.loading = true;
                axios.post(url, vm.record)
                    .then((response) => {
                        if (response.data.error) {
                            vm.errors.push(response.data.error);
                        } else {
                            vm.errors = [];
                            vm.loading = false;
                            vm.showMessage("store");
                            location.href = response.data.redirect;
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
            }
        },

        updateRecord(url) {
            const vm = this;
            vm.loading = true;

            if (vm.fileMovementListOriginal.length > 0) {
                vm.record.bank_balance = vm.fileMovementListOriginal[vm.fileMovementListOriginal.length - 1][4];
            }

            axios.put(`${url}/${vm.record.id}`, vm.record)
                .then((response) => {
                    if (response.data.error) {
                        vm.errors.push(response.data.error);
                    } else {
                        vm.errors = [];
                        vm.loading = false;
                        vm.showMessage("update");
                        location.href = response.data.redirect;
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

        reset() {
            const vm = this;

            vm.record = {
                start_date: '',
                end_date: '',
                finance_bank_account_id: '',
                currency_id: null,
                institution_id: '',
                movementToConsolidate: [],
            };

            vm.data = {
                currency: null,
                currency_id: '',
            };

            vm.movementSystemList = [];
            vm.movementSystemSelected = '';
            vm.fileMovementListOriginal = [];
            vm.fileMovementListCpValue = [];
            vm.fileMovementSelected = '';
        },

        addConsolidation() {
            const vm = this;
            const [mvID, mvDate, mvCode, mvConcept, mvAmount, mvBankReference] = vm.movementSystemSelected.split('|');
            const [coDate, coConcept, coDebit, coAssets, coTotal, coBankReference] = vm.fileMovementSelected.split('|');

            if (typeof vm.record.movementToConsolidate === 'string') {
                vm.record.movementToConsolidate = [];
            }

            if (vm.record.movementToConsolidate.find((v) => v.movement.id === mvID)) {
                vm.showMessage(
                    'custom', 'Error', 'danger', 'screen-error', 'El movimiento no puede ser conciliado más de una vez.'
                );
                return;
            }

            vm.record.movementToConsolidate.push({
                movement: {
                    id: mvID,
                    date: mvDate,
                    code: mvCode,
                    concept: mvConcept,
                    amount: mvAmount,
                    bank_reference: mvBankReference
                },
                consolidation: {
                    date: coDate,
                    concept: coConcept,
                    debit: coDebit,
                    assets: coAssets,
                    current_balance: coTotal,
                    bank_reference: coBankReference
                }
            });

            vm.movementSystemSelected = '';
            vm.fileMovementSelected = '';
        },

        getMovementsApprovedByAccount(bankAccountId) {
            const vm = this;

            const dates = vm.getMonthDates(vm.record.month, vm.record.year);

            vm.record.start_date = `${vm.record.year}-${vm.record.month > 9 ? vm.record.month : `0${vm.record.month}`}-${dates.firstDay > 9 ? dates.firstDay : `0${dates.firstDay}`}`
            vm.record.end_date = `${vm.record.year}-${vm.record.month > 9 ? vm.record.month : `0${vm.record.month}`}-${dates.lastDay > 9 ? dates.lastDay : `0${dates.lastDay}`}`

            console.log(vm.record);

            if (bankAccountId) {
                axios.get(`${window.app_url}/finance/conciliation/movements/vue-list-by-account/${vm.record.institution_id}/${vm.record.currency_id}/${bankAccountId}/${vm.record.start_date}/${vm.record.end_date}`)
                    .then(response => {
                        vm.movementSystemList = [
                            {
                                default: true,
                                id: '',
                                text: 'Seleccione...'
                            }
                        ];
                        if (response.data.records.length > 0) {

                            response.data.records.forEach(element => {
                                vm.movementSystemList.push({
                                    id: `${element.id}|${element.entries.from_date}|${element.entries.reference}|${element.entries.concept}|${this.formatAmount(element.amount)}|${element.bank_reference ?? ""}`,
                                    text: `${element.entries.from_date} | ${element.entries.reference}  | ${element.entries.concept} --- ${this.formatAmount(element.amount)} ${element.bank_reference ? "| Ref Bco. " + element.bank_reference : ""}`,
                                })
                            });

                            vm.showMessage('custom', '¡Consulta exitosa!', 'success', 'screen-ok', 'Movimientos cargas en lista.');
                        } else {
                            vm.showMessage(
                                'custom', 'Sin registros', 'danger', 'screen-error', 'No se encontraron movimientos.'
                            );
                        }
                    });
            }
        },

        formatAmount(amount) {
            return parseFloat(amount).toFixed(this.data.currency.decimal_places ?? 2);
        },

        exportToExcel() {
            const data = [
                ['Fecha', 'Descripción', 'Referencia bancaria', 'Débito', 'Crédito', 'Saldo'],
                ['20XX-1X-0X', 'Movimiento #1', '---------', '1.000,00', 0, '1.000,00'],
            ];

            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Movimientos Bancarios');
            XLSX.writeFile(wb, 'movimientos_bancarios.xlsx');
        },

        openFileInput() {
            // Simular clic en el input file
            this.$refs.fileInput.click();
        },

        onFileChange(event) {
            const fileInput = event.target;
            const file = fileInput.files[0];
            if (file) {
                this.readFile(file);
            }
        },

        async autoSelection() {
            const vm = this;
            vm.loading = true;
            vm.record.movementToConsolidate = []

            vm.movementSystemList.forEach(elem => {
                if (elem.id !== "") {
                    let cpMovSys = elem.id.split('|');

                    cpMovSys = {
                        id: cpMovSys[0],
                        date: cpMovSys[1],
                        code: cpMovSys[2],
                        concept: cpMovSys[3],
                        amount: cpMovSys[4],
                        bank_reference: cpMovSys[5] ?? null,
                    }

                    let n = vm.fileMovementListOriginal.filter((el) => (el[0] === cpMovSys.date && (el[3] == cpMovSys.amount || el[4] == cpMovSys.amount)));

                    if (n.length > 0) {
                        const [mvID, mvDate, mvCode, mvConcept, mvAmount, mvBankReference] = elem.id.split('|');
                        const [coDate, coConcept, coBankReference, coDebit, coAssets, coTotal] = n[0];

                        if (typeof vm.record.movementToConsolidate === 'string') {
                            vm.record.movementToConsolidate = [];
                        }

                        if (vm.record.movementToConsolidate.find((v) => v.movement.id === mvID)) {
                            vm.showMessage(
                                'custom', 'Error', 'danger', 'screen-error', 'El movimiento no puede ser conciliado más de una vez.'
                            );
                            return;
                        }

                        vm.record.movementToConsolidate.push({
                            movement: {
                                id: mvID,
                                date: mvDate,
                                code: mvCode,
                                concept: mvConcept,
                                amount: mvAmount,
                                bank_reference: mvBankReference
                            },
                            consolidation: {
                                date: coDate,
                                concept: coConcept,
                                debit: coDebit,
                                assets: coAssets,
                                current_balance: coTotal,
                                bank_reference: coBankReference
                            }
                        });
                    }
                }
            })
            vm.loading = false;
        },

        async readFile(file) {
            // Verifica el tipo archivo
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            if (!allowedTypes.includes(file.type)) {
                this.showMessage('custom', 'Error', 'danger', 'screen-error', 'El formato de archivo no es válido. Solo se admiten archivos .xlsx o .xls.');
                return;
            }
            const reader = new FileReader();

            reader.onload = (e) => {
                const data = e.target.result;
                const workbook = XLSX.read(data, { type: 'binary' });

                // Obtener la información de la primera hoja del archivo
                const sheetName = workbook.SheetNames[0];
                const sheet = workbook.Sheets[sheetName];

                this.fileMovementListOriginal = XLSX.utils.sheet_to_json(sheet, { header: 1, cellDates: true });

                // Se formatean los campos de fecha y de montos
                this.fileMovementListOriginal.forEach(element => {
                    const dateValue = element[0];
                    if (typeof dateValue === 'number') {
                        element[0] = moment(new Date(1899, 12, dateValue - 1)).format('YYYY-MM-DD');
                        element[3] = this.validateAmount(this.formatAmount(element[3] ?? 0.00)); // Débito
                        element[4] = this.validateAmount(this.formatAmount(element[4] ?? 0.00)); // Crédito
                        element[5] = this.validateAmount(this.formatAmount(element[5] ?? 0.00)); // Saldo Final
                    }
                });

            };

            reader.readAsBinaryString(file);
        },

        validateAmount(amount) {
            if (amount === NaN || amount == 'NaN' || amount === undefined || amount === 'undefined') {
                return 0.00;
            }
            return amount;
        },

        /**
         * Completa los campos de banco y de tipo de cuenta según el número de cuenta seleccionado
         *
         * @author Juan Rosas <juan.rosasr01@gmail.com>
         */
        getBankAccountData() {
            const vm = this;
            vm.record.bank = '';
            vm.record.account_type = '';

            if (!vm.record.finance_bank_account_id && vm.conciliation) {
                vm.record.finance_bank_account_id = vm.conciliation.finance_bank_account_id;
            }

            if (vm.record.finance_bank_account_id) {
                for (let account of vm.bank_accounts) {
                    if (vm.record.finance_bank_account_id == account.id) {
                        vm.record.bank = account.bank_name ? account.bank_name : '';
                        vm.record.account_type = account.bank_account_type ? account.bank_account_type : '';
                    }
                }
            }

            vm.record.start_date = '2022-01-01';
            vm.record.end_date = '2022-12-30';
        },

        /**
         * Obtiene los datos de las entidades bancarias registradas
         * y actualiza la informacion del formulario en la edicion

         * @author Juan Rosas <juan.rosasr01@gmail.com>
         */
        getFinanceBankAccounts(editConciliation = null) {
            const vm = this;
            vm.bank_accounts = [];

            axios.get(`${window.app_url}/finance/get-bank-accounts`).then(response => {
                vm.bank_accounts = response.data;

                if (editConciliation) {

                    vm.record.id = editConciliation.id;
                    vm.record.currency_id = editConciliation.currency_id;
                    vm.record.institution_id = editConciliation.institution_id;

                    vm.record.start_date = editConciliation.start_date;
                    vm.record.end_date = editConciliation.end_date;
                    vm.record.bank_balance = editConciliation.bank_balance;

                    vm.record.movementToConsolidate = [];

                    editConciliation.finance_conciliation_bank_movements.forEach((elem) => {
                        if (elem.accounting_entry_account) {
                            vm.record.movementToConsolidate.push({
                                consolidation: {
                                    date: elem?.accounting_entry_account.entries.from_date,
                                    concept: elem.concept,
                                    debit: vm.formatAmount(elem.debit),
                                    assets: vm.formatAmount(elem.assets)
                                },
                                movement: {
                                    id: elem?.accounting_entry_account_id,
                                    amount: vm.formatAmount(elem?.accounting_entry_account.amount),
                                    concept: elem?.accounting_entry_account.entries.concept,
                                    date: elem?.accounting_entry_account.entries.from_date,
                                }
                            })
                        }
                    })

                    vm.getMovementsApprovedByAccount(editConciliation.finance_bank_account_id);
                }
            });

        },

        /**
         * cambia el tipo de moneda en el que se expresa el asiento contable
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        changeCurrency(currency_id) {
            if (currency_id) {
                axios.get(`${window.app_url}/currencies/info/${currency_id}`).then(response => {
                    this.data.currency = response.data.currency;
                    this.data.currency_id = response.data.currency.id;
                });
            } else {
                this.data.currency = {
                    id: '',
                    symbol: '',
                    name: '',
                    decimal_places: 2,
                };
                this.data.currency_id = '';
            }
        },
    },

}
</script>