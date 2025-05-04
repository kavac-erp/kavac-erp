<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha de generación</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de generación"
                    tabindex="1"
                    v-model="filterBy.compromised_at"
                />
            </div>
            <div class="col-md-2">
                <label for="" class="form-label">Código del compromiso</label>
                <input
                    id="prefix"
                    class="form-control"
                    type="text"
                    placeholder="Código del compromiso"
                    tabindex="2"
                    v-model="filterBy.code"
                />
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button
                        type="reset"
                        class="btn btn-default btn-icon btn-xs-responsive px-3"
                        aria-label="Search"
                        @click="resetFilters()"
                        title="Limpiar filtro"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-info btn-icon btn-xs-responsive px-3"
                        aria-label="Search"
                        @click="filterTable()"
                        title="Buscar"
                    >
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Final de filtros de la tabla -->
        <hr>
        <v-server-table
            :columns="columns"
            :url="'budget/compromises/list/all'"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="description" slot-scope="props" class="text-justify">
                <div v-html="props.row.description"></div>
            </div>
            <div slot="compromised_at" slot-scope="props" class="text-center">
                {{ new Date(props.row.compromised_at).toLocaleDateString('en-GB', {timeZone: 'UTC'}) }}
            </div>
            <div slot="code_acc" slot-scope="props" class="text-center">
                {{ props.row.budget_compromise_details[0] &&
                    props.row.budget_compromise_details[0].
                    budget_sub_specific_formulation ?
                    props.row.budget_compromise_details[0].
                    budget_sub_specific_formulation.specific_action.code :
                    'No definido'
                }}
            </div>
            <div slot="status" slot-scope="props" class="text-center">
                <span class="text-warning"
                    v-if="props.row.status === 'PE'"> Pendiente
                </span>
                <span class="text-success"
                    v-else-if="props.row.status === 'AP'"> Aprobado(a)
                </span>
                <span class="text-info"
                    v-else-if="props.row.status === 'CAU'"> Causado(a)
                </span>
                <span class="text-success"
                    v-else-if="props.row.status === 'PA'"> Pagado(a)
                </span>
                <span class="text-danger"
                    v-else-if="props.row.status === 'AN'"> Anulado(a)
                </span>
            </div>
            <div slot="id" slot-scope="props" class="d-inline-flex text-center">
                <button
                    @click.prevent="setDetails('BudgetCompromise',
                        props.row.id, 'BudgetCompromiseInfo')"
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                    title="Ver registro"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    type="button"
                >
                    <i class="fa fa-eye"></i>
                </button>
                <template
                    v-if="
                        (lastYear && format_date(
                            props.row.compromised_at, 'YYYY'
                        ) <= lastYear)
                        || props.row.status != 'PE'
                    "
                >
                    <button
                        class="btn btn-success btn-xs btn-icon btn-action"
                        type="button"
                        title="Aprobar registro"
                        disabled
                    >
                        <i class="fa fa-check"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar registro"
                        data-toggle="tooltip"
                        type="button"
                        @click="approveBudgetCompromisePermission
                        ? approveBudgetCompromise(props.row.id)
                        : showMessage(
                            'custom',
                            'Acceso Denegado',
                            'danger', 'screen-error',
                            'No posee los permisos necesarios para ejecutar esta funcionalidad'
                        )"
                    >
                        <i class="fa fa-check"></i>
                    </button>
                </template>
                <template v-if="
                        (lastYear && format_date(props.row.compromised_at, 'YYYY') <= lastYear
                        || (props.row.exist_pay_order && props.row.status_pay_order
                        && props.row.status_pay_order != 'AN')
                        || props.row.status === 'AP'
                        || props.row.status === 'CAU'
                        || props.row.status === 'AN'
                        || props.row.status === 'PA'
                    )">
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        type="button"
                        title="Modificar registro"
                        disabled
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        class="btn btn-warning btn-xs btn-icon btn-action"
                        data-toggle="tooltip"
                        title="Modificar registro"
                        type="button"
                        @click="editForm(props.row.id)"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                </template>
                <a
                    class="btn btn-primary btn-xs btn-icon"
                    title="Imprimir registro"
                    data-toggle="tooltip"
                    target="_blank"
                    :href="purchase_compromises_pdf + props.row.id"
                    v-has-tooltip
                >
                    <i class="fa fa-print"></i>
                </a>
                <budget-cancel-compromise
                    v-if="
                        cancelBudgetCompromisePermission
                        && props.row.status === 'AP'
                    "
                    :cancelBudgetCompromisePermission="
                        cancelBudgetCompromisePermission
                    "
                    :id="props.row.id"
                    :code="props.row.code"
                    :observations="
                        props.row.description
                        ? props.row.description.replace(/(<([^>]+)>)/ig, '') : ''
                    "
                    :fiscal_year="
                        (fiscal_years.length > 0) ? fiscal_years[0].text : ''
                    "
                />
                <div v-else>
                    <a
                        @click="
                        showMessage(
                            'custom',
                            'Acceso Denegado',
                            'danger', 'screen-error',
                            !cancelBudgetCompromisePermission
                                ? 'No tiene los permisos necesarios para ejecutar esta funcionalidad'
                                : 'Este registro se está usando en otro proceso',
                        )"
                        class="btn btn-xs btn-dark btn-icon btn-action"
                        title="Anular registro"
                        data-toggle="tooltip"
                        type="button"
                        disabled
                    >
                        <i class="ion ion-android-close"></i>
                    </a>
                </div>
            </div>
        </v-server-table>
        <budget-compromise-info ref="BudgetCompromise"></budget-compromise-info>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                fiscal_years: [],
                purchase_compromises_pdf: `${window.app_url}/budget/compromise/pdf/`,
                tmpRecords: [],
                columns: [
                    'compromised_at',
                    'code',
                    'document_number',
                    'code_acc',
                    'description',
                    'status',
                    'id'
                ],
                filterBy: {
                    compromised_at: '',
                    code: '',
                },
                cancelBudgetCompromisePermission: false,
                approveBudgetCompromisePermission: false,
            }
        },
        created() {
            this.table_options.headings = {
                'compromised_at': 'Fecha de generación',
                'code': 'Código del compromiso',
                'document_number': 'Documento de origen',
                'code_acc': 'Código de la Acción Específica',
                'description': 'Descripción',
                'status': 'Estatus',
                'id': 'Acción'
            };
            this.table_options.sortable = ['code', 'document_number', 'code_acc', 'compromised_at', 'description'];
            this.table_options.filterable = ['code', 'document_number', 'code_acc', 'compromised_at', 'description'];
            this.table_options.columnsClasses = {
                'compromised_at': 'col-md-1',
                'code': 'col-md-2 text-center',
                'document_number': 'col-md-2 text-center',
                'code_acc': 'col-md-1',
                'description': 'col-md-2',
                'status': 'col-md-2',
                'id': 'col-md-2 text-center'
            };
        },
        methods: {
            /**
             * Método para reestablecer valores iniciales del formulario de filtros.
             *
             * @method resetFilters
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@cenditel.gob.ve>
             */
            resetFilters() {
                const vm = this;
                vm.filterBy = {
                    compromised_at: '',
                    code: '',
                };
                vm.$refs.tableResults.refresh();
            },

            /**
             * Método que permite filtrar los datos de la tabla.
             *
             * @method filterTable
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            filterTable() {
                const vm = this;

                let params = {
                    query: vm.filterBy.compromised_at ? vm.format_date(vm.filterBy.compromised_at) : vm.filterBy.code,
                    limit: 10,
                    ascending: 1,
                    page: 1,
                    byColumn: 0
                }

                axios.get(`${window.app_url}/budget/compromises/list/all`, {params: params})
                .then(response => {
                        vm.$refs.tableResults.data = response.data.data;
                    });
            },

            /**
             * Método que establece los datos del registro seleccionado para el cual se desea mostrar detalles
             *
             * @method    setDetails
             *
             * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             * @param     string   ref       Identificador del componente
             * @param     integer  id        Identificador del registro seleccionado
             * @param     object  var_list  Objeto con las variables y valores a asignar en las variables del componente
             */
            setDetails(ref, id, modal ,var_list = null) {
                const vm = this;
                if (var_list) {
                    for(var i in var_list){
                        vm.$refs[ref][i] = var_list[i];
                    }
                }else{
                    vm.$refs[ref].record = vm.$refs.tableResults.data.filter(r => {
                        return r.id === id;
                    })[0];
                }
                vm.$refs[ref].id = id;
                $(`#${modal}`).modal('show');
                vm.calculateTot(vm.$refs[ref].record);
                vm.calculateTotalTax(vm.$refs[ref].record);
                vm.currencySymbol(vm.$refs[ref].record);
                let record = vm.$refs[ref].record;
                vm.showAccountsTable(record);
            },

            /**
             * Método para visualizar solo las cuentas que no son padres en la tabla
             *
             * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
             *
             */
            showAccountsTable(record) {
                const vm = this;
                for (let [index, account] of record.budget_compromise_details.entries()) {
                    let item = account.budget_account.code.split('.');
                    if (item[3] == '00') {
                        record.budget_compromise_details.splice(index, 1);
                    }
                }
            },

                /**
             * Calcula el total de la suma de las cuentas.
             *
             * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
             */
            calculateTot(record) {
                const vm = this;
                record.total = 0;
                for (let i = 0; i < record.budget_compromise_details.length; i++) {
                    let r = record.budget_compromise_details[i];
                    if(r.tax_amount == 0){
                        record.total += parseFloat(r.amount);
                    }
                }

                return record.total;
            },

            /**
             * Calcula el total de la suma de los impuestos.
             *
             * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
             */
            calculateTotalTax(record) {
                const vm = this;
                record.totalTax = 0;
                for (let i = 0; i < record.budget_compromise_details.length; i++) {
                    let t = record.budget_compromise_details[i];
                    record.totalTax += parseFloat(t.tax_amount);
                }

                return record.totalTax;
            },

            currencySymbol(record) {
                const vm = this;
                record.currency_symbol = "";
                record.currency_symbol = record.budget_compromise_details
                    ? record.budget_compromise_details[0].budget_sub_specific_formulation.currency.symbol
                    : '';

                return record.currency_symbol;
            },
            /**
         * Método para aprobar un compromiso
         *
         * @method approveBudgetCompromise
         *
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         */
        approveBudgetCompromise(id) {
            const vm = this;
            const url = vm.setUrl('budget/compromises/approve');
            bootbox.confirm({
                title: "Aprobar registro",
                message: "¿Está seguro? Una vez aprobado el registro no se podrá modificar y/o eliminar.",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No',
                        className: 'btn btn-default btn-sm btn-round'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si',
                        className: 'btn btn-primary btn-sm btn-round'
                    }
                },
                callback: function(result) {
                    if (result) {
                        vm.loading = true;
                        axios.post(url, { id: id }).then(response => {
                            if (response.status == 200){
                                vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Compromiso aprobado correctamente');
                                location.reload();
                            }
                        }).catch(error => {
                            if (typeof(error.response) !="undefined") {
                                if (error.response.status == 500) {
                                    const messages = error.response.data.message;
                                    vm.showMessage(
                                        messages.type, messages.title, messages.class, messages.icon, messages.text
                                    );
                                }
                            }
                            console.error(error);
                        });
                        vm.loading = false;
                    }
                }
            });
        },
        },
        async mounted() {
            const vm = this;
            vm.loadingState(true); // Inicio de spinner de carga.
            // Obtener la lista de todos los compromisos.
            let url = this.setUrl('budget/compromises/list/all');
            axios.get(url).then(response => {
                this.cancelBudgetCompromisePermission = response.data.cancelBudgetCompromisePermission;
                this.approveBudgetCompromisePermission = response.data.approveBudgetCompromisePermission;
                // Variable usada para el reseteo de los filtros de la tabla.
                this.tmpRecords = response.data.records;
            });
            await vm.queryLastFiscalYear();
            await vm.getOpenedFiscalYears();
            vm.loadingState(); // Finaliza spinner de carga.
        },
    };
</script>
