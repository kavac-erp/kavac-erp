<template>
    <div>
        <button
            @click="addRecord('show_requirement_' + id, route_show, $event)"
            class="btn btn-info btn-xs btn-icon btn-action"
            title="Visualizar registro"
            data-toggle="tooltip"
            v-has-tooltip
        >
            <i class="fa fa-eye"></i>
        </button>
        <div
            class="modal fade text-left"
            tabindex="-1"
            role="dialog"
            :id="'show_requirement_' + id"
        >
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <!-- modal-header -->
                    <div class="modal-header">
                        <button
                            type="reset"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6>
                            <i class="fa fa-list inline-block"></i>
                            INFORMACIÓN DEL REQUERIMIENTO
                        </h6>
                    </div>
                    <!-- Final modal-header -->
                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="row mt-3">
                            <div class="col-3">
                                <strong>Código del requerimiento:</strong>
                                {{ records.code }}
                            </div>
                            <div class="col-3">
                                <strong>Fecha de generación:</strong>
                                {{ format_date(records.date) }}
                            </div>
                            <div class="col-3">
                                <strong>Año Fiscal:</strong>
                                {{ fiscal_year }}
                            </div>
                            <div class="col-3">
                                <strong>Unidad contratante:</strong>
                                {{ contracting_department }}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-3">
                                <strong>Unidad usuario:</strong>
                                {{ user_department }}
                            </div>
                            <div class="col-3">
                                <strong>Tipo:</strong>
                                {{ purchase_supplier_object }}
                            </div>
                            <div class="col-3">
                                <strong>Estatus:</strong>
                                <span
                                    class="badge badge-danger"
                                    v-if="records.requirement_status == 'WAIT'"
                                >
                                    <strong>EN ESPERA</strong>
                                </span>
                                <span
                                    class="badge badge-info"
                                    v-if="
                                        records.requirement_status ==
                                            'PROCESSED'
                                    "
                                >
                                    <strong>PROCESADO</strong>
                                </span>
                                <span
                                    class="badge badge-success"
                                    v-if="
                                        records.requirement_status == 'BOUGHT'
                                    "
                                >
                                    <strong>COMPRADO</strong>
                                </span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <strong>Descripción:</strong>
                                {{ description }}
                            </div>
                        </div>
                        <h6 class="card-title text-center">
                            Listado de productos
                        </h6>
                        <!-- Lista de Productos -->
                        <v-client-table
                            class="mt-3"
                            :columns="columns"
                            :data="purchase_requirement_items"
                            :options="table_options"
                        >
                            <div
                                slot="measurement_unit.name"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{
                                    props.row.measurement_unit_id ?
                                    props.row.measurement_unit.name :
                                    'N/A'
                                }}
                            </div>
                            <div
                                slot="quantity"
                                slot-scope="props"
                                class="text-center"
                            >
                                {{ props.row.quantity }}
                            </div>
                        </v-client-table>
                        <!-- Final de Lista de Productos -->
                        <!-- Firmas autorizadas -->
                        <h6 class="card-title">Firmas autorizadas</h6>
                        <div class="row">
                            <div class="col-3">
                                <strong class="d-block">Preparado por</strong>
                                {{
                                    records.prepared_by
                                        ? records.prepared_by.payroll_staff
                                            ? records.prepared_by.payroll_staff
                                                .first_name +
                                            " " +
                                            records.prepared_by.payroll_staff
                                                .last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Revisado por</strong>
                                {{
                                    records.reviewed_by
                                        ? records.reviewed_by.payroll_staff
                                            ? records.reviewed_by.payroll_staff
                                                .first_name +
                                            " " +
                                            records.reviewed_by.payroll_staff
                                                .last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Verificado por</strong>
                                {{
                                    records.verified_by
                                        ? records.verified_by.payroll_staff
                                            ? records.verified_by.payroll_staff
                                                .first_name +
                                            " " +
                                            records.verified_by.payroll_staff
                                                .last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Firmado por</strong>
                                {{
                                    records.first_signature
                                        ? records.first_signature.payroll_staff
                                            ? records.first_signature
                                                .payroll_staff.first_name +
                                            " " +
                                            records.first_signature
                                                .payroll_staff.last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                            <div class="col-3">
                                <strong class="d-block">Firmado por</strong>
                                {{
                                    records.second_signature
                                        ? records.second_signature.payroll_staff
                                            ? records.second_signature
                                                .payroll_staff.first_name +
                                            " " +
                                            records.second_signature
                                                .payroll_staff.last_name
                                            : "No definido"
                                        : "No definido"
                                }}
                            </div>
                        </div>
                        <!-- Fin de Firmas autorizadas -->
                    </div>
                    <!-- Final modal-body -->
                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light"
                            data-dismiss="modal"
                        >
                            Cerrar
                        </button>
                    </div>
                    <!-- Final modal-footer -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["id"],
    data() {
        return {
            records: [],
            columns: [
                "name",
                "measurement_unit.name",
                "technical_specifications",
                "quantity",
            ],
        };
    },
    created() {
        this.table_options.headings = {
            name: "Producto",
            "measurement_unit.name": "Unidad de medida",
            technical_specifications: "Especificaciones técnicas",
            quantity: "Cantidad",
        };
        this.table_options.columnsClasses = {
            name: "col-xs-4",
            "measurement_unit.name": "col-xs-2",
            technical_specifications: "col-xs-4",
            quantity: "col-xs-2",
        };
    },
    methods: {
        /**
         * Método que borra todos los datos del formulario
         *
         * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         */
        reset() {},
    },
    computed: {
        contracting_department: function() {
            if (this.records.contrating_department) {
                return this.records.contrating_department.name;
            }
        },
        user_department: function() {
            if (this.records.user_department) {
                return this.records.user_department.name;
            }
        },
        purchase_supplier_object: function() {
            if (this.records.purchase_supplier_object) {
                var type = this.records.purchase_supplier_object.type;
                if (type == "B") {
                    type = "Bienes";
                } else if (type == "O") {
                    type = "Obras";
                } else if (type == "S") {
                    type = "Servivios";
                }
                return (
                    type + " > " + this.records.purchase_supplier_object.name
                );
            }
        },
        fiscal_year: function() {
            if (this.records.fiscal_year) {
                return this.records.fiscal_year.year;
            }
        },
        description: function() {
            if (this.records.description) {
                return this.records.description;
            }
        },
        purchase_requirement_items: function() {
            if (this.records.purchase_requirement_items) {
                return this.records.purchase_requirement_items;
            }
            return [];
        },
    },
};
</script>
