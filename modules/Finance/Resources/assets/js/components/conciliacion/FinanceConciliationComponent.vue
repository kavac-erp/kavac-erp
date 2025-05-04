<template>
    <div>
        <div class="alert alert-danger" v-if="errors.length > 0">
            <div class="alert-icon">
                <i class="now-ui-icons objects_support-17"></i>
            </div>
            <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
            <button type="button" class="close" data-dismiss="alert" @click="resetErrors"
                aria-label="Close">
                <span aria-hidden="true">
                    <i class="now-ui-icons ui-1_simple-remove"></i>
                </span>
            </button>
            <ul>
                <li v-for="(error, index) in errors"
                    :key="index">{{ error }}
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group is-required">
                    <label>Cuenta bancaria:</label>
                    <select2 :options="accounts" v-model="record.account_id"></select2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group is-required">
                    <label>Mes:</label>
                    <select2
                        :options="months"
                        v-model="record.month"
                    >
                    </select2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group is-required">
                    <label>Año:</label>
                    <select2
                        :options="years"
                        v-model="record.year"
                    >
                    </select2>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group text-right">
            <button type="button"
                class="btn btn-warning btn-sm btn-round"
                @click="reset()">
                Cancelar
            </button>
            <button type="button" @click="showTableResults()"
                class="btn btn-primary btn-sm btn-round">
                Consultar
            </button>
        </div>
        <br>
        <table v-if="tableResults" table border="1px" cellpadding="0px"
            cellspacing="0px" style="width:100%">
            <thead>
                <tr bgcolor="#D3D3D3">
                    <th colspan="7" align="center">
                        Movimientos registrados en la cuenta n° : {{ getBankAccount() }}
                    </th>
                </tr>
                <tr bgcolor="#D3D3D3">
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>N° Referencia</th>
                    <th>Monto</th>
                    <th>Coincide</th>
                    <th>Sub-total</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(movement, index) in movements" :key="index" class="text-center" bgcolor="#eeeeee">
                    <td >
                        {{ movement.payment_date }}
                    </td>
                    <td>
                        {{ movement.concept }}
                    </td>
                    <td>
                        {{ movement.reference }}
                    </td>
                    <td>
                        {{ movement.amount }}
                    </td>
                    <td>
                        {{ 'No' }}
                    </td>
                    <td v-for="i in 2" :key="i">
                        &nbsp;
                    </td>
                </tr>
                <tr bgcolor="#e6e6e6">
                    <th colspan="3" class="text-center">
                       {{ 'Sub-Total:' }}
                    </th>
                    <td v-for="i in 4" :key="i">
                        &nbsp;
                    </td>
                </tr>
                <tr bgcolor="#D3D3D3">
                    <th colspan="3" class="text-center">
                       {{ 'Saldo según libros:' }}
                    </th>
                    <td v-for="i in 4" :key="i">
                        &nbsp;
                    </td>
                </tr>
                <tr bgcolor="#e6e6e6">
                    <th colspan="7">
                        {{ 'Movimientos bancarios' }}
                    </th>
                </tr>
                <tr v-for="(movement, index) in movements" :key="index" class="text-center" bgcolor="#eeeeee">
                    <td >
                        {{ movement.payment_date }}
                    </td>
                    <td>
                        {{ movement.concept }}
                    </td>
                    <td>
                        {{ movement.reference }}
                    </td>
                    <td>
                        {{ movement.amount }}
                    </td>
                    <td>
                        {{ 'No' }}
                    </td>
                    <td v-for="i in 2" :key="i">
                        &nbsp;
                    </td>
                </tr>
                <tr bgcolor="#D3D3D3">
                    <th colspan="3" class="text-center">
                       {{ 'Sub-Total:' }}
                    </th>
                    <td v-for="i in 4" :key="i">
                        &nbsp;
                    </td>
                </tr>
                <tr bgcolor="#e6e6e6">
                    <th colspan="3" class="text-center">
                       {{ 'Sub-Total General:' }}
                    </th>
                    <td v-for="i in 4" :key="i">
                        &nbsp;
                    </td>
                    
                </tr>
                <tr bgcolor="#D3D3D3">
                    <th colspan="3" class="text-center">
                       {{ 'Saldo según Banco:' }}
                    </th>
                    <td v-for="i in 4" :key="i">
                        &nbsp;
                    </td>
                    
                </tr>
            </tbody>
        </table>
        <div class="text-right mt-4" v-if="tableResults">
            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal" title="Cerrar ventana modal" data-toggle="tooltip" v-has-tooltip>
                Cerrar
            </button>
            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear" title="Limpiar información del formulario" data-toggle="tooltip" v-has-tooltip>
                Cancelar
            </button>
            <button type="button" class="btn btn-primary btn-sm btn-round btn-modal-save" title="Guardar información de registro" data-toggle="tooltip" v-has-tooltip>
                Guardar
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                record: {
                    account_id: '',
                    month: '',
                    year: '',
                    file: '',
                    coincidences: false,
                },
                accounts: [],
                errors: [],
                movements: [],
                months: [
                    { "id": "", "text": "Seleccione..." },
                    { "id": 1, "text": "Enero"},
                    { "id": 2, "text": "Febrero"},
                    { "id": 3, "text": "Marzo"},
                    { "id": 4, "text": "Abril"},
                    { "id": 5, "text": "Mayo"},
                    { "id": 6, "text": "Junio"},
                    { "id": 7, "text": "Julio"},
                    { "id": 8, "text": "Agosto"},
                    { "id": 9, "text": "Septiembre"},
                    { "id": 10, "text": "Octubre"},
                    { "id": 11, "text": "Noviembre"},
                    { "id": 12, "text": "Diciembre"},
                ],
                years: [
                    { "id": "", "text": "Seleccione..." },
                ],
                tableResults: false
            }
        },
        methods: {
            fileUpload(event) {
                const vm = this;
                vm.record.file = event.target.files[0];
                console.log(vm.record.file);
            },

            /**
             * Método que limpia todos los datos del formulario.
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    account_id: '',
                    month: '',
                    year: '',
                    file: '',
                    coincidences: false,
                };
                this.$refs.file.value = null;
                this.tableResults = false;
                this.resetErrors();
            },

            /**
             * Método que muestra la tabla de resultados.
             * 
             * @author  José Briceño <josejorgebriceno9@gmail.com>
             */
            async showTableResults() {
                const vm = this;
                vm.resetErrors();

                let formData = new FormData();

                for (const key in vm.record) {
                    formData.append(key, vm.record[key]);
                }


                try {
                    let response = await axios.post(`${vm.app_url}/finance/get-bank-account-conciliation`, formData);
                    vm.movements = response.data.movements;
                    await vm.getBankAccount();
                    vm.tableResults = true;
                } catch (error) {
                    console.log(error.response.data.errors);
                    vm.errors = [];

                    if (typeof(error.response) !="undefined") {
                        for (var index in error.response.data.errors) {
                            if (error.response.data.errors[index]) {
                                vm.errors.push(error.response.data.errors[index][0]);
                            }
                        }
                    }
                }
            },

            /**
             * Obtiene los datos de las cuentas bancarias.
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            async getBankAccounts() {
                let vm = this;
                await axios.get(`${vm.app_url}/finance/get-bank-accounts/`).then(response => {
                    vm.accounts = response.data;
                }).catch(error => {
                    vm.logs('Budget/Resources/assets/js/_all.js', 127, error, 'getBankAccounts');
                });
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
                    for (var i=start_operations_year; i < year+1; i++) {
                        vm.years.push({ "id": i, "text": i});
                    }
                }).catch(error => {
                    console.log("Error");
                });
            },

            /**
             * Retorna la forma numerica de la cuenta bancaria seleccionada
             * 
             * @author José Briceño <josejorgebriceno9@gmail.com>
             */
            getBankAccount() {
                const vm = this;
                
                let acc = vm.accounts.find(account => 
                    account.id == vm.record.account_id
                );
                return acc.id != '' ? acc.text : '';
            },

            resetErrors() {
                this.errors = [];
            }
        },
        created() {
            this.getBankAccounts();
            this.getInstitutionStartOperationYear();
        },
        mounted() {
        },
    };
</script>
