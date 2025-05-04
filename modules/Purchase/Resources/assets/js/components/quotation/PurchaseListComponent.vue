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
                    v-model="filterBy.date"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Código de cotización</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Código de cotización"
                    tabindex="2"
                    v-model="filterBy.code"
                />
            </div>
            <div class="col-md-2">
                <label class="form-label">Proveedor</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Proveedor"
                    tabindex="3"
                    v-model="filterBy.purchase_supplier"
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
                        @click="filterQuotations()"
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
            :url="'purchase/quotations/vue-list'"
            :options="table_options"
        >
            <div slot="date" slot-scope="props">
                {{ props.row.date ? format_date(props.row.date, 'DD/MM/YYYY') : "Sin fecha asignada" }}
            </div>
            <div slot="status" slot-scope="props">
                {{ translateStatus(props.row) }}
            </div>
            <div
                slot="relatable[0].purchase_requirement_item.purchase_requirement.code"
                slot-scope="props"
                target="_blank"
            >
                <span v-for="codes in getUniqueCars(props.row.relatable)" :key="codes">
                    <span>
                        {{ codes }},
                    </span>
                </span>
            </div>
            <div
                slot="purchase_supplier.purchase_supplier_object"
                slot-scope="props"
                class="text-center"
            >
                <div v-if="props.row.purchase_supplier.purchase_supplier_object">
                    <div
                        v-if="
                            props.row.purchase_supplier.
                            purchase_supplier_object.type == 'S'
                        "
                    >
                        <strong>
                            Servicios / {{ props.row.purchase_supplier.purchase_supplier_object.name }}
                        </strong>
                    </div>
                    <div
                        v-else-if="
                            props.row.purchase_supplier.
                            purchase_supplier_object.type == 'O'
                        "
                    >
                        <strong>
                            Obras / {{ props.row.purchase_supplier.purchase_supplier_object.name }}
                        </strong>
                    </div>
                    <div
                        v-else-if="
                            props.row.purchase_supplier.
                            purchase_supplier_object.type == 'B'
                        "
                        >
                        <strong>
                            Bienes / {{ props.row.purchase_supplier.purchase_supplier_object.name }}
                        </strong>
                    </div>
                </div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <template v-if="(lastYear && format_date(props.row.date, 'YYYY') <= lastYear)">
                        <button class="btn btn-success btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            v-show="props.row.status != 'APPROVED'"
                            class="btn btn-success btn-xs btn-icon btn-action"
                            type="button"
                            data-toggle="tooltip"
                            title="Aprobar"
                            @click="approveQuotation(props.row.id)"
                        >
                            <i class="fa fa-check"></i>
                        </button>
                    </template>
                    <purchase-quotation-show :id="props.row.id" />
                    <a
                        class="btn btn-primary btn-xs btn-icon"
                        :href="purchase_quotation_pdf + props.row.id"
                        title="Imprimir registro"
                        data-toggle="tooltip"
                        v-has-tooltip target="_blank"
                    >
                        <i class="fa fa-print"></i>
                    </a>
                    <template v-if="(lastYear && format_date(props.row.date, 'YYYY') <= lastYear)">
                        <button class="btn btn-warning btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btn-icon btn-action" type="button" disabled>
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            v-show="props.row.status_purchase_order != 'APPROVED'"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            data-toggle="tooltip"
                            v-has-tooltip
                            title="Modificar registro"
                            @click="editForm(props.row.id)"
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                        <button
                            v-show="props.row.status_purchase_order != 'APPROVED'"
                            class="btn btn-danger btn-xs btn-icon btn-action"
                            data-toggle="tooltip"
                            title="Eliminar registro"
                            v-has-tooltip
                            @click="deleteRecord(props.row.id, '/purchase/quotation')"
                        >
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </template>
                </div>
            </div>
        </v-server-table>
    </section>
</template>
<script>
export default {
    props: {
        record_lists: {
            type: Array,
            default: function() {
                return [];
            }
        },
        employments:{
            type: Array,
            default: function() {
                return [];
            }
        },
        has_budget: {
            type: Boolean,
            default: function() {
                return false;
            }
        }
    },
    data() {
        return {
            records: [],
            lastYear: "",
            tmpRecords: [],
            columns: [
                'date',
                'code',
                'relatable[0].purchase_requirement_item.purchase_requirement.code',
                'purchase_supplier.name',
                'status',
                'id'
            ],
            purchase_quotation_pdf: `${window.app_url}/purchase/quotation/pdf/`,
            filterBy: {
                date: '',
                code: '',
                purchase_supplier: '',
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
                date: '',
                code: '',
                purchase_supplier: '',
            };
            vm.records = vm.tmpRecords;
        },

        /**
         * Método que permite filtrar los datos de la tabla.
         *
         * @method filterQuotations
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        filterQuotations() {
            const vm = this;
            vm.records = vm.tmpRecords.filter((rec) => {
                return (vm.filterBy.code) ? (rec.code === vm.filterBy.code) : true;
            }).filter((rec) => {
                return (vm.filterBy.date) ? (rec.date === vm.filterBy.date) : true;
            }).filter((rec) => {
                return (vm.filterBy.purchase_supplier)
                    ? (rec.purchase_supplier.name
                    === vm.filterBy.purchase_supplier) : true;
            })
        },

        getUniqueCars(id) {
            return id.filter(
                x => x.purchase_requirement_item!==null &&
                x.purchase_requirement_item.purchase_requirement !== null)
                .map(x => x.purchase_requirement_item.purchase_requirement.code)
                .filter((v,i,s) => s.indexOf(v) === i
            )
        },

        avalibleForm(id) {
            var rute = "/purchase/budgetary_availability/{id}";
            location.href = (rute.indexOf("{id}") >= 0)
                ? rute.replace("{id}", id)
                : rute + '/' + id;
        },

        /**
         * Método para traducir el estado de la cotización de lo que está en la
         * base de datos a algo lenguaje humano.
         *
         * @method translateStatus
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        translateStatus(quotation) {
            if (quotation.status == "WAIT") { return "En espera" }
            if (quotation.status == "QUOTED") { return "Cotizado" }
            if (quotation.status == "APPROVED") {
                if(quotation.status_purchase_order == 'APPROVED'){
                    return "Aprobado"
                } else if(!quotation.status_purchase_order){
                    return "Espera por crear orden de compra o servicio"
                } else return "Espera por aprobar orden de compra o servicio"
            }
            return false;
        },

        /**
         * Método para aprobar una cotización
         *
         * @method approveQuotation
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         */
        approveQuotation(id) {
            const vm = this;
            const url = vm.setUrl('purchase/change-quotation-status');
            bootbox.confirm({
                title: "Aprobar registro",
                message: `
                    ¿Está seguro? Una vez aprobado el registro no se podrá
                    modificar y/o eliminar el Requerimiento ni el Presupuesto base.
                `,
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
                                vm.showMessage(
                                    'custom',
                                    '¡Éxito!',
                                    'success',
                                    'screen-ok',
                                    'Cotización aprobada con éxito'
                                );
                                location.reload();
                            }
                        }).catch(error => {
                            console.error(error);
                        });
                        vm.loading = false;
                    }
                }
            });
        },
    },
    created() {
        this.table_options.headings = {
            'date': 'Fecha de generación',
            'code': 'Código de Cotización',
            'relatable[0].purchase_requirement_item.purchase_requirement.code': "Código del requerimiento",
            'purchase_supplier.name': 'Proveedor',
            'status': 'Estado',
            'id': 'ACCIÓN'
        };
        this.table_options.columnsClasses = {
            'date': 'col-2 text-center',
            'code': 'col-2 text-center',
            'relatable[0].purchase_requirement_item.purchase_requirement.code':'col-2 text-center',
            'purchase_supplier.name': 'col-2',
            'status': 'col-2 text-center',
            'id': 'col-2'
        };
        this.table_options.sortable = ['purchase_supplier.name'];
        this.table_options.filterable = ['purchase_supplier.name'];
    },
    async mounted () {
        const vm = this;
        await vm.queryLastFiscalYear();
        this.records = this.record_lists;
        // Variable usada para el reseteo de los filtros de la tabla.
        this.tmpRecords = this.records;
    },
};
</script>
