<template>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mt-2 mb-2 text-center">
        <a
            class="btn-simplex btn-simplex-md btn-simplex-primary"
            href="javascript:void(0)"
            title="Registro de impuestos y tasas"
            data-toggle="tooltip"
            @click="addRecord('add_tax', 'taxes', $event)"
        >
            <i class="icofont icofont-deal ico-3x"></i>
            <span>Impuestos</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_tax">
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
                            <i class="icofont icofont-deal inline-block"></i>
                            Impuestos
                        </h6>
                    </div>
                    <div class="modal-body">
                        <form-errors :listErrors="errors"></form-errors>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Nombre:</label>
                                    <input
                                        type="text"
                                        placeholder="Impuesto"
                                        data-toggle="tooltip"
                                        title="Indique el nombre del impuesto (requerido)"
                                        class="form-control input-sm"
                                        v-model="record.name"
                                        v-is-text
                                    >
                                    <input type="hidden" v-model="record.id">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Descripción:</label>
                                    <input
                                        type="text"
                                        placeholder="Descripción"
                                        data-toggle="tooltip"
                                        title="Indique una descripción breve del impuesto (requerido)"
                                        class="form-control input-sm"
                                        v-model="record.description"
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Fecha entrada en vigencia:</label>
                                    <input
                                        type="date" placeholder="dd/mm/yyyy"
                                        data-toggle="tooltip"
                                        title="Seleccione una fecha del calendario (requerido)"
                                        class="form-control input-sm"
                                        v-model="record.operation_date"
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group is-required">
                                    <label>Porcentaje:</label>
                                    <input
                                        type="number"
                                        placeholder="0"
                                        step="0.01"
                                        data-toggle="tooltip"
                                        title="Indique el porcentaje del impuesto (requerido)"
                                        class="form-control input-sm"
                                        v-model="record.percentage"
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Afecta cuenta de IVA:</label>
                                    <div
                                        class="custom-control custom-switch"
                                        data-toggle="tooltip"
                                        title="
                                            Indique si el impuesto afecta la cuenta de IVA
                                        "
                                    >
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="taxAffect"
                                            v-model="record.affect_tax"
                                            :value="true"
                                        >
                                        <label
                                            class="custom-control-label"
                                            for="taxAffect"
                                        ></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group is-required">
                                    <label>Activo:</label>
                                    <div
                                        class="custom-control custom-switch"
                                        data-toggle="tooltip" 
                                        title="Indique si el impuesto esta o no activo"
                                    >
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="taxActive"
                                            v-model="record.active"
                                            :value="true"
                                        >
                                        <label
                                            class="custom-control-label"
                                            for="taxActive"
                                        ></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button
                                type="button"
                                class="
                                    btn btn-default btn-sm btn-round btn-modal-close
                                "
                                @click="clearFilters"
                                data-dismiss="modal"
                            >
                                Cerrar
                            </button>
                            <button
                                type="button"
                                class="
                                    btn btn-warning btn-sm btn-round btn-modal btn-modal-clear
                                "
                                @click="reset()"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="createRecord('taxes')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save"
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table
                            :columns="columns"
                            :data="records"
                            :options="table_options"
                        >
                            <div
                                slot="histories.operation_date"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span class="text-center">
                                    {{
                                        format_date(
                                            props.row.histories[0].operation_date
                                        )
                                    }}
                                </span>
                            </div>
                            <div
                                slot="histories.percentage"
                                slot-scope="props"
                                class="text-center"
                            >
                                <span class="text-center">
                                    {{ props.row.histories[0].percentage }} %
                                </span>
                            </div>
                            <div slot="active" slot-scope="props" class="text-center">
                                <span
                                    v-if="props.row.active"
                                    class="text-bold text-success"
                                >
                                    SI
                                </span>
                                <span v-else class="text-bold text-danger">NO</span>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button
                                    @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action"
                                    title="Modificar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="deleteRecord(props.row.id, 'taxes')"
                                    class="btn btn-danger btn-xs btn-icon btn-action"
                                    title="Eliminar registro"
                                    data-toggle="tooltip"
                                    type="button"
                                >
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </v-client-table>
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
                record: {
                    id: '',
                    name: '',
                    description: '',
                    affect_tax: false,
                    active: false,
                    operation_date: '',
                    percentage: '',
                },
                errors: [],
                records: [],
                columns: [
                    'name',
                    'description',
                    'histories.operation_date',
                    'histories.percentage',
                    'active', 'id',
                ],
            }
        },
        methods: {
            /**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
                    name: '',
                    description: '',
                    affect_tax: false,
                    active: false,
                    operation_date: '',
                    percentage: 0,
                };
            },

            /**
             * Obtener los datos guardados.
             */
            initUpdate(id, event) {
                let vm = this;
                vm.errors = [];

                let recordEdit = JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                    return rec.id === id;
                })[0])) || vm.reset();

                vm.record = recordEdit;
                vm.record.operation_date = recordEdit.histories[0].operation_date;
                vm.record.percentage  = recordEdit.histories[0].percentage ;

                /**
                 * Recorre todos los campos para determinar si existe un elemento
                 * booleano para, posteriormente, seleccionarlo en el formulario
                 * en el caso de que se encuentre activado en BD
                 */
                $.each(vm.record, function(el, value) {
                    if ($("input[name=" + el + "]").hasClass('bootstrap-switch')) {
                        /** verifica los elementos bootstrap-switch para
                         * seleccionar el que corresponda según los registros
                         * del sistema
                         */
                        $("input[name=" + el + "]").each(function() {
                            if ($(this).val() === value) {
                                $(this).bootstrapSwitch('state', value, true)
                            }

                        });
                    }
                    if (value === true || value === false) {
                        $("input[name=" + el + "].bootstrap-switch")
                            .bootstrapSwitch('state', value, true);
                    }
                });
                event.preventDefault();
            },
        },
        created() {
            this.table_options.headings = {
                'name': 'Nombre',
                'description': 'Descripción',
                'histories.operation_date': 'Fecha de Vigencia',
                'histories.percentage': 'Porcentage',
                'active': 'Activo',
                'id': 'Acción',
            };
            this.table_options.sortable = [
                'name',
                'description',
                'histories.operation_date',
                'histories.percentage',
            ];
            this.table_options.filterable = [
                'name',
                'description',
                'histories.operation_date',
                'histories.percentage',
            ];
            this.table_options.columnsClasses = {
                'name': 'col-md-2',
                'description': 'col-md-3',
                'histories.operation_date': 'col-md-2',
                'histories.percentage': 'col-md-2',
                'active': 'col-md-1',
                'id': 'col-md-2',
            };
        }
    };
</script>
