<template>
    <div class="text-center">
        <a class="btn-simplex btn-simplex-md btn-simplex-primary" href="" title="Registros de Bienes a Comercializar"
            data-toggle="tooltip" @click="addRecord('add_good_to_be_traded', 'sale/good_to_be_traded', $event)">
            <i class="icofont icofont-read-book ico-3x"></i>
            <span>Servicios a Comercializar</span>
        </a>
        <div class="modal fade text-left" tabindex="-1" role="dialog" id="add_good_to_be_traded">
            <div class="modal-dialog vue-crud" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h6><i class="icofont icofont-read-book ico-3x"></i> Bienes a Comercializar</h6>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="errors.length > 0">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                    @click.prevent="errors = []">
                                    <span aria-hidden="true">
                                        <i class="now-ui-icons ui-1_simple-remove"></i>
                                    </span>
                                </button>
                                <ul>
                                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Tipo de Servicio:</label>
                                    <select2 :options="sale_type_goods" v-model="record.sale_type_good_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="name">Nombre:</label>
                                    <input type="text" id="name" placeholder="Nombre" class="form-control input-sm"
                                        v-model="record.name" data-toggle="tooltip"
                                        title="Indique el nombre (requerido)">
                                    <input type="hidden" name="id" id="id" v-model="record.id">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="name">Descripción:</label>
                                    <input type="text" id="description" placeholder="Descripción"
                                        class="form-control input-sm" v-model="record.description" data-toggle="tooltip"
                                        title="descripción (requerido)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label for="price">Precio unitario:</label>
                                    <input id="price" class="form-control" type="text" data-toggle="tooltip"
                                        title="Indique el precio unitario (requerido)" tabindex="10"
                                        v-model="record.unit_price" required v-input-mask data-inputmask="
                                       'alias': 'numeric',
                                       'allowMinus': 'false'" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Moneda:</label>
                                    <select2 :options="currencies" v-model="record.currency_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Unidad de medida:</label>
                                    <select2 :options="measurement_units" v-model="record.measurement_unit_id">
                                    </select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Unidades y dependencias a cargo:</label>
                                    <select2 :options="departments" v-model="record.department_id"></select2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group is-required">
                                    <label>Lista de trabajadores:</label>
                                    <v-multiselect :options="payroll_staffs" track_by="text" :hide_selected="false"
                                        v-model="record.payroll_staffs_id">
                                    </v-multiselect>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div v-show="this.record.iva || this.record.history_tax_id" class="form-group">
                                    <label>IVA:</label>
                                    <select2 :options="taxes" v-model="record.history_tax_id"></select2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a data-toggle="tooltip" title="IVA">
                                        <label for="" class="control-label">IVA:</label>
                                        <div class="col-12">
                                            <div class="bootstrap-switch-mini">
                                                <input type="checkbox" class="form-control bootstrap-switch" name="iva"
                                                    data-on-label="Si" data-off-label="No" value="true"
                                                    v-model="record.iva">
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a data-toggle="tooltip"
                                        title="Establecer los atributos del tipo de bien para gestionar las variantes">
                                        <label for="" class="control-label">Atributos Personalizados</label>
                                        <div class="col-12">
                                            <div class="bootstrap-switch-mini">
                                                <input type="checkbox" class="form-control bootstrap-switch"
                                                    name="define_attributes" data-on-label="Si" data-off-label="No"
                                                    value="true" v-model="record.define_attributes">
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-show="this.record.define_attributes">
                            <div class="row" style="margin: 10px 0">
                                <h6 class="card-title cursor-pointer" @click="addAttribute()">
                                    Gestionar nuevo atributo <i class="fa fa-plus-circle"></i>
                                </h6>
                            </div>
                            <div class="row" style="margin: 20px 0">
                                <div class="col-6" v-for="(attribute, index) in record.sale_goods_attribute"
                                    :key="index">
                                    <div class="d-inline-flex">
                                        <div class="col-10">
                                            <div class="form-group">
                                                <input type="text" placeholder="Nombre del nuevo atributo"
                                                    data-toggle="tooltip"
                                                    title="Indique el nombre del atributo del tipo de bien que desee hacer seguimiento (opcional)"
                                                    v-model="attribute.name" class="form-control input-sm">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <button class="btn btn-sm btn-danger btn-action" type="button"
                                                    @click="removeRow(index, record.sale_goods_attribute)"
                                                    title="Eliminar este dato" data-toggle="tooltip">
                                                    <i class="fa fa-minus-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                                @click="clearFilters" data-dismiss="modal">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-warning btn-sm btn-round btn-modal btn-modal-clear"
                                @click="reset()">
                                Cancelar
                            </button>
                            <button type="button" @click="createRecord('sale/good_to_be_traded')"
                                class="btn btn-primary btn-sm btn-round btn-modal-save">
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="modal-body modal-table">
                        <v-client-table :columns="columns" :data="records" :options="table_options">
                            <div slot="sale_type_good" slot-scope="props">
                                {{ props.row.sale_type_good ? props.row.sale_type_good.name : '' }}
                            </div>
                            <div slot="currency" slot-scope="props">
                                {{ props.row.currency.symbol + ' - ' + props.row.currency.name }}
                            </div>
                            <div slot="measurement_unit" slot-scope="props">
                                {{ props.row.measurement_unit.name }}
                            </div>
                            <div slot="department" slot-scope="props">
                                {{ props.row.department.acronym + ' - ' + props.row.department.name }}
                            </div>
                            <div slot="history_tax" slot-scope="props">
                                <div v-if="props.row.history_tax">
                                    {{ props.row.history_tax.percentage }}
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div slot="payroll_staffs" slot-scope="props"
                                v-if="props.row.payroll_staffs && props.row.payroll_staffs.length > 0">
                                <ul>
                                    <li v-for="payroll_staff in props.row.payroll_staffs" :key="payroll_staff.id">
                                        {{ payroll_staff.first_name + ' ' + payroll_staff.last_name
                                                + ' - ' + payroll_staff.id_number
                                        }}
                                    </li>
                                </ul>
                            </div>
                            <div slot="attributes" slot-scope="props">
                                <div v-if="props.row.define_attributes">
                                    <div v-for="(att, index) in props.row.sale_goods_attribute" :key="index">
                                        <span>
                                            {{ att.name }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else>
                                    <span>N/A</span>
                                </div>
                            </div>
                            <div slot="id" slot-scope="props" class="text-center">
                                <button @click="initUpdate(props.row.id, $event)"
                                    class="btn btn-warning btn-xs btn-icon btn-action" title="Modificar"
                                    data-toggle="tooltip" type="button">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteRecord(props.row.id, 'sale/good_to_be_traded')"
                                    class="btn btn-danger btn-xs btn-icon btn-action" title="Eliminar"
                                    data-toggle="tooltip" type="button">
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
                unit_price: '',
                sale_type_good_id: '',
                currency_id: '',
                history_tax_id: '',
                measurement_unit_id: '',
                department_id: '',
                payroll_staff_id: '',
                iva: false,
                define_attributes: false,
                sale_goods_attribute: [],
                payroll_staffs_id: [],
                payroll_staffs: [],
            },
            currencies: [],
            taxes: [],
            measurement_units: [],
            departments: [],
            payroll_staffs: [],
            sale_type_goods: [],
            errors: [],
            records: [],
            columns: [
                'sale_type_good', 'name', 'description', 'unit_price',
                'currency', 'measurement_unit', 'department', 'payroll_staffs', 'history_tax', 'attributes', 'id'
            ],
        }
    },
    created() {
        this.table_options.headings = {
            'sale_type_good': 'Tipo de servicio',
            'name': 'Nombre',
            'description': 'Descripción',
            'unit_price': 'Precio unitario',
            'currency': 'Moneda',
            'measurement_unit': 'Unidad de medida',
            'department': 'Unidades y dependencias a cargo',
            'payroll_staffs': 'Trabajador',
            'history_tax': 'IVA',
            'attributes': 'Atributos Personalizados',
            'id': 'Acción'
        };
        this.table_options.sortable = ['sale_type_good', 'name', 'description', 'unit_price',
            'currency', 'measurement_unit', 'department', 'payroll_staffs', 'history_tax', 'attributes'];
        this.table_options.filterable = ['sale_type_good', 'name', 'description', 'unit_price',
            'currency', 'measurement_unit', 'department', 'payroll_staffs', 'history_tax', 'attributes'];
        this.getCurrencies();
        this.getTaxes();
        this.getMeasurementUnits();
        this.getDepartments();
        this.getPayrollStaffs();
        this.getSaleTypeGoods();
    },
    methods: {
        reset() {
            this.record = {
                id: '',
                name: '',
                unit_price: '',
                currency_id: '',
                history_tax_id: '',
                measurement_unit_id: '',
                department_id: '',
                payroll_staff_id: '',
                iva: false,
                define_attributes: false,
                sale_goods_attribute: [],
            };
        },

        /**
         * Método que agrega un nuevo campo de atributo al formulario
         *
         * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
         */
        addAttribute() {
            let field = { id: '', name: '', sale_goods_to_be_traded_id: '' };
            this.record.sale_goods_attribute.push(field);
        },

        getCurrencies() {
            const vm = this;
            vm.currencies = [];

            axios.get('/sale/get-currencies').then(response => {
                vm.currencies = response.data;
            });
        },

        getTaxes() {
            const vm = this;
            vm.taxes = [];

            axios.get('/sale/get-taxes').then(response => {
                vm.taxes = response.data;
            });
        },

        getMeasurementUnits() {
            const vm = this;
            vm.measurement_units = [];

            axios.get('/sale/get-measurement-units').then(response => {
                vm.measurement_units = response.data;
            });
        },

        getDepartments() {
            const vm = this;
            vm.departments = [];

            axios.get('/sale/get-departments').then(response => {
                vm.departments = response.data;
            });
        },

        getPayrollStaffs() {
            const vm = this;
            vm.payroll_staffs = [];

            axios.get('/sale/get-payroll-staffs').then(response => {
                vm.payroll_staffs = response.data;
                // se remueve la opcion seleccione...
                vm.payroll_staffs.splice(0, 1);
            });
        },

        getSaleTypeGoods() {
            const vm = this;
            vm.sale_type_goods = [];

            axios.get('/sale/get-type-goods').then(response => {
                vm.sale_type_goods = response.data.records;
            });
        },
        /**
         * Reescribe el metodo para cambiar su comportamiento por defecto
         * Método que carga el formulario con los datos a modificar
         *
         * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
         *
         * @param  {integer} index Identificador del registro a ser modificado
         * @param {object} event   Objeto que gestiona los eventos
         */
        async initUpdate(id, event) {
            let vm = this;
            vm.errors = [];

            let recordEdit = await JSON.parse(JSON.stringify(vm.records.filter((rec) => {
                return rec.id === id;
            })[0])) || vm.reset();

            vm.record = recordEdit;

            vm.record.payroll_staffs_id = [];
            vm.record.payroll_staffs.forEach(element => {
                vm.record.payroll_staffs_id.push({
                    id: element.id,
                    text: `${element.first_name} ${element.last_name} - ${element.id_number}`,
                })
            });

            event.preventDefault();
        },
    },
    mounted() {
        const vm = this;
        vm.switchHandler('define_attributes');
        vm.switchHandler('iva');
    },
};
</script>
