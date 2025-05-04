<template>
    <section>
        <!-- Filtros de la tabla -->
        <div class="row">
            <div class="col-md-1">
                <b>Filtros</b>
            </div>
            <div class="col-md-2">
                <label class="form-label">Código del requerimiento</label>
                <input
                    class="form-control"
                    type="text"
                    placeholder="Código del requerimiento"
                    tabindex="1"
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
        >
            <a
                slot="code"
                slot-scope="props"
                target="_blank"
            >
                <span>
                    {{
                        props.row.code
                    }}
                </span>
            </a>
            <a
                slot="description"
                slot-scope="props"
                target="_blank"
            >
                <span>
                    <span>
                        {{
                            props.row.description
                        }}
                    </span>
                </span>
            </a>
            <div slot="available" slot-scope="props">
                <div class="d-inline-flex">
                    <span
                        class="badge badge-success"
                        v-show="
                            props.row.available == 'AP'
                        "
                    >
                        <strong>Aprobado(a)</strong>
                    </span>
                    <span
                        class="badge badge-info"
                        v-show="
                            props.row.available == 'Disponible'
                            || props.row.available == 'available'
                        "
                    >
                        <strong>Por aprobar</strong>
                    </span>
                    <span
                        class="badge badge-danger"
                        v-show="
                            props.row.available == 'No_Disponible'
                            || props.row.available == 'not_available'
                        "
                    >
                        <strong>No Disponible</strong>
                    </span>
                    <span
                        class="badge badge-danger"
                        v-show="
                            props.row.available == ''
                            || props.row.available == 'send'
                        "
                    >
                        <strong>PENDIENTE</strong>
                    </span>
                    <span
                        class="badge badge-danger"
                        v-show="
                            props.row.available == 'AN'
                        "
                    >
                        <strong>ANULADO(a)</strong>
                    </span>
                </div>
            </div>
            <div slot="id" slot-scope="props" class="text-center">
                <div class="d-inline-flex">
                    <!-- Show modal -->
                    <shared-budgetary-availability-show
                        v-if="props.row.module == 'Purchase'"
                        :id="props.row.id"
                        :route_show="
                            app_url + '/purchase/base_budget/' + props.row.id
                        "
                    />
                    <payroll-availability-show
                        v-if="props.row.module == 'Payroll'"
                        :id="props.row.id"
                        :route_show="
                            app_url + '/payroll/registers/availability/show/' + props.row.id
                        "
                    />
                    <!-- Final de Show modal -->
                    <!-- Aprobar disponibilidad presupuestaria -->
                    <button
                        v-show="
                                props.row.available == 'Disponible'
                                || props.row.available == 'available'
                            "
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Aprobar disponibilidad presupuestaria"
                        data-toggle="tooltip"
                        type="button"
                        @click="approveBudgetAvailability(props.row)"
                    >
                        <i class="fa fa-check"></i>
                    </button>
                    <!-- Final de Aprobar disponibilidad presupuestaria -->
                    <!-- Completar solicitud -->
                    <span v-if="props.row.module == 'Purchase'">
                        <button
                            v-if="
                                props.row.available == ''
                                || props.row.available == 'send'
                            "
                            @click="avalibleForm(props.row.id, props.row.module)"
                            class="btn btn-success btn-xs btn-icon btn-action"
                            title="Completar solicitud presupuestaria"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            <i class="icofont icofont-checked"></i>
                        </button>
                    </span>
                    <button
                        v-if="
                            props.row.module == 'Payroll'
                            && (props.row.available == 'send')
                        "
                        @click="avalibleForm(props.row.id, props.row.module)"
                        class="btn btn-success btn-xs btn-icon btn-action"
                        title="Completar solicitud presupuestaria"
                        data-toggle="tooltip"
                        v-has-tooltip
                    >
                        <i class="icofont icofont-checked"></i>
                    </button>
                    <!-- Final de Completar solicitud -->
                    <!-- Editar solicitud -->
                    <span v-if="props.row.module == 'Purchase' && props.row.available == 'Disponible'">
                        <button
                            v-if="props.row.module == 'Purchase' && props.row.available"
                            @click="editForm(props.row.id)"
                            class="btn btn-warning btn-xs btn-icon btn-action"
                            title="Modificar registro"
                            data-toggle="tooltip"
                            v-has-tooltip
                        >
                            <i class="fa fa-edit"></i>
                        </button>
                    </span>
                    <!-- Final de Editar solicitud -->
                    <a
                        v-if="props.row.available"
                        class="btn btn-primary btn-xs btn-icon"
                        :href="app_url+'/budget/budgetary_availability/pdf/'+props.row.id+'/'+props.row.module"
                        title="Imprimir Registro"
                        data-toggle="tooltip"
                        v-has-tooltip
                        target="_blank"
                    >
                        <i class="fa fa-print" style="text-align: center;"></i>
                    </a>
                </div>
            </div>
        </v-client-table>
    </section>
</template>

<script>
import PayrollBudgetAvailabilityShowComponent from '../../../../modules/Payroll/Resources/assets/js/components/registers/availability/PayrollBudgetAvailabilityShowComponent.vue';

/**
 * Componente para mostrar el botón de ver en el caso de la disponibilidad de nómina
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 */
Vue.component('payroll-availability-show', PayrollBudgetAvailabilityShowComponent);

export default {
    props: {
        record_lists: {
            type: Array,
            default: function () {
                return [];
            },
        },
    },
    data() {
        return {
            records: [],
            tmpRecords: [],
            records_outstanding: [],
            table_outstanding_options: {},
            columns: [
                "code",
                "description",
                "currency_name",
                "available",
                "id",
            ],
            filterBy: {
                code: '',
            },
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
                return (vm.filterBy.code) ? (rec.code == vm.filterBy.code) : true;
            })
        },

        getUniqueCars(id) {
            return id
            .filter(
                (x) =>
                    x.purchase_requirement_item !== null &&
                    x.purchase_requirement_item.purchase_requirement !== null
            )
            .map(
                (x) => x.purchase_requirement_item.purchase_requirement.code
            )
            .filter((v, i, s) => s.indexOf(v) === i);
        },

        avalibleForm(id, module) {
            if (module == 'Payroll') {
                var rute = this.app_url + "/payroll/registers/availability/{id}";
                location.href = rute.indexOf("{id}") >= 0 ? rute.replace("{id}", id) : rute + "/" + id;
            } else {
                var rute = this.app_url + "/purchase/budgetary_availability/{id}/edit";
                location.href = rute.indexOf("{id}") >= 0 ? rute.replace("{id}", id) : rute + "/" + id;
            }
        },

        deleteRecord(id, url) {
            const vm = this;
            /** @type {string} URL que atiende la petición de eliminación del registro */
            var url = vm.setUrl(url ? url : vm.route_delete);
            bootbox.confirm({
                title: "¿Eliminar registro?",
                message: "¿Está seguro de eliminar este registro?",
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> Cancelar',
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Confirmar',
                    },
                },
                callback: function (result) {
                    if (result) {
                        /** @type {object} Objeto con los datos del registro a eliminar */
                        let recordDelete = JSON.parse(
                            JSON.stringify(
                                vm.records.filter((rec) => {
                                    return rec.id === id;
                                })[0]
                            )
                        );
                        axios
                            .delete(
                                `${url}${url.endsWith("/") ? "" : "/"}${
                                    recordDelete.id
                                }`
                            )
                            .then((response) => {
                                if (
                                    typeof response.data.error !== "undefined"
                                ) {
                                    /** Muestra un mensaje de error si sucede
                                     * algún evento en la eliminación
                                     * */
                                    vm.showMessage(
                                        "custom",
                                        "Alerta!",
                                        "warning",
                                        "screen-error",
                                        response.data.message
                                    );
                                    return false;
                                }
                                /** @type {array} Arreglo de registros filtrado
                                 * sin el elemento eliminado
                                 */
                                vm.records = JSON.parse(
                                    JSON.stringify(
                                        vm.records.filter((rec) => {
                                            return rec.id !== id;
                                        })
                                    )
                                );
                                if (
                                    typeof vm.$refs.tableResults !== "undefined"
                                ) {
                                    vm.$refs.tableResults.refresh();
                                }
                                vm.showMessage("destroy");
                                window.location.reload(true);
                            })
                            .catch((error) => {
                                vm.logs(
                                    "mixins.js",
                                    498,
                                    error,
                                    "deleteRecord"
                                );
                            });
                    }
                },
            });
        },

        /**
         * Método para aprobar una disponibilidad presupuestaria
         *
         * @method approveBudgetAvailability
         *
         * @param {object} budget_availability Disponibilidad presupuestaria
         *
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         */
         approveBudgetAvailability(budget_availability) {
            const vm = this;
            const url = vm.setUrl('/purchase/budgetary_availability/approve');
            bootbox.confirm({
                title: "Aprobar disponibilidad presupuestaria",
                message: "Código del requrimiento: " + budget_availability.code,
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> No'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> Si'
                    }
                },
                callback: function(result) {
                    if (result) {

                        bootbox.confirm({
                            title: "Aprobar disponibilidad presupuestaria",
                            message: "¿Está seguro? Una vez aprobado no se podrá modificar y/o eliminar este registro.\n\n" +
                                    "Código del requrimiento: " + budget_availability.code,
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
                                    let records = {
                                        id: budget_availability.id,
                                        status: budget_availability.available,
                                        module: budget_availability.module,
                                    }
                                    axios.post(url, records).then(response => {
                                        if (response.status == 200){
                                            vm.showMessage('custom', '¡Éxito!', 'success', 'screen-ok', 'Disponibilidad presupuestaria aprobada');
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
                    }
                }
            });
        },
    },
    created() {
        this.table_options.headings = {
            "code": "Código del requerimiento",
            "description": "Descripcion",
            "currency_name": "Moneda",
            "available": "Estatus",
            id: "ACCIÓN",
        };
        this.table_options.columnsClasses = {
            "code":
                "col-xs-3 text-center",
            "description":
                "col-xs-3",
            "currency_name": "col-xs-3 text-center",
            "available": "text-center",
            "id": "col-xs-1",
        };
        this.table_options.sortable = [
            "description",
            "currency_name",
        ];
        this.table_options.filterable = [
            "code",
            "description",
            "currency_name",
        ];
    },
    mounted() {
        this.loadingState(true); // Inicio de spinner de carga.
        this.record_lists.forEach((element) => {
            if (element.currency === null) {
            }
            else {
                this.records.push(element);
            }
        });
        // Variable usada para resetear los filtros de la tabla.
        this.tmpRecords = this.records;
        this.loadingState(); // Finaliza spinner de carga.
    },
};
</script>
