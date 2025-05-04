<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha</label>
                <input
                    class="form-control"
                    type="date"
                    placeholder="Fecha"
                    tabindex="1"
                    v-model="filterBy.approved_at"
                />
            </div>
            <div class="col-md-2">
                <label for="" class="form-label">Código de la AE</label>
                <input
                    id="prefix"
                    class="form-control"
                    type="text"
                    placeholder="Código de la AE"
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
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
            ref="tableResultsTransfer"
        >
            <div slot="approved_at" slot-scope="props" class="text-center">
                {{ format_date(props.row.approved_at, 'DD/MM/YYYY') }}
            </div>
            <div
                slot="code" slot-scope="props">
                {{
                    props.row.budget_modification_accounts[0] &&
                    props.row.budget_modification_accounts[0].
                    budget_sub_specific_formulation ?
                    props.row.budget_modification_accounts[0].
                    budget_sub_specific_formulation.specific_action.code :
                    'No defindo'
                }}
            </div>
            <div slot="status" slot-scope="props" class="text-center">
                <span
                    v-if="
                        props.row.status === 'PE'
                        || props.row.status === null
                    "
                    class="text-warning"
                >
                    Pendiente
                </span>
                <span
                    v-else
                    class="text-success"
                >
                    Aprobado
                </span>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <button
                    @click.prevent="
                        setDetails(
                            'BudgetTransferListModel',
                            props.row.id,
                            'BudgetTransferListModel'
                        )
                    "
                    class="btn btn-info btn-xs btn-icon btn-action btn-tooltip"
                    title="Ver registro"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    type="button"
                >
                    <i class="fa fa-eye"></i>
                </button>
                <a
                    class="btn btn-primary btn-xs btn-icon"
                    title="Imprimir registro"
                    data-toggle="tooltip"
                    target="_blank"
                    :href="budget_transfers_pdf + props.row.id"
                    v-has-tooltip
                >
                    <i class="fa fa-print"></i>
                </a>
                <button
                    v-if="
                        props.row.status === 'PE'
                        || props.row.status === null
                    "
                    class="btn btn-success btn-xs btn-icon btn-action"
                    title="Aprobar registro"
                    @click="changeStatus('AP', props.row.id)"
                    type="button"
                >
                    <i class="fa fa-check"></i>
                </button>
                <template v-if="
                        (lastYear && format_date(
                        props.row.approved_at, 'YYYY') <= lastYear)
                    "
                >
                    <button
                        class="btn btn-warning btn-xs btn-icon"
                        type="button"
                        disabled
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        class="btn btn-danger btn-xs btn-icon"
                        type="button"
                        disabled
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
                <template v-else>
                    <button
                        @click="editForm(props.row.id)"
                        data-placement="bottom"
                        class="btn btn-warning btn-xs btn-icon"
                        title="Modificar registro"
                        data-toggle="tooltip"
                        type="button"
                        :disabled="props.row.status === 'AP'"
                    >
                        <i class="fa fa-edit"></i>
                    </button>
                    <button
                        @click="deleteRecord(props.row.id, '')"
                        data-placement="bottom"
                        class="btn btn-danger btn-xs btn-icon"
                        title="Eliminar registro"
                        data-toggle="tooltip"
                        type="button"
                        :disabled="props.row.status === 'AP'"
                    >
                        <i class="fa fa-trash-o"></i>
                    </button>
                </template>
            </div>
        </v-client-table>
        <budget-modtinfo ref="BudgetTransferListModel"></budget-modtinfo>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                records: [],
                lastYear: "",
                tmpRecords: [],
                budget_transfers_pdf: `${window.app_url}/budget/transfers/pdf/`,
                columns: [
                    'approved_at',
                    'code',
                    'description',
                    'document',
                    'status',
                    'id'
                ],
                filterBy: {
                    approved_at: '',
                    code: '',
                },
            }
        },
        created() {
            this.table_options.headings = {
                'approved_at': 'Fecha',
                'code': 'Código de la Acción Específica',
                'description': 'Descripción',
                'document': 'Documento',
                'status': 'Estatus',
                'id': 'Acción'
            };
            this.table_options.sortable = [
                'code',
                'approved_at',
                'description',
                'status',
                'document'
            ];
            this.table_options.filterable = [
                'code',
                'approved_at',
                'description',
                'document'
            ];
            this.table_options.columnsClasses = {
                'approved_at': 'col-md-2 text-center',
                'code': 'col-md-2 text-center',
                'description': 'col-md-3',
                'document': 'col-md-2',
                'status': 'col-md-1 text-center',
                'id': 'col-md-2'
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
                    approved_at: '',
                    code: '',
                };
                vm.records = vm.tmpRecords;
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
                    return (vm.filterBy.approved_at) ? (this.format_date(rec.approved_at, 'YYYY-MM-DD') === vm.filterBy.approved_at) : true;
                }).filter((rec) => {
                    return (vm.filterBy.code) ? (rec.budget_modification_accounts[0].budget_sub_specific_formulation.specific_action.code == vm.filterBy.code) : true;
                })
            },

            /**
             * Modifica el estatus del registro.
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author Argenis Osorio <aosorio@cenditel.gob.ve> | <aosorio@cenditel.gob.ve>
             *
             * @param   {String}  status  Estatus a modificar.
             * @param   {Object}  id  ID del registro a modificar.
             */
            changeStatus(status, id) {
                const vm = this;
                const url = vm.setUrl(
                    `${window.app_url}/budget/modifications/change-status/${id}`
                );
                const titleList = ["Aprobar registro"];
                const textList = [
                    "¿Está seguro? Una vez aprobado el registro no se podrá modificar y/o eliminar.",
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
                            axios.post(url, {status: status}).then(response => {
                                vm.showMessage(
                                    'custom',
                                    '¡Éxito!',
                                    'success',
                                    'screen-ok',
                                    response.data.message
                                );
                                location.reload();
                            }).catch(error => {
                                vm.showMessage(
                                    'custom',
                                    'Acceso Denegado',
                                    'danger', 'screen-error',
                                    'No tiene los permisos necesarios para ejecutar esta funcionalidad'
                                )
                            });
                        }
                    }
                });
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
            format_date: function(value, format = 'DD/MM/YYYY') {
                return moment.utc(value).format(format);
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
                    vm.$refs[ref].record = vm.$refs.tableResultsTransfer.data.filter(r => {
                        return r.id === id;
                    })[0];
                }
                vm.$refs[ref].id = id;
                $(`#${modal}`).modal('show');
                let record = vm.$refs[ref].record;
                vm.loadData(record);
            },

            /**
             * Carga los datos para mostrar en la tabla al detallar la información
             *
             * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
             */
            loadData(record) {
                const vm = this;
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
                $.each(record.budget_modification_accounts, function(index, account) {
                    let item = account.budget_account.code.split('.');
                    if (item[2] != '00' && item[3] != '00') {
                        var sp = account.budget_sub_specific_formulation.specific_action;
                        var spac_desc = `${sp.specificable.code} - ${sp.code} | ${sp.name}`;
                        var acc = account.budget_account;
                        var code = `${acc.group}.${acc.item}.${acc.generic}.${acc.specific}.${acc.subspecific}`;
                        if (account.operation === "D") {
                            from_add.spac_description = spac_desc;
                            from_add.code = code;
                            from_add.description = account.budget_account.denomination;
                            from_add.amount = account.amount;
                            from_add.account_id = acc.id;
                            from_add.specific_action_id = sp.id;
                        }
                        else {
                            to_add.spac_description = spac_desc;
                            to_add.code = code;
                            to_add.description = account.budget_account.denomination;
                            to_add.amount = account.amount;
                            to_add.account_id = acc.id;
                            to_add.specific_action_id = sp.id;
                        }

                        if ((index % 2) === 1) {
                            array_accounts[i] = {
                                from_spac_description: from_add.spac_description,
                                from_code: from_add.code,
                                from_description: from_add.description,
                                from_amount: from_add.amount,
                                from_account_id: from_add.account_id,
                                from_specific_action_id: from_add.specific_action_id,
                                to_spac_description: to_add.spac_description,
                                to_code: to_add.code,
                                to_description: to_add.description,
                                to_amount: to_add.amount,
                                to_account_id: to_add.account_id,
                                to_specific_action_id: to_add.specific_action_id,
                            };
                            i++;
                        }
                    }
                });
                vm.modification_accounts = array_accounts;
            },
        },
        async mounted() {
            // Obtener los registros.
            axios.get('budget/modifications/vue-list/TR').then(response => {
                this.records = response.data.records;
                // Variable usada para el reseteo de los filtros de la tabla.
                this.tmpRecords = response.data.records;
            });

            const vm = this;
            await vm.queryLastFiscalYear();
        },
    };
</script>
