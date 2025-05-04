<template>
    <section>
        <div class="form-group form-inline pull-left VueTables__search-2">
            <div class="VueTables__search-field">
                <label class=""> Buscar: </label>
                <input
                    type="text"
                    class="form-control"
                    placeholder="Buscar..."
                    v-model="search"
                />
            </div>
        </div>
        <div class="form-group form-inline pull-right VueTables__limit-2">
            <div class="VueTables__limit-field">
                <label class="">Registros</label>
                <select2 :options="perPageValues" v-model="perPage"> </select2>
            </div>
        </div>
        <v-client-table
            :columns="columns"
            :data="records"
            :options="table_options"
            ref="tableResults"
        >
            <div slot="from_date" slot-scope="props" class="text-center">
                {{ formatDate(props.row.from_date) }}
            </div>
            <div slot="reference" slot-scope="props" class="text-center">
                {{ props.row.reference }}
            </div>
            <div slot="total" slot-scope="props" class="text-right">
                <strong>Debe: </strong>
                {{ props.row.currency.symbol }}
                {{
                    parseFloat(props.row.tot_debit).toFixed(
                        props.row.currency.decimal_places
                    )
                }}
                <br />
                <strong>Haber</strong>
                {{ props.row.currency.symbol }}
                {{
                    parseFloat(props.row.tot_assets).toFixed(
                        props.row.currency.decimal_places
                    )
                }}
            </div>
            <div slot="approved" slot-scope="props" class="text-center">
                <span v-if="props.row.approved" class="badge badge-success"
                    ><strong>Aprobado</strong></span
                >
                <span v-else class="badge badge-danger"
                    ><strong>No Aprobado</strong></span
                >
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <button
                        v-if="
                            lastYear &&
                            format_date(props.row.from_date, 'YYYY') <= lastYear
                        "
                        class="btn btn-success btn-xs btn-icon btn-action"
                        type="button"
                        disabled
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <button
                        v-else
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar Registro"
                        data-toggle="tooltip"
                        :disabled="props.row.approved"
                        v-has-tooltip
                        @click="!props.row.approved ? approve(props.index) : ''"
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <!-- Generar Reverso -->
                    <accounting-entry-reverse
                        v-if="
                            props.row.approved &&
                            props.row.pivot_entryable_count == 0 &&
                            !props.row.reversed &&
                            !props.row.reversed_id
                        "
                        :id="props.row.id"
                        :recordsAccounting="props.row"
                        :reversePermission="true"
                        :fiscal_year="lastYear"
                    />
                    <button
                        v-else
                        disabled
                        class="btn btn-secondary btn-xs btn-icon btn-action"
                        title="Generar Reverso de asiento"
                        data-toggle="tooltip"
                        v-has-tooltip
                    >
                        <i class="fa fa-exchange"></i>
                    </button>
                    <accounting-entry-show
                        modal_name="show_accounting_approved_"
                        :id="props.row.id"
                        :route_show="
                            app_url + '/accounting/entries/' + props.row.id
                        "
                    />
                    <template
                        v-if="
                            lastYear &&
                            format_date(props.row.from_date, 'YYYY') <= lastYear
                        "
                    >
                        <button
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            type="button"
                            disabled
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            type="button"
                            disabled
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            data-toggle="tooltip"
                            title="Modificar registro"
                            v-has-tooltip
                            @click="
                                !props.row.approved
                                    ? editForm(props.row.id)
                                    : ''
                            "
                            :disabled="
                                props.row.approved ||
                                props.row.pivot_entryable_count > 0
                            "
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            data-toggle="tooltip"
                            title="Eliminar Registro"
                            v-has-tooltip
                            @click="
                                !props.row.approved
                                    ? deleteRecord(
                                          props.row.id,
                                          '/accounting/entries'
                                      )
                                    : ''
                            "
                            :disabled="
                                props.row.approved ||
                                props.row.pivot_entryable_count > 0
                            "
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <a
                        class="btn btn-primary btn-xs btn-icon"
                        data-toggle="tooltip"
                        title="Imprimir Registro"
                        v-has-tooltip
                        :disabled="!props.row.approved"
                        :href="
                            props.row.approved
                                ? urlPdf + '/pdf/' + props.row.id
                                : '#'
                        "
                        :target="props.row.approved ? '_blank' : ''"
                    >
                        <i class="fa fa-print"></i>
                    </a>
                </div>
            </div>
        </v-client-table>
        <div class="VuePagination-2 row col-md-12">
            <nav class="text-center">
                <ul class="pagination VuePagination__pagination" style="">
                    <li
                        class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk"
                        v-if="page != 1"
                    >
                        <a class="page-link" @click="changePage(1)">PRIMERO</a>
                    </li>
                    <li
                        class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk disabled"
                    >
                        <a class="page-link">&lt;&lt;</a>
                    </li>
                    <li
                        v-if="page > 1"
                        class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-page"
                    >
                        <a class="page-link" @click="changePage(page - 1)"
                            >&lt;</a
                        >
                    </li>
                    <li
                        v-for="(number, index) in pageValues"
                        :class="
                            page == number
                                ? 'VuePagination__pagination-item page-item active'
                                : 'VuePagination__pagination-item page-item'
                        "
                        :key="index"
                    >
                        <a
                            v-if="number <= lastPage"
                            class="page-link active"
                            role="button"
                            @click="changePage(number)"
                        >
                            {{ number }}
                        </a>
                    </li>
                    <li
                        class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-page"
                        v-if="page < lastPage"
                    >
                        <a class="page-link" @click="changePage(page + 1)"
                            >&gt;</a
                        >
                    </li>
                    <li
                        class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-chunk disabled"
                    >
                        <a class="page-link">&gt;&gt;</a>
                    </li>
                    <li
                        class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk"
                        v-if="lastPage && lastPage != page"
                    >
                        <a class="page-link" @click="changePage(lastPage)"
                            >ÚLTIMO</a
                        >
                    </li>
                </ul>
                <p
                    class="VuePagination__count text-center col-md-12"
                    style=""
                ></p>
            </nav>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        entries: {
            type: Array,
            default() {
                return [];
            },
        },
    },
    data() {
        return {
            url: "/accounting/entries/Filter-Records",
            lastYear: "",
            dataForm: {},
            records: [],
            search: "",
            page: 1,
            total: "",
            perPage: 10,
            lastPage: "",
            pageValues: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            perPageValues: [
                {
                    id: 10,
                    text: "10",
                },
                {
                    id: 25,
                    text: "25",
                },
                {
                    id: 50,
                    text: "50",
                },
            ],

            urlPdf: `${window.app_url}/accounting/entries`,
            columns: [
                "from_date",
                "reference",
                "concept",
                "total",
                "approved",
                "id",
            ],
        };
    },
    watch: {
        perPage(res) {
            $(".form-group.form-inline.pull-right.VueTables__limit").remove();
            if (this.page == 1) {
                this.initRecords(this.url + "/" + res, "");
            } else {
                this.changePage(1);
            }
        },
        page(res) {
            this.initRecords(this.url + "/" + this.perPage + "/" + res, "");
        },
        search(res) {
            this.changePage(1);
            this.initRecords(this.url);
        },
    },
    methods: {
        /**
         * Cambia la pagina actual de la tabla
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param integer $page Número de pagina actual
         */
        changePage(page) {
            const vm = this;
            vm.page = page;
            var pag = 0;
            while (1) {
                if (pag + 10 >= vm.page) {
                    pag += 1;
                    break;
                } else {
                    pag += 10;
                }
            }
            vm.pageValues = [];
            for (var i = 0; i < 10; i++) {
                vm.pageValues.push(pag + i);
            }
        },

        /**
         * Reescribe el método initRecords para cambiar su comportamiento por defecto y realiza la consulta
         * en base a la informacion del formulario
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param string url      Ruta que obtiene los datos a ser mostrado en listados
         */
        initRecords(url) {
            const vm = this;
            vm.dataForm["search"] = vm.search;
            axios
                .post(url, vm.dataForm)
                .then((response) => {
                    if (response.data.records.length == 0) {
                        vm.showMessage(
                            "custom",
                            "Error",
                            "danger",
                            "screen-error",
                            "No se encontraron asientos contables aprobados con los parámetros de busqueda dados."
                        );
                    } else {
                        if (vm.dataForm["firstSearch"]) {
                            vm.showMessage(
                                "custom",
                                "Éxito",
                                "success",
                                "screen-ok",
                                "Busqueda realizada de manera exitosa."
                            );
                            vm.dataForm["firstSearch"] = false;
                        }
                    }
                    vm.records = response.data.records;
                    vm.total = response.data.total;
                    vm.lastPage = response.data.lastPage;
                    vm.$refs.tableResults.setLimit(vm.perPage);
                })
                .catch((error) => {
                    if (typeof error.response !== "undefined") {
                        if (error.response.status == 403) {
                            vm.showMessage(
                                "custom",
                                "Acceso Denegado",
                                "danger",
                                "screen-error",
                                error.response.data.message
                            );
                        } else {
                            vm.logs(
                                "resources/js/all.js",
                                343,
                                error,
                                "initRecords"
                            );
                        }
                    }
                });
        },

        /**
         * Genera reverso de asiento contable
         *
         * @author Juan Rosas <juan.rosasr01@gmail.com>
         */
        reverse(index) {
            var url = `${window.app_url}/accounting/entries/reverse`;
            var records = this.records;
            var confirmated = false;
            index = index - 1;
            const vm = this;

            if (records[index].reversed) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "Los asientos solo pueden ser revertidos un vez."
                );

                return;
            } else if (records[index].reversed_id) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "No se permite generar un asiento reverso de un otro asiento reverso."
                );

                return;
            } else if (!records[index].approved) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "Solo se puede generar un reverso de un asiento contable aprobado."
                );

                return;
            } else if (records[index].pivot_entryable_count != 0) {
                vm.showMessage(
                    "custom",
                    "Acceso Denegado",
                    "danger",
                    "screen-error",
                    "Solo se pueden generar reversos de asientos contables generados manualmente."
                );

                return;
            }

            bootbox.confirm({
                title: "Reverso de asiento contable",
                message: "¿Está seguro de generar reverso de este asiento?",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                        className: "btn btn-default btn-sm btn-round",
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                        className: "btn btn-primary btn-sm btn-round",
                    },
                },
                callback: function (result) {
                    if (result) {
                        confirmated = true;
                        vm.loading = true;

                        axios
                            .post(`${url}/${records[index].id}`)
                            .then((response) => {
                                if (
                                    typeof response.data.error !== "undefined"
                                ) {
                                    /** Muestra un mensaje de error si sucede algún evento en la eliminación */
                                    vm.showMessage(
                                        "custom",
                                        "Alerta!",
                                        "danger",
                                        "screen-error",
                                        response.data.message
                                    );
                                    return false;
                                }

                                EventBus.$emit("entry:index.searchRecords");
                                vm.showMessage("update");
                                vm.reload = true;

                                if (
                                    typeof response.data.redirect !==
                                    "undefined"
                                ) {
                                    location.href = response.data.redirect;
                                }

                                vm.loading = false;
                            })
                            .catch((error) => {
                                if (typeof error.response != "undefined") {
                                    if (error.response.status == 403) {
                                        vm.showMessage(
                                            "custom",
                                            "Acceso Denegado",
                                            "danger",
                                            "screen-error",
                                            error.response.data.message
                                        );
                                    }
                                    if (error.response.status == 500) {
                                        const messages =
                                            error.response.data.message;

                                        vm.showMessage(
                                            messages.type,
                                            messages.title,
                                            messages.class,
                                            messages.icon,
                                            messages.text
                                        );
                                    }
                                }
                                vm.loading = false;
                                console.log(error);
                            });
                    }
                },
            });

            if (confirmated) {
                vm.records = records;
                vm.reload = true;
            }
        },
    },
    created() {
        this.table_options.headings = {
            from_date: "FECHA",
            reference: "REFERENCIA",
            concept: "CONCEPTO",
            total: "TOTAL",
            approved: "ESTATUS",
            id: "ACCIÓN",
        };

        this.table_options.sortable = [
            "from_date",
            "reference",
            "concept",
            "total",
            "approved",
        ];

        this.table_options.filterable = [
            "from_date",
            "reference",
            "concept",
            "total",
            "approved",
        ];

        this.table_options.columnsClasses = {
            from_date: "col-xs-1",
            reference: "col-xs-2",
            denomination: "col-xs-4",
            total: "col-xs-2",
            approved: "col-xs-1",
            id: "col-xs-2",
        };

        this.table_options.filterable = [];

        if (this.entries) {
            this.records = this.entries;
        }

        EventBus.$on("reload:listing", (data) => {
            this.records = data;
        });

        EventBus.$on("list:entries", (data) => {
            this.search = "";
            this.dataForm = data;
            this.initRecords(this.url);
        });

        EventBus.$emit("entry:index.searchRecords");
    },
    async mounted() {
        const vm = this;
        await vm.queryLastFiscalYear();
    },
};
</script>
