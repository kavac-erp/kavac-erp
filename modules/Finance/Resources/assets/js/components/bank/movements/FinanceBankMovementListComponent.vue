<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Código</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Código"
                    tabindex="1"
                    v-model="filterBy.code"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha de pago</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de pago"
                    tabindex="2"
                    v-model="filterBy.date"
                />
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button
                        class="btn btn-default btn-icon btn-xs-responsive px-3"
                        type="reset"
                        aria-label="Search"
                        title="Limpiar filtro"
                        @click="resetFilters()"
                    >
                        <i class="fa fa-eraser"></i>
                    </button>
                    <button
                        class="btn btn-info btn-icon btn-xs-responsive px-3"
                        type="button"
                        aria-label="Search"
                        title="Buscar"
                        @click="filterTable()"
                    >
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Final de filtros de la tabla -->
        <hr>
        <!-- Tabla de registros de Movimientos bancarios -->
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="code" slot-scope="props" class="text-center">
                <span>{{ props.row.code }}</span>
            </div>
            <div slot="payment_date" slot-scope="props" class="text-center">
                <span>{{ format_date(props.row.payment_date ) }}</span>
            </div>
            <div slot="amount" slot-scope="props">
                {{ props.row.currency_id ? props.row.currency.symbol : 'XXX' }}
                {{ addDecimals(props.row.amount) }}
            </div>
            <div slot="document_status" slot-scope="props">
                <span class="text-success"
                        v-if="props.row.document_status.action === 'AP' ">
                        {{ props.row.document_status.name }}
                    </span>
                    <span class="text-warning" title="Este registro puede ser aprobado desde asientos contables"
                        v-else-if="props.row.document_status.action === 'PR'">
                        Pendiente
                    </span>
                    <span class="text-danger"
                        v-else-if="props.row.document_status.action === 'AN'">
                        {{ props.row.document_status.name }}
                    </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <template v-if="(lastYear && format_date(props.row.payment_date, 'YYYY') <= lastYear)">
                        <button
                            class="btn btn-success btn-xs btn-icon btn-action"
                            type="button"
                            disabled
                        >
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            v-show="props.row.document_status.action === 'PR'"
                            class="btn btn-success btn-xs btn-icon btn-action"
                            title="Aprobar"
                            data-toggle="tooltip"
                            type="button"
                            @click="approveBankMovement(props.row.id)"
                        >
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <button
                        @click.prevent="
                            setDetails(
                                'BankMovementInfo',
                                props.row.id,
                                'FinanceBankMovementInfo'
                            )
                        "
                        class="
                            btn btn-info btn-xs btn-icon btn-action btn-tooltip
                        "
                        title="Ver registro"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        type="button"
                    >
                        <i class="fa fa-eye"></i>
                    </button>

                    <template v-if="(lastYear && format_date(props.row.payment_date, 'YYYY') <= lastYear)
                                    || (props.row.is_payment_executed
                                    || props.row.document_status.action === 'AP'
                                    || props.row.document_status.action === 'AN')
                    ">
                        <button
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            type="button"
                            title="Modificar registro"
                            disabled
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            type="button"
                            title="Eliminar registro"
                            disabled
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            type="button"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            @click="deleteRecord(props.row.id)"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            title="Eliminar registro"
                            data-toggle="tooltip"
                            type="button"
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <finance-cancel-bank-movements
                        v-show="cancelBankMovementPermission
                            && props.row.document_status.action === 'AP'
                            && !props.row.is_payment_executed"
                        :cancelBankMovementPermission="cancelBankMovementPermission"
                        :id="props.row.id"
                        :code="props.row.code"
                        :concept="props.row.concept ? props.row.concept.replace(/(<([^>]+)>)/ig, '') : ''"
                        :is_payment_executed="props.row.is_payment_executed"
                        :fiscal_year="(fiscal_years.length > 0) ? fiscal_years[0].text : ''"
                    />
                </div>
            </div>
        </v-client-table>
        <!-- Final de Tabla de registros de Movimientos bancarios -->
        <!-- Modal -->
        <finance-bank-movements-info
            ref="BankMovementInfo">
        </finance-bank-movements-info>
        <!-- Final de Modal -->
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                tmpRecords: [],
                fiscal_years: [],
                columns: [
                    'payment_date',
                    'code',
                    'reference',
                    'transaction_type',
                    'concept',
                    'amount',
                    'document_status',
                    'id'
                ],
                filterBy: {
                    code: '',
                    date: '',
                },
            }
        },
        created() {
            this.table_options.headings = {
                'payment_date': 'Fecha de pago',
                'code': 'Código',
                'reference': 'Documento de referencia',
                'transaction_type': 'Tipo de transacción',
                'concept': 'Concepto',
                'amount': 'Monto',
                'document_status': 'Estatus',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'payment_date',
                'code',
                'reference',
                'transaction_type',
                'concept',
                'document_status',
                'amount'
            ];
            this.table_options.filterable = [
                'payment_date',
                'code',
                'reference',
                'transaction_type',
                'concept',
                'document_status',
                'amount'
            ];
            this.table_options.columnsClasses = {
                'payment_date': 'col-md-1',
                'code': 'col-md-1',
                'reference': 'col-md-2',
                'transaction_type': 'col-md-2',
                'concept': 'col-md-3',
                'amount': 'col-md-1',
                'document_status': 'col-md-1',
                'id': 'col-md-1'
            };
        },
        async mounted () {
            const vm = this;
            axios.get(`${window.app_url}/finance/movements/vue-list`)
                .then(response => {
                vm.records = response.data.records;
                vm.cancelBankMovementPermission = response.data.cancelBankMovementPermission;
                // Variable usada para el reseteo de los filtros de la tabla.
                vm.tmpRecords = vm.records;
            });
            await vm.queryLastFiscalYear();
            await vm.getOpenedFiscalYears();
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
                    code: '',
                    date: ''
                };
                vm.records = vm.tmpRecords;
            },

            /**
             * Truncar y redondear una cifra según el número pasado como segundo
             * parámetro del método toFixed().
             */
            addDecimals(value) {
                return parseFloat(value).toFixed(2);
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
                vm.records = vm.tmpRecords.filter((rec) => {
                    return (vm.filterBy.code)
                        ? (rec.code
                        === vm.filterBy.code)
                        : true;
                }).filter((rec) => {
                    return (vm.filterBy.date)
                        ? (rec.payment_date
                        === vm.filterBy.date)
                        : true;
                })
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
            },

        /**
         * Método para aprobar un movimiento bancario
         *
         * @method approveBankMovement
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        approveBankMovement(id) {
            const vm = this;
            const url = vm.setUrl('finance/movements/change-document-status');
            bootbox.confirm({
                title: "Aprobar registro",
                message: "¿Está seguro? Una vez aprobado no se podrá modificar y/o eliminar este registro.",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar'
                    }
                },
                callback: function(result) {
                    if (result) {
                        vm.loading = true;
                        axios.post(url, { id: id }).then(response => {
                            if (response.status == 200){
                                vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Movimiento bancario aprobado');
                                location.reload();
                            }
                        }).catch(error => {
                            if (typeof(error.response) !="undefined") {
                                if (error.response.status == 403) {
                                    vm.showMessage(
                                        'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
                                    );
                                }
                                if (error.response.status == 500) {
                                    const messages = error.response.data.message;
                                    vm.showMessage(
                                        messages.type, messages.title, messages.class, messages.icon, messages.text
                                    );
                                }
                                console.error(error);
                            }
                        });
                        vm.loading = false;
                    }
                }
            });
        },
        }
    };
</script>