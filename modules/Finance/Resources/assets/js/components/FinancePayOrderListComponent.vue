<template>
    <div>
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
                <label class="form-label">Fecha de solicitud</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha de solicitud"
                    tabindex="2"
                    v-model="filterBy.date"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Monto"
                    tabindex="3"
                    oninput="
                        this.value=this.value.replace(/[^0-9,.]/g, '').replace(/,/g, '.');
                    "
                    v-model="filterBy.amount"
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
            :url="'finance/pay-orders/vue-list'"
            :options="table_options"
            ref="tableOptions"
        >
            <div slot="code" slot-scope="props" class="text-center">
                {{ props.row.code }}
            </div>
            <div slot="name_sourceable" slot-scope="props" class="text-center">
                {{
                    props.row.name_sourceable ?
                    props.row.name_sourceable.description ?
                    props.row.name_sourceable.description :
                    props.row.name_sourceable.payroll_staff_id ?
                    props.row.receiver_name :
                    props.row.name_sourceable.name :
                    'N/A'
                }}
            </div>
            <div slot="source_amount" slot-scope="props" class="text-center">
                {{ parseFloat(props.row.source_amount).toFixed(2) }}
            </div>
            <div slot="status" slot-scope="props" class="text-center">
                <span class="text-danger"
                    v-if="props.row.status_aux === 'AN'">Anulado(a)</span>
                <span class="text-danger"
                    v-else-if="props.row.status_aux === 'RE'">Rechazado(a)</span>
                <span class="text-success"
                    v-else-if="props.row.status_aux === 'AP'">Aprobado(a)</span>
                <span class="text-success"
                    v-else-if="props.row.status_aux === 'PA'">Pagado(a)</span>
                <span class="text-warning"
                    v-else-if="props.row.status_aux === 'PE'">Pendiente</span>
            </div>
            <div slot="ordered_at" slot-scope="props" class="text-center">
                <span>{{ format_date(props.row.ordered_at) }}</span>
            </div>
            <div slot="id" slot-scope="props" class="d-inline-flex text-center">
                <div class="row text-center">
                    <div class="col-1 mb-1">
                        <button
                            class="btn btn-info btn-xs btn-icon btn-action"
                            data-title="Ver registro"
                            data-toggle="modal"
                            type="button"
                            data-target="#payOrderDetails"
                            @click="setDetails(props.row)"
                        >
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="col-1 mb-1">
                        <button
                            v-if="
                                props.row.status_aux === 'RE'
                                || props.row.status_aux === 'AP'
                                || props.row.status_aux === 'PA'
                                || props.row.status_aux === 'AN'
                                || (lastYear && format_date(props.row.ordered_at, 'YYYY') <= lastYear)
                            "
                            class="btn btn-success btn-xs btn-icon btn-action"
                            title="Aprobar registro"
                            type="button"
                            disabled
                        >
                            <i class="fa fa-check"></i>
                        </button>
                        <button
                            v-else
                            class="btn btn-success btn-xs btn-icon btn-action"
                            title="Aprobar registro"
                            data-toggle="tooltip"
                            type="button"
                            @click="approvePayOrderPermission
                            ? changeDocumentStatus('AP', props.row)
                            : showMessage(
                                'custom',
                                'Acceso Denegado',
                                'danger', 'screen-error',
                                'No posee los permisos necesarios para ejecutar esta funcionalidad'
                            )"
                        >
                            <i class="fa fa-check"></i>
                        </button>
                    </div>
                    <div class="col-1 mb-1">
                        <a
                            :href="
                                setUrl(`finance/pay-orders/pdf/${props.row.id}`)
                            "
                            target="_blank"
                            class="btn btn-primary btn-xs btn-icon btn-action"
                            title="Imprimir registro"
                            data-toggle="tooltip"
                        >
                            <i class="fa fa-print"></i>
                        </a>
                    </div>
                    <div class="col-1 mb-1">
                        <button
                            v-if="
                                props.row.status_payment_execute === 'PA'
                                || props.row.status_aux === 'AN'
                                || props.row.status_aux === 'AP'
                                || (lastYear && format_date(props.row.ordered_at, 'YYYY') <= lastYear)
                            "
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            type="button"
                            title="Modificar registro"
                            disabled
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            v-else
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            type="button"
                            @click="editForm(props.row.id)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>
                    <div class="col-1 mb-1">
                        <finance-cancel-pay-order
                            v-if="(cancelPayOrderPermission
                                    && (props.row.status_payment_execute === 'AN'
                                    || props.row.status_payment_execute === '')
                                    && props.row.status_aux === 'AP'
                                    && props.row.status =='PE'
                                    && !props.row.month
                                    && !props.row.period
                                )"
                            :cancelPayOrderPermission="cancelPayOrderPermission"
                            :id="props.row.id"
                            :code="props.row.code"
                            :observations="
                                props.row.concept
                                ? props.row.concept.replace(/(<([^>]+)>)/ig, '') : ''
                            "
                            :is_payroll_contribution="props.row.is_payroll_contribution"
                            :fiscal_year="(fiscal_years.length > 0) ? fiscal_years[0].text : ''"
                        />

                        <a
                            v-else
                            @click="
                            showMessage(
                                'custom',
                                'Acceso Denegado',
                                'danger', 'screen-error',
                                !cancelPayOrderPermission
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
            </div>
        </v-server-table>
        <div
            id="payOrderDetails"
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
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
                            <i class="fa fa-info-circle inline-block"></i>
                            Detalles de la orden de pago
                        </h6>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-center">
                            Datos de la Órden
                        </h6>
                        <h6
                            v-if="
                                details.document_status_action === 'AN'
                            "
                            class="text-center text-danger"
                        >
                            {{ details.document_status_name }}
                        </h6>
                        <br>
                        <div class="row">
                            <div class="col-6">
                                <b>Institución:</b> {{ details.institution.name }}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <b>Fecha:</b> {{ format_date(details.ordered_at) }}
                            </div>
                            <div class="col-md-3">
                                <b>Tipo de Orden:</b> {{ (details.type === 'PR') ? '' : 'No' }} Presupuestario
                            </div>
                            <div class="col-md-3">
                                <b>Tipo de Documento:</b>
                                {{
                                    details.document_type == 'C' ? 'Cotización' :
                                    details.document_type == 'M' ? 'Manual' :
                                    details.document_type == 'R' ? 'Reintegro' :
                                    details.document_type == 'P' ? 'Compromiso presupuestario' :
                                    details.document_type == 'O' ? 'Otro' :
                                    details.document_type == 'T' ? 'Retenciones' : ''
                                }}
                            </div>
                            <div class="col-md-3">
                                <b>Nro. Doc. Origen:</b>
                                {{
                                    details.document_sourceable.code ?
                                    details.document_sourceable.code :
                                    details.document_sourceable.reference ?
                                    details.document_sourceable.reference :
                                    details.document_sourceable.name ?
                                    details.document_sourceable.name :
                                    ''
                                }}
                            </div>
                            <div v-if="details.month && details.period" class="col-md-6">
                                <b>Periodo de pago:</b>
                                {{
                                    'Del ' + details.start_date + ' al ' + details.end_date
                                }}
                            </div>
                        </div>
                        <div v-if="details.month && details.period && details.document_type == 'T' && deductionsToPay.length" class="row justify-content-center">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Lista de Retenciones a ser pagadas</h6>
                            </div>
                        </div>
                        <div v-if="details.month && details.period && details.document_type == 'T' && deductionsToPay.length" class="row">
                            <div class="col-md-1">
                            </div>
                            <div class="col-md-10">
                                <table
                                    class="table table-hover table-striped"
                                >
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Tipo</th>
                                            <th>Monto en {{details.currency.symbol}}</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(deduction,
                                            index) in deductionsToPay"
                                            :key="index"
                                        >
                                            <td class="text-center">
                                                {{
                                                    index + 1
                                                }}
                                            </td>
                                            <td class="text-center">
                                                {{
                                                    deduction.name
                                                }}
                                            </td>
                                            <td class="text-center">
                                                {{
                                                    deduction.amount
                                                }}
                                            </td>
                                            <td class="text-center">
                                                {{
                                                    deduction.deducted_at
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Datos del Proveedor ó Beneficiario</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <b>Nombre o Razón Social:</b>
                                {{
                                    details.name_sourceable ?
                                    details.name_sourceable.description ?
                                    details.name_sourceable.description :
                                    details.name_sourceable.payroll_staff_id ?
                                    details.receiver_name :
                                    details.name_sourceable.name :
                                    'N/A'
                                }}
                            </div>
                            <div v-if="details.document_number" class="col-md-4">
                                <b>Nro. de Documento:</b> {{ details.document_number || 'No indica' }}
                            </div>
                            <div class="col-md-4">
                                <b>Monto:</b> {{ formatToCurrency(details.source_amount, details.currency.symbol) }}
                            </div>
                            <div class="col-md-4">
                                <b>Concepto:</b> {{ details.concept }}
                            </div>
                            <div class="col-12">
                                <b>Observación:</b>
                                <div v-html="details.observations"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12 mt-4 mb-4">
                                <h6>Datos Contables</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-sm table-striped table-hover">
                                    <col class="col-8">
                                    <col class="col-2">
                                    <col class="col-2">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase"
                                                width="50%"
                                            >
                                                CÓDIGO DE CUENTA - DENOMINACIÓN
                                            </th>
                                            <th
                                                class="text-uppercase"
                                                width="20%"
                                            >
                                                DEBE
                                            </th>
                                            <th
                                                class="text-uppercase"
                                                width="20%"
                                            >
                                                HABER
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="details.accounting_entryable">
                                        <tr
                                            v-for="
                                                (acc, index) in
                                                    details.accounting_entryable.accounting_entry.accounting_accounts
                                            "
                                            :key="index"
                                        >
                                            <td class="text-justify">
                                                {{ acc.account.code }} - {{ acc.account.denomination }}
                                            </td>
                                            <td class="text-right">
                                                {{ formatToCurrency(acc.debit, details.currency.symbol) }}
                                            </td>
                                            <td class="text-right">
                                                {{ formatToCurrency(acc.assets, details.currency.symbol) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td
                                                class="text-uppercase"
                                                colspan="4"
                                            >
                                                Sin Asiento Contable registrado
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
                            class="btn btn-default"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            records: [],
            lastYear: "",
            tmpRecords: [],
            fiscal_years: [],
            details: {
                ordered_at: '',
                type: '',
                documentType: '',
                document_number: '',
                source_amount: 0,
                amount: 0,
                concept: '',
                is_partial: false,
                observations: '',
                document_sourceable: {
                    reference: '',
                    code: ''
                },
                institution: {
                    name: ''
                },
                name_sourceable: {
                    name: ''
                },
                currency: {
                    symbol: '',
                    decimal_places: 2
                },
                accounting_entryable: {
                    accounting_entry: {
                        accounting_accounts: []
                    }
                },
            },
            cancelPayOrderPermission: false,
            approvePayOrderPermission: false,
            deductionsToPay: [],
            columns: [
                'code',
                'ordered_at',
                'name_sourceable',
                'concept',
                'source_amount',
                'status',
                'id'
            ],
            filterBy: {
                code: '',
                date: '',
                amount: '',
            },
        }
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
                date: '',
                amount: '',
            };
            vm.$refs.tableOptions.refresh();
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
                query: vm.filterBy.date ? vm.format_date(vm.filterBy.date) : vm.filterBy.code ? vm.filterBy.code : vm.filterBy.amount,
                limit: 10,
                ascending: 1,
                page: 1,
                byColumn: 0
            }

            axios.get(`${window.app_url}/finance/pay-orders/vue-list`, {params: params})
            .then(response => {
                vm.$refs.tableOptions.data = response.data.data;
            });
        },

        /**
         * Modifica el estatus del documento de la orden de pago
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param   {String}  status  Estatus a modificar
         * @param   {Object}  record  Datos del registro a modificar
         */
        changeDocumentStatus(status, record) {
            const vm = this;
            const url = vm.setUrl('finance/pay-orders/change-document-status');
            const titleList = ["Aprobar registro", "Rechazar registro"];
            const textList = [
                "¿Está seguro? Una vez aprobado el registro no se podrá modificar y/o eliminar.",
                "¿Está seguro? Una vez rechazado el regristro no se podrá continuar con el proceso."
            ];
            const titleConfirm = (status == 'AP') ? titleList[0] : titleList[1];
            const messageConfirm = (status == 'AP') ? textList[0] : textList[1];

            bootbox.confirm({
                title: titleConfirm,
                message: messageConfirm,
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
                        axios.post(url, { id: record.id, action: status }).then(response => {
                            record = response.data.record;
                            vm.showMessage(
                                'custom',
                                '¡Éxito!',
                                'success',
                                'screen-ok',
                                (status == 'AP') ? 'Órden pago aprobada' : 'Órden pago rechazada'
                            );
                            location.reload();
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
                            }
                            console.error(error);
                        });
                        vm.loading = false;
                    }
                }
            });
        },

        /**
         * Establece los datos para mostrar detalles del registro seleccionado
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param   {Object}  record  Datos del registro a mostrar
         */
        async setDetails(record) {
            this.details = record;
            this.details.document_status_name = this.details.document_status.name;
            this.details.document_status_action = this.details.document_status.action;
            await this.getDeductionsToPay(this.details.deductions_ids);
        }
    },
    created() {
        this.table_options.headings = {
            'code': 'Código',
            'ordered_at': 'Fecha de solicitud',
            'name_sourceable': 'Proveedor / Beneficiario',
            'concept': 'Concepto',
            'source_amount': 'Monto',
            'status': 'Estatus',
            'id': 'Acción'
        };
        this.table_options.columnsClasses = {
            'code': 'col-md-2',
            'ordered_at': 'col-md-2',
            'name_sourceable': 'col-md-2',
            'concept': 'col-md-2',
            'source_amount': 'col-md-1',
            'status': 'col-md-1',
            'id': 'col-md-2 text-center'
        };
        this.table_options.sortable = [
            'code',
            'ordered_at',
            'name_sourceable',
            'concept',
            'source_amount',
            'status'
        ];
        this.table_options.filterable = [
            'code',
            'ordered_at',
            'name_sourceable',
            'concept',
            'source_amount',
            'status'
        ];
    },

    async mounted() {
        const vm = this;
        vm.loadingState(true); // Inicio de spinner de carga.
        axios.get(`${window.app_url}/finance/pay-orders/vue-list`)
            .then(response => {
            vm.cancelPayOrderPermission = response.data.cancelPayOrderPermission;
            vm.approvePayOrderPermission = response.data.approvePayOrderPermission;
        });
        await vm.queryLastFiscalYear();
        await vm.getOpenedFiscalYears();
        vm.loadingState(); // Finaliza spinner de carga.
    },
}
</script>
